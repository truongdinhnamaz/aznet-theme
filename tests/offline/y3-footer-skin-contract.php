<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$footer = (string) file_get_contents($root . '/inc/theme/footer.php');
$template = (string) file_get_contents($root . '/template-parts/footer/site-footer.php');
$css = (string) file_get_contents($root . '/assets/css/components/site-footer.css');

$fail = static function (string $message): never {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

foreach ([
    "'skin'        =>",
    "homepage_preset",
    "'law-01' ===",
] as $needle) {
    if (! str_contains($footer, $needle)) {
        $fail('Footer context must expose presentation skin independently of content preset: ' . $needle);
    }
}

foreach ([
    "aznet-theme-site-footer--skin-",
    "\$context['skin']",
] as $needle) {
    if (! str_contains($template, $needle)) {
        $fail('Footer template must render presentation skin class: ' . $needle);
    }
}

foreach ([
    '.aznet-theme-site-footer--skin-law-01',
    '--aznet-theme-footer-accent: var(--aznet-theme-law01-gold)',
    'var(--aznet-theme-law01-burgundy-deep)',
    'minmax(250px, 1fr)',
    'minmax(160px, .62fr)',
    'minmax(320px, 1.18fr)',
    'padding-inline-start: clamp(var(--aznet-theme-space-6), 2.35vw, var(--aznet-theme-space-7))',
] as $needle) {
    if (! str_contains($css, $needle)) {
        $fail('Law presentation skin must preserve burgundy/gold Footer shell: ' . $needle);
    }
}

echo "PASS: Footer content preset and visual skin remain independently composable\n";
