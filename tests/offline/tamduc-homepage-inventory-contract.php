<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$workflow = $root . '/.github/workflows/tamduc-homepage-inventory.yml';
$browser = $root . '/tests/browser/tamduc-homepage-inventory.mjs';

assert(is_file($workflow), 'Tâm Đức inventory workflow must exist');
assert(is_file($browser), 'Tâm Đức inventory browser harness must exist');

$workflowText = file_get_contents($workflow);
$browserText = file_get_contents($browser);

assert(is_string($workflowText));
assert(is_string($browserText));

foreach ([
    'ops/tamduc-homepage-completion',
    'permissions:',
    'contents: read',
    'PILOT_WP_USER',
    'PILOT_WP_PASSWORD',
    'tests/browser/tamduc-homepage-inventory.mjs',
] as $needle) {
    assert(str_contains($workflowText, $needle), "Inventory workflow missing required marker: {$needle}");
}

foreach ([
    'site_title',
    'tagline',
    'theme_version',
    'custom_logo',
    'primary_menu',
    'front_page',
    'homepage_preset',
    'homepage_variant',
    'public_contact_links',
    'homepage_sections',
    'wp_public_inventory',
    'published_pages',
    'published_posts',
    'categories',
    'media_candidates',
    'page_content_inventory',
    '_fields=id,parent,slug,link,title,excerpt,content,featured_media',
    'observed',
    'not_observed',
    'unknown',
] as $needle) {
    assert(str_contains($browserText, $needle), "Inventory harness missing required output marker: {$needle}");
}

foreach ([
    'click({ force: true })',
    'wp-submit-delete',
    'update-core.php',
    'plugin-install.php',
    'theme-install.php',
    'aznet_theme_apply_provisioning',
    'aznet_theme_save',
] as $forbidden) {
    assert(!str_contains($browserText, $forbidden), "Inventory harness must remain read-only: {$forbidden}");
}

echo "PASS: Tâm Đức homepage inventory static contract\n";
