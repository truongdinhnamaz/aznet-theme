<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { define('ABSPATH', __DIR__ . '/'); }
$root = dirname(__DIR__, 2);
$module = $root . '/inc/theme/provisioning-blueprints.php';
if (!is_file($module)) { fwrite(STDERR, "FAIL: provisioning blueprint module missing\n"); exit(1); }
require_once $module;
$bp = \AZnet\Theme\provisioning_blueprint('law01-v1');
assert(is_array($bp));
assert($bp['key'] === 'law01-v1');
assert($bp['homepage_preset'] === 'law-01');
assert(\AZnet\Theme\provisioning_blueprint('unknown') === null);
assert(\AZnet\Theme\provisioning_blueprint_keys() === ['law01-v1', 'law01-v1-1']);
$pageRoles = ['home','about','services','service_business','service_civil','service_criminal','service_real_estate','service_family','service_labor','team','process','faq','contact'];
$categoryRoles = ['knowledge','knowledge_business','knowledge_civil','knowledge_criminal','knowledge_real_estate','knowledge_family','knowledge_labor','case_analysis','legal_news'];
assert(array_keys($bp['pages']) === $pageRoles);
assert(array_keys($bp['categories']) === $categoryRoles);
assert($bp['pages']['home']['status'] === 'publish');
assert($bp['pages']['services']['status'] === 'publish');
assert($bp['pages']['service_criminal']['parent_role'] === 'services');
assert($bp['categories']['knowledge']['parent_role'] === null);
assert($bp['categories']['knowledge_civil']['parent_role'] === 'knowledge');
assert($bp['editorial_examples']['status'] === 'draft');
assert($bp['editorial_examples']['items'] === [], 'v1 starter editorial behavior must remain unchanged');
assert($bp['menu']['location'] === 'primary');
foreach ($bp['pages'] as $role => $page) {
    assert(in_array($page['status'], ['publish'], true), "Structural page must publish: {$role}");
    assert(isset($page['title'], $page['excerpt'], $page['content']));
    assert(array_key_exists('parent_role', $page));
}
$allCopy = json_encode($bp, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
foreach (['500+','98%','1000+','tỷ lệ thắng','hàng đầu','luật sư Nguyễn','địa chỉ mẫu','090','@example','Điều 123','khách hàng nói','giải thưởng'] as $bad) {
    assert(!str_contains((string)$allCopy, $bad), "Forbidden starter claim/copy: {$bad}");
}
assert(str_contains($bp['pages']['process']['content'], 'Tiếp nhận yêu cầu'));
assert(str_contains($bp['pages']['process']['content'], 'Đánh giá vấn đề'));
assert(str_contains($bp['pages']['process']['content'], 'Đề xuất phương án'));
assert(str_contains($bp['pages']['process']['content'], 'Đồng hành thực hiện'));
assert(str_contains($bp['pages']['faq']['content'], '<details>'));
assert(str_contains($bp['pages']['faq']['content'], '<summary>'));
echo "PASS: Law 01 provisioning blueprint contract\n";
