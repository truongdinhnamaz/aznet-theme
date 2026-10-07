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

$headerSelector = 'body.aznet-theme-preset--industrial-01 .aznet-theme-page:has(.aznet-theme-industrial01-about-page) > .aznet-theme-page__header';
if (! preg_match('/' . preg_quote($headerSelector, '/') . '\\s*\\{([^}]*)\\}/s', $css, $headerRule)) {
    $fail('Industrial 01 About Page must suppress the generic Page header when the authored hero is present.');
}
if (! str_contains($headerRule[1], 'display: none;')) {
    $fail('Industrial 01 About Page generic Page header suppression is incomplete.');
}

foreach ([
    'body.aznet-theme-preset--industrial-01 .aznet-theme-industrial01-about-page .aznet-theme-industrial01-solution-hero',
    'body.aznet-theme-preset--industrial-01 .aznet-theme-industrial01-about-page .aznet-theme-industrial01-solution-hero__media img',
] as $selector) {
    if (! str_contains($css, $selector)) {
        $fail('Industrial 01 About Page presentation selector missing: ' . $selector);
    }
}

echo "PASS: Industrial 01 About Page suppresses duplicate generic header and retains authored hero presentation\n";
