#!/usr/bin/env python3
"""Build a *preview-only* RQA Theme candidate from the actual published v1.3.92 ZIP.

No tags, no release publication, no WordPress mutations, no build stack change.
Only aznet-theme/assets/css/components/woocommerce-archive.css may differ.
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

REPO = "truongdinhnamaz/aznet-theme"
RELEASE = "v1.3.92"
RELEASE_SHA256 = "204458e8518096fcf08f01f42c75d0880471f0b6f26ebfbc283f57b894b9b3ed"
RELATIVE_FILE = "assets/css/components/woocommerce-archive.css"
OUTPUT_ZIP = "RQA_PREVIEW_ONLY_aznet-theme-v1.3.92-woo-grid.zip"


def sha256(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def git(*args: str) -> bytes:
    return subprocess.check_output(["git", *args], stderr=subprocess.PIPE)


def validate_members(names: list[str]) -> str:
    if len(names) != len(set(names)):
        raise RuntimeError("Duplicate ZIP members")
    roots = {x.split("/")[0] for x in names if x}
    if roots != {"aznet-theme"}:
        raise RuntimeError(f"Unexpected WordPress package root: {roots!r}")
    for x in names:
        p = Path(x)
        if p.is_absolute() or ".." in p.parts or "\\" in x:
            raise RuntimeError(f"Unsafe ZIP entry: {x}")
    if len(names) < 100:
        raise RuntimeError("Unexpectedly small published Theme package")
    return "aznet-theme/" + RELATIVE_FILE


def assert_only_one_insertion(baseline: bytes, candidate: bytes) -> bytes:
    # Use an exact anchor rather than SequenceMatcher's ambiguous matching:
    # identical float/width/margin lines also occur in the baseline stylesheet.
    marker = b".woocommerce ul.products li.product .woocommerce-loop-product__link {"
    if baseline.count(marker) != 1 or candidate.count(marker) != 1:
        raise RuntimeError("Canonical insertion anchor is missing or duplicated")
    left_old, right_old = baseline.split(marker)
    left_new, right_new = candidate.split(marker)
    if right_new != right_old or not left_new.startswith(left_old):
        raise RuntimeError("Candidate CSS contains unauthorized edits")
    inserted = left_new[len(left_old):]
    if candidate != left_old + inserted + marker + right_old:
        raise RuntimeError("Candidate is not an insertion-only CSS delta")
    expected = (
        b'.woocommerce ul.products[class*="columns-"] li.product {\n'
        b"    float: none;\n"
        b"    clear: none;\n"
        b"    width: auto;\n"
        b"    margin: 0;\n"
        b"}\n"
    )
    if expected not in inserted or candidate.count(expected) != 1 or expected in baseline:
        raise RuntimeError("Wrong or duplicate WooCommerce CSS fix")
    if len(inserted) != 353:
        raise RuntimeError(f"Unexpected inserted source length {len(inserted)}")
    if b"choiceguide" in inserted.lower():
        raise RuntimeError("Provider-specific CSS copied into Theme candidate")
    return inserted


def build_zip(source: Path, destination: Path, old_name: str, content: bytes) -> None:
    with ZipFile(source) as source_zip, ZipFile(destination, "w") as out:
        for info in source_zip.infolist():
            data = content if info.filename == old_name else source_zip.read(info)
            out.writestr(info, data)
    with ZipFile(destination) as check:
        if check.testzip() is not None:
            raise RuntimeError("Produced ZIP failed CRC")
        if check.read(old_name) != content:
            raise RuntimeError("Output ZIP did not retain exact CSS candidate")


def main() -> None:
    source = Path(os.environ["RQA_OFFICIAL_RELEASE_ZIP"])
    output = Path(os.environ.get("RQA_RC_DIR", "/tmp/rqa-rc"))
    output.mkdir(parents=True, exist_ok=True)

    raw = source.read_bytes()
    digest = sha256(raw)
    if digest != RELEASE_SHA256:
        raise RuntimeError(f"Official released ZIP SHA-256 mismatch: {digest}")

    file_bytes = Path(RELATIVE_FILE).read_bytes()
    tag_bytes = git("show", RELEASE + ":" + RELATIVE_FILE)
    if file_bytes == tag_bytes:
        raise RuntimeError("Candidate CSS has no change versus official release tag")

    with ZipFile(source) as z:
        original_files = z.namelist()
        full_path = validate_members(original_files)
        baseline = z.read(full_path)
        if baseline != tag_bytes:
            raise RuntimeError("Official ZIP CSS does not match exact Git tag CSS")
        css_delta = assert_only_one_insertion(baseline, file_bytes)
        if b"Version: 1.3.92" not in z.read("aznet-theme/style.css"):
            raise RuntimeError("Official Theme version mismatch")
        if b"1.3.92" not in z.read("aznet-theme/functions.php"):
            raise RuntimeError("Official Theme runtime version mismatch")
        original_hashes = {name: sha256(z.read(name)) for name in original_files if not name.endswith("/")}

    final = output / OUTPUT_ZIP
    second = output / (OUTPUT_ZIP + ".repro")
    build_zip(source, final, full_path, file_bytes)
    build_zip(source, second, full_path, file_bytes)
    package_sha = sha256(final.read_bytes())
    if package_sha != sha256(second.read_bytes()):
        raise RuntimeError("Non-deterministic candidate package")
    second.unlink()

    with ZipFile(final) as z:
        names = z.namelist()
        hashes = {name: sha256(z.read(name)) for name in names if not name.endswith("/")}
    if names != original_files:
        raise RuntimeError("File list/order differs from official release")
    changed = sorted(name for name in hashes if hashes[name] != original_hashes.get(name))
    if changed != [full_path]:
        raise RuntimeError(f"Unexpected changed packaged files: {changed}")
    if set(hashes) != set(original_hashes):
        raise RuntimeError("Packaged runtime file additions/deletions detected")

    tag_sha = git("rev-parse", RELEASE + "^{commit}").decode("ascii").strip()
    head_sha = git("rev-parse", "HEAD").decode("ascii").strip()
    report = {
        "status": "PREFLIGHT_PACKAGING_PASS_NOT_RELEASE",
        "official_release": RELEASE,
        "official_release_sha256": digest,
        "official_tag_commit": tag_sha,
        "candidate_source_commit": head_sha,
        "source_repo": REPO,
        "candidate_theme_metadata": "1.3.92 (intentionally unchanged: NOT an approved version promotion)",
        "package": final.name,
        "candidate_package_sha256": package_sha,
        "runtime_file_count": len(original_hashes),
        "changed_runtime_file_count": len(changed),
        "changed_runtime_files": changed,
        "old_css_sha256": sha256(baseline),
        "new_css_sha256": sha256(file_bytes),
        "css_insert_bytes": len(css_delta),
        "repacked_zip_reproducible": True,
        "production_deployment_approved": False,
        "release_or_tag_created": False,
        "core_wordpress_clean_runtime_gate": "NOT_TESTED_BY_PACKAGING",
        "core_a11y_upgrade_rollback_gate": "NOT_TESTED_BY_PACKAGING",
        "site_hotfix_id": 1133,
        "site_hotfix_status": "already live, do not remove before new Theme is approved and verified",
    }
    (output / "manifest.json").write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    (output / "CSS_DELTA.diff").write_bytes(
        "".join(
            difflib.unified_diff(
                baseline.decode("utf-8").splitlines(keepends=True),
                file_bytes.decode("utf-8").splitlines(keepends=True),
                fromfile=f"{RELEASE}:{RELATIVE_FILE}",
                tofile=f"rc:{RELATIVE_FILE}",
            )
        ).encode("utf-8")
    )
    print("RQA_PREVIEW_RC_PASS " + json.dumps(report, ensure_ascii=False))
    print("RQA_PREVIEW_RC_SHA256 " + package_sha)


if __name__ == "__main__":
    try:
        main()
    except Exception as exc:
        print("RQA_PREVIEW_RC_BLOCKED " + str(exc), file=sys.stderr)
        raise
