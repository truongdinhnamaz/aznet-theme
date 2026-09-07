#!/usr/bin/env bash
set -euo pipefail

php tests/offline/g3-core-release-static-contract.php
bash scripts/verify-e5b.sh
bash scripts/verify-w1.sh
bash scripts/verify-w2.sh
bash scripts/verify-w3.sh
bash scripts/verify-w4.sh
bash scripts/verify-w5.sh
bash scripts/verify-w6.sh
php tests/offline/w8-convertflow-theme-contract.php
php tests/offline/f1-front-page-native-shell-contract.php
php tests/offline/f2-front-page-content-boundary-contract.php
php tests/offline/f3-homepage-ownership-static-contract.php
php tests/offline/f5-homepage-absent-failsoft-contract.php

mapfile -d '' production_php < <(find . -type f -name '*.php' \
    -not -path './.git/*' \
    -not -path './.github/*' \
    -not -path './docs/*' \
    -not -path './scripts/*' \
    -not -path './tests/*' -print0)

if [[ ${#production_php[@]} -eq 0 ]]; then
    echo 'FAIL: no production PHP files found' >&2
    exit 1
fi

for file in "${production_php[@]}"; do
    php -l "$file"
done

for excluded in .git .github docs scripts tests README.md aznet-preview.png; do
    printf 'PACKAGE_EXCLUDE=%s\n' "$excluded"
done

echo "PASS: G3 core static/security/package hygiene (${#production_php[@]} production PHP files)"
