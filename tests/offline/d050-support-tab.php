<?php
/**
 * D-050 Support placement: visible Theme Support tab, hidden operator-only danger control.
 * Source-only regression. The existing D-050 role and POST tests prove server behavior.
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$center = @file_get_contents($root . '/inc/admin/control-center.php');
$zone = @file_get_contents($root . '/inc/admin/danger-zone.php');
$bootstrap = @file_get_contents($root . '/inc/admin/bootstrap.php');

function fail_d050_support(string $reason): never {
    fwrite(STDERR, "FAIL D-050 Support: {$reason}\n");
    exit(1);
}
if (!is_string($center) || !is_string($zone) || !is_string($bootstrap)) {
    fail_d050_support('missing exact Theme source');
}
if (!preg_match('/function control_center_section\(\): string\s*\{\s*\$allowed\s*=\s*\[(.*?)\];/s', $center, $allowed)
    || !str_contains($allowed[1], "'support'")) {
    fail_d050_support('Support section missing from Theme route allowlist');
}
if (1 !== substr_count($center, "\$tabs['support'] = 'Support';")
    || 1 !== substr_count($center, "\$tabs['system-health'] = 'System Health';")
    || !str_contains($center, 'if ( danger_zone_is_authorized() ) {')) {
    fail_d050_support('Support and System Health must appear under the operator gate for both WooCommerce modes');
}
$overview = strpos($center, "if ( 'overview' === \$section ) {");
$support = strpos($center, "} elseif ( 'support' === \$section ) {");
$after = false === $support ? false : strpos($center, "} elseif ( 'footer' === \$section ) {", $support);
if (false === $overview || false === $support || false === $after || $overview >= $support) {
    fail_d050_support('dedicated Support section not composed after Overview');
}
$overview_source = substr($center, $overview, $support - $overview);
$support_source = substr($center, $support, $after - $support);
if (str_contains($overview_source, 'render_danger_zone_entry_link();')) {
    fail_d050_support('privileged entry must no longer appear on Overview');
}
if (!str_contains($support_source, 'render_danger_zone_entry_link();')) {
    fail_d050_support('Support must compose existing privileged entry helper');
}
if (!str_contains($support_source, "'section' => 'system-health'")) {
    fail_d050_support('Support must link to existing diagnostics without duplicating report logic');
}
if (str_contains($center, "'danger-zone' =>") || str_contains($bootstrap, 'add_submenu_page(')) {
    fail_d050_support('do not expose Danger Zone as an ordinary menu or make a separate WP admin UI');
}
if (!str_contains($center, "if ( 'danger-zone' === \$section && ! danger_zone_is_authorized() )")
    || !str_contains($zone, "if ( ! danger_zone_is_authorized() )")
    || !str_contains($zone, "'section' => 'danger-zone'")) {
    fail_d050_support('do not weaken existing server authorization or Site Guard return route');
}
if (str_contains($support_source, 'render_site_guard_admin_controls();')
    || str_contains($support_source, 'aznet_site_guard_operator')
    || str_contains($support_source, 'admin_post_')) {
    fail_d050_support('Support is navigation only, not independent security enforcement');
}
echo "PASS: Theme Support tab + privileged-only Danger entry, no contract or site-policy takeover\n";
