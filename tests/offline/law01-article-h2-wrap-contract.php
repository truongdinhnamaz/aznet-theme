<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$cssPath = $root . '/assets/css/components/article.css';
$css = (string) file_get_contents($cssPath);

$fail = static function (string $message): void {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

$selector = '.aznet-theme-article--law01 .aznet-theme-article__content h2 {';
$start = strpos($css, $selector);
if (false === $start) {
    $fail('Law 01 article H2 selector is missing.');
}

$end = strpos($css, '}', $start);
if (false === $end) {
    $fail('Law 01 article H2 rule is malformed.');
}

$rule = substr($css, $start, $end - $start + 1);

if (! str_contains($rule, 'text-wrap: pretty;')) {
    $fail('Law 01 article H2 must use natural/pretty wrapping instead of balanced wrapping.');
}

if (str_contains($rule, 'text-wrap: balance;')) {
    $fail('Law 01 article H2 still uses balanced wrapping, which causes premature line breaks.');
}

echo "PASS: Law 01 article H2 natural wrapping contract\n";
