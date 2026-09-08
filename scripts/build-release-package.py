#!/usr/bin/env python3
"""Build a deterministic AZnet Theme release ZIP."""

from __future__ import annotations

import argparse
import hashlib
import re
import sys
import zipfile
from pathlib import Path, PurePosixPath

PACKAGE_ROOT = 'aznet-theme'
FIXED_ZIP_TIME = (1980, 1, 1, 0, 0, 0)
EXCLUDED_DIRS = {'.git', '.github', 'docs', 'scripts', 'tests'}
EXCLUDED_ROOT_FILES = {'README.md', 'aznet-preview.png'}


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--source', required=True, type=Path, help='Theme source directory')
    parser.add_argument('--output', required=True, type=Path, help='Output ZIP path')
    parser.add_argument('--version', required=True, help='Expected AZNET_THEME_VERSION')
    return parser.parse_args()


def style_version(source: Path) -> str:
    text = (source / 'style.css').read_text(encoding='utf-8')
    match = re.search(r'^Version:\s*(\S+)\s*$', text, re.MULTILINE)
    if not match:
        raise ValueError('style.css Version header missing')
    return match.group(1)


def php_version(source: Path) -> str:
    text = (source / 'functions.php').read_text(encoding='utf-8')
    match = re.search(r"define\(\s*'AZNET_THEME_VERSION'\s*,\s*'([^']+)'\s*\)\s*;", text)
    if not match:
        raise ValueError('AZNET_THEME_VERSION definition missing from functions.php')
    return match.group(1)


def excluded(relative: PurePosixPath) -> bool:
    if not relative.parts:
        return True
    if relative.parts[0] in EXCLUDED_DIRS:
        return True
    return len(relative.parts) == 1 and relative.name in EXCLUDED_ROOT_FILES


def source_files(source: Path) -> list[Path]:
    files: list[Path] = []
    for path in source.rglob('*'):
        if not path.is_file():
            continue
        relative = PurePosixPath(path.relative_to(source).as_posix())
        if excluded(relative):
            continue
        files.append(path)
    return sorted(files, key=lambda item: item.relative_to(source).as_posix())


def build_zip(source: Path, output: Path, version: str) -> tuple[int, str]:
    source = source.resolve()
    if not source.is_dir():
        raise ValueError(f'source directory does not exist: {source}')

    style = style_version(source)
    php = php_version(source)
    if style != version or php != version:
        raise ValueError(
            f'version mismatch: requested={version} style.css={style} functions.php={php}'
        )

    files = source_files(source)
    if not files:
        raise ValueError('release package would be empty')

    output.parent.mkdir(parents=True, exist_ok=True)
    if output.exists():
        output.unlink()

    with zipfile.ZipFile(
        output,
        mode='w',
        compression=zipfile.ZIP_DEFLATED,
        compresslevel=9,
        strict_timestamps=True,
    ) as archive:
        for path in files:
            relative = PurePosixPath(path.relative_to(source).as_posix())
            archive_name = str(PurePosixPath(PACKAGE_ROOT) / relative)
            info = zipfile.ZipInfo(archive_name, date_time=FIXED_ZIP_TIME)
            info.create_system = 3
            info.compress_type = zipfile.ZIP_DEFLATED
            info.external_attr = (0o100644 & 0xFFFF) << 16
            info.flag_bits |= 0x800
            archive.writestr(info, path.read_bytes(), compress_type=zipfile.ZIP_DEFLATED, compresslevel=9)

    digest = hashlib.sha256(output.read_bytes()).hexdigest()
    return len(files), digest


def main() -> int:
    args = parse_args()
    try:
        count, digest = build_zip(args.source, args.output, args.version)
    except (OSError, ValueError) as exc:
        print(f'FAIL: {exc}', file=sys.stderr)
        return 1

    print(f'PASS: deterministic package {args.output} files={count} sha256={digest}')
    return 0


if __name__ == '__main__':
    raise SystemExit(main())
