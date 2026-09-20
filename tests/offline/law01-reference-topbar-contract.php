<?php
declare(strict_types=1);
namespace AZnet\Theme;
define('ABSPATH', __DIR__);
$GLOBALS['variant'] = 'burgundy-gold';
$GLOBALS['tagline'] = 'Source <tagline>';
$GLOBALS['menus'] = ['header-utility' => true, 'footer-social' => true];
$GLOBALS['menu_calls'] = [];
function homepage_law01_variant(): string { return $GLOBALS['variant']; }
function setting($key, $default = null) { return $default; }
function get_bloginfo($key): string { return $GLOBALS['tagline']; }
function has_nav_menu($key): bool { return $GLOBALS['menus'][$key] ?? false; }
function esc_html($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_attr_e($text, $domain = ''): void { echo esc_html($text); }
function wp_nav_menu($options): void { $GLOBALS['menu_calls'][] = $options; echo '<ul id="' . $options['menu_id'] . '"><li><a href="https://example.test/contact/">Source link</a></li></ul>'; }
$path = dirname(__DIR__, 2) . '/template-parts/header/law01-topbar.php';
if (!is_file($path)) { fwrite(STDERR, "FAIL: source-backed Law 01 topbar is missing.\n"); exit(1); }
$render = static function (bool $active) use ($path): string {
    $args = ['law01_homepage' => $active];
    ob_start(); require $path; return (string) ob_get_clean();
};
$html = $render(true);
if (!str_contains($html, 'Source &lt;tagline&gt;') || count($GLOBALS['menu_calls']) !== 2) {
    fwrite(STDERR, "FAIL: topbar must render escaped native tagline and existing menus.\n"); exit(1);
}
$ids = array_column($GLOBALS['menu_calls'], 'menu_id');
if (count(array_unique($ids)) !== 2 || in_array('aznet-theme-law01-hero-contact-menu', $ids, true)) {
    fwrite(STDERR, "FAIL: topbar menu IDs must be distinct from each other and Hero.\n"); exit(1);
}
if ($render(false) !== '') { fwrite(STDERR, "FAIL: non-homepage topbar escaped scope.\n"); exit(1); }
$GLOBALS['variant'] = 'navy-gold';
if ($render(true) !== '') { fwrite(STDERR, "FAIL: other variant must remain unchanged.\n"); exit(1); }
$GLOBALS['variant'] = 'burgundy-gold';
$GLOBALS['tagline'] = ''; $GLOBALS['menus'] = [];
if ($render(true) !== '') { fwrite(STDERR, "FAIL: empty sources must not invent business facts.\n"); exit(1); }
echo "PASS: topbar uses only escaped native sources, unique IDs and empty/scope guards.\n";
