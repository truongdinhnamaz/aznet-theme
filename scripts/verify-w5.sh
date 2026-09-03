#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

php tests/offline/w5-checkout-asset-scope-contract.php
php tests/offline/w5-checkout-css-contract.php
php tests/offline/w5-checkout-ownership-static-contract.php

echo 'PASS: W5 offline contracts'

php tests/offline/w4-cart-asset-scope-contract.php
php tests/offline/w4-cart-ownership-static-contract.php

echo 'PASS: retained W4 invalidated regression subset'

php -l inc/theme/woocommerce-checkout.php >/dev/null
php -l inc/theme/assets.php >/dev/null
php -l inc/theme/bootstrap.php >/dev/null

echo 'PASS: W5 changed/new production PHP lint 3/3'
