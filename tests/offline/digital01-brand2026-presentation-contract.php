<?php
/**
 * Digital 01 optional 2026 light-brand presentation: opt-in, isolated, WCAG-aware.
 * WordPress owns the HTML, logo and links. Theme owns only presentation.
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$cssPath = $root . '/assets/css/components/homepage-digital-01-brand-2026.css';
$assetPath = $root . '/inc/theme/assets.php';
$fail = static function (string $reason): never {
    fwrite(STDERR, "FAIL: Digital 01 Brand 2026 — {$reason}\n");
    exit(1);
};

if (! is_file($cssPath)) {
    $fail('opt-in stylesheet is missing');
}
$css = (string) file_get_contents($cssPath);
$assets = (string) file_get_contents($assetPath);
if (! str_contains($css, '.aznet-digital01--brand-2026')
    || ! str_contains($css, ':has(.aznet-digital01--brand-2026)')) {
    $fail('all branded styling must be opt-in, including site Header');
}
foreach ([
    '.aznet-digital01__hero-grid',
    '.aznet-digital01__hero-art',
    '.aznet-digital01__laptop',
    '.aznet-digital01__hero-checks',
    '.aznet-digital01__services-grid',
    '.aznet-digital01__service-icon',
    '.aznet-digital01__brand-why',
    '.aznet-digital01__brand-feature-grid',
    ':focus-visible',
    'prefers-reduced-motion',
    'max-width: 900px',
    'max-width: 600px',
] as $required) {
    if (! str_contains($css, $required)) {
        $fail('required screenshot/responsive/accessibility surface absent: ' . $required);
    }
}
if (preg_match('~https?://|/wp-content/uploads/|post_meta|rootprofile|choiceguide_~i', $css)) {
    $fail('Theme CSS must not own site asset URLs or provider/domain state');
}
if (! str_contains($assets, 'function digital01_brand2026_page_active()')
    || ! str_contains($assets, 'function enqueue_digital01_brand2026_asset(')
    || ! str_contains($assets, 'enqueue_digital01_brand2026_asset( $version )')
    || ! str_contains($assets, 'aznet-digital01--brand-2026')
    || ! str_contains($assets, "'aznet-theme-homepage-digital-01-brand-2026'")) {
    $fail('no verified surface-aware native Page opt-in asset path');
}
if (! str_contains($assets, "get_queried_object()")
    || ! str_contains($assets, "get_theme_file_uri( \$path )")) {
    $fail('WordPress-native current Page / Theme asset resolver missing');
}

$hexLuminance = static function (string $hex): float {
    $rgb = array_map(
        static function (string $v): float {
            $x = hexdec($v) / 255;
            return $x <= .04045 ? $x / 12.92 : (($x + .055) / 1.055) ** 2.4;
        },
        str_split(substr($hex, 1), 2)
    );
    return .2126 * $rgb[0] + .7152 * $rgb[1] + .0722 * $rgb[2];
};
$contrast = static function (string $a, string $b) use ($hexLuminance): float {
    $first = $hexLuminance($a);
    $second = $hexLuminance($b);
    return (max($first, $second) + .05) / (min($first, $second) + .05);
};
preg_match('/--d01-brand-action:\s*(#[0-9a-f]{6})/i', $css, $action);
preg_match('/--d01-brand-text:\s*(#[0-9a-f]{6})/i', $css, $text);
preg_match('/--d01-brand-muted:\s*(#[0-9a-f]{6})/i', $css, $muted);
if (empty($action[1]) || empty($text[1]) || empty($muted[1])) {
    $fail('semantic brand color tokens missing');
}
foreach ([
    'white-on-action' => ['#ffffff', $action[1]],
    'action-on-white' => [$action[1], '#ffffff'],
    'primary-body-on-white' => [$text[1], '#ffffff'],
    'muted-on-white' => [$muted[1], '#ffffff'],
] as $name => [$foreground, $background]) {
    if ($contrast($foreground, $background) < 4.5) {
        $fail(sprintf('%s is %.2f:1 below AA 4.5:1', $name, $contrast($foreground, $background)));
    }
}
// A real WordPress utility menu contains nested <ul class="sub-menu"> items.
// Only its final top-level <li> is the consultation CTA: descendant li:last-child
// also matches the last entry of every dropdown and makes it a false red CTA.
if (! str_contains($css, '.aznet-theme-site-header__utility-menu > li:last-child > a')
    || str_contains($css, '.aznet-theme-site-header__utility-menu li:last-child > a')) {
    $fail('Header CTA must target only final top-level menu item, not submenu children');
}
// Only opt-in Brand Light may render the three equal WordPress-authored paths.
foreach (['.aznet-digital01__offer-paths','.aznet-digital01__offer-inner',
          '.aznet-digital01__offer-grid','.aznet-digital01__offer-card',
          '.aznet-digital01__offer-kind','.aznet-digital01__offer-links',
          '.aznet-digital01__offer-more'] as $selector) {
    if (! str_contains($css, $selector)) {
        $fail('missing three-path presentation selector ' . $selector);
    }
}
if (! str_contains($css, 'grid-template-columns:repeat(3,minmax(0,1fr))')) {
    $fail('three commercial paths must use three equal desktop columns');
}
if (! preg_match('~@media\s*\(max-width:\s*900px\)\s*\{\s*body\.aznet-theme-preset--digital-01\s+\.aznet-digital01--brand-2026\s+\.aznet-digital01__offer-grid\s*\{[^}]*grid-template-columns:\s*1fr~', $css)) {
    $fail('three commercial paths must stack at 900px and below');
}
echo "PASS: Digital 01 2026 reference — opt-in WordPress ownership, responsive components and WCAG AA token pairs\n";
