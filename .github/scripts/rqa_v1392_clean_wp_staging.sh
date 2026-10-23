#!/usr/bin/env bash
# Disposable WordPress 6.9 + PHP 8.1 acceptance for RQA's preview-only CSS candidate.
# Uses a local MySQL CI service and an isolated filesystem. Never talks to live RQA.
set -euo pipefail

: "$RQA_OFFICIAL_RELEASE_ZIP"
: "$RQA_RC_DIR"
: "$RQA_STAGING_DIR"
mkdir -p "$RQA_STAGING_DIR" "$RQA_RC_DIR"
WP_ROOT="$RQA_STAGING_DIR/wp"
RUNTIME="$RQA_STAGING_DIR/installable"
ORIGINAL="$RQA_STAGING_DIR/original"
CANDIDATE="$RQA_RC_DIR/RQA_PREVIEW_ONLY_aznet-theme-v1.3.92-woo-grid.zip"
mkdir -p "$WP_ROOT" "$RUNTIME" "$ORIGINAL"

test -f "$CANDIDATE"
test -f "$RQA_OFFICIAL_RELEASE_ZIP"
python3 -m json.tool "$RQA_RC_DIR/manifest.json" > /dev/null
test "$(jq -r '.status' "$RQA_RC_DIR/manifest.json")" = "PREFLIGHT_PACKAGING_PASS_NOT_RELEASE"
test "$(sha256sum "$CANDIDATE" | cut -d' ' -f1)" = "$(jq -r '.candidate_package_sha256' "$RQA_RC_DIR/manifest.json")"

unzip -q "$CANDIDATE" -d "$RUNTIME"
unzip -q "$RQA_OFFICIAL_RELEASE_ZIP" -d "$ORIGINAL"
test -f "$RUNTIME/aznet-theme/style.css"
test -f "$ORIGINAL/aznet-theme/style.css"

echo '==> WordPress 6.9 clean runtime install'
wp core download --version=6.9 --path="$WP_ROOT" --force --allow-root
wp config create --path="$WP_ROOT" --dbname=wordpress --dbuser=root --dbpass=root \
  --dbhost=127.0.0.1 --skip-check --allow-root
wp core install --path="$WP_ROOT" --url=http://127.0.0.1:8080 \
  --title='RQA Preview Clean Runtime' --admin_user=ciadmin \
  --admin_password=ci-local-test-password --admin_email=ci@example.test \
  --skip-email --allow-root
test "$(wp core version --path="$WP_ROOT" --allow-root)" = "6.9"

mkdir -p "$WP_ROOT/wp-content/themes/aznet-theme"
rsync -a --delete "$RUNTIME/aznet-theme/" "$WP_ROOT/wp-content/themes/aznet-theme/"
wp theme activate aznet-theme --path="$WP_ROOT" --allow-root
wp plugin deactivate --all --path="$WP_ROOT" --allow-root > /dev/null || true
test -z "$(wp plugin list --status=active --field=name --path="$WP_ROOT" --allow-root)"
test "$(wp option get stylesheet --path="$WP_ROOT" --allow-root)" = aznet-theme
test "$(sha256sum "$WP_ROOT/wp-content/themes/aznet-theme/assets/css/components/woocommerce-archive.css" | cut -d' ' -f1)" = "$(jq -r '.new_css_sha256' "$RQA_RC_DIR/manifest.json")"
test "$(sed -n 's/^Version:[[:space:]]*//p' "$WP_ROOT/wp-content/themes/aznet-theme/style.css" | head -n1)" = 1.3.92

POST_ID=$(wp post create --post_type=post --post_title='RQA Clean WP Sentinel' \
  --post_content='Source-owned WordPress post survives Theme switch.' \
  --post_status=publish --porcelain --path="$WP_ROOT" --allow-root)
test -n "$POST_ID"
test "$(wp post get "$POST_ID" --field=post_title --path="$WP_ROOT" --allow-root)" = 'RQA Clean WP Sentinel'

echo '==> Standalone Theme response smoke'
php -S 127.0.0.1:8080 -t "$WP_ROOT" > "$RQA_STAGING_DIR/php-server.log" 2>&1 &
PHP_PID=$!
trap 'kill "$PHP_PID" 2>/dev/null || true' EXIT
LIVE=0
for try in $(seq 1 45); do
  if curl --silent --fail --max-time 3 http://127.0.0.1:8080/ > "$RQA_STAGING_DIR/home.html"; then
    LIVE=1
    break
  fi
  sleep 1
