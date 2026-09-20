<?php
declare(strict_types=1);
namespace {
    define('ABSPATH', '/test/');
    class WP_Post { public function __construct(public int $ID, public string $title = 'Source page') {} }
    $pages = [10 => new WP_Post(10, 'About source'), 20 => new WP_Post(20, 'Team source'), 30 => new WP_Post(30, 'Services source')];
    $settings = ['homepage_about_page' => 10, 'homepage_team_page' => 20, 'homepage_services_page' => 30];
    $children = []; $caption = 'AI illustration - not actual staff'; $image = '<img src="/wp-content/uploads/illustration.webp" alt="AI illustration" width="1036" height="320">';
    function get_the_excerpt($post) { return 'Editorial source summary'; }
    function get_the_title($post) { return $post->title; }
    function has_post_thumbnail($post) { return $post->ID === 20 || $post->ID === 40; }
    function get_the_post_thumbnail($post, $size, $attrs = []) { return $GLOBALS['image']; }
    function get_the_post_thumbnail_caption($post) { return $GLOBALS['caption']; }
    function get_permalink($post) { return '/?page_id=' . $post->ID; }
    function __($text, $domain = '') { return $text; }
    function esc_html($text) { return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8'); }
    function esc_attr($text) { return esc_html($text); }
    function esc_url($text) { return esc_html($text); }
    function esc_html_e($text, $domain = '') { echo esc_html($text); }
    function esc_attr_e($text, $domain = '') { echo esc_attr($text); }
    function wp_strip_all_tags($text) { return strip_tags($text); }
    function wp_kses_post($html) { return strip_tags($html, '<img><a><em><strong>'); }
    function render_profile(): string {
        ob_start(); include dirname(__DIR__, 2) . '/template-parts/homepage/law-01/profile.php'; return (string) ob_get_clean();
    }
    function must(bool $condition, string $message): void { if (!$condition) { throw new \RuntimeException($message); } }
}
namespace AZnet\Theme {
    function setting($key, $default = null) { return $GLOBALS['settings'][$key] ?? $default; }
    function homepage_page_reference($id) { return $GLOBALS['pages'][$id] ?? null; }
    function homepage_direct_published_children($id, $limit) { return array_slice($GLOBALS['children'][$id] ?? [], 0, $limit); }
}
namespace {
    $html = render_profile();
    must(str_contains($html, 'aznet-theme-law01-profile__team-illustration'), 'Mapped Team featured artwork with caption must render without fabricated member Pages.');
    must(str_contains($html, '<figcaption>AI illustration - not actual staff</figcaption>'), 'Source caption must remain visibly attached to the artwork.');
    must(!str_contains($html, 'aznet-theme-law01-profile__member '), 'Illustration must not become a member record.');
    $caption = '';
    must(!str_contains(render_profile(), 'aznet-theme-law01-profile__team-illustration'), 'Uncaptioned fallback artwork must remain hidden.');
    $caption = '  <b></b>  ';
    must(!str_contains(render_profile(), 'aznet-theme-law01-profile__team-illustration'), 'Empty markup is not a meaningful caption.');
    $caption = 'AI <strong>illustration</strong> & reference';
    must(str_contains(render_profile(), '<figcaption>AI illustration &amp; reference</figcaption>'), 'Caption must be escaped plain text.');
    $image = '';
    must(!str_contains(render_profile(), 'aznet-theme-law01-profile__team-illustration'), 'Missing image output must not leave an empty figure.');
    $image = '<img src="/illustration.webp" alt="AI illustration">';
    $children[20] = [new WP_Post(40, 'Verified editorial member')];
    $html = render_profile();
    must(str_contains($html, 'Verified editorial member'), 'Real source member cards must retain precedence.');
    must(!str_contains($html, 'aznet-theme-law01-profile__team-illustration'), 'Artwork must not replace populated source members.');
    unset($settings['homepage_team_page']);
    must(!str_contains(render_profile(), 'aznet-theme-law01-profile__team-illustration'), 'No mapped Team means no fallback artwork.');
    $children[30] = [new WP_Post(50, 'A mapped service')];
    $html = render_profile();
    $cta_pos = strpos($html, 'class="aznet-theme-law01-button"');
    $facts_pos = strpos($html, 'class="aznet-theme-law01-profile__facts"');
    must(false !== $cta_pos && false !== $facts_pos && $cta_pos < $facts_pos, 'About CTA must precede reference facts, matching the approved demo.');
    echo "PASS: source-captioned Team artwork fallback and About CTA order, 8 cases\n";
}
