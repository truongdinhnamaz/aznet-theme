#!/usr/bin/env python3
"""Run the existing Wellness QA against disposable native PHP/MySQL binaries.

No downloads, system services, production connections or Theme modifications.
Dependencies and the official exact WordPress ZIP are supplied explicitly.
"""
import argparse
import hashlib
import http.client
import json
import os
from pathlib import Path, PurePosixPath
import re
import subprocess
import tempfile
import time
import uuid
import zipfile


def digest(path):
    return hashlib.sha256(Path(path).read_bytes()).hexdigest()


def production_inventory(repo, source_sha):
    """Keep approved auxiliary files and discover added Theme files at the pinned commit."""
    if not re.fullmatch(r"[0-9a-f]{40}", source_sha):
        raise ValueError("An exact 40-character source commit is required")
    baseline = json.loads(subprocess.check_output([
        "git", "show", source_sha + ":docs/evidence/wellness01/2026-10-29-v06-source-manifest.json"
    ], cwd=repo))["production_files"]
    tracked = set(subprocess.check_output([
        "git", "ls-tree", "-r", "--name-only", source_sha
    ], cwd=repo, text=True).splitlines())
    # Reuse the pinned release packager's exclusion policy. An independent
    # directory allowlist would omit future production folders all over again.
    policy_source = subprocess.check_output([
        "git", "show", source_sha + ":scripts/build-release-package.py"
    ], cwd=repo)
    policy = {"__name__": "wellness_pinned_package_policy"}
    exec(compile(policy_source, "pinned-build-release-package.py", "exec"), policy)
    if not callable(policy.get("excluded")):
        raise RuntimeError("Pinned package inclusion policy is unavailable")
    packaged = {path for path in tracked if not policy["excluded"](PurePosixPath(path))}
    return sorted((set(baseline) | packaged) & tracked)


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    for name in ["php", "mysql-base", "wp-cli", "wordpress-zip", "output", "work-root", "source-sha"]:
        parser.add_argument("--" + name, required=True)
    parser.add_argument("--http-port", type=int, default=9430)
    parser.add_argument("--db-port", type=int, default=9431)
    parser.add_argument("--browser-candidate-sha256", help="Explicit hash of a test-only browser candidate; production and imported probe stay exact")
    args = parser.parse_args()
    repo = Path(__file__).resolve().parents[2]
    php, mysql_base = Path(args.php).resolve(), Path(args.mysql_base).resolve()
    phar, wp_zip = Path(args.wp_cli).resolve(), Path(args.wordpress_zip).resolve()
    out, work_root = Path(args.output).resolve(), Path(args.work_root).resolve()
    if not re.fullmatch(r"[0-9a-f]{40}", args.source_sha):
        raise ValueError("Provide an exact 40-character source commit")
    if php.read_bytes()[:4] != b"\x7fELF":
        raise ValueError("PHP must be a native ELF executable, not a WASM wrapper")
    out.mkdir(parents=True, exist_ok=False)
    work_root.mkdir(parents=True, exist_ok=True)
    work = Path(tempfile.mkdtemp(prefix="wellness-native-", dir=work_root))
    run_token = uuid.uuid4().hex
    base = f"http://127.0.0.1:{args.http_port}"
    env = {**os.environ, "GITHUB_WORKSPACE": str(repo), "WP_CLI_CONFIG_PATH": os.devnull,
           "WP_CLI_CACHE_DIR": str(work / "wp-cli-cache"),
           "PATH": str(mysql_base / "bin") + os.pathsep + str(php.parent) + os.pathsep + os.environ["PATH"]}
    mysql = [str(mysql_base / "bin/mysql"), "--no-defaults", "--protocol=TCP", "--host=127.0.0.1",
             "--port=" + str(args.db_port), "--user=root"]
    database = php_server = None
    db_log = php_log = None

    def run(command, *, input=None):
        result = subprocess.run(command, cwd=repo, env=env, input=input, capture_output=True, text=True, timeout=120)
        if result.returncode:
            raise RuntimeError(f"Command failed ({Path(command[0]).name}): {result.stderr[-3000:]} {result.stdout[-1000:]}")
        return result.stdout.strip()

    def git_bytes(path):
        return subprocess.check_output(["git", "show", args.source_sha + ":" + path], cwd=repo)

    try:
        # Derive current Theme paths as well as hashes from the selected commit;
        # a historical inventory alone can silently omit newly added assets.
        inventory = production_inventory(repo, args.source_sha)
        manifest = {"production_files": {path: hashlib.sha256(git_bytes(path)).hexdigest() for path in inventory}}
        browser_script = "tests/browser/wellness01-native-l4.mjs"
        source_browser_hash = hashlib.sha256(git_bytes(browser_script)).hexdigest()
        expected_browser_hash = args.browser_candidate_sha256 or source_browser_hash
        if not re.fullmatch(r"[0-9a-f]{64}", expected_browser_hash) or digest(repo / browser_script) != expected_browser_hash:
            raise RuntimeError("Browser script differs from the chosen source commit")
        probe = "tests/browser/wellness01-layout-probe.mjs"
        if (repo / probe).read_bytes() != git_bytes(probe):
            raise RuntimeError("Imported layout probe differs from chosen source commit")
        fixture = "tests/fixtures/wellness01-button-colors.html"
        if (repo / fixture).read_bytes() != git_bytes(fixture):
            raise RuntimeError("Button fixture differs from the chosen source commit")
        # Fence shared offline helpers/fixtures as well as each contract itself.
        test_inputs = subprocess.check_output(["git", "ls-tree", "-r", "--name-only", args.source_sha,
                                              "tests/offline", "tests/fixtures"], cwd=repo, text=True).splitlines()
        for path in test_inputs:
            if (repo / path).read_bytes() != git_bytes(path):
                raise RuntimeError("Offline helper/fixture differs from chosen commit: " + path)
        with zipfile.ZipFile(wp_zip) as archive:
            for item in archive.infolist():
                target = (work / item.filename).resolve()
                if not target.is_relative_to(work):
                    raise RuntimeError("Unsafe WordPress archive path")
            archive.extractall(work)
        wp = work / "wordpress"
        theme = wp / "wp-content/themes/aznet-theme"
        for path, expected in manifest["production_files"].items():
            content = git_bytes(path)
            if hashlib.sha256(content).hexdigest() != expected:
                raise RuntimeError("Production fingerprint mismatch: " + path)
            if (repo / path).read_bytes() != content:
                raise RuntimeError("Working production input differs from chosen commit: " + path)
            target = theme / path
            target.parent.mkdir(parents=True, exist_ok=True)
            target.write_bytes(content)

        # Native equivalents of the pattern workflow plus retained affected checks.
        previous = json.loads(git_bytes("docs/evidence/wellness01/2026-10-29-v06-php81-regression.json"))
        contracts = sorted({row["file"] for row in previous} | {
            str(path.relative_to(repo)) for path in (repo / "tests/offline").glob("r2-*.php")})
        results = []
        for path in contracts:
            if (repo / path).read_bytes() != git_bytes(path):
                raise RuntimeError("Contract differs from chosen commit: " + path)
            result = run([str(php), "-d", "zend.assertions=1", "-d", "assert.exception=1", path])
            if "PASS:" not in result or re.search(r"FAIL:|Fatal error|Parse error|AssertionError", result):
                raise RuntimeError("Contract did not explicitly pass: " + path + " " + result)
            results.append({"file": path, "output": result, "pass": True})
        lint = [path for path in manifest["production_files"] if path.endswith(".php")]
        for path in lint:
            result = run([str(php), "-l", str(theme / path)])
            if "No syntax errors detected" not in result:
                raise RuntimeError("Native syntax check failed: " + path)
        (out / "native-static.json").write_text(json.dumps({"contracts": results, "lint_files": len(lint)}, indent=2) + "\n")
        print(f"Native PHP static PASS: {len(results)} contracts, {len(lint)} production files", flush=True)

        data = work / "mysql-data"
        common = [str(mysql_base / "bin/mysqld"), "--no-defaults", "--basedir=" + str(mysql_base),
                  "--datadir=" + str(data), "--innodb-use-native-aio=OFF"]
        if os.geteuid() == 0:
            common.append("--user=root")
        run([*common, "--initialize-insecure"])
        db_log = (out / "mysql.log").open("w")
        # Upstream MySQL skips its Unix listener for an empty socket name.
        # Use only the allowed loopback TCP transport; no host policy changes.
        database = subprocess.Popen([*common, "--bind-address=127.0.0.1", "--port=" + str(args.db_port),
                                     "--socket=", "--pid-file=" + str(work / "mysqld.pid"), "--mysqlx=OFF"],
                                    env=env, stdout=db_log, stderr=subprocess.STDOUT)
        for _ in range(60):
            if database.poll() is not None:
                raise RuntimeError("Disposable MySQL exited during startup")
            result = subprocess.run([*mysql, "--batch", "--skip-column-names", "-e", "SELECT @@datadir, VERSION()"],
                                    env=env, capture_output=True, text=True, timeout=3)
            if result.returncode == 0:
                break
            time.sleep(0.5)
        else:
            raise RuntimeError("Disposable MySQL did not become ready")
        fields = result.stdout.strip().split("\t")
        if len(fields) != 2 or Path(fields[0]).resolve() != data.resolve():
            raise RuntimeError("Refusing SQL mutation: MySQL port does not belong to this disposable datadir")
        db_version = fields[1]
        if not db_version.startswith("8.0."):
            raise RuntimeError("Expected MySQL 8.0, got " + db_version)
        run([*mysql, "-e", "CREATE DATABASE wellness_native CHARACTER SET utf8mb4; ALTER USER 'root'@'localhost' IDENTIFIED BY 'wellness01-local-db-password';"])
        cli = [str(php), str(phar), "--path=" + str(wp), "--allow-root"]
        run([*cli, "config", "create", "--dbname=wellness_native", "--dbuser=root", "--dbpass=wellness01-local-db-password",
             f"--dbhost=127.0.0.1:{args.db_port}", "--skip-check"])
        run([*cli, "config", "set", "WP_DEBUG", "true", "--raw"])
        run([*cli, "core", "install", "--url=" + base, "--title=Wellness 01 Native QA", "--admin_user=admin",
             "--admin_password=wellness01-local-ci-password", "--admin_email=wellness-ci@example.test", "--skip-email"])
        run([*cli, "theme", "activate", "aznet-theme"])
        bootstrap = work / "setup.php"
        blueprint = json.loads(git_bytes("docs/evidence/wellness01/2026-10-29-v06-playground-blueprint.json"))
        setup = blueprint["steps"][1]["code"]
        setup = setup.replace("require_once '/wordpress/wp-load.php';", "")
        setup = setup.replace("/qa-evidence", str(out)).replace("/wordpress/wp-content/themes/aznet-theme/tests/fixtures/wellness01-button-colors.html", str(repo / fixture))
        setup = setup.replace("/wordpress/wp-content/themes/aznet-theme", str(theme))
        setup = setup.replace("Playground SQLite infrastructure adapter; not MySQL CI", "Native MySQL " + db_version)
        setup = setup.replace("b77290562a97c1b1a325fba60aa21a47681cbb54", args.source_sha)
        bootstrap.write_text(setup)
        run([*cli, "eval-file", str(bootstrap)])
        runtime = json.loads((out / "runtime.json").read_text())
        if runtime["wordpress"] != "6.9" or not runtime["php"].startswith("8.1."):
            raise RuntimeError("Runtime does not match WordPress 6.9/PHP 8.1")
        if runtime["active_plugins"] or (wp / "wp-content/db.php").exists():
            raise RuntimeError("Expected plugin-free WordPress with no database drop-in")
        (wp / "qa-native-state.php").write_text("""<?php
require_once __DIR__.'/wp-load.php';
header('Content-Type: application/json');
echo wp_json_encode(['run_token'=>'__WELLNESS_NATIVE_TOKEN__','wordpress'=>get_bloginfo('version'),'php'=>PHP_VERSION,'sapi'=>php_sapi_name(),
'mysql'=>$wpdb->get_var('SELECT VERSION()'),'mysqli'=>extension_loaded('mysqli'),'client'=>mysqli_get_client_info(),
'db_dropin'=>file_exists(WP_CONTENT_DIR.'/db.php'),'active_plugins'=>get_option('active_plugins',[]),
'preset'=>\\AZnet\\Theme\\homepage_preset(),'stylesheet'=>get_stylesheet(),'front'=>get_option('page_on_front'),
'show'=>get_option('show_on_front'),'blog_public'=>get_option('blog_public')]);
""".replace('__WELLNESS_NATIVE_TOKEN__', run_token))
        php_log = (out / "php-server.log").open("w")
        php_server = subprocess.Popen([str(php), "-d", "display_errors=1", "-S", f"127.0.0.1:{args.http_port}", "-t", str(wp)],
                                env=env, stdout=php_log, stderr=subprocess.STDOUT)
        for _ in range(60):
            if php_server.poll() is not None:
                raise RuntimeError("Disposable PHP server exited during startup")
            try:
                connection = http.client.HTTPConnection("127.0.0.1", args.http_port, timeout=3)
                connection.request("GET", "/qa-native-state.php")
                response = connection.getresponse()
                state = json.loads(response.read())
                connection.close()
                if response.status == 200:
                    if state.get("run_token") != run_token or php_server.poll() is not None:
                        raise RuntimeError("HTTP port does not belong to this disposable PHP server")
                    break
            except (OSError, http.client.HTTPException, json.JSONDecodeError):
                time.sleep(0.5)
        else:
            raise RuntimeError("Native HTTP runtime did not become ready")
        if state["sapi"] != "cli-server" or state["preset"] != "wellness-01" or state["mysql"] != db_version or state["db_dropin"]:
            raise RuntimeError("Native runtime diagnostic failed: " + json.dumps(state))
        (out / "request-state.json").write_text(json.dumps(state, indent=2) + "\n")
        provenance = {"source_sha": args.source_sha, "production_files": manifest["production_files"],
                      "browser_script_sha256": digest(repo / browser_script), "runner_sha256": digest(__file__),
                      "source_runner_sha256": hashlib.sha256(git_bytes("tests/browser/wellness01-native-local.py")).hexdigest(),
                      "source_browser_script_sha256": source_browser_hash, "test_candidate": bool(args.browser_candidate_sha256),
                      "layout_probe_sha256": digest(repo / probe), "mysql_datadir_fenced": True,
                      "http_run_token_fenced": True, "work_directory": str(work),
                      "php_binary_sha256": digest(php), "php_native_elf": True,
                      "wp_zip_sha256": digest(wp_zip), "wp_cli_sha256": digest(phar),
                      "php_version": run([str(php), "-v"]), "php_modules": run([str(php), "-m"]),
                      "wp_cli_version": run([*cli, "cli", "version"]), "mysql_version": db_version}
        provenance["mysql_binary_sha256"] = digest(mysql_base / "bin/mysqld")
        provenance["browser_tools"] = json.loads(run(["node", "--input-type=module", "-e", """
import {chromium} from 'playwright';
import {createRequire} from 'node:module';
import fs from 'node:fs';
import path from 'node:path';
const require = createRequire(import.meta.url);
const version = name => {
  let directory = path.dirname(require.resolve(name));
  while (!fs.existsSync(path.join(directory, 'package.json'))) {
    const parent = path.dirname(directory);
    if (parent === directory) throw new Error('Missing package metadata: ' + name);
    directory = parent;
  }
  return JSON.parse(fs.readFileSync(path.join(directory, 'package.json'), 'utf8')).version;
};
console.log(JSON.stringify({node:process.version,playwright:version('playwright'),
  axe:version('@axe-core/playwright'),chromium:chromium.executablePath()}));
"""]))
        provenance["browser_tools"]["chromium_version"] = run([provenance["browser_tools"]["chromium"], "--version"])
        (out / "provenance.json").write_text(json.dumps(provenance, indent=2) + "\n")
        env.update({"WELLNESS_BASE_URL": base, "WELLNESS_PAGE_ID": str(runtime["page_id"]),
                    "WELLNESS_DRAFT_ID": str(runtime["draft_id"]), "WELLNESS_BLOCK_DRAFT_ID": str(runtime["block_draft_id"]),
                    "WELLNESS_EVIDENCE_DIR": str(out), "WELLNESS_EDITOR_USER": "admin",
                    "WELLNESS_EDITOR_PASS": "wellness01-local-ci-password", "WELLNESS_EDITOR_ONLY": "0"})
        print("Native WordPress ready: " + json.dumps(state), flush=True)
        result = subprocess.run(["node", browser_script], cwd=repo, env=env, timeout=300)
        if result.returncode:
            raise RuntimeError("Native browser QA failed; see verification.json")
        php_log.flush()
        errors = re.findall(r".*(?:PHP (?:Warning|Fatal error|Parse error)|Uncaught).*", (out / "php-server.log").read_text())
        if errors:
            raise RuntimeError("Native PHP request errors: " + "\n".join(errors[:8]))
        print("PASS: exact-source native PHP 8.1 / MySQL 8.0 / WordPress 6.9 Wellness QA", flush=True)
    except Exception as error:
        (out / "failure.json").write_text(json.dumps({"error": str(error)}, indent=2) + "\n")
        raise
    finally:
        for process in [php_server, database]:
            if process is not None and process.poll() is None:
                process.terminate()
                try:
                    process.wait(timeout=15)
                except subprocess.TimeoutExpired:
                    process.kill()
                    process.wait()
        for handle in [php_log, db_log]:
            if handle:
                handle.close()


if __name__ == "__main__":
    main()
