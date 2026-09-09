<?php
declare(strict_types=1);
$root = dirname(__DIR__, 2);
$css = $root . '/assets/css/components/homepage-law-01.css';
if (! is_file($css)) { fwrite(STDERR, "FAIL: Law 01 stylesheet missing\n"); exit(1); }
$cssSource = file_get_contents($css);
assert(str_contains($cssSource, '.aznet-theme-homepage--law-01'));
assert(str_contains($cssSource, '--law01-gold-text: #8a632b;'), 'Light-surface eyebrow color must retain WCAG contrast on white/cream.');
assert(str_contains($cssSource, '.aznet-theme-law01-analysis .aznet-theme-law01-meta { color: #b8c5d3; }'), 'Analysis metadata must use a light contrast-safe color on the dark card surface.');
assert(str_contains($cssSource, '.aznet-theme-law01-hero .aznet-theme-law01-eyebrow,'), 'Dark-surface eyebrow override must retain the brighter gold role.');

$baseCss = file_get_contents($root . '/style.css');
assert(str_contains($baseCss, '.aznet-theme-main {'));
assert(str_contains($baseCss, 'var(--aznet-theme-container-wide)'), 'Generic Theme main must remain constrained outside Law 01.');
foreach ([
    '.aznet-theme-main--front-page',
    'width: 100%;',
    'max-width: none;',
    'margin-inline: 0;',
    'padding-block: 0;',
] as $needle) {
    assert(str_contains($cssSource, $needle), "Law 01 full-width surface contract missing: {$needle}");
}
assert(str_contains($cssSource, '.aznet-theme-homepage--law-01 .aznet-theme-law01-container'));
assert(str_contains($cssSource, 'var(--aznet-theme-container-shell)'), 'Law 01 inner content must remain constrained while section surfaces go full width.');

$assets = file_get_contents($root . '/inc/theme/assets.php');
assert(str_contains($assets, 'homepage-law-01.css'));
assert(str_contains($assets, "'law-01'"));
assert(str_contains($assets, 'homepage_composer_active()'));
assert(str_contains($assets, 'function asset_content_version('), 'Theme must provide content-derived asset cache busting for changed scoped assets.');
assert(str_contains($assets, "asset_content_version( '/assets/css/components/homepage-law-01.css', \$version )"), 'Law 01 stylesheet URL must change when its bytes change even if Theme metadata version is unchanged.');
assert(str_contains($assets, "hash_file( 'sha256', \$path )"), 'Law 01 cache key must derive from file bytes rather than only the Theme version.');

$composer = file_get_contents($root . '/inc/theme/homepage-composer.php');
$order = ['hero','services','about','team','topics','latest','analysis','news','process','faq','final-cta'];
foreach ($order as $slug) {
    $path = $root . '/template-parts/homepage/law-01/' . $slug . '.php';
    assert(is_file($path), "Missing Law 01 section {$slug}");
}
$all = '';
foreach (glob($root . '/template-parts/homepage/law-01/*.php') as $file) { $all .= file_get_contents($file); }
foreach (['500+', '98%', '1.000+', '1000+', 'Nguyễn Văn A', 'testimonial', 'certification'] as $forbidden) {
    assert(! str_contains($all, $forbidden), "Fabricated default found: {$forbidden}");
}
assert(str_contains($all, 'homepage_direct_published_children'));
assert(str_contains($all, 'homepage_latest_posts'));
assert(str_contains($all, 'homepage_ledger_ids'));
assert(str_contains($all, 'homepage_ledger_add'));
assert(str_contains($all, 'contact_surface_model'));
echo "PASS: Law 01 presentation contract\n";
