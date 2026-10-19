#!/usr/bin/env python3
"""Regression: generated ZIPs must never enter a subsequent package build."""

import hashlib
import subprocess
import sys
import tempfile
import zipfile
from pathlib import Path

REPO = Path(__file__).resolve().parents[2]
BUILDER = REPO / "scripts" / "build-release-package.py"
VERSION = "1.3.89"

with tempfile.TemporaryDirectory(prefix="aznet-release-output-exclusion-") as tmp:
    root = Path(tmp) / "fixture"
    root.mkdir()
    (root / "style.css").write_text("/*\nTheme Name: AZnet Theme\nVersion: 1.3.89\n*/\n", encoding="utf-8")
    (root / "functions.php").write_text("<?php define( 'AZNET_THEME_VERSION', '1.3.89' );\n", encoding="utf-8")
    (root / "theme.json").write_text("{}\n", encoding="utf-8")
    (root / "front-page.php").write_text("<?php // WordPress-front fixture.\n", encoding="utf-8")
    (root / "index.php").write_text("<?php // WordPress-index fixture.\n", encoding="utf-8")
    (root / "dist").mkdir()
    outputs = [root / "dist" / "first.zip", root / "dist" / "second.zip"]

    for target in outputs:
        subprocess.run(
            [
                sys.executable,
                str(BUILDER),
                "--source", str(root),
                "--output", str(target),
                "--version", VERSION,
            ],
            check=True,
            capture_output=True,
            text=True,
        )
    first, second = (path.read_bytes() for path in outputs)
    if first != second:
        raise RuntimeError(
            "ZIP output self-inclusion: repeated builds differ "
            + hashlib.sha256(first).hexdigest()
            + " versus "
            + hashlib.sha256(second).hexdigest()
        )
    with zipfile.ZipFile(outputs[1]) as archive:
        members = archive.namelist()
    if not members or any("/dist/" in member for member in members):
        raise RuntimeError("ZIP includes generated source/dist/ build output")
print("PASS: built-in-source dist output excluded, deterministic double-build")
