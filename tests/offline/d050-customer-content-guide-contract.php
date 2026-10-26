<?php
/**
 * Customer-friendly WordPress-native editing-source guide (#525).
 *
 * AZnet Theme may explain which owner edits which content; it must
 * not become a content editor, a mailbox owner, or a plugin admin.
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$center = (string) file_get_contents($root . '/inc/admin/control-center.php');
$fail = static function (string $reason): never {
    fwrite(STDERR, "FAIL: customer editing guide: {$reason}\n");
    exit(1);
};

if (!str_contains($center, 'function render_content_editing_guide(): void {')) {
    $fail('Overview lacks the reusable WordPress-native editing-source guide');
}
$start = strpos($center, 'function render_content_editing_guide(): void {');
$stop = strpos($center, 'function render_control_center(): void {', $start);
if ($start === false || $stop === false || $start >= $stop) {
    $fail('guide must be a bounded read-only presentation function');
}
$guide = substr($center, $start, $stop - $start);
foreach ([
    'Chỉnh sửa nội dung ở đâu?',
    'WordPress',
    'Ảnh đại diện',
    'Chuyên mục',
    'Hỏi đáp',
    'Footer',
    'biểu mẫu',
    'aznet-theme-content-editing-guide',
] as $marker) {
    if (!str_contains($guide, $marker)) {
        $fail('missing editing-source explanation: ' . $marker);
    }
}
if (!str_contains($center, 'render_content_editing_guide();')) {
    $fail('guide is not shown in the ordinary customer Overview');
}
$overview = strpos($center, "if ( 'overview' === \$section ) {");
$support = strpos($center, "} elseif ( 'support' === \$section ) {", (int) $overview);
if ($overview === false || $support === false || !str_contains(substr($center, $overview, $support - $overview), 'render_content_editing_guide();')) {
    $fail('guide must be in customer Overview, not gated operator Support');
}
$overviewSource = substr($center, $overview, $support - $overview);
foreach ([
    "admin_url( 'edit.php' )",
    "admin_url( 'edit-tags.php?taxonomy=category' )",
    "admin_url( 'edit.php?post_type=page' )",
    "admin_url( 'upload.php' )",
    "'section' => 'homepage'",
    "'section' => 'footer'",
    "admin_url( 'post-new.php?post_type=page' )",
    "admin_url( 'nav-menus.php' )",
] as $marker) {
    if (!str_contains($overviewSource, $marker)) {
        $fail('missing WordPress-native or existing Theme route: ' . $marker);
    }
}
foreach ([
    'wp_insert_post', 'wp_update_post', 'update_option', 'update_post_meta',
    'get_post_meta', '$wpdb', 'wpcf7_', 'choiceguide_', 'rootprofile_',
    '$_POST', 'register_post_type', 'wp_mail',
] as $forbidden) {
    if (str_contains($guide, $forbidden)) {
        $fail('guide crossed data/provider ownership boundary: ' . $forbidden);
    }
}
if (!str_contains($center, "in_array( \$section, [ 'support', 'system-health' ], true ) && ! danger_zone_is_authorized()")) {
    $fail('developer-only Support/Danger boundary must remain unchanged');
}
echo "PASS: WordPress-native customer editing guide without Theme content takeover\n";
