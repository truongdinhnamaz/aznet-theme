#!/usr/bin/env bash
set -euo pipefail

STATE_DIR="${WOO_L3_STATE_DIR:-/tmp/woo-l3}"
BASE_URL="${WOO_L3_BASE_URL:-http://127.0.0.1:8080}"
STATE_FILE="$STATE_DIR/runtime-ids.env"

if [ ! -f "$STATE_FILE" ]; then
    echo "missing runtime state: $STATE_FILE" >&2
    exit 1
fi

# shellcheck disable=SC1090
source "$STATE_FILE"
mkdir -p "$STATE_DIR/html"

WOO_HANDLES=(
    aznet-theme-woocommerce-product-css
    aznet-theme-woocommerce-archive-css
    aznet-theme-woocommerce-cart-css
    aznet-theme-woocommerce-checkout-css
    aznet-theme-woocommerce-account-css
)
GENERIC_HANDLE=aznet-theme-generic-content-css

fetch_surface() {
    local label="$1"
    local url="$2"
    local output="$STATE_DIR/html/$label.html"
    curl --fail --silent --show-error --location --max-time 30 "$url" -o "$output"
    grep -q '<body' "$output" || {
        echo "surface $label did not render a body" >&2
        exit 2
    }
    printf '%s\n' "$output"
}

assert_has() {
    local file="$1"
    local needle="$2"
    grep -q "$needle" "$file" || {
        echo "expected $needle in $file" >&2
        exit 3
    }
}

assert_not_has() {
    local file="$1"
    local needle="$2"
    if grep -q "$needle" "$file"; then
        echo "unexpected $needle in $file" >&2
        exit 4
    fi
}

assert_woo_surface() {
    local label="$1"
    local url="$2"
    local expected="$3"
    local file
    file="$(fetch_surface "$label" "$url")"

    assert_has "$file" "$expected"
    assert_not_has "$file" "$GENERIC_HANDLE"

    local handle
    for handle in "${WOO_HANDLES[@]}"; do
        if [ "$handle" != "$expected" ]; then
            assert_not_has "$file" "$handle"
        fi
    done

    echo "PASS: $label -> $expected"
}

assert_woo_surface \
    product \
    "$BASE_URL/?post_type=product&p=$PRODUCT_ID" \
    aznet-theme-woocommerce-product-css

assert_woo_surface \
    archive \
    "$BASE_URL/?post_type=product" \
    aznet-theme-woocommerce-archive-css

assert_woo_surface \
    cart \
    "$BASE_URL/?page_id=$CART_PAGE_ID" \
    aznet-theme-woocommerce-cart-css

assert_woo_surface \
    checkout \
    "$BASE_URL/?page_id=$CHECKOUT_PAGE_ID" \
    aznet-theme-woocommerce-checkout-css

assert_woo_surface \
    account \
    "$BASE_URL/?page_id=$ACCOUNT_PAGE_ID" \
    aznet-theme-woocommerce-account-css

generic_file="$(fetch_surface generic "$BASE_URL/?page_id=$GENERIC_PAGE_ID")"
assert_has "$generic_file" "$GENERIC_HANDLE"
for handle in "${WOO_HANDLES[@]}"; do
    assert_not_has "$generic_file" "$handle"
done

echo 'PASS: generic page -> aznet-theme-generic-content-css only'
echo 'PASS: Woo L3 real HTTP surface/asset smoke'
