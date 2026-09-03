#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

php tests/offline/w2-product-asset-scope-contract.php
php tests/offline/w2-product-css-contract.php
php tests/offline/w2-product-ownership-static-contract.php

echo 'PASS: W2 offline contracts'

bash scripts/verify-w1.sh

echo 'PASS: retained W1 -> E5-B + production lint chain'
