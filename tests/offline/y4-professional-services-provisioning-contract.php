<?php
/**
 * Y4 Professional Services provisioning ownership contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
if (!defined('ABSPATH')) {
    define('ABSPATH', $root . '/');
}

require_once $root . '/inc/theme/provisioning-professional-services.php';
require_once $root . '/inc/theme/provisioning-blueprints.php';

$historical = [];
foreach (['law01-v1', 'law01-v1-1', 'law01-v1-2'] as $law_key) {
    $historical[$law_key] = AZnet\Theme\provisioning_blueprint($law_key);
    assert(is_array($historical[$law_key]), "Historical Law01 blueprint missing: {$law_key}");
    assert('law-01' === $historical[$law_key]['homepage_preset'], "Historical Law01 homepage preset changed: {$law_key}");
}

assert(in_array('professional-services-v1', AZnet\Theme\provisioning_blueprint_keys(), true), 'Y4 generic blueprint key missing');
$bp = AZnet\Theme\provisioning_blueprint('professional-services-v1');
assert(is_array($bp), 'Y4 professional-services-v1 blueprint missing');
assert('professional-services-v1' === $bp['key']);
assert('off' === $bp['homepage_preset']);
assert(['home', 'about', 'services', 'team', 'contact'] === array_keys($bp['pages']));
assert(isset($bp['categories']) && is_array($bp['categories']));
assert([] === $bp['categories'] || ['news'] === array_keys($bp['categories']));
assert('primary' === ($bp['menu']['location'] ?? null));
assert(['home', 'about', 'services', 'team', 'contact'] === ($bp['menu']['roles'] ?? null));
assert([] === ($bp['editorial_examples']['items'] ?? null));

$copy = json_encode($bp, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
assert(is_string($copy));
foreach ([
    'luật sư', 'pháp lý', 'điều luật', 'vụ án', 'bệnh viện', 'bác sĩ', 'chẩn đoán',
    'sản phẩm', 'giỏ hàng', 'checkout', 'woocommerce', '090', '@example', 'địa chỉ mẫu',
    'chứng nhận', 'giải thưởng', 'khách hàng nói', '98%', '500+',
] as $forbidden) {
    assert(!str_contains(mb_strtolower($copy, 'UTF-8'), mb_strtolower($forbidden, 'UTF-8')), "Forbidden vertical/fabricated starter truth: {$forbidden}");
}

$module = $root . '/inc/theme/provisioning-professional-services.php';
assert(is_file($module), 'Y4 focused professional-services blueprint module missing');
$module_source = file_get_contents($module);
assert(is_string($module_source));
foreach (['get_post_meta(', 'update_post_meta(', 'get_option(', 'update_option(', '$wpdb', 'WC_', 'woocommerce'] as $forbidden) {
    assert(!str_contains($module_source, $forbidden), "Y4 blueprint crossed ownership boundary: {$forbidden}");
}
assert(str_contains($module_source, 'professional_page_kit_content'));

$planner = file_get_contents($root . '/inc/theme/provisioning-plan.php');
assert(is_string($planner));
assert(str_contains($planner, "['homepage_preset']"), 'Planner must derive Homepage preset from blueprint');
assert(!preg_match('/set_homepage_preset[^\n]{0,240}law-01/s', $planner), 'Planner must not unconditionally force law-01');

$admin = file_get_contents($root . '/inc/admin/provisioning.php');
assert(is_string($admin));
assert(str_contains($admin, 'professional-services-v1'), 'Provisioning wizard must allow selecting the Y4 blueprint');
assert(str_contains($admin, 'Professional Services'), 'Provisioning wizard must expose the Professional Services label');
assert(str_contains($admin, 'law01-v1-2'), 'Y4 must preserve the existing Law01 wizard path');
assert(str_contains($admin, 'Bước %d/4'), 'Y4 must preserve the four-step confirmation flow');
assert(str_contains($admin, 'confirm_plan'), 'Y4 must preserve explicit plan confirmation before mutation');

$style = file_get_contents($root . '/style.css');
$functions = file_get_contents($root . '/functions.php');
assert(is_string($style) && preg_match('/^Version:\s*1\.2\.0\s*$/mi', $style) === 1);
assert(is_string($functions) && preg_match("/define\(\s*'AZNET_THEME_VERSION'\s*,\s*'1\.2\.0'\s*\)/", $functions) === 1);

echo "PASS: Y4 Professional Services provisioning ownership contract\n";
