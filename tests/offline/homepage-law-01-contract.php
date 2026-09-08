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
$assets = file_get_contents($root . '/inc/theme/assets.php');
assert(str_contains($assets, 'homepage-law-01.css'));
assert(str_contains($assets, "'law-01'"));
assert(str_contains($assets, 'homepage_composer_active()'));
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
