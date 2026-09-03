#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

php tests/offline/w4-cart-asset-scope-contract.php
php tests/offline/w4-cart-css-contract.php
php tests/offline/w4-cart-ownership-static-contract.php

echo 'PASS: W4 offline contracts'

php tests/offline/w3-archive-asset-scope-contract.php
php tests/offline/w3-archive-ownership-static-contract.php

echo 'PASS: retained W3 invalidated regression subset'

php -l inc/theme/woocommerce-cart.php >/dev/null
php -l inc/theme/assets.php >/dev/null
php -l inc/theme/bootstrap.php >/dev/null

echo 'PASS: W4 changed/new production PHP lint 3/3'