done
test "$LIVE" = 1
grep -F 'aznet-theme-site-header' "$RQA_STAGING_DIR/home.html" > /dev/null
grep -F 'aznet-theme-site-footer' "$RQA_STAGING_DIR/home.html" > /dev/null
grep -F 'id="main"' "$RQA_STAGING_DIR/home.html" > /dev/null
if grep -E 'woocommerce-archive\.css|choiceguide/product-filter\.js' "$RQA_STAGING_DIR/home.html"; then
  echo 'FAIL: optional WooCommerce/ConvertFlow asset leaked onto clean-WP Home' >&2
  exit 1
fi
curl --silent --fail --max-time 10 \
  "http://127.0.0.1:8080/?p=$POST_ID" > "$RQA_STAGING_DIR/post.html"
grep -F 'RQA Clean WP Sentinel' "$RQA_STAGING_DIR/post.html" > /dev/null

echo '==> Chromium clean-WordPress visual, keyboard and axe-core smoke'
RQA_STAGING_DIR="$RQA_STAGING_DIR" RQA_SENTINEL_POST_ID="$POST_ID" \
  node .github/scripts/rqa_v1392_clean_wp_l4.mjs

echo '==> Theme switching preserves WordPress source-owned data'
FALLBACK=$(wp theme list --status=inactive --field=name --path="$WP_ROOT" --allow-root \
  | grep '^twenty' | head -n 1)
test -n "$FALLBACK"
wp theme activate "$FALLBACK" --path="$WP_ROOT" --allow-root
test "$(wp post get "$POST_ID" --field=post_title --path="$WP_ROOT" --allow-root)" = 'RQA Clean WP Sentinel'
wp theme activate aznet-theme --path="$WP_ROOT" --allow-root
test "$(wp option get stylesheet --path="$WP_ROOT" --allow-root)" = aznet-theme
curl --silent --fail --max-time 10 http://127.0.0.1:8080/ > "$RQA_STAGING_DIR/home-after-switch.html"
grep -F 'aznet-theme-site-footer' "$RQA_STAGING_DIR/home-after-switch.html" > /dev/null

echo '==> Isolated rollback candidate to unchanged official published ZIP'
wp theme activate "$FALLBACK" --path="$WP_ROOT" --allow-root
rsync -a --delete "$ORIGINAL/aznet-theme/" "$WP_ROOT/wp-content/themes/aznet-theme/"
test "$(sha256sum "$WP_ROOT/wp-content/themes/aznet-theme/assets/css/components/woocommerce-archive.css" | cut -d' ' -f1)" = "$(jq -r '.old_css_sha256' "$RQA_RC_DIR/manifest.json")"
wp theme activate aznet-theme --path="$WP_ROOT" --allow-root
test "$(wp post get "$POST_ID" --field=post_title --path="$WP_ROOT" --allow-root)" = 'RQA Clean WP Sentinel'
curl --silent --fail --max-time 10 http://127.0.0.1:8080/ > "$RQA_STAGING_DIR/home-after-rollback.html"
grep -F 'aznet-theme-site-header' "$RQA_STAGING_DIR/home-after-rollback.html" > /dev/null
if grep -E 'PHP (Fatal error|Parse error)' "$RQA_STAGING_DIR/php-server.log"; then
  echo 'FAIL: WordPress clean PHP fatal/parse error during smoke/switch/rollback' >&2
  exit 1
fi

printf '{"status":"L3_WP_CLEAN_STAGING_PASS","wordpress":"6.9","php":"8.1","plugins_active":0,"theme":"aznet-theme","candidate_version":"1.3.92","theme_switch":"PASS","wp_data_survived":"PASS","rollback_to_official_zip":"PASS","production_deployment":false}\n' \
  > "$RQA_STAGING_DIR/runtime-summary.json"
cp "$RQA_STAGING_DIR/runtime-summary.json" "$RQA_RC_DIR/runtime-summary.json"
echo 'RQA_WP_CLEAN_L3_PASS WordPress 6.9, PHP 8.1, theme switch and rollback; no live-site write'
