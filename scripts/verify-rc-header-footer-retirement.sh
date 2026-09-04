#!/bin/sh
set -eu

ROOT=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)

php "$ROOT/tests/offline/rc-header-footer-retirement-static-contract.php"

for file in \
    header.php \
    footer.php \
    template-parts/header/site-header.php \
    template-parts/footer/site-footer.php \
    inc/theme/assets.php \
    inc/theme/setup.php
do
    php -l "$ROOT/$file" >/dev/null
    printf 'PASS php -l: %s\n' "$file"
done

printf 'PASS: R-C Header/Footer retirement L1/L2 verifier\n'
