<?php
/**
 * Law 01 FAQ: Classic Editor <details> projection regression (#527).
 *
 * WordPress owns the authored Page and FAQ HTML. The Theme may present a
 * bounded, sanitized preview from the already mapped public Page only.
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$faq = (string) file_get_contents($root . '/template-parts/homepage/law-01/faq.php');
$css = (string) file_get_contents($root . '/assets/css/components/homepage-law-01-variants.css');
$fail = static function (string $message): never {
    fwrite(STDERR, "FAIL: Classic FAQ homepage preview: {$message}\n");
    exit(1);
};

foreach ([
    "homepage_source_value( 'law-01', 'faq' )",
    'homepage_page_reference(',
    "parse_blocks( (string) \$page->post_content )",
    "'core/details'",
    'render_block( $detail )',
    'aznet-theme-law01-faq__items',
] as $required) {
    if (!str_contains($faq, $required)) {
        $fail('existing Gutenberg/WordPress Page FAQ path was not retained: ' . $required);
    }
}
foreach ([
    "if ( [] === \$details",
    '\DOMDocument',
    'LIBXML_NONET',
    "getElementsByTagName( 'details' )",
    "getElementsByTagName( 'summary' )",
    'wp_kses_post(',
    'wp-block-details',
    '$classic_details',
    'has_excerpt( $page )',
] as $required) {
    if (!str_contains($faq, $required)) {
        $fail('Classic Editor native Details safety/presentation fallback missing: ' . $required);
    }
}
if (!str_contains($css, 'details.wp-block-details')) {
    $fail('reuse existing scoped FAQ details presentation, do not duplicate CSS');
}
foreach ([
    'wp_insert_post', 'wp_update_post', 'update_post_meta', 'update_option',
    'get_post_meta', '$wpdb', 'choiceguide_', 'rootprofile_', 'fetch(', 'file_get_contents(',
] as $forbidden) {
    if (str_contains($faq, $forbidden)) {
        $fail('preview must not copy content, mutate source, or bypass public WordPress Page: ' . $forbidden);
    }
}
echo "PASS: Classic Editor FAQ preview renders sanitized native details with existing block priority\n";
