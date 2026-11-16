<?php
/** Offline TDD contract for native WordPress logo continuity. */
declare(strict_types=1);
define('ABSPATH', '/wordpress/');
class WP_Theme {
    private string $stylesheet;
    public function __construct(string $stylesheet) { $this->stylesheet = $stylesheet; }
    public function get_stylesheet(): string { return $this->stylesheet; }
}
$GLOBALS['mods'] = [];
$GLOBALS['opts'] = [];
$GLOBALS['images'] = [];
$GLOBALS['html'] = [];
$GLOBALS['writes'] = [];
$GLOBALS['hooks'] = [];
$GLOBALS['screen'] = 'toplevel_page_aznet-theme';
$GLOBALS['can_edit'] = true;
function add_action(string $hook, $callback, int $priority = 10, int $accepted_args = 1): void {
    $GLOBALS['hooks'][$hook] = ['callback' => $callback, 'args' => $accepted_args];
}
function get_theme_mod(string $name, $default = false) { return $GLOBALS['mods'][$name] ?? $default; }
function set_theme_mod(string $name, $value): void { $GLOBALS['mods'][$name] = $value; $GLOBALS['writes'][] = [$name, $value]; }
function get_option(string $key, $default = false) { return $GLOBALS['opts'][$key] ?? $default; }
function wp_attachment_is_image(int $id): bool { return (bool) ($GLOBALS['images'][$id] ?? false); }
function wp_get_attachment_image(int $id, string $size = 'full') { return $GLOBALS['html'][$id] ?? ''; }
function has_custom_logo(): bool { return (int) get_theme_mod('custom_logo', 0) > 0; }
function current_user_can(string $cap): bool { return $GLOBALS['can_edit']; }
function get_current_screen() { return (object) ['id' => $GLOBALS['screen']]; }
function admin_url(string $path = ''): string { return '/wp-admin/' . $path; }
function esc_url(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function esc_html__(string $value, string $domain): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function esc_html(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function reset_fixture(): void {
    $GLOBALS['mods'] = []; $GLOBALS['opts'] = []; $GLOBALS['images'] = [];
    $GLOBALS['html'] = []; $GLOBALS['writes'] = [];
    $GLOBALS['screen'] = 'toplevel_page_aznet-theme'; $GLOBALS['can_edit'] = true;
}
function expect_true(bool $value, string $message): void {
    if (!$value) { throw new RuntimeException($message); }
}
require __DIR__ . '/../../inc/theme/logo-continuity.php';
expect_true(isset($GLOBALS['hooks']['after_switch_theme']), 'hook absent');
expect_true($GLOBALS['hooks']['after_switch_theme']['args'] === 2, 'must accept old theme');
expect_true(isset($GLOBALS['hooks']['admin_notices']), 'admin notice hook absent');
reset_fixture();
$GLOBALS['opts']['theme_mods_legacy-theme'] = ['custom_logo' => 42];
$GLOBALS['images'][42] = true; $GLOBALS['html'][42] = '<img src="logo.png">';
\AZnet\Theme\preserve_native_custom_logo('Legacy Theme', new WP_Theme('legacy-theme'));
expect_true((int) get_theme_mod('custom_logo') === 42, 'valid old native logo not retained');
expect_true(count($GLOBALS['writes']) === 1, 'unexpected number of writes');
reset_fixture();
$GLOBALS['mods']['custom_logo'] = 123;
$GLOBALS['opts']['theme_mods_legacy-theme'] = ['custom_logo' => 42];
$GLOBALS['images'][42] = true; $GLOBALS['html'][42] = '<img>';
\AZnet\Theme\preserve_native_custom_logo('Legacy Theme', new WP_Theme('legacy-theme'));
expect_true((int) get_theme_mod('custom_logo') === 123, 'current logo overwritten');
expect_true([] === $GLOBALS['writes'], 'existing logo triggered write');
reset_fixture();
$GLOBALS['opts']['theme_mods_legacy-theme'] = ['custom_logo' => 99];
$GLOBALS['images'][99] = false;
\AZnet\Theme\preserve_native_custom_logo('Legacy Theme', new WP_Theme('legacy-theme'));
expect_true([] === $GLOBALS['writes'], 'invalid image imported');
reset_fixture();
$GLOBALS['opts']['theme_mods_legacy-theme'] = ['legacy_logo_url' => 'https://example.com/sample.png'];
\AZnet\Theme\preserve_native_custom_logo('Legacy Theme', new WP_Theme('legacy-theme'));
expect_true([] === $GLOBALS['writes'], 'legacy/private logo guessed');
reset_fixture();
$GLOBALS['opts']['theme_mods_legacy-theme'] = ['custom_logo' => 42];
$GLOBALS['images'][42] = true; $GLOBALS['html'][42] = '<img>';
\AZnet\Theme\preserve_native_custom_logo('Legacy Theme', new WP_Theme('../legacy-theme'));
expect_true([] === $GLOBALS['writes'], 'invalid stylesheet read');
reset_fixture();
ob_start(); \AZnet\Theme\logo_configuration_notice(); $out = ob_get_clean();
expect_true(str_contains($out, 'custom_logo'), 'missing-logo notice link absent');
reset_fixture();
$GLOBALS['mods']['custom_logo'] = 42;
$GLOBALS['images'][42] = true; $GLOBALS['html'][42] = '<img>';
ob_start(); \AZnet\Theme\logo_configuration_notice(); $out = ob_get_clean();
expect_true('' === $out, 'valid-logo notice not suppressed');
reset_fixture();
$GLOBALS['can_edit'] = false;
ob_start(); \AZnet\Theme\logo_configuration_notice(); $out = ob_get_clean();
expect_true('' === $out, 'permission guard failed');
reset_fixture();
$GLOBALS['screen'] = 'edit-post';
ob_start(); \AZnet\Theme\logo_configuration_notice(); $out = ob_get_clean();
expect_true('' === $out, 'notice escaped theme admin');
reset_fixture();
$GLOBALS['mods']['custom_logo'] = 77;
$GLOBALS['images'][77] = true; $GLOBALS['html'][77] = '';
ob_start(); \AZnet\Theme\logo_configuration_notice(); $out = ob_get_clean();
expect_true(str_contains($out, 'custom_logo'), 'unrenderable logo unreported');
echo "PASS: WordPress native custom_logo continuity\n";
