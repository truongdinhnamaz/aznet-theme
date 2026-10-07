<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$fail = static function (string $message): void {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

$cssPath = $root . '/assets/css/components/homepage-industrial-01.css';
if (!is_file($cssPath)) {
    $fail('Industrial 01 homepage stylesheet missing');
}

$css = (string) file_get_contents($cssPath);

$expectations = [
    '.aznet-theme-industrial01-section' => [
        'padding-block: clamp(3.5rem, 6vw, 6rem);',
    ],
    '.aznet-theme-industrial01-hero__grid' => [
        'min-height: clamp(30rem, 62vh, 42rem);',
        'gap: clamp(1.75rem, 4vw, 4rem);',
    ],
    '.aznet-theme-industrial01-hero__content' => [
        'padding-block: clamp(3.5rem, 6vw, 6rem);',
    ],
    '.aznet-theme-industrial01-hero h1' => [
        'font-size: clamp(2.8rem, 5vw, 5.2rem);',
    ],
    '.aznet-theme-industrial01-hero__panel' => [
        'width: min(100%, 30rem);',
    ],
    '.aznet-theme-industrial01-heading' => [
        'margin-bottom: clamp(1.75rem, 3vw, 2.75rem);',
    ],
    '.aznet-theme-industrial01-category-card__body' => [
        'min-height: 8.5rem;',
    ],
    '.aznet-theme-industrial01-product-card__body' => [
        'min-height: 11rem;',
    ],
];

foreach ($expectations as $selector => $needles) {
    if (!preg_match('/' . preg_quote($selector, '/') . '\\s*\\{([^}]*)\\}/s', $css, $match)) {
        $fail('Industrial 01 homepage selector missing: ' . $selector);
    }
    foreach ($needles as $needle) {
        if (!str_contains($match[1], $needle)) {
            $fail('Industrial 01 homepage compact rhythm missing: ' . $selector . ' / ' . $needle);
        }
    }
}

foreach ([
    '.aznet-theme-industrial01-grid--categories',
    '.aznet-theme-industrial01-grid--products',
    '.aznet-theme-industrial01-solutions__grid',
    '.aznet-theme-industrial01-process__grid',
    '.aznet-theme-industrial01-knowledge__grid',
] as $selector) {
    if (!str_contains($css, $selector)) {
        $fail('Industrial 01 homepage retained surface missing: ' . $selector);
    }
}

echo "PASS: Industrial 01 homepage uses compact B2B rhythm while retaining all core surfaces\n";
