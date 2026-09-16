<?php

$slug = 'x1-comments-surface';
$post = get_page_by_path($slug, OBJECT, 'post');

$post_id = wp_insert_post([
    'ID'             => $post instanceof WP_Post ? $post->ID : 0,
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'post_title'     => 'X1 Comments Surface',
    'post_name'      => $slug,
    'post_content'   => '<p>X1 comments surface body.</p>',
    'comment_status' => 'open',
], true);

if (is_wp_error($post_id)) {
    fwrite(STDERR, 'FAIL: cannot create X1 post: ' . $post_id->get_error_message() . "\n");
    exit(1);
}

foreach (get_comments(['post_id' => $post_id, 'status' => 'all']) as $comment) {
    wp_delete_comment($comment->comment_ID, true);
}

$parent_id = wp_insert_comment([
    'comment_post_ID'      => $post_id,
    'comment_author'       => 'X1 Parent',
    'comment_author_email' => 'x1-parent@example.test',
    'comment_content'      => 'X1 parent comment',
    'comment_approved'     => 1,
    'comment_date'         => '2026-01-01 10:00:00',
    'comment_date_gmt'     => '2026-01-01 10:00:00',
]);

$child_id = wp_insert_comment([
    'comment_post_ID'      => $post_id,
    'comment_parent'       => $parent_id,
    'comment_author'       => 'X1 Child',
    'comment_author_email' => 'x1-child@example.test',
    'comment_content'      => 'X1 nested reply',
    'comment_approved'     => 1,
    'comment_date'         => '2026-01-01 10:01:00',
    'comment_date_gmt'     => '2026-01-01 10:01:00',
]);

$second_parent_id = wp_insert_comment([
    'comment_post_ID'      => $post_id,
    'comment_author'       => 'X1 Second Parent',
    'comment_author_email' => 'x1-second@example.test',
    'comment_content'      => 'X1 second parent comment',
    'comment_approved'     => 1,
    'comment_date'         => '2026-01-02 10:00:00',
    'comment_date_gmt'     => '2026-01-02 10:00:00',
]);

if (! $parent_id || ! $child_id || ! $second_parent_id) {
    fwrite(STDERR, "FAIL: cannot create X1 comments\n");
    exit(1);
}

update_option('thread_comments', 1);
update_option('thread_comments_depth', 5);
update_option('page_comments', 1);
update_option('comments_per_page', 1);
update_option('default_comments_page', 'oldest');
update_option('comment_order', 'asc');

if (3 !== (int) get_comments_number($post_id)) {
    fwrite(STDERR, "FAIL: expected three approved X1 comments\n");
    exit(1);
}

$url = get_permalink($post_id);
if (! is_string($url) || '' === $url) {
    fwrite(STDERR, "FAIL: missing X1 permalink\n");
    exit(1);
}

echo wp_json_encode([
    'post_id'          => $post_id,
    'parent_id'        => $parent_id,
    'child_id'         => $child_id,
    'second_parent_id' => $second_parent_id,
    'url'              => $url,
], JSON_UNESCAPED_SLASHES) . "\n";
