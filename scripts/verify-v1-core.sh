#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)
cd "$ROOT_DIR"

printf '%s\n' '==> PHP lint: production Theme tree'
mapfile -t production_php < <(
  {
    find . -maxdepth 1 -type f -name '*.php' -print
    for dir in inc template-parts patterns; do
      if [[ -d "$dir" ]]; then
        find "$dir" -type f -name '*.php' -print
      fi
    done
  } | LC_ALL=C sort -u
)

if [[ ${#production_php[@]} -eq 0 ]]; then
  echo 'FAIL: no production PHP files found' >&2
  exit 1
fi

for file in "${production_php[@]}"; do
  php -l "$file" >/dev/null
done
printf 'PASS: production PHP lint %s files\n' "${#production_php[@]}"

printf '%s\n' '==> Retained core/static/security/package boundary chain'
bash scripts/verify-g3-core.sh

run_contract_group() {
  local pattern="$1"
  local found=0
  local contract
  for contract in $pattern; do
    if [[ ! -f "$contract" ]]; then
      continue
    fi
    found=1
    php -d zend.assertions=1 -d assert.exception=1 "$contract"
  done
  if [[ "$found" -ne 1 ]]; then
    echo "FAIL: no contracts matched $pattern" >&2
    exit 1
  fi
}

printf '%s\n' '==> Retained v1.1 R1-R4 contracts'
run_contract_group 'tests/offline/r1-*.php'
run_contract_group 'tests/offline/r2-*.php'
run_contract_group 'tests/offline/r3-*.php'
run_contract_group 'tests/offline/r4-*.php'

printf '%s\n' '==> Retained R5 Control Center contract'
php -d zend.assertions=1 -d assert.exception=1 tests/offline/r5-control-center-static-contract.php

printf '%s\n' '==> Woo asset scope regression'
php -d zend.assertions=1 -d assert.exception=1 tests/offline/w1-woocommerce-asset-scope-contract.php

printf '%s\n' '==> R6 contracts'
php -d zend.assertions=1 -d assert.exception=1 tests/offline/r6-asset-scope-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/r6-convertflow-asset-gate-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/r6-ci-trigger-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/r6-release-workflow-contract.php

printf '%s\n' '==> P2 Editorial Single-Post contract'
php -d zend.assertions=1 -d assert.exception=1 tests/offline/editorial-single-post-contract.php

printf '%s\n' '==> P3 Editorial Listing/Search contract'
php -d zend.assertions=1 -d assert.exception=1 tests/offline/editorial-listing-search-contract.php

printf '%s\n' '==> P4 Native Editable Homepage blueprint contracts'
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-blueprint-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-blueprint-asset-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-blueprint-control-center-contract.php

printf '%s\n' '==> Homepage Composer + Law 01 contracts'
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-composer-settings-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-content-map-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-composer-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-control-center-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-law-01-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-law-01-premium-presentation-contract.php

printf '%s\n' '==> Law Site Provisioning contracts'
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-blueprint-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-activation-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-discovery-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-recommendations-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-starter-editorial-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-v1-1-plan-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-media-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-plan-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-runner-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-idempotency-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-readiness-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-admin-a11y-contract.php

printf '%s\n' '==> Classic Editor policy contract'
php -d zend.assertions=1 -d assert.exception=1 tests/offline/classic-editor-policy-contract.php

printf '%s\n' 'PASS: reusable v1 core verification'
