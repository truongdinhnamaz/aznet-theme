<?php

$post_id = wp_insert_post([
    'post_type' => 'post',
    'post_status' => 'publish',
    'post_title' => 'X2 Recovery Search Target',
    'post_content' => 'Native WordPress content for the X2 search result fixture.',
], true);

if (is_wp_error($post_id) || (int) $post_id <= 0) {
    fwrite(STDERR, "FAIL: unable to create X2 Post\n");
    exit(1);
}

$term = wp_insert_term('X2 Empty Archive', 'category');
if (is_wp_error($term)) {
    fwrite(STDERR, "FAIL: unable to create X2 empty Category\n");
    exit(1);
}

$term_id = (int) $term['term_id'];

$result = [
    'post_id' => (int) $post_id,
    'term_id' => $term_id,
    'result_url' => home_url('/?s=' . rawurlencode('X2 Recovery Search Target')),
    'empty_search_url' => home_url('/?s=' . rawurlencode('X2 Definitely Missing Phrase')),
    'empty_archive_url' => get_category_link($term_id),
    'not_found_url' => home_url('/?p=999999999'),
    'post_url' => get_permalink((int) $post_id),
    'home_url' => home_url('/'),
];

if (is_wp_error($result['empty_archive_url'])) {
    fwrite(STDERR, "FAIL: unable to resolve X2 Category URL\n");
    exit(1);
}

if (!is_string($result['post_url']) || '' === $result['post_url']) {
    fwrite(STDERR, "FAIL: unable to resolve X2 Post URL\n");
    exit(1);
}

echo wp_json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
