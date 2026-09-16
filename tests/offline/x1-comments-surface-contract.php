<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function fail_x1_comments(string $message): never {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

function source_x1_comments(string $path): string {
    if (! is_file($path)) {
        fail_x1_comments('missing required file: ' . $path);
    }

    $source = file_get_contents($path);
    if (false === $source) {
        fail_x1_comments('cannot read required file: ' . $path);
    }

    return $source;
}

$single   = source_x1_comments($root . '/single.php');
$comments = source_x1_comments($root . '/comments.php');
$assets   = source_x1_comments($root . '/inc/theme/assets.php');
$css      = source_x1_comments($root . '/assets/css/components/comments.css');

foreach ([
    "is_singular( 'post' )",
    'comments_template()',
    'comments_open()',
    'get_comments_number()',
] as $marker) {
    if (! str_contains($single, $marker)) {
        fail_x1_comments('single.php missing comments-surface marker: ' . $marker);
    }
}

foreach ([
    'post_password_required()',
    'have_comments()',
    'wp_list_comments(',
    'the_comments_pagination(',
    'comments_open()',
    'get_comments_number()',
    'comment_form(',
    'id="comments"',
    'aznet-theme-comments',
] as $marker) {
    if (! str_contains($comments, $marker)) {
        fail_x1_comments('comments.php missing native comments marker: ' . $marker);
    }
}

foreach ([
    'should_enqueue_comments_assets',
    'post_password_required()',
    "'aznet-theme-comments'",
    "'/assets/css/components/comments.css'",
    "wp_enqueue_script( 'comment-reply' )",
    "get_option( 'thread_comments' )",
] as $marker) {
    if (! str_contains($assets, $marker)) {
        fail_x1_comments('comments asset gate missing marker: ' . $marker);
    }
}

foreach ([
    '.aznet-theme-comments',
    '.comment-list',
    '.children',
    '.comment-body',
    '.comment-content',
    '.comment-reply-link',
    '.comment-respond',
    ':focus-visible',
    'overflow-wrap',
] as $marker) {
    if (! str_contains($css, $marker)) {
        fail_x1_comments('comments.css missing presentation marker: ' . $marker);
    }
}

$sources = $single . "\n" . $comments . "\n" . $assets;
foreach ([
    'new WP_Query',
    'WP_Query(',
    'get_posts(',
    '$wpdb',
    'get_post_meta(',
    'choiceguide_',
    'RootProfile',
    'ConvertFlow',
] as $marker) {
    if (str_contains($sources, $marker)) {
        fail_x1_comments('forbidden ownership/provider marker found: ' . $marker);
    }
}

echo "PASS: X1 native comments presentation contract\n";
