<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

if (!class_exists('WP_Post')) {
    final class WP_Post
    {
        public int $ID;
        public string $post_type;

        public function __construct(int $id, string $postType)
        {
            $this->ID = $id;
            $this->post_type = $postType;
        }
    }
}

function classic_editor_policy_fail(string $message): never
{
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

function classic_editor_policy_assert(bool $expected, bool $actual, string $message): void
{
    if ($expected !== $actual) {
        classic_editor_policy_fail($message . ' expected=' . ($expected ? 'true' : 'false') . ' actual=' . ($actual ? 'true' : 'false'));
    }
}

$root = dirname(__DIR__, 2);
$policyPath = $root . '/inc/admin/editor-policy.php';
$bootstrapPath = $root . '/inc/admin/bootstrap.php';

if (!is_file($policyPath)) {
    classic_editor_policy_fail('editor policy module missing');
}

require_once $policyPath;

$filter = 'AZnet\\Theme\\Admin\\use_classic_editor_for_regular_content';
if (!is_callable($filter)) {
    classic_editor_policy_fail('editor policy callback missing');
}

classic_editor_policy_assert(false, $filter(true, new WP_Post(101, 'post')), 'Posts must use Classic Editor');
classic_editor_policy_assert(false, $filter(true, new WP_Post(202, 'page')), 'Regular Pages must use Classic Editor');
classic_editor_policy_assert(false, $filter(true, new WP_Post(300, 'page')), 'Static Front Page must also use Classic Editor');
classic_editor_policy_assert(false, $filter(false, new WP_Post(300, 'page')), 'Static Front Page must remain Classic when upstream already disabled Block Editor');
classic_editor_policy_assert(true, $filter(true, new WP_Post(404, 'product')), 'Other post types must preserve an enabled upstream editor decision');
classic_editor_policy_assert(false, $filter(false, new WP_Post(404, 'product')), 'Other post types must preserve a disabled upstream editor decision');

$policy = (string) file_get_contents($policyPath);
foreach (["get_option( 'show_on_front'", "get_option( 'page_on_front'"] as $needle) {
    if (str_contains($policy, $needle)) {
        classic_editor_policy_fail('editor policy must not special-case the Front Page: ' . $needle);
    }
}

$bootstrap = (string) file_get_contents($bootstrapPath);
if (!str_contains($bootstrap, "require_once __DIR__ . '/editor-policy.php';")) {
    classic_editor_policy_fail('admin bootstrap does not load editor policy');
}
if (!str_contains($bootstrap, "'use_block_editor_for_post'")) {
    classic_editor_policy_fail('admin bootstrap does not register the post-specific block editor filter');
}
if (!str_contains($bootstrap, 'use_classic_editor_for_regular_content')) {
    classic_editor_policy_fail('admin bootstrap does not wire the editor policy callback');
}

echo "PASS: Classic editor policy contract\n";
