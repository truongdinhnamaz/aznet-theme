#!/usr/bin/env python3
"""Fail-closed 1.3.97 preview-only version/package gate; no release/deploy."""
import hashlib
import json
import os
import subprocess
import sys
from pathlib import Path
from zipfile import ZipFile

MAIN = "afdeeacd3927eb0b7763045ab732b2f0cbebd1c7"
PRIOR = "331415e5eb64921a15c89ec986bf26f11f02bf74"
CSS = "assets/css/components/woocommerce-archive.css"
EXPECTED = {"aznet-theme/style.css", "aznet-theme/functions.php", "aznet-theme/" + CSS}

def git(*args):
    return subprocess.check_output(["git", *args], stderr=subprocess.PIPE)

def ancestor(sha):
    git("merge-base", "--is-ancestor", sha, "HEAD")

def prior(path):
    return git("show", PRIOR + ":" + path)

def replace_once(raw, old, new):
    if raw.count(old) != 1:
        raise AssertionError("Version marker not unique in original source")
    return raw.replace(old, new, 1)

def verify():
    ancestor(MAIN)
    ancestor(PRIOR)
    head = git("rev-parse", "HEAD").decode().strip()
    style = Path("style.css").read_bytes()
    php = Path("functions.php").read_bytes()
    if style != replace_once(prior("style.css"), b"Version: 1.3.96", b"Version: 1.3.97"):
        raise AssertionError("Style metadata is not an exact version-only delta")
    if php != replace_once(prior("functions.php"), b"'AZNET_THEME_VERSION', '1.3.96'", b"'AZNET_THEME_VERSION', '1.3.97'"):
        raise AssertionError("PHP metadata is not an exact version-only delta")
    if Path(CSS).read_bytes() != prior(CSS):
        raise AssertionError("Retained Woo CSS was modified")
    paths = set(git("diff", "--name-only", PRIOR, "HEAD", "--", "style.css", "functions.php", "theme.json", "inc", "assets", "templates", "patterns", "template-parts", "woocommerce").decode().splitlines())
    if paths != {"style.css", "functions.php"}:
        raise AssertionError("Unexpected runtime source delta: " + repr(paths))
    main = Path(os.environ["RQA1397_MAIN_ZIP"])
    new = Path(os.environ["RQA1397_CANDIDATE_ZIP"])
    repeat = Path(os.environ["RQA1397_REPEAT_ZIP"])
    with ZipFile(main) as a, ZipFile(new) as b, ZipFile(repeat) as c:
        if a.testzip() or b.testzip() or c.testzip():
            raise AssertionError("ZIP CRC failure")
        names = a.namelist()
        if names != b.namelist() or names != c.namelist() or len(names) < 200:
            raise AssertionError("Package inventory changed or incomplete")
        if any(n.startswith("/") or ".." in Path(n).parts for n in names):
            raise AssertionError("Unsafe ZIP paths")
        changed = {n for n in names if a.read(n) != b.read(n)}
        if changed != EXPECTED:
            raise AssertionError("Unexpected runtime byte delta: " + repr(changed))
        for n in names:
            if not n.startswith("aznet-theme/"):
                raise AssertionError("Unexpected ZIP root")
            if b.read(n) != Path(n[len("aznet-theme/"):]).read_bytes() or b.read(n) != c.read(n):
                raise AssertionError("ZIP mismatch: " + n)
    if new.read_bytes() != repeat.read_bytes():
        raise AssertionError("Rebuilt ZIP bytes not identical")
    digest = lambda p: hashlib.sha256(p.read_bytes()).hexdigest()
    result = {"status": "PASS_VERSION_CANDIDATE_NOT_RELEASE", "head": head,
              "main": MAIN, "previous_draft": PRIOR, "version": "1.3.97",
              "runtime_files": len(names), "changed": sorted(EXPECTED),
              "zip_sha256": digest(new), "old_zip_sha256": digest(main),
              "exact_rebuild": True, "production_modified": False,
              "convertflow_l5": "BLOCKED", "production_restore": "UNKNOWN"}
    out = Path(os.environ["RQA1397_OUTPUT_DIR"])
    out.mkdir(parents=True, exist_ok=True)
    (out / "manifest.json").write_text(json.dumps(result, indent=2) + "\n")
    print("RQA_1397_CANDIDATE_PASS " + json.dumps(result))

if __name__ == "__main__":
    try:
        verify()
    except Exception as exc:
        print("RQA_1397_CANDIDATE_BLOCKED " + str(exc), file=sys.stderr)
        raise
