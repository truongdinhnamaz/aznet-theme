<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function x3_fail(string $message): never {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

function x3_source(string $relative): string {
    $path = dirname(__DIR__, 2) . '/' . $relative;
    if (! is_file($path)) {
        x3_fail('missing required file: ' . $relative);
    }

    $source = file_get_contents($path);
    if (false === $source) {
        x3_fail('cannot read required file: ' . $relative);
    }

    return $source;
}

$assets = x3_source('inc/theme/assets.php');
$design = x3_source('inc/theme/design-system.php');
$page_wrapper = x3_source('page.php');
$page_content = x3_source('template-parts/content/page.php');
$page = $page_wrapper . "\n" . $page_content;
$front = x3_source('front-page.php');
$content = x3_source('template-parts/content/content-single.php');
$style = x3_source('style.css');
$functions = x3_source('functions.php');
$media = x3_source('assets/css/components/media.css');

if (! str_contains($page_wrapper, "get_template_part( 'template-parts/content/page' );")) {
    x3_fail('native Page wrapper must delegate to the shared authored-content renderer');
}

foreach ([$page, $front, $content] as $surface) {
    if (! str_contains($surface, 'aznet-theme-entry__content')) {
        x3_fail('authored-content wrapper missing from an X3 surface');
    }
    if (! str_contains($surface, 'the_content()')) {
        x3_fail('WordPress the_content() boundary missing from an X3 surface');
    }
}

foreach ([
    'should_enqueue_media_assets',
    'enqueue_media_assets',
    "is_singular( 'post' )",
    'is_page()',
    'assets/css/components/media.css',
    "'aznet-theme-media'",
] as $marker) {
    if (! str_contains($assets, $marker)) {
        x3_fail('X3 asset boundary missing marker: ' . $marker);
    }
}

if (! str_contains($design, "'assets/css/components/media.css'")) {
    x3_fail('media.css must use the existing editor_stylesheets path');
}

foreach ([
    '.aznet-theme-entry__content',
    '.wp-block-image',
    'figcaption',
    '.wp-block-gallery',
    '.wp-block-video',
    '.wp-block-audio',
    '.wp-block-embed',
    '.wp-caption',
    '.wp-caption-text',
    '.gallery',
    '.gallery-item',
    '.gallery-caption',
    '.alignwide',
    '.alignfull',
    ':focus-visible',
    'body#tinymce',
] as $marker) {
    if (! str_contains($media, $marker)) {
        x3_fail('media.css missing required modern/legacy/editor marker: ' . $marker);
    }
}

foreach (preg_split('/\R/', $media) ?: [] as $line) {
    $selector = trim($line);
    if ('' === $selector || ! str_ends_with($selector, '{') || str_starts_with($selector, '@')) {
        continue;
    }
    if (str_contains($selector, '.editor-styles-wrapper') || str_contains($selector, 'body#tinymce') || str_contains($selector, '.mce-content-body')) {
        continue;
    }
    if (! str_contains($selector, '.aznet-theme-entry__content')) {
        x3_fail('unscoped frontend media selector: ' . $selector);
    }
}

$production = $assets . "\n" . $design . "\n" . $media;
foreach ([
    'new WP_Query',
    'WP_Query(',
    'query_posts(',
    'get_posts(',
    '$wpdb',
    'get_post_meta(',
    'get_attached_file(',
    'wp_get_attachment_metadata(',
    'lightbox',
    'fancybox',
    'photoswipe',
    'swiper',
    'javascript:',
    '100vw',
] as $forbidden) {
    if (str_contains(strtolower($production), strtolower($forbidden))) {
        x3_fail('forbidden X3 ownership/application marker: ' . $forbidden);
    }
}

preg_match('/^Version:\s*([^\r\n]+)/m', $style, $styleMatch);
preg_match("/define\\( 'AZNET_THEME_VERSION', '([^']+)' \\);/", $functions, $phpMatch);
$styleVersion = trim($styleMatch[1] ?? '');
$phpVersion = $phpMatch[1] ?? '';
if ($styleVersion === '' || $phpVersion === '' || $styleVersion !== $phpVersion) {
    x3_fail('Theme version declarations must remain present and equal.');
}

echo "PASS: X3 Media/Gallery/Embed ownership and presentation contract\n";
