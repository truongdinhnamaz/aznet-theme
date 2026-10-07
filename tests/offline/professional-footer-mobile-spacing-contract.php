<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$fail = static function (string $message): void {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

$cssPath = $root . '/assets/css/components/site-footer.css';
if (! is_file($cssPath)) {
    $fail('Footer stylesheet missing.');
}

$css = (string) file_get_contents($cssPath);
$mobilePos = strrpos($css, '@media (max-width: 48rem)');
if ($mobilePos === false) {
    $fail('Footer mobile breakpoint missing.');
}
$mobile = substr($css, $mobilePos);

$expectations = [
    '.aznet-theme-site-footer--professional .aznet-theme-site-footer__column-content + .aznet-theme-site-footer__column-content' => [
        'padding-top: 1.75rem;',
        'border-top: 1px solid var(--aznet-theme-footer-border);',
    ],
    '.aznet-theme-site-footer--professional .aznet-theme-site-footer__rich-content li + li' => [
        'margin-top: .8rem;',
    ],
    '.aznet-theme-site-footer--professional .aznet-theme-site-footer__rich-content' => [
        'line-height: 1.7;',
    ],
    '.aznet-theme-site-footer--professional .aznet-theme-site-footer__rich-content strong' => [
        'display: inline-block;',
        'margin-bottom: .35rem;',
    ],
];

foreach ($expectations as $selector => $needles) {
    if (! preg_match('/' . preg_quote($selector, '/') . '\\s*\\{([^}]*)\\}/s', $mobile, $match)) {
        $fail('Professional Footer mobile selector missing: ' . $selector);
    }

    foreach ($needles as $needle) {
        if (! str_contains($match[1], $needle)) {
            $fail('Professional Footer mobile spacing missing: ' . $selector . ' / ' . $needle);
        }
    }
}

echo "PASS: Professional Footer mobile columns and rows have readable separation\n";
