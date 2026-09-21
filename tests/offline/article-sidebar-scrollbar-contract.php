<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$css = (string) file_get_contents($root . '/assets/css/components/article.css');

$fail = static function (string $message): void {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

foreach ([
    'scrollbar-width: thin;',
    'scrollbar-color:',
    '.aznet-theme-article__sidebar::-webkit-scrollbar',
    '.aznet-theme-article__sidebar::-webkit-scrollbar-thumb',
    '.aznet-theme-article__sidebar:hover::-webkit-scrollbar-thumb',
] as $needle) {
    if (! str_contains($css, $needle)) {
        $fail('missing sidebar scrollbar marker: ' . $needle);
    }
}

echo "PASS: Article Sidebar scrollbar presentation contract\n";
