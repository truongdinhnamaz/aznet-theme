<?php
/**
 * Disposable WordPress 6.9 FAQ Classic Editor runtime/browser fixture (#527).
 *
 * Run after the retained Gutenberg Core Details L4 matrix in GitHub Actions.
 * This script MUST NOT be used on any real WordPress site.
 */
if (!defined('ABSPATH') || getenv('GITHUB_ACTIONS') !== 'true'
    || !str_contains((string) home_url('/'), '127.0.0.1:8080')) {
    throw new RuntimeException('Refusing to mutate any non-disposable WordPress site.');
}
$settings = (array) get_theme_mod('aznet_theme_settings', []);
$faq_id = (int) ($settings['homepage_faq_page'] ?? 0);
$page = $faq_id > 0 ? get_post($faq_id) : null;
if (!$page instanceof WP_Post || $page->post_type !== 'page' || $page->post_status !== 'publish') {
    throw new RuntimeException('Retained published FAQ Page fixture not found.');
}

$classic = '<header id="faq-classic-author-hero"><h1>Do not duplicate this FAQ Page hero</h1></header>';
for ($i = 1; $i <= 6; $i++) {
    $suffix = (string) $i;
    $body = $i === 3
        ? '<p>Classic answer ' . $suffix . ' <img src="x" onerror="window.__faq_injection=true"></p><script>window.__faq_injection=true</script>'
        : '<p>Classic answer ' . $suffix . ' — tiếng Việt.</p>';
    $classic .= '<details><summary>Classic question ' . $suffix . '</summary>' . $body . '</details>';
}

$updated = wp_update_post(['ID' => $faq_id, 'post_content' => $classic], true);
if (is_wp_error($updated) || (int) $updated !== $faq_id) {
    throw new RuntimeException('Could not switch disposable FAQ fixture to native Classic Editor HTML.');
}

ob_start();
get_template_part('template-parts/homepage/law-01/faq');
$preview = (string) ob_get_clean();
$expect = static function (bool $condition, string $reason): void {
    if (!$condition) {
        throw new RuntimeException('FAIL: classic FAQ mapped-Page preview: ' . $reason);
    }
};
$expect(str_contains($preview, 'aznet-theme-law01-faq__items'), 'missing items container');
$expect(substr_count($preview, '<details') === 5, 'must emit exactly five native details');
for ($i = 1; $i <= 5; $i++) {
    $expect(str_contains($preview, 'Classic question ' . $i), 'missing authored question ' . $i);
}
$expect(!str_contains($preview, 'Classic question 6'), 'must not leak sixth question');
$expect(!str_contains($preview, 'faq-classic-author-hero'), 'must not duplicate authored Page hero');
$expect(!str_contains($preview, '<h1'), 'must not duplicate FAQ Page H1 in Homepage');
$expect(!str_contains($preview, '<script') && !str_contains($preview, 'onerror='), 'unsafe HTML/attribute leaked');
$expect(substr_count($preview, 'wp-block-details') === 5, 'must reuse existing scoped Details presentation');
$expect(str_contains($preview, 'Classic answer 1'), 'must preserve authored answer');
$expect(str_contains($preview, 'tiếng Việt'), 'must preserve Unicode text');

echo "PASS: raw Classic Editor FAQ Page projected as five sanitized native Details without Hero/header or mutation beyond disposable fixture\n";
