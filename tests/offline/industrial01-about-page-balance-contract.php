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
    '.aznet-theme-industrial01-about-collage' => [
        'height: clamp(',
        'min-height: 0;',
        'align-self: center;',
    ],
    '.aznet-theme-industrial01-about-collage__item' => [
        'min-height: 0;',
    ],
    '.aznet-theme-industrial01-about-card' => [
        'display: flex;',
        'flex-direction: column;',
    ],
    '.aznet-theme-industrial01-about-card__body' => [
        'display: flex;',
        'flex: 1;',
        'flex-direction: column;',
    ],
    '.aznet-theme-industrial01-about-card__body > p:last-child' => [
        'margin-top: auto;',
    ],
    '.aznet-theme-industrial01-about-needs__media' => [
        'align-self: center;',
        'aspect-ratio: 4 / 3;',
    ],
    '.aznet-theme-industrial01-about-process' => [
        'grid-auto-rows: 1fr;',
    ],
    'body.aznet-theme-preset--industrial-01 .aznet-theme-industrial01-about-page .aznet-theme-industrial01-solution-media' => [
        'aspect-ratio: 4 / 3;',
    ],
    'body.aznet-theme-preset--industrial-01 .aznet-theme-industrial01-about-page .aznet-theme-industrial01-solution-media img' => [
        'width: 100%;',
        'height: 100%;',
        'object-fit: cover;',
    ],
];

foreach ($expectations as $selector => $needles) {
    if (! preg_match('/' . preg_quote($selector, '/') . '\\s*\\{([^}]*)\\}/s', $css, $match)) {
        $fail('Industrial 01 About balance selector missing: ' . $selector);
    }

    foreach ($needles as $needle) {
        if (! str_contains($match[1], $needle)) {
            $fail('Industrial 01 About balanced-column treatment missing: ' . $selector . ' / ' . $needle);
        }
    }
}

echo "PASS: Industrial 01 About two-column media and equal-height card rows stay balanced\n";
