<?php
/**
 * D-050 customer/developer separation: internal Theme operations require a
 * per-user operator capability; ordinary Administrator retains public site UX.
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$center = (string) file_get_contents($root . '/inc/admin/control-center.php');
$handoff = (string) file_get_contents($root . '/inc/admin/project-handoff.php');
$bootstrap = (string) file_get_contents($root . '/inc/admin/bootstrap.php');

function fail_operator_boundary(string $message): never {
    fwrite(STDERR, "FAIL D-050 operator/customer boundary: {$message}\n");
    exit(1);
}

if (!str_contains($center, "if ( 'danger-zone' === \$section && ! danger_zone_is_authorized() )")) {
    fail_operator_boundary('existing Danger Zone server gate was removed');
}
if (!str_contains($center, "in_array( \$section, [ 'support', 'system-health' ], true ) && ! danger_zone_is_authorized()")) {
    fail_operator_boundary('developer Support and System Health must be server-denied to unprivileged administrators');
}
$navStart = strpos($center, '$tabs = [ \'overview\'');
$navEnd = strpos($center, '$center_class =', (int) $navStart);
if (false === $navStart || false === $navEnd || $navStart >= $navEnd) {
    fail_operator_boundary('cannot locate Theme navigation assembly');
}
$nav = substr($center, $navStart, $navEnd - $navStart);
if (!str_contains($nav, 'if ( danger_zone_is_authorized() ) {')
    || !str_contains($nav, "\$tabs['support'] = 'Support';")
    || !str_contains($nav, "\$tabs['system-health'] = 'System Health';")) {
    fail_operator_boundary('private developer tabs must be appended only under explicit per-user authorization');
}
foreach (["'overview' => 'Tổng quan'", "'header' => 'Header'", "'footer' => 'Footer'", "'homepage' => 'Trang chủ'", "'provisioning' => 'Thiết lập nhanh'", "'commerce' => 'Commerce'"] as $publicTab) {
    if (!str_contains($nav, $publicTab)) {
        fail_operator_boundary('customer site presentation tab disappeared: ' . $publicTab);
    }
}
$overview = strpos($center, "if ( 'overview' === \$section ) {");
$support = strpos($center, "} elseif ( 'support' === \$section ) {");
$footer = false === $support ? false : strpos($center, "} elseif ( 'footer' === \$section ) {", $support);
if (false === $overview || false === $support || false === $footer || $overview >= $support || $support >= $footer) {
    fail_operator_boundary('existing Support composition/route missing');
}
$overviewSource = substr($center, $overview, $support - $overview);
$supportSource = substr($center, $support, $footer - $support);
if (str_contains($overviewSource, 'Project Handoff') || str_contains($overviewSource, 'aznet_theme_export_handoff')) {
    fail_operator_boundary('developer handoff is exposed on customer Overview');
}
if (!str_contains($supportSource, 'Project Handoff')
    || !str_contains($supportSource, 'aznet_theme_export_handoff')
    || !str_contains($supportSource, 'render_danger_zone_entry_link();')
    || !str_contains($supportSource, "'section' => 'system-health'")) {
    fail_operator_boundary('developer Support must retain owner-only handoff, diagnostics and Danger entry');
}
if (!str_contains($handoff, "require_settings_capability();\n    danger_zone_require_authorized();\n    check_admin_referer(")) {
    fail_operator_boundary('handoff POST must check the developer capability server-side before nonce/export');
}
if (!str_contains($bootstrap, "'edit_theme_options',") || str_contains($center, 'truongdinhnam')) {
    fail_operator_boundary('must preserve customer Theme presentation access and never hardcode operator login');
}
echo "PASS: D-050 developer tools hidden and denied to client admins, customer presentation retained\n";
