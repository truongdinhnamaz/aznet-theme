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

$expectations = [
    '.aznet-theme-industrial01-about-hero__meta' => [
        'display: flex;',
        'flex-wrap: wrap;',
    ],
    '.aznet-theme-industrial01-about-collage' => [
        'display: grid;',
        'grid-template-columns:',
    ],
    '.aznet-theme-industrial01-about-collage img' => [
        'object-fit: cover;',
    ],
    '.aznet-theme-industrial01-about-card__media' => [
        'aspect-ratio: 4 / 3;',
        'overflow: hidden;',
    ],
    '.aznet-theme-industrial01-about-card__media img' => [
        'width: 100%;',
        'height: 100%;',
        'object-fit: cover;',
    ],
    '.aznet-theme-industrial01-about-needs' => [
        'display: grid;',
        'grid-template-columns:',
    ],
    '.aznet-theme-industrial01-about-process' => [
        'display: grid;',
        'grid-template-columns:',
    ],
];

foreach ($expectations as $selector => $needles) {
    if (! preg_match('/' . preg_quote($selector, '/') . '\\s*\\{([^}]*)\\}/s', $css, $match)) {
        $fail('Industrial 01 About visual selector missing: ' . $selector);
    }

    foreach ($needles as $needle) {
        if (! str_contains($match[1], $needle)) {
            $fail('Industrial 01 About visual treatment missing: ' . $selector . ' / ' . $needle);
        }
    }
}

foreach ([
    '@media (max-width: 64rem)',
    '@media (max-width: 48rem)',
] as $needle) {
    if (! str_contains($css, $needle)) {
        $fail('Industrial 01 About responsive visual treatment missing: ' . $needle);
    }
}

echo "PASS: Industrial 01 About page has image-led collage, cards, needs and process presentation\n";
