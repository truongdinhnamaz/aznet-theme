#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

php tests/offline/w3-archive-asset-scope-contract.php
php tests/offline/w3-archive-css-contract.php
php tests/offline/w3-archive-ownership-static-contract.php

echo 'PASS: W3 offline contracts'

php tests/offline/w2-product-asset-scope-contract.php
php tests/offline/w2-product-ownership-static-contract.php

echo 'PASS: retained W2 invalidated regression subset'

php -l inc/theme/woocommerce-archive.php >/dev/null
php -l inc/theme/assets.php >/dev/null
php -l inc/theme/bootstrap.php >/dev/null

echo 'PASS: W3 changed/new production PHP lint 3/3'
