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

$selector = 'body.aznet-theme-preset--industrial-01 .aznet-theme-page--landing:has(.aznet-theme-industrial01-project-page) > .aznet-theme-page__header';
if (!str_contains($css, $selector)) {
    $fail('Industrial 01 Project Page must suppress the generic Page header when its authored hero is present.');
}

if (!preg_match('/' . preg_quote($selector, '/') . '\s*\{([^}]*)\}/s', $css, $match)) {
    $fail('Industrial 01 Project Page header suppression rule missing.');
}

if (!str_contains($match[1], 'display: none;')) {
    $fail('Industrial 01 Project Page generic header must be hidden.');
}

foreach ([
    '.aznet-theme-industrial01-project-page .aznet-theme-industrial01-solution-hero',
    '.aznet-theme-industrial01-project-page .aznet-theme-industrial01-solution-section--soft',
    '.aznet-theme-industrial01-project-page .aznet-theme-industrial01-solution-section--dark',
] as $scopeSelector) {
    if (!str_contains($css, $scopeSelector)) {
        $fail('Industrial 01 Project Page scoped presentation selector missing: ' . $scopeSelector);
    }
}

echo "PASS: Industrial 01 Project Page authored hero replaces duplicate generic Page header\n";
