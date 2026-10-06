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
    '.aznet-theme-industrial01-solution-page',
    '.aznet-theme-industrial01-solution-section',
    '.aznet-theme-industrial01-solution-shell',
    '.aznet-theme-industrial01-solution-hero',
    '.aznet-theme-industrial01-solution-grid',
    '.aznet-theme-industrial01-solution-card',
    '.aznet-theme-industrial01-solution-cta',
] as $selector) {
    if (!str_contains($css, $selector)) {
        $fail('Industrial 01 solution presentation selector missing: ' . $selector);
    }
}

if (!preg_match('/\.aznet-theme-industrial01-solution-section\s*\{([^}]*)\}/s', $css, $sectionMatch)) {
    $fail('Industrial 01 solution section rule missing');
}
foreach ([
    'width: 100%;',
    'max-width: none;',
] as $needle) {
    if (!str_contains($sectionMatch[1], $needle)) {
        $fail('Industrial 01 solution outer section is not full width: ' . $needle);
    }
}

if (!preg_match('/\.aznet-theme-industrial01-solution-shell\s*\{([^}]*)\}/s', $css, $shellMatch)) {
    $fail('Industrial 01 solution inner shell rule missing');
}
foreach ([
    'var(--aznet-theme-container-shell)',
    'margin-inline: auto;',
] as $needle) {
    if (!str_contains($shellMatch[1], $needle)) {
        $fail('Industrial 01 solution inner content is not constrained to shared shell: ' . $needle);
    }
}

foreach ([
    '@media (max-width: 64rem)',
    '@media (max-width: 48rem)',
    'grid-template-columns: repeat(2, minmax(0, 1fr));',
    'grid-template-columns: 1fr;',
    ':focus-visible',
] as $needle) {
    if (!str_contains($css, $needle)) {
        $fail('Industrial 01 solution responsive/a11y presentation missing: ' . $needle);
    }
}

echo "PASS: Industrial 01 Solution Page uses full-width outer sections with constrained inner shell\n";
