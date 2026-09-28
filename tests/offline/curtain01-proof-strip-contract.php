<?php
/** Curtain 01 quick-proof strip contract. */
declare(strict_types=1);

if (! defined('ABSPATH')) { define('ABSPATH', __DIR__ . '/'); }

$root = dirname(__DIR__, 2);
require_once $root . '/inc/theme/settings.php';

$defaults = AZnet\Theme\settings_defaults();
if (! array_key_exists('homepage_proof_block', $defaults) || 0 !== $defaults['homepage_proof_block']) {
    fwrite(STDERR, "FAIL: homepage_proof_block default must exist and be 0.\n");
    exit(1);
}

$normalized = AZnet\Theme\normalize_settings(['homepage_proof_block' => '991']);
if (991 !== ($normalized['homepage_proof_block'] ?? null)) {
    fwrite(STDERR, "FAIL: homepage_proof_block must normalize as a positive WordPress post ID.\n");
    exit(1);
}

$invalid = AZnet\Theme\normalize_settings(['homepage_proof_block' => '-2']);
if (0 !== ($invalid['homepage_proof_block'] ?? null)) {
    fwrite(STDERR, "FAIL: invalid homepage_proof_block must normalize to 0.\n");
    exit(1);
}

$composer = file_get_contents($root . '/inc/theme/homepage-composer.php');
$surface = file_get_contents($root . '/inc/theme/homepage-surface-map.php');
$template_path = $root . '/template-parts/homepage/curtain-01/proof-strip.php';
$css = file_get_contents($root . '/assets/css/components/homepage-curtain-01.css');
$admin = file_get_contents($root . '/inc/admin/homepage.php');

if (! is_string($composer) || ! str_contains($composer, "homepage_effective_surface_map( 'curtain-01' )")) {
    fwrite(STDERR, "FAIL: Curtain 01 composer must consume the shared effective surface map.\n");
    exit(1);
}

if (! is_string($surface)) {
    fwrite(STDERR, "FAIL: Curtain 01 shared surface model is missing.\n");
    exit(1);
}

$curtain_start = strpos($surface, 'function homepage_curtain01_effective_surface_map(');
$curtain_end = strpos($surface, 'function homepage_effective_surface_map(', false === $curtain_start ? 0 : $curtain_start);
if (false === $curtain_start || false === $curtain_end || $curtain_end <= $curtain_start) {
    fwrite(STDERR, "FAIL: Curtain 01 effective surface-map function boundary is missing.\n");
    exit(1);
}
$curtain_map = substr($surface, $curtain_start, $curtain_end - $curtain_start);
$hero_pos = strpos($curtain_map, "'hero'");
$proof_pos = strpos($curtain_map, "'proof'");
if (false === $hero_pos || false === $proof_pos || $proof_pos <= $hero_pos) {
    fwrite(STDERR, "FAIL: shared Curtain 01 map must place proof after Hero.\n");
    exit(1);
}

if (! is_file($template_path)) {
    fwrite(STDERR, "FAIL: Curtain 01 proof-strip template is missing.\n");
    exit(1);
}

$template = file_get_contents($template_path);
foreach (["homepage_effective_source_value( 'curtain-01', 'proof' )", 'homepage_block_reference', 'do_blocks'] as $needle) {
    if (! is_string($template) || ! str_contains($template, $needle)) {
        fwrite(STDERR, "FAIL: proof strip must consume a WordPress-owned synced block: {$needle}\n");
        exit(1);
    }
}

foreach ([
    '2 thế hệ tiếp nối nghề rèm',
    'Tư vấn — sản xuất — thi công',
    'Không gian sống & công trình chuyên nghiệp',
    'Đồng hành từ khảo sát đến hoàn thiện',
] as $copy) {
    if (is_string($template) && str_contains($template, $copy)) {
        fwrite(STDERR, "FAIL: brand proof copy must stay in WordPress content, not Theme PHP.\n");
        exit(1);
    }
}

foreach ([
    '.aznet-theme-curtain01-proof-strip {',
    '.aznet-theme-curtain01-proof-strip__list {',
    'grid-template-columns: repeat(4, minmax(0, 1fr));',
] as $needle) {
    if (! is_string($css) || ! str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: approved proof-strip presentation missing: {$needle}\n");
        exit(1);
    }
}

if (! is_string($admin) || ! str_contains($admin, 'homepage_proof_block') || ! str_contains($admin, 'Bằng chứng nhanh')) {
    fwrite(STDERR, "FAIL: Homepage admin must expose the WordPress source mapping for Bằng chứng nhanh.\n");
    exit(1);
}

echo "PASS: Curtain 01 quick-proof strip contract\n";
