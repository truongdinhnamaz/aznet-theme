<?php
/**
 * R2 Content pattern contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$expected = [
    'content-intro.php'           => 'aznet-theme/content-intro',
    'content-alternating.php'     => 'aznet-theme/content-alternating',
    'content-article-grid.php'    => 'aznet-theme/content-article-grid',
    'content-faq.php'             => 'aznet-theme/content-faq',
    'content-authority-quote.php' => 'aznet-theme/content-authority-quote',
];

foreach ($expected as $filename => $slug) {
    $path = $root . '/patterns/' . $filename;
    if (! is_file($path)) {
        fwrite(STDERR, "FAIL: missing R2 Content pattern {$filename}\n");
        exit(1);
    }

    $content = file_get_contents($path);
    if (false === $content) {
        fwrite(STDERR, "FAIL: unable to read {$filename}\n");
        exit(1);
    }

    foreach ([
        'Slug: ' . $slug,
        'Categories: aznet-theme-content',
        'aznet-theme-pattern',
    ] as $needle) {
        if (! str_contains($content, $needle)) {
            fwrite(STDERR, "FAIL: {$filename} missing {$needle}\n");
            exit(1);
        }
    }

    if (preg_match('/<h1\b|"level"\s*:\s*1\b/i', $content)) {
        fwrite(STDERR, "FAIL: {$filename} hard-codes an H1 into an insertable content pattern\n");
        exit(1);
    }

    if (preg_match('/(?:href|src)=["\']https?:\/\//i', $content)) {
        fwrite(STDERR, "FAIL: {$filename} contains a hard-coded external URL\n");
        exit(1);
    }
}

$article_grid = file_get_contents($root . '/patterns/content-article-grid.php');
if (false === $article_grid || ! str_contains($article_grid, '<!-- wp:query ')) {
    fwrite(STDERR, "FAIL: content-article-grid must use the native Query block\n");
    exit(1);
}
foreach (['WP_Query', 'get_posts(', 'pre_get_posts'] as $needle) {
    if (str_contains($article_grid, $needle)) {
        fwrite(STDERR, "FAIL: content-article-grid takes over WordPress query semantics via {$needle}\n");
        exit(1);
    }
}

$faq = file_get_contents($root . '/patterns/content-faq.php');
if (false === $faq) {
    fwrite(STDERR, "FAIL: unable to inspect content-faq.php\n");
    exit(1);
}
foreach (['FAQPage', 'application/ld+json', 'schema.org', 'register_post_type('] as $needle) {
    if (str_contains($faq, $needle)) {
        fwrite(STDERR, "FAIL: content-faq adds schema/domain behavior via {$needle}\n");
        exit(1);
    }
}

echo "PASS: R2 Content patterns contract\n";
