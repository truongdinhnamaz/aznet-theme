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

foreach ([
    'body.aznet-theme-preset--industrial-01.woocommerce-shop #main',
    'body.aznet-theme-preset--industrial-01.tax-product_cat #main',
] as $selector) {
    if (!preg_match('/' . preg_quote($selector, '/') . '\\s*\\{([^}]*)\\}/s', $css, $match)) {
        $fail('Industrial 01 catalogue shell rule missing: ' . $selector);
    }

    foreach ([
        'var(--aznet-theme-container-shell)',
        'margin-inline: auto;',
        'var(--aznet-theme-gutter)',
    ] as $needle) {
        if (!str_contains($match[1], $needle)) {
            $fail('Industrial 01 catalogue shell is not constrained by the shared site shell: ' . $selector . ' / ' . $needle);
        }
    }
}

foreach ([
    'body.aznet-theme-preset--industrial-01.woocommerce-shop ul.products',
    'body.aznet-theme-preset--industrial-01.tax-product_cat ul.products',
    '@media (max-width: 64rem)',
    '@media (max-width: 48rem)',
] as $needle) {
    if (!str_contains($css, $needle)) {
        $fail('Industrial 01 catalogue responsive presentation missing: ' . $needle);
    }
}

if (!str_contains($css, 'grid-template-columns: repeat(4, minmax(0, 1fr));')) {
    $fail('Industrial 01 desktop catalogue must remain four columns.');
}
if (!str_contains($css, 'grid-template-columns: repeat(2, minmax(0, 1fr));')) {
    $fail('Industrial 01 tablet catalogue must collapse to two columns.');
}
if (!str_contains($css, 'grid-template-columns: 1fr;')) {
    $fail('Industrial 01 mobile catalogue must collapse to one column.');
}

echo "PASS: Industrial 01 Woo catalogue uses shared constrained shell and responsive grid\n";
