#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

php tests/offline/w1-woocommerce-absent-contract.php
php tests/offline/w1-woocommerce-surface-contract.php
php tests/offline/w1-woocommerce-asset-scope-contract.php
php tests/offline/w1-woocommerce-ownership-static-contract.php

echo 'PASS: W1 offline contracts'

bash scripts/verify-e5b.sh

echo 'PASS: retained E5-B verifier'

count=0
while IFS= read -r -d '' file; do
    php -l "$file" >/dev/null
    count=$((count + 1))
done < <(find . -type f -name '*.php' \
    ! -path './tests/*' \
    ! -path './docs/*' \
    -print0)

echo "PASS: production PHP lint ${count}/${count}"
