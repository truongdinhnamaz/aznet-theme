<?php
declare(strict_types=1);
if (! defined('ABSPATH')) { define('ABSPATH', __DIR__ . '/'); }
$root = dirname(__DIR__, 2);
require_once $root . '/inc/theme/settings.php';
$defaults = AZnet\Theme\settings_defaults();
assert($defaults['schema_version'] === 2);
assert($defaults['homepage_preset'] === 'off');
assert($defaults['homepage_services_page'] === 0);
assert($defaults['homepage_knowledge_terms'] === []);
$normalized = AZnet\Theme\normalize_settings([
  'homepage_preset' => 'law-01',
  'homepage_services_page' => '12',
  'homepage_about_page' => -5,
  'homepage_knowledge_terms' => ['9', 9, 3, 0, -2, 'bad'],
  'foreign_homepage_state' => ['must' => 'drop'],
]);
assert($normalized['homepage_preset'] === 'law-01');
assert($normalized['homepage_services_page'] === 12);
assert($normalized['homepage_about_page'] === 0);
assert($normalized['homepage_knowledge_terms'] === [9, 3]);
assert(! array_key_exists('foreign_homepage_state', $normalized));
echo "PASS: Homepage Composer settings contract\n";
