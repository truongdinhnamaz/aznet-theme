<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$fail = static function (string $message): void {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

$cssPath = $root . '/assets/css/presets/industrial-01.css';
if (!is_file($cssPath)) {
    $fail('Industrial 01 preset stylesheet missing');
}

$css = (string) file_get_contents($cssPath);

$mainSelector = 'body.aznet-theme-preset--industrial-01.single-product #main';
if (!preg_match('/' . preg_quote($mainSelector, '/') . '\\s*\\{([^}]*)\\}/s', $css, $mainRule)) {
    $fail('Industrial 01 single product shared shell rule missing.');
}

foreach ([
    'var(--aznet-theme-container-shell)',
    'margin-inline: auto;',
    'var(--aznet-theme-gutter)',
] as $needle) {
    if (!str_contains($mainRule[1], $needle)) {
        $fail('Industrial 01 single product is not constrained by the shared shell: ' . $needle);
    }
}

$productSelector = 'body.aznet-theme-preset--industrial-01.single-product #main > .product';
if (!preg_match('/' . preg_quote($productSelector, '/') . '\\s*\\{([^}]*)\\}/s', $css, $productRule)) {
    $fail('Industrial 01 single product two-column layout rule missing.');
}

foreach ([
    'display: grid;',
    'grid-template-columns:',
    'gap:',
] as $needle) {
    if (!str_contains($productRule[1], $needle)) {
        $fail('Industrial 01 single product top layout is incomplete: ' . $needle);
    }
}

foreach ([
    '.woocommerce-tabs',
    '.related',
    '.up-sells',
    'grid-column: 1 / -1;',
] as $needle) {
    if (!str_contains($css, $needle)) {
        $fail('Industrial 01 single product full-width lower section rule missing: ' . $needle);
    }
}

foreach ([
    'body.aznet-theme-preset--industrial-01.single-product #main .woocommerce-Tabs-panel',
    'overflow-x: auto;',
    'body.aznet-theme-preset--industrial-01.single-product #main .related ul.products',
    'grid-template-columns: repeat(4, minmax(0, 1fr));',
    '@media (max-width: 64rem)',
    'grid-template-columns: repeat(2, minmax(0, 1fr));',
    '@media (max-width: 48rem)',
    'grid-template-columns: 1fr;',
] as $needle) {
    if (!str_contains($css, $needle)) {
        $fail('Industrial 01 single product responsive/detail presentation missing: ' . $needle);
    }
}

echo "PASS: Industrial 01 single product uses shared shell, two-column hero and responsive lower surfaces\n";
