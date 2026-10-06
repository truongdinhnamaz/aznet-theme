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
    'max-width: 36rem',
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

if (! preg_match('/\\.aznet-theme-article--law01 \\.aznet-theme-article__featured-media\\s*\\{[^}]*max-width:\\s*36rem;/s', $css)) {
    $fail('Law 01 featured media must stay visibly narrower than the 50rem reading-content width');
}

if (! preg_match('/\\.aznet-theme-article--law01 \\.aznet-theme-article__header\\s*\\{[^}]*max-width:\\s*50rem;/s', $css)) {
    $fail('Law 01 article header must align to the 50rem reading-content column and not span into the sidebar zone');
}

if (! preg_match('/\\.aznet-theme-article--law01 \\.aznet-theme-article__reading-main\\s*\\{[^}]*max-width:\\s*50rem;/s', $css)) {
    $fail('Law 01 reading content must retain the 50rem reading width');
}

if (! preg_match('/\\.aznet-theme-article--law01 \\.aznet-theme-article__header\\s*\\{[^}]*text-align:\\s*left;/s', $css)) {
    $fail('Law 01 article header must use conventional left-aligned editorial presentation');
}

if (! preg_match('/\\.aznet-theme-article--law01 \\.aznet-theme-article__title\\s*\\{[^}]*font-size:\\s*clamp\\(2rem,\\s*3vw,\\s*3rem\\);[^}]*text-align:\\s*left;/s', $css)) {
    $fail('Law 01 article title must use a conventional left-aligned 2rem-3rem scale');
}

if (! preg_match('/\\.aznet-theme-article--law01 \\.aznet-theme-article__categories\\s*\\{[^}]*justify-content:\\s*flex-start;/s', $css)
    || ! preg_match('/\\.aznet-theme-article--law01 \\.aznet-theme-article__meta\\s*\\{[^}]*justify-content:\\s*flex-start;/s', $css)) {
    $fail('Law 01 article category/meta rows must align with the left content edge');
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

$readingStart = strpos($content, '<div class="<?php echo esc_attr( $reading_layout_class ); ?>">');
$header = strpos($content, 'aznet-theme-article__header');
$readingMain = strpos($content, 'aznet-theme-article__reading-main');
$featured = strpos($content, 'aznet-theme-article__featured-media');
$sidebar = strpos($content, 'aznet-theme-article__sidebar');
if (false === $readingStart || false === $header || false === $readingMain || false === $featured || false === $sidebar
    || ! ($readingStart < $header && $header < $readingMain && $readingMain < $featured && $featured < $sidebar)) {
    $fail('Law 01 reading grid must contain header then reading main in the left column, with sidebar as the right-column sibling');
}

foreach ([
    'grid-column: 1;',
    'grid-row: 1;',
    'grid-column: 2;',
] as $marker) {
    if (! str_contains($css, $marker)) {
        $fail('Law 01 article grid placement missing: ' . $marker);
    }
}


echo "PASS: Law 01 legal-article reading experience contract\n";
