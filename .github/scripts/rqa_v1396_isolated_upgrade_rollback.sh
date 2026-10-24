#!/usr/bin/env bash
# Disposable RQA-shaped Theme upgrade/rollback rehearsal, /tmp/wp only.
set -euo pipefail
ROOT=/tmp/wp
OUT=/tmp/curtain01-c5
THEMES="$ROOT/wp-content/themes"
THEME="$THEMES/aznet-theme"
CANDIDATE="$OUT/rqa-1396-preview.zip"
BASE_ZIP="$OUT/aznet-theme-1.3.92.zip"
SNAPSHOT="$GITHUB_WORKSPACE/.github/scripts/rqa_v1396_pilot_data_snapshot.php"
EXPECTED_RC_SHA=50a4189be371cda131eda1eb0dd95ff739c43e4e4228015e1b4d4a958703a266
EXPECTED_BASE_SHA=204458e8518096fcf08f01f42c75d0880471f0b6f26ebfbc283f57b894b9b3ed
wpc() { wp "$@" --path="$ROOT" --allow-root; }
# Native WP-CLI theme-mod get emits a key/value table; use the public WP API for scalar readback.
css_mod() { wpc eval 'echo (string) get_theme_mod( "custom_css_post_id", -1 );'; }
get_fallback() {
  wpc theme list --status=inactive --field=name | awk '/^twenty/{print;exit}'
}
check_preset() {
  test "$(wpc option get stylesheet)" = aznet-theme
  wpc eval 'if ( "curtain-01" !== (string) get_theme_mod( "aznet_theme_settings", [] )["visual_preset"] ) { exit(1); }' >/dev/null
}
snapshot() {
  wpc eval-file "$SNAPSHOT" > "$1"
  python3 -m json.tool "$1" >/dev/null
}
safe_restore() {
  local zip="$1"
  wpc theme activate "$FALLBACK" >/dev/null
  rm -rf "$THEME"
  mkdir -p "$THEMES"
  unzip -q "$zip" -d "$THEMES"
  wpc theme activate aznet-theme >/dev/null
  check_preset
}
mkdir -p "$OUT"
test -s "$CANDIDATE"
printf '%s  %s\n' "$EXPECTED_RC_SHA" "$CANDIDATE" | sha256sum -c -
FALLBACK="$(get_fallback)"
test -n "$FALLBACK"

if [ "$1" = "upgrade" ]; then
  echo '==> Verify official previous Theme v1.3.92'
  gh release download v1.3.92 --repo truongdinhnamaz/aznet-theme \
    --pattern aznet-theme-1.3.92.zip --dir "$OUT"
  printf '%s  %s\n' "$EXPECTED_BASE_SHA" "$BASE_ZIP" | sha256sum -c -
  test "$(wpc theme get aznet-theme --field=version)" = 1.3.96
  safe_restore "$BASE_ZIP"
  test "$(wpc theme get aznet-theme --field=version)" = 1.3.92

  echo '==> Simulate WordPress Theme-scoped Additional CSS hotfix'
  cat > "$OUT/hotfix.css" <<'CSS'
/* AZnet Theme RQA scoped Woo archive grid hotfix; canonical source PR #506. */
.woocommerce ul.products[class*="columns-"] li.product {
    float: none;
    clear: none;
    width: auto;
    margin: 0;
}
CSS
  CSS_ID="$(wpc post create --post_type=custom_css --post_status=publish \
    --post_title=aznet-theme --post_name=aznet-theme \
    --post_content="$(cat "$OUT/hotfix.css")" --porcelain)"
  test "$CSS_ID" -gt 0
  echo "RQA_STAGE_CSS_POST_CREATED id=$CSS_ID status=$(wpc post get "$CSS_ID" --field=post_status)"
  printf '%s\n' "$CSS_ID" > "$OUT/hotfix-post-id.txt"
  wpc theme mod set custom_css_post_id "$CSS_ID" >/dev/null
  STAGED_CSS_MOD="$(css_mod)"
  echo "RQA_STAGE_CSS_MOD_READBACK value=$STAGED_CSS_MOD"
  test "$STAGED_CSS_MOD" = "$CSS_ID"
  echo 'RQA_STAGE_SNAPSHOT_START'
  snapshot "$OUT/ownership-before.json"
  echo 'RQA_STAGE_SNAPSHOT_DONE'

  echo '==> Upgrade Theme files 1.3.92 to exact 1.3.96 candidate'
  safe_restore "$CANDIDATE"
  test "$(wpc theme get aznet-theme --field=version)" = 1.3.96
  snapshot "$OUT/ownership-after-upgrade.json"
  cmp -s "$OUT/ownership-before.json" "$OUT/ownership-after-upgrade.json"
  test "$(css_mod)" = "$CSS_ID"

  SHA_NEW="$(sha256sum "$THEME/assets/css/components/woocommerce-archive.css" | cut -d' ' -f1)"
  test "$SHA_NEW" = c6fa157bb4ad7ab4118ba40ea6922c532013d5721239147aa77ba51c8cb301a4
  wpc theme mod set custom_css_post_id -1 >/dev/null
  wpc post update "$CSS_ID" --post_status=draft >/dev/null
  test "$(css_mod)" = -1
  test "$(wpc post get "$CSS_ID" --field=post_status)" = draft

  printf '%s\n' '{"status":"STAGING_UPGRADE_PRESERVED_DATA","from":"1.3.92","to":"1.3.96","temporary_css_disabled":true,"production_write":false}' \
    > "$OUT/upgrade-stage.json"
  echo 'RQA_1396_UPGRADE_STAGE_PASS Theme mod/page/menu/product parity and scoped CSS de-duplication'
  exit 0
fi

if [ "$1" = "rollback" ]; then
  test -s "$OUT/ownership-before.json"
  test -s "$OUT/summary.json"
  test "$(wpc theme get aznet-theme --field=version)" = 1.3.96
  CSS_ID="$(cat "$OUT/hotfix-post-id.txt")"
  test "$(css_mod)" = -1

  echo '==> Restore official Theme v1.3.92 and re-enable its old CSS hotfix'
  safe_restore "$BASE_ZIP"
  test "$(wpc theme get aznet-theme --field=version)" = 1.3.92
  wpc post update "$CSS_ID" --post_status=publish >/dev/null
  wpc theme mod set custom_css_post_id "$CSS_ID" >/dev/null
  test "$(css_mod)" = "$CSS_ID"
  snapshot "$OUT/ownership-after-rollback.json"
  cmp -s "$OUT/ownership-before.json" "$OUT/ownership-after-rollback.json"

  curl --fail --silent --show-error --max-time 25 \
    http://127.0.0.1:8080/ > "$OUT/home-after-rollback.html"
  grep -F 'aznet-theme-homepage--curtain-01' "$OUT/home-after-rollback.html" >/dev/null
  grep -F 'AZnet Theme RQA scoped Woo archive grid hotfix' "$OUT/home-after-rollback.html" >/dev/null
  grep -F 'wp-custom-css' "$OUT/home-after-rollback.html" >/dev/null

  printf '%s\n' '{"status":"STAGING_ROLLBACK_PRESERVED_DATA","from":"1.3.96","to":"1.3.92","temporary_css_restored":true,"production_write":false}' \
    > "$OUT/rollback-stage.json"
  echo 'RQA_1396_ROLLBACK_STAGE_PASS exact Theme restore and ownership snapshot parity'
  exit 0
fi

echo "Usage: $0 upgrade|rollback" >&2
exit 2
