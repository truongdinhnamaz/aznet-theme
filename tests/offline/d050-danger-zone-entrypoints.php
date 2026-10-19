<?php
/** D-050: direct form requests cannot bypass Theme-only privileged gate. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$bootstrap = file_get_contents($root . '/inc/admin/bootstrap.php');
$center = file_get_contents($root . '/inc/admin/control-center.php');
$reset = file_get_contents($root . '/inc/admin/settings-actions.php');
$port = file_get_contents($root . '/inc/admin/settings-portability.php');
$zone = @file_get_contents($root . '/inc/admin/danger-zone.php');
function fail_d050(string $msg): never { fwrite(STDERR, "FAIL: {$msg}\n"); exit(1); }
foreach ([$bootstrap, $center, $reset, $port, $zone] as $content) {
    if (!is_string($content) || $content === '') fail_d050('missing a required Theme-only Danger Zone source');
}
foreach ([
    ["danger-zone bootstrap", $bootstrap, "require_once __DIR__ . '/danger-zone.php'"],
    ["unlock handler registered", $bootstrap, 'admin_post_aznet_theme_danger_unlock'],
    ["lock handler registered", $bootstrap, 'admin_post_aznet_theme_danger_lock'],
    ["danger section", $center, "'danger-zone'"],
    ["danger gate", $center, 'danger_zone_is_authorized()'],
    ["danger render", $center, 'render_danger_zone_screen()'],
    ["reset POST authorization", $reset, "danger_zone_require_unlocked( 'reset' )"],
    ["import POST authorization", $port, "danger_zone_require_unlocked( 'import' )"],
    ["reset consumes unlock", $reset, "danger_zone_complete_action( 'reset' )"],
    ["import consumes unlock", $port, "danger_zone_complete_action( 'import' )"],
] as [$name, $source, $needle]) {
    if (!str_contains($source, $needle)) fail_d050("missing {$name}");
}
if (str_contains($center, "'danger-zone' => '") || str_contains($center, "'danger-zone' => \"")) {
    fail_d050('Danger Zone must not appear in ordinary navigation tabs');
}
if (str_contains($center, 'value="aznet_theme_reset_settings"') || str_contains($center, 'value="aznet_theme_import_settings"')) {
    fail_d050('dangerous Theme forms still exposed on Control Center Overview');
}
foreach (['DISALLOW_FILE_MODS', 'switch_theme(', 'delete_theme(', "update_option( 'show_on_front'", "update_option( 'page_on_front'"] as $forbidden) {
    if (str_contains($zone, $forbidden)) fail_d050('Theme must not take over site-level policy: ' . $forbidden);
}
// D-050C moved the actual Site Guard absence notice to an optional public
// projection consumer; retain the original fail-soft requirement at the
// composed UI boundary instead of pinning text inside danger-zone.php.
$consumer = @file_get_contents($root . '/inc/admin/site-guard-consumer.php');
if (!is_string($consumer)
    || !str_contains($bootstrap, "require_once __DIR__ . '/site-guard-consumer.php';")
    || !str_contains($zone, 'render_site_guard_admin_controls();')
    || !str_contains($consumer, 'CHƯA XÁC MINH')
    || !str_contains($consumer, "apply_filters( 'aznet_site_guard/v1/admin_projection', null )")
) {
    fail_d050('without real site guard the composed Theme Danger Zone must warn and fail soft');
}
echo "PASS: D-050 privileged UI, direct POST boundaries, no false site-lock or WordPress ownership takeover\n";
