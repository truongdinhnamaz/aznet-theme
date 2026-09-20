<?php
declare(strict_types=1);
namespace AZnet\Theme;
// Render the real template using only a minimal public WordPress API fixture.
define('ABSPATH', __DIR__);
$GLOBALS['law01_test_posts'] = [(object) ['ID' => 41, 'post_title' => 'Example article']];
function homepage_latest_posts(...$args): array { return $GLOBALS['law01_test_posts']; }
function setting($key, $default = null) { return $default; }
function homepage_ledger_ids(): array { return []; }
function homepage_ledger_add(array $ids): void {}
function get_option($key, $default = null) { return $default; }
function get_the_category($id): array { return []; }
function has_post_thumbnail($post): bool { return false; }
function get_permalink($post): string { return 'https://example.test/article-' . $post->ID . '/'; }
function get_the_title($post): string { return $post->post_title; }
function get_the_date($format, $post): string { return '2026-09-20'; }
function get_the_excerpt($post): string { return 'Example excerpt'; }
function esc_html($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_attr($value): string { return esc_html($value); }
function esc_url($value): string { return esc_attr($value); }
function __($text, $domain = ''): string { return $text; }
function esc_html_e($text, $domain = ''): void { echo esc_html($text); }
function esc_attr__($text, $domain = ''): string { return esc_attr($text); }
ob_start();
require dirname(__DIR__, 2) . '/template-parts/homepage/law-01/latest.php';
$html = (string) ob_get_clean();
preg_match_all('/<a\b([^>]*\bclass="aznet-theme-law01-text-link"[^>]*)>/', $html, $matches);
if (count($matches[1]) !== 1 || !str_contains($matches[1][0], 'href="https://example.test/article-41/"')) {
    fwrite(STDERR, "FAIL: article read-more must be a real keyboard-accessible permalink, not a decorative span.\n");
    exit(1);
}
if (str_contains($matches[1][0], 'aria-hidden="true"')) {
    fwrite(STDERR, "FAIL: article read-more must not be hidden from assistive technology.\n");
    exit(1);
}
echo "PASS: real template renders an accessible read-more permalink.\n";
