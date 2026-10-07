<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$fail = static function (string $message): void {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

$cssPath = $root . '/assets/css/presets/industrial-01.css';
if (! is_file($cssPath)) {
    $fail('Industrial 01 preset stylesheet missing');
}

$css = (string) file_get_contents($cssPath);

$mobilePos = strpos($css, '@media (max-width: 48rem)');
if ($mobilePos === false) {
    $fail('Industrial 01 mobile breakpoint missing.');
}

$mobile = substr($css, $mobilePos);

$expectations = [
    'body.aznet-theme-preset--industrial-01 .aznet-theme-industrial01-about-page .aznet-theme-industrial01-solution-hero' => [
        'padding-block: 2.75rem;',
    ],
    'body.aznet-theme-preset--industrial-01 .aznet-theme-industrial01-about-page .aznet-theme-industrial01-solution-hero h2' => [
        'font-size: clamp(2.1rem, 10vw, 2.6rem);',
    ],
    'body.aznet-theme-preset--industrial-01 .aznet-theme-industrial01-about-page .aznet-theme-industrial01-solution-actions' => [
        'display: grid;',
        'grid-template-columns: 1fr;',
    ],
    'body.aznet-theme-preset--industrial-01 .aznet-theme-industrial01-about-page .aznet-theme-industrial01-solution-button' => [
        'width: 100%;',
    ],
    '.aznet-theme-industrial01-about-collage' => [
        'height: 18rem;',
    ],
    '.aznet-theme-industrial01-about-card' => [
        'flex-direction: row;',
    ],
    '.aznet-theme-industrial01-about-card__media' => [
        'width: 38%;',
        'aspect-ratio: auto;',
    ],
    '.aznet-theme-industrial01-about-card__body' => [
        'padding: 1rem;',
    ],
    '#nhu-cau .wp-block-group:last-child' => [
        'order: -1;',
    ],
    '.aznet-theme-industrial01-about-process .aznet-theme-industrial01-solution-card' => [
        'padding: 1rem 1rem 1rem 1.2rem;',
    ],
    '#du-an .aznet-theme-industrial01-solution-split__content' => [
        'order: -1;',
    ],
];

foreach ($expectations as $selector => $needles) {
    if (! preg_match('/' . preg_quote($selector, '/') . '\\s*\\{([^}]*)\\}/s', $mobile, $match)) {
        $fail('Industrial 01 About mobile selector missing: ' . $selector);
    }

    foreach ($needles as $needle) {
        if (! str_contains($match[1], $needle)) {
            $fail('Industrial 01 About mobile treatment missing: ' . $selector . ' / ' . $needle);
        }
    }
}

echo "PASS: Industrial 01 About mobile layout is compact, touch-friendly and content-first\n";
