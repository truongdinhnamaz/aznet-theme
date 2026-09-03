#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

php tests/offline/w6-account-asset-scope-contract.php
php tests/offline/w6-account-css-contract.php
php tests/offline/w6-account-ownership-static-contract.php

echo 'PASS: W6 offline contracts'

php tests/offline/w2-product-asset-scope-contract.php
php tests/offline/w3-archive-asset-scope-contract.php
php tests/offline/w4-cart-asset-scope-contract.php
php tests/offline/w5-checkout-asset-scope-contract.php
php tests/offline/w5-checkout-ownership-static-contract.php

echo 'PASS: retained W2-W5 invalidated asset regression chain'

php -l inc/theme/woocommerce-account.php >/dev/null
php -l inc/theme/assets.php >/dev/null
php -l inc/theme/bootstrap.php >/dev/null

echo 'PASS: W6 changed/new production PHP lint 3/3'
