<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);

$files = [
    'header-utility' => $root . '/assets/css/components/header-utility.css',
    'site-footer' => $root . '/assets/css/components/site-footer.css',
    'homepage' => $root . '/assets/css/components/homepage.css',
    'law-01' => $root . '/assets/css/components/homepage-law-01.css',
    'page' => $root . '/assets/css/components/page.css',
    'article' => $root . '/assets/css/components/article.css',
    'forms' => $root . '/assets/css/components/forms.css',
    'woo-archive' => $root . '/assets/css/components/woocommerce-archive.css',
    'woo-product' => $root . '/assets/css/components/woocommerce-product.css',
    'commerce-preset' => $root . '/assets/css/presets/commerce.css',
    'editorial-preset' => $root . '/assets/css/presets/editorial.css',
];

foreach ($files as $name => $path) {
    assert(is_file($path), "Typography role mapping input missing: {$name}");
    $files[$name] = (string) file_get_contents($path);
}

$required = [
    'header-utility' => [
        'font-size: var(--aznet-theme-text-button);',
        'line-height: var(--aznet-theme-line-height-button);',
    ],
    'site-footer' => [
        'font-size: var(--aznet-theme-text-h4);',
        'font-size: var(--aznet-theme-text-small);',
        'font-size: var(--aznet-theme-text-meta);',
        'line-height: var(--aznet-theme-line-height-meta);',
    ],
    'homepage' => [
        'font-size: var(--aznet-theme-text-display);',
        'font-size: var(--aznet-theme-text-lead);',
        'font-size: var(--aznet-theme-text-h2);',
    ],
    'law-01' => [
        'font-size: var(--aznet-theme-text-display);',
        'font-size: var(--aznet-theme-text-h2);',
        'font-size: var(--aznet-theme-text-h3);',
        'font-size: var(--aznet-theme-text-label);',
        'font-size: var(--aznet-theme-text-meta);',
    ],
    'page' => [
        'font-size: var(--aznet-theme-text-lead);',
        'font-size: var(--aznet-theme-text-meta);',
    ],
    'article' => [
        'font-size: var(--aznet-theme-text-body);',
        'font-size: var(--aznet-theme-text-meta);',
    ],
    'forms' => [
        'font-size: var(--aznet-theme-text-form);',
        'font-size: var(--aznet-theme-text-button);',
    ],
    'woo-archive' => [
        'font-size: var(--aznet-theme-text-h4);',
        'font-size: var(--aznet-theme-text-price);',
    ],
    'woo-product' => [
        'font-size: var(--aznet-theme-text-h1);',
        'font-size: var(--aznet-theme-text-price);',
    ],
];

foreach ($required as $name => $needles) {
    foreach ($needles as $needle) {
        assert(str_contains($files[$name], $needle), "{$name} missing semantic mapping: {$needle}");
    }
}

foreach (['commerce-preset', 'editorial-preset'] as $name) {
    assert(
        ! preg_match('/--aznet-theme-(?:font-size|line-height)-[a-z0-9-]+\s*:/', $files[$name]),
        "{$name} must not override the canonical typography scale."
    );
}

echo "PASS: semantic typography role mapping contract\n";
