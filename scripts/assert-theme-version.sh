#!/usr/bin/env bash
set -euo pipefail

STYLE_VERSION=$(sed -n 's/^Version:[[:space:]]*//p' style.css | head -n 1)
PHP_VERSION=$(sed -n "s/.*define( 'AZNET_THEME_VERSION', '\([^']*\)' );.*/\1/p" functions.php | head -n 1)

test -n "$STYLE_VERSION"
test -n "$PHP_VERSION"
test "$STYLE_VERSION" = "$PHP_VERSION"
[[ "$STYLE_VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]

if [[ ${1:-} != '' ]]; then
  test "$STYLE_VERSION" = "$1"
fi

printf 'PASS: Theme metadata version %s is consistent\n' "$STYLE_VERSION"
