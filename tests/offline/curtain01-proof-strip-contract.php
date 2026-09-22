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
$template_path = $root . '/template-parts/homepage/curtain-01/proof-strip.php';
$css = file_get_contents($root . '/assets/css/components/homepage-curtain-01.css');
$admin = file_get_contents($root . '/inc/admin/homepage.php');

if (! is_string($composer) || ! str_contains($composer, "render_curtain01_part( 'proof-strip' )")) {
    fwrite(STDERR, "FAIL: Curtain 01 proof strip must render immediately after the Hero.\n");
    exit(1);
}

$hero_pos = strpos($composer, "render_curtain01_part( 'hero' )");
$proof_pos = strpos($composer, "render_curtain01_part( 'proof-strip' )");
if (false === $hero_pos || false === $proof_pos || $proof_pos <= $hero_pos) {
    fwrite(STDERR, "FAIL: proof strip ordering must follow the Curtain 01 Hero.\n");
    exit(1);
}

if (! is_file($template_path)) {
    fwrite(STDERR, "FAIL: Curtain 01 proof-strip template is missing.\n");
    exit(1);
}

$template = file_get_contents($template_path);
foreach (["setting( 'homepage_proof_block', 0 )", 'homepage_block_reference', 'do_blocks'] as $needle) {
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
