#!/usr/bin/env python3
"""Preflight the *unreleased* main@1.3.96 + verified RQA CSS only.
Never tag, release, deploy, or touch WordPress from this script.
"""
from __future__ import annotations
import difflib
import hashlib
import json
import os
import subprocess
import sys
from pathlib import Path
from zipfile import ZipFile

BASE = "afdeeacd3927eb0b7763045ab732b2f0cbebd1c7"
VERSION = "1.3.96"
CSS = "aznet-theme/assets/css/components/woocommerce-archive.css"
CSS_REL = "assets/css/components/woocommerce-archive.css"
APPROVED_SELECTOR = b'.woocommerce ul.products[class*="columns-"] li.product {\n'
RC_NAME = "RQA_PREVIEW_ONLY_aznet-theme-v1.3.96-woo-grid.zip"


def digest(b: bytes) -> str:
    return hashlib.sha256(b).hexdigest()


def git(*args: str) -> bytes:
    return subprocess.check_output(["git", *args], stderr=subprocess.PIPE)


def assert_source_delta(old: bytes, new: bytes) -> bytes:
    # Exact insertion with no changes elsewhere, including whitespace.
    marker = b".woocommerce ul.products li.product .woocommerce-loop-product__link {"
    if old.count(marker) != 1 or new.count(marker) != 1:
        raise RuntimeError("Source CSS insertion anchor must exist exactly once")
    left, right = old.split(marker)
    patched_left, patched_right = new.split(marker)
    if patched_right != right or not patched_left.startswith(left):
        raise RuntimeError("Candidate modified baseline CSS outside the bounded insertion")
    addition = patched_left[len(left):]
    if new != left + addition + marker + right:
        raise RuntimeError("Expected a pure CSS insertion")
    if len(addition) != 353 or addition.count(APPROVED_SELECTOR) != 1:
        raise RuntimeError("Unexpected RQA CSS candidate size or selector")
    if b"choiceguide" in addition.lower() or b"get_option" in addition.lower():
        raise RuntimeError("Provider/private logic in Theme CSS candidate")
    return addition


def main() -> None:
    base_zip = Path(os.environ["RQA_BASE_ZIP"])
    rc_zip = Path(os.environ["RQA_CANDIDATE_ZIP"])
    repeat_zip = Path(os.environ["RQA_REPRO_ZIP"])
    out = Path(os.environ["RQA_OUTPUT_DIR"])
    out.mkdir(parents=True, exist_ok=True)
    head = git("rev-parse", "HEAD").decode().strip()
    git("merge-base", "--is-ancestor", BASE, head)
    source_changes = git("diff", "--name-only", BASE, head, "--", "assets", "functions.php", "style.css", "inc", "templates", "patterns", "template-parts", "theme.json").decode().splitlines()
    if source_changes != [CSS_REL]:
        raise RuntimeError(f"Unexpected runtime source changes relative to main@1.3.96: {source_changes}")
    if git("show", BASE + ":style.css").decode().count("Version: 1.3.96") != 1:
        raise RuntimeError("Invalid canonical 1.3.96 main anchor")
    if VERSION not in git("show", BASE + ":functions.php").decode():
        raise RuntimeError("Mismatched canonical PHP version")
    if b"Version: 1.3.96" not in Path("style.css").read_bytes():
        raise RuntimeError("Wrong candidate CSS theme metadata")
    if b"1.3.96" not in Path("functions.php").read_bytes():
        raise RuntimeError("Wrong candidate PHP theme metadata")
    with ZipFile(base_zip) as b, ZipFile(rc_zip) as c, ZipFile(repeat_zip) as r:
        if c.testzip() or b.testzip() or r.testzip():
            raise RuntimeError("ZIP CRC or structure failure")
        baseline_names = b.namelist()
        candidate_names = c.namelist()
        if baseline_names != candidate_names or candidate_names != r.namelist():
            raise RuntimeError("Candidate ZIP changed baseline package file list/order")
        if len(candidate_names) < 200 or any(x.startswith("/") or ".." in Path(x).parts for x in candidate_names):
            raise RuntimeError("Unexpected or unsafe runtime package file entries")
        old_css, new_css = b.read(CSS), c.read(CSS)
        addition = assert_source_delta(old_css, new_css)
        if old_css != git("show", BASE + ":" + CSS_REL):
            raise RuntimeError("Baseline ZIP CSS not equal to canonical main commit")
        if new_css != Path(CSS_REL).read_bytes():
            raise RuntimeError("RC ZIP CSS not equal to branch source bytes")
        if c.read("aznet-theme/style.css") != b.read("aznet-theme/style.css") or c.read("aznet-theme/functions.php") != b.read("aznet-theme/functions.php"):
            raise RuntimeError("Theme metadata/runtime changed outside CSS")
        changed = [name for name in baseline_names if b.read(name) != c.read(name)]
        if changed != [CSS]:
            raise RuntimeError(f"Unexpected changed packaged runtime files: {changed}")
        if any(c.read(name) != r.read(name) for name in candidate_names):
            raise RuntimeError("Reproducibility second ZIP contents differ")
    if rc_zip.read_bytes() != repeat_zip.read_bytes():
        raise RuntimeError("Different bytes from two deterministic package builds")
    result = {
        "status": "PREVIEW_RC_PACKAGE_PASS_NOT_RELEASE",
        "base": f"main@{BASE}",
        "candidate_commit": head,
        "metadata_version": VERSION,
        "published_release_exists": False,
        "candidate_package": rc_zip.name,
        "candidate_package_sha256": digest(rc_zip.read_bytes()),
        "base_package_sha256": digest(base_zip.read_bytes()),
        "runtime_files": len(candidate_names),
        "changed_runtime_files": changed,
        "css_insert_bytes": len(addition),
        "base_css_sha256": digest(old_css),
        "candidate_css_sha256": digest(new_css),
        "built_twice_byte_identical": True,
        "production_deployment": False,
        "release_tag_created": False,
        "theme_hotfix_custom_css_id": 1133,
        "keep_live_hotfix_until_approved_deployment": True
    }
    (out / "manifest.json").write_text(json.dumps(result, indent=2, ensure_ascii=False)+"\n", encoding="utf-8")
    diff = "".join(difflib.unified_diff(
        old_css.decode().splitlines(keepends=True), new_css.decode().splitlines(keepends=True),
        fromfile=BASE + ":" + CSS_REL, tofile=head + ":" + CSS_REL
    ))
    (out / "CSS_DELTA.diff").write_text(diff, encoding="utf-8")
    print("RQA_V1396_PREVIEW_PASS "+json.dumps(result, ensure_ascii=False))


if __name__ == "__main__":
    try:
        main()
    except Exception as e:
        print("RQA_V1396_PREVIEW_BLOCKED "+str(e), file=sys.stderr)
        raise
