<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$fail = static function (string $message): never {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

$read = static function (string $relative) use ($root, $fail): string {
    $path = $root . '/' . $relative;
    if (! is_file($path)) {
        $fail('missing required file: ' . $relative);
    }
    $source = file_get_contents($path);
    if (false === $source) {
        $fail('cannot read required file: ' . $relative);
    }
    return $source;
};

$content = $read('template-parts/content/content-single.php');
$css = $read('assets/css/components/article.css');
$assets = $read('inc/theme/assets.php');

foreach ([
    'header_law01_active()',
    'aznet-theme-article--law01',
    'has_excerpt()',
    'aznet-theme-article__dek',
    'aznet-theme-article__reading-layout',
    'aznet-theme-article__reading-main',
    'the_content()',
    "esc_html__( 'Bài trước', 'aznet-theme' )",
    "esc_html__( 'Bài sau', 'aznet-theme' )",
] as $marker) {
    if (! str_contains($content, $marker)) {
        $fail('single Post template missing approved legal-article marker: ' . $marker);
    }
}

foreach ([
    '.aznet-theme-article--law01',
    '.aznet-theme-article--law01 .aznet-theme-article__header',
    '.aznet-theme-article--law01 .aznet-theme-article__dek',
    '.aznet-theme-article--law01 .aznet-theme-article__featured-media',
    '.aznet-theme-article--law01 .aznet-theme-article__reading-main',
    '.aznet-theme-article--law01 .aznet-theme-article__content h2',
    '.aznet-theme-article--law01 .aznet-theme-article__content h3',
    '.aznet-theme-article--law01 .aznet-theme-article__content a',
    '.aznet-theme-article--law01 .aznet-theme-article__content blockquote',
    '.aznet-theme-article--law01 .aznet-theme-article__content table',
    '--law01-article-burgundy',
    'max-width: 50rem',
    'max-width: 26ch;',
    'font-size: clamp(3rem, 4vw, 4rem);',
    'line-height: 1.08;',
    'text-align: center;',
    'line-height: 1.82',
    '@media (max-width: 47.999rem)',
] as $marker) {
    if (! str_contains($css, $marker)) {
        $fail('article.css missing approved legal-reading marker: ' . $marker);
    }
}

if (! str_contains($assets, 'asset_content_version( \'/assets/css/components/article.css\', $version )')) {
    $fail('article.css must use content-aware cache versioning so Theme updates are visible immediately');
}

$production = $content . "\n" . $css;
foreach ([
    'new WP_Query',
    'WP_Query(',
    'get_posts(',
    'get_post_meta(',
    '$wpdb',
    'DOMDocument',
    'preg_match_all',
    'convertflow_toc',
    'choiceguide_',
] as $forbidden) {
    if (str_contains($production, $forbidden)) {
        $fail('article presentation crossed WordPress/ConvertFlow ownership boundary: ' . $forbidden);
    }
}

if (1 !== substr_count($content, 'the_content();')) {
    $fail('single Post must retain exactly one native the_content() boundary');
}

echo "PASS: Law 01 legal-article reading experience contract\n";
