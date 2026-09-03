#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

php tests/offline/e5-current-surface-consumer-contract.php
php tests/offline/e5-current-surface-model-contract.php
php tests/offline/e5-current-surface-dispatcher-contract.php
php tests/offline/e5-no-takeover-static-contract.php

PHP_COUNT=0
while IFS= read -r -d '' file; do
    php -l "$file" >/dev/null
    PHP_COUNT=$((PHP_COUNT + 1))
done < <(find . -type f -name '*.php' \
    -not -path './tests/*' \
    -not -path './vendor/*' \
    -print0)

echo "PASS: E5-B offline contracts"
echo "PASS: production PHP lint ${PHP_COUNT}/${PHP_COUNT}"
