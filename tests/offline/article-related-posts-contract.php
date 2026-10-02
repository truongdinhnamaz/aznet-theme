<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$fail = static function (string $message): never {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

$path = $root . '/template-parts/content/related.php';
if (! is_file($path)) {
    $fail('missing related Post template part');
}

$source = file_get_contents($path);
if (false === $source) {
    $fail('cannot read related Post template part');
}

$css_path = $root . '/assets/css/components/article.css';
$css = file_get_contents($css_path);
if (false === $css) {
    $fail('cannot read article CSS');
}

foreach ([
    'wp_get_post_categories( $current_post_id )',
    'get_posts(',
    "'post_type'           => 'post'",
    "'post_status'         => 'publish'",
    "'posts_per_page'      => 8",
    "'post__not_in'        => [ $current_post_id ]",
    "'category__in'        => $category_ids",
    'has_post_thumbnail( $candidate )',
    '3 === count( $related_posts )',
    "esc_html_e( 'Bài viết liên quan', 'aznet-theme' )",
    'get_the_post_thumbnail(',
    'wp_trim_words(',
    "esc_html_e( 'Đọc tiếp', 'aznet-theme' )",
    "esc_attr_e( 'Chuyên mục liên quan', 'aznet-theme' )",
    'get_category_link( $category )',
    'sanitize_title( $category->name )',
] as $marker) {
    if (! str_contains($source, $marker)) {
        $fail('related Post template missing bounded native marker: ' . $marker);
    }
}

foreach ([
    'aspect-ratio: 16 / 9;',
    'flex-direction: column;',
] as $marker) {
    if (! str_contains($css, $marker)) {
        $fail('related Post card CSS missing landscape-card marker: ' . $marker);
    }
}

foreach ([
    'new WP_Query',
    'WP_Query(',
    'query_posts(',
    '$wpdb',
    'get_post_meta(',
    'choiceguide_',
    'rootprofile_',
    'convertflow_',
] as $forbidden) {
    if (str_contains($source, $forbidden)) {
        $fail('related Post template crossed ownership boundary: ' . $forbidden);
    }
}

echo "PASS: related Posts use bounded WordPress category reads and separate category links\n";
