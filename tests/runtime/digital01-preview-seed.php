<?php
/**
 * Disposable WordPress 6.9 fixture for Digital 01 L3/L4.
 * wp eval-file tests/runtime/digital01-preview-seed.php
 */
// WP-CLI eval-file executes this fixture inside eval(); no declare(strict_types) is valid here.

$manifest = \AZnet\Theme\template_manifest('digital-01');
if (!is_array($manifest)) {
    throw new RuntimeException('Digital 01 manifest not registered');
}

$pattern = \WP_Block_Patterns_Registry::get_instance()->get_registered('aznet-theme/homepage-digital-01');
if (!is_array($pattern) || '' === trim((string)($pattern['content'] ?? ''))) {
    throw new RuntimeException('Digital 01 WordPress-native pattern missing');
}

for ($i = 1; $i <= 4; ++$i) {
    $id = wp_insert_post([
        'post_type' => 'post',
        'post_status' => 'publish',
        'post_title' => 'Digital 01 Technology Article ' . $i,
        'post_content' => '<p>WordPress-owned technology editorial fixture ' . $i . '.</p>',
        'post_excerpt' => 'WordPress content is authoritative for article ' . $i . '.',
    ], true);
    if (is_wp_error($id)) {
        throw new RuntimeException('Post insertion failed: ' . $id->get_error_message());
    }
}

$frontId = wp_insert_post([
    'post_type' => 'page',
    'post_status' => 'publish',
    'post_title' => 'Digital 01 Preview Homepage',
    'post_name' => 'digital01-native-preview',
    'post_content' => wp_slash((string) $pattern['content']),
], true);
if (is_wp_error($frontId)) {
    throw new RuntimeException('Front Page insertion failed: ' . $frontId->get_error_message());
}

$settings = \AZnet\Theme\settings();
$settings['visual_preset'] = 'digital-01';
$settings['homepage_preset'] = 'digital-01';
set_theme_mod('aznet_theme_settings', \AZnet\Theme\normalize_settings($settings));
update_option('show_on_front', 'page');
update_option('page_on_front', (int) $frontId);

if ('digital-01' !== \AZnet\Theme\visual_preset()
    || 'digital-01' !== \AZnet\Theme\homepage_preset()
    || ! \AZnet\Theme\homepage_composer_active()) {
    throw new RuntimeException('Digital 01 composed Homepage not active');
}

$surfaceMap = \AZnet\Theme\homepage_effective_surface_map('digital-01');
if (count($surfaceMap) !== 1
    || ($surfaceMap[0]['key'] ?? null) !== 'front-page-content'
    || (int) ($surfaceMap[0]['source_id'] ?? 0) !== (int) $frontId) {
    throw new RuntimeException('Digital native front Page and effective Homepage source model disagree');
}
$nativeSummary = \AZnet\Theme\Admin\homepage_authoring_source_summary('digital-01', 'content');
if (($nativeSummary['status'] ?? null) !== 'READY'
    || ($nativeSummary['title'] ?? '') !== 'Digital 01 Preview Homepage') {
    throw new RuntimeException('Native authoring summary not bound to the selected WordPress Front Page');
}

$stored = get_post((int) $frontId);
if (!$stored instanceof \WP_Post
    || !str_contains((string) $stored->post_content, 'aznet-digital01')
    || !str_contains((string) $stored->post_content, '<!-- wp:query')) {
    throw new RuntimeException('Native WordPress Page did not retain authored block content');
}

file_put_contents('/tmp/digital01-front-id.txt', (string) $frontId);
file_put_contents('/tmp/digital01-content-sha.txt', hash('sha256', (string) $stored->post_content));
echo "PASS: Digital 01 WordPress-native source, pattern, explicit Front Page and empty-provider runtime fixture\n";
