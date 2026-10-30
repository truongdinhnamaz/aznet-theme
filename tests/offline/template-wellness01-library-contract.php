<?php
/** Wellness 01 must be registered in the same manifest-driven Homepage library as Digital 01. */
declare(strict_types=1);
$theme = dirname(__DIR__, 2);
if (! defined('ABSPATH')) { define('ABSPATH', $theme . '/'); }
function __(string $text, string $domain = ''): string { return $text; }
function sanitize_title(string $text): string { return trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($text)), '-'); }
function esc_html(string $text): string { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
function esc_attr(string $text): string { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
function esc_html__(string $text, string $domain = ''): string { return esc_html($text); }
function esc_attr__(string $text, string $domain = ''): string { return esc_attr($text); }
function checked(mixed $actual, mixed $expected, bool $echo = true): string { return $actual === $expected ? 'checked="checked"' : ''; }
require_once $theme . '/inc/theme/template-registry.php';
require_once $theme . '/inc/theme/template-loader.php';
require_once $theme . '/inc/theme/settings.php';
require_once $theme . '/inc/admin/homepage.php';

function wellness_check(bool $ok, string $message): void {
    $GLOBALS['wellness_check_count'] = ($GLOBALS['wellness_check_count'] ?? 0) + 1;
    if (! $ok) { fwrite(STDERR, "FAIL: $message\n"); exit(1); }
}
$store =& \AZnet\Theme\template_manifest_store();
$store = [];
$summary = \AZnet\Theme\load_local_template_manifests();
wellness_check([] === $summary['rejected'], 'registered template manifests must load without rejection');
wellness_check(in_array('wellness-01', $summary['loaded'], true), 'Wellness 01 manifest missing from template loader');
$manifest = \AZnet\Theme\template_manifest('wellness-01');
wellness_check(is_array($manifest), 'Wellness 01 is not registered in canonical template registry');
wellness_check(($manifest['name'] ?? null) === 'Wellness 01', 'Wellness 01 name not owned by its manifest');
wellness_check(in_array('homepage', $manifest['capabilities'] ?? [], true), 'Homepage capability missing');
wellness_check(! in_array('woocommerce', $manifest['capabilities'] ?? [], true), 'Wellness 01 must not depend on WooCommerce');
wellness_check(($manifest['contract_version'] ?? null) === 2, 'Manifest v2 required for native WordPress page mode');
wellness_check(($manifest['homepage']['composition']['mode'] ?? null) === 'native', 'Wellness 01 must consume WordPress native Front Page body');
wellness_check(($manifest['presentation']['homepage_preset'] ?? null) === 'wellness-01', 'Homepage preset ID mismatched');
wellness_check(in_array('wellness-01', \AZnet\Theme\homepage_preset_ids(), true), 'Settings registry must allow selecting Wellness 01');
wellness_check(($manifest['homepage']['authoring']['sections'] ?? []) === ['content'], 'Native Homepage must edit WordPress page body only');
wellness_check(($manifest['provisioning'] ?? []) === [], 'Do not silently provision or mutate WordPress content');
$items = \AZnet\Theme\Admin\homepage_template_library_items();
$matches = array_values(array_filter($items, static fn(array $item): bool => ($item['template_id'] ?? '') === 'wellness-01'));
wellness_check(count($matches) === 1, 'Control Center library must show Wellness 01 exactly once');
wellness_check(($matches[0]['install_state'] ?? null) === 'installed', 'Installed state is incorrect');
wellness_check(($matches[0]['name'] ?? null) === 'Wellness 01', 'Admin title must come from manifest');
wellness_check(($matches[0]['category_name'] ?? '') !== '', 'Filter category missing in Control Center');
ob_start();
\AZnet\Theme\Admin\render_homepage_template_library('off');
$html = (string) ob_get_clean();
wellness_check(str_contains($html, '>Wellness 01</strong>'), 'Control Center must render Wellness 01 card');
wellness_check(str_contains($html, 'value=\"wellness-01\"') || str_contains($html, 'value="wellness-01"'), 'Control Center must permit Wellness 01 selection');
wellness_check(substr_count($html, 'aznet-theme-template-library__card"') === 5, 'Template Library must show five selectable cards');
$assets = (array) ($manifest['assets']['homepage'] ?? []);
wellness_check(count($assets) === 1 && ($assets[0]['type'] ?? null) === 'style', 'Expected one surface-aware stylesheet');
wellness_check(($assets[0]['path'] ?? null) === '/assets/css/components/homepage-wellness-01.css', 'Stylesheet descriptor must be scoped to Wellness');
$cssPath = $theme . $assets[0]['path'];
wellness_check(is_file($cssPath), 'Manifest references a missing stylesheet');
$css = file_get_contents($cssPath);
wellness_check(is_string($css) && str_contains($css, '.aznet-theme-wellness01__hero'), 'Wellness-owned Hero CSS missing');
wellness_check(str_contains($css, 'max-width: 767px'), 'Mobile scoped layout contract missing');
$controlCenterCss = (string) file_get_contents($theme . '/assets/css/admin/control-center.css');
wellness_check(str_contains($controlCenterCss, '.aznet-theme-template-library__preview--wellness-01'), 'Wellness card lacks visual identification');
wellness_check(is_file($theme . '/patterns/homepage-wellness-01.php'), 'Native WordPress pattern must be retained');
foreach (['group', 'guide', 'meditation', 'mobility'] as $image) {
    wellness_check(is_file($theme . '/assets/starter/wellness01/' . $image . '.webp'), "Missing starter WebP image: $image");
}
echo 'PASS: Wellness 01 registered in Control Center + native Front Page (' . $GLOBALS['wellness_check_count'] . ' checks)' . "\n";
