<?php
/**
 * Y3 Footer System 2.0 ownership, settings and presentation contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function y3_fail(string $message): never {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

$footerHelperPath = $root . '/inc/theme/footer.php';
if (! is_file($footerHelperPath)) {
    y3_fail('missing inc/theme/footer.php');
}

$bootstrap = file_get_contents($root . '/inc/theme/bootstrap.php');
if (false === $bootstrap || ! str_contains($bootstrap, "require_once __DIR__ . '/footer.php';")) {
    y3_fail('bootstrap must load footer.php');
}

if (! defined('ABSPATH')) {
    define('ABSPATH', $root . '/');
}

$GLOBALS['y3_theme_mods'] = [
    'aznet_theme_settings' => ['footer_preset' => 'professional'],
    'custom_logo'          => 42,
];
$GLOBALS['y3_menu_html'] = [
    'footer'         => '<ul class="aznet-theme-site-footer__menu"><li><a href="/about/">About sentinel</a></li></ul>',
    'footer-contact' => '<ul class="aznet-theme-site-footer__contact-menu"><li><a href="/contact/">Contact sentinel</a></li></ul>',
    'footer-social'  => '<ul class="aznet-theme-site-footer__social-menu"><li><a href="/social/">Social sentinel</a></li></ul>',
    'footer-policy'  => '<ul class="aznet-theme-site-footer__policy-menu"><li><a href="/policy/">Policy sentinel</a></li></ul>',
];

if (! function_exists('get_theme_mod')) {
    function get_theme_mod(string $name, mixed $default = false): mixed {
        return $GLOBALS['y3_theme_mods'][$name] ?? $default;
    }
}
if (! function_exists('get_bloginfo')) {
    function get_bloginfo(string $show = ''): string {
        return 'description' === $show ? 'Verified tagline sentinel' : 'AZnet Y3 Sentinel';
    }
}
if (! function_exists('home_url')) {
    function home_url(string $path = ''): string {
        return 'https://example.test' . ('/' === $path ? '/' : $path);
    }
}
if (! function_exists('wp_get_attachment_image')) {
    function wp_get_attachment_image(int $attachment_id, string|array $size = 'thumbnail', bool $icon = false, array $attr = []): string {
        return 42 === $attachment_id ? '<img class="aznet-theme-site-footer__logo" src="logo.jpg" alt="">' : '';
    }
}
if (! function_exists('wp_nav_menu')) {
    function wp_nav_menu(array $args = []): string {
        $location = isset($args['theme_location']) ? (string) $args['theme_location'] : '';
        return (string) ($GLOBALS['y3_menu_html'][$location] ?? '');
    }
}
if (! function_exists('wp_date')) {
    function wp_date(string $format): string {
        return 'Y' === $format ? '2026' : '';
    }
}

require_once $root . '/inc/theme/settings.php';
require_once $footerHelperPath;

$defaults = \AZnet\Theme\settings_defaults();
if (($defaults['footer_preset'] ?? null) !== 'standard') {
    y3_fail('footer_preset default must be standard');
}

foreach (['standard', 'professional', 'compact'] as $preset) {
    $normalized = \AZnet\Theme\normalize_settings(['footer_preset' => $preset]);
    if (($normalized['footer_preset'] ?? null) !== $preset) {
        y3_fail('valid footer_preset was not preserved: ' . $preset);
    }
}

$invalid = \AZnet\Theme\normalize_settings([
    'footer_preset' => 'domain-owned-footer',
    'foreign_state' => 'must-drop',
]);
if (($invalid['footer_preset'] ?? null) !== 'standard') {
    y3_fail('invalid footer_preset must fail soft to standard');
}
if (array_key_exists('foreign_state', $invalid)) {
    y3_fail('foreign setting escaped the Theme allow-list');
}

if (! function_exists('AZnet\\Theme\\footer_preset')) {
    y3_fail('footer_preset() missing');
}
if (! function_exists('AZnet\\Theme\\footer_context')) {
    y3_fail('footer_context() missing');
}
if ('professional' !== \AZnet\Theme\footer_preset()) {
    y3_fail('footer_preset() must read the normalized Theme setting');
}

$context = \AZnet\Theme\footer_context();
$expectedContextKeys = ['preset', 'site_title', 'tagline', 'home_url', 'logo_html', 'menus', 'year'];
if ($expectedContextKeys !== array_keys($context)) {
    y3_fail('footer_context() shape changed');
}
if ('professional' !== $context['preset']) {
    y3_fail('footer_context() must expose normalized preset');
}
if ('AZnet Y3 Sentinel' !== $context['site_title'] || 'Verified tagline sentinel' !== $context['tagline']) {
    y3_fail('footer_context() must consume WordPress-native site identity');
}
if ('https://example.test/' !== $context['home_url']) {
    y3_fail('footer_context() home URL mismatch');
}
if (! str_contains($context['logo_html'], 'aznet-theme-site-footer__logo')) {
    y3_fail('footer_context() must render the WordPress custom logo when available');
}
if ('2026' !== $context['year']) {
    y3_fail('footer_context() must use WordPress date context');
}

$expectedMenuKeys = ['footer', 'footer-contact', 'footer-social', 'footer-policy'];
if (! is_array($context['menus']) || $expectedMenuKeys !== array_keys($context['menus'])) {
    y3_fail('footer_context() must expose only the four registered Footer menu locations');
}
foreach ($expectedMenuKeys as $location) {
    if (! str_contains((string) $context['menus'][$location], 'sentinel')) {
        y3_fail('footer_context() did not consume WordPress menu location ' . $location);
    }
}

$originalMenus = $GLOBALS['y3_menu_html'];
foreach ($expectedMenuKeys as $location) {
    $GLOBALS['y3_menu_html'] = $originalMenus;
    $GLOBALS['y3_menu_html'][$location] = '';
    $emptyContext = \AZnet\Theme\footer_context();
    if ('' !== ($emptyContext['menus'][$location] ?? null)) {
        y3_fail('absent Footer menu must fail soft to an empty string: ' . $location);
    }
}
$GLOBALS['y3_menu_html'] = $originalMenus;

$helper = file_get_contents($footerHelperPath);
if (false === $helper) {
    y3_fail('unable to read footer.php');
}
foreach (['get_bloginfo(', 'home_url(', "get_theme_mod( 'custom_logo'", 'wp_get_attachment_image(', 'wp_nav_menu(', 'wp_date('] as $required) {
    if (! str_contains($helper, $required)) {
        y3_fail('Footer context helper missing approved WordPress source: ' . $required);
    }
}
foreach (['get_option(', 'get_post_meta(', '$wpdb', 'WP_Query', 'query_posts(', 'get_posts(', 'wp_insert_post(', 'register_post_type(', 'choiceguide_', 'rootprofile_'] as $forbidden) {
    if (str_contains($helper, $forbidden)) {
        y3_fail('Footer helper contains forbidden ownership/storage marker: ' . $forbidden);
    }
}

$setup = file_get_contents($root . '/inc/theme/setup.php');
if (false === $setup) {
    y3_fail('unable to read setup.php');
}
foreach ($expectedMenuKeys as $location) {
    if (! str_contains($setup, "'{$location}'")) {
        y3_fail('registered Footer menu location missing: ' . $location);
    }
}

$controlCenter = file_get_contents($root . '/inc/admin/control-center.php');
if (false === $controlCenter) {
    y3_fail('unable to read Control Center');
}
if (! str_contains($controlCenter, "'footer'")) {
    y3_fail('Control Center must expose a Footer section/tab');
}
if (! str_contains($controlCenter, "[ 'footer_preset' ]")) {
    y3_fail('Footer Control Center section must expose only footer_preset');
}
if (! str_contains($controlCenter, "field_select( 'footer_preset'")) {
    y3_fail('Footer Control Center must render footer_preset with field_select()');
}
foreach (['standard', 'professional', 'compact'] as $preset) {
    if (! str_contains($controlCenter, "'{$preset}'")) {
        y3_fail('Footer preset choice missing from Control Center: ' . $preset);
    }
}
if (! str_contains($controlCenter, 'render_hidden_settings( $visible_keys );')) {
    y3_fail('Control Center must preserve unrelated Theme settings');
}

$template = file_get_contents($root . '/template-parts/footer/site-footer.php');
if (false === $template) {
    y3_fail('unable to read Footer template');
}
if (! str_contains($template, 'footer_context()')) {
    y3_fail('Footer template must consume footer_context()');
}
foreach (['get_bloginfo(', 'home_url(', 'get_theme_mod(', 'wp_nav_menu(', 'wp_date('] as $forbidden) {
    if (str_contains($template, $forbidden)) {
        y3_fail('Footer template must not gather data directly: ' . $forbidden);
    }
}
if (1 !== substr_count($template, 'role="contentinfo"')) {
    y3_fail('Footer template must keep exactly one semantic contentinfo region');
}
if (str_contains($template, 'aznet-theme-site-footer--standard"')) {
    y3_fail('Footer template must derive preset class from normalized context instead of hard-coding standard');
}
foreach ($expectedMenuKeys as $location) {
    if (! str_contains($template, "['{$location}']")) {
        y3_fail('Footer template must conditionally consume menu context: ' . $location);
    }
}

$css = file_get_contents($root . '/assets/css/components/site-footer.css');
if (false === $css) {
    y3_fail('unable to read Footer stylesheet');
}
foreach (['.aznet-theme-site-footer--professional', '.aznet-theme-site-footer--compact'] as $selector) {
    if (! str_contains($css, $selector)) {
        y3_fail('Footer stylesheet missing preset selector ' . $selector);
    }
}
if (! str_contains($css, '@media (max-width: 48rem)')) {
    y3_fail('Footer stylesheet must stack all presets at <= 48rem');
}
if (! str_contains($css, ':focus-visible')) {
    y3_fail('Footer stylesheet must retain visible keyboard focus');
}
if (preg_match('/#[0-9a-f]{3,8}\b|rgba?\s*\(/i', $css)) {
    y3_fail('Footer presentation must use existing Theme tokens instead of hard-coded brand colors');
}

$style = file_get_contents($root . '/style.css');
$functions = file_get_contents($root . '/functions.php');
if (false === $style || false === $functions) {
    y3_fail('unable to read Theme version declarations');
}
if (! preg_match('/^Version:\s*([0-9]+\.[0-9]+\.[0-9]+)\s*$/m', $style, $styleVersion)) {
    y3_fail('Y3 retained contract requires a semantic style.css Theme version');
}
if (! preg_match("/define\(\s*'AZNET_THEME_VERSION'\s*,\s*'([0-9]+\.[0-9]+\.[0-9]+)'\s*\)/", $functions, $functionVersion)) {
    y3_fail('Y3 retained contract requires AZNET_THEME_VERSION');
}
if ($styleVersion[1] !== $functionVersion[1]) {
    y3_fail('Theme version declarations must remain consistent');
}

echo "PASS: Y3 Footer System 2.0 ownership/settings/presentation contract\n";