<?php
/**
 * RED -> GREEN: native WordPress posts-on-front must delegate to home.php.
 *
 * The actual home.php template is exercised, not a duplicate card renderer.
 * WordPress-owned Page content must remain intact in static-page mode.
 */
declare(strict_types=1);

namespace AZnet\Theme {
    function content_shell_classes(bool $with_navigation = false): array {
        return [ 'aznet-theme-content-shell' ];
    }
}

namespace {
    define('ABSPATH', __DIR__);

    $GLOBALS['aznet_front_mode'] = 'posts';
    $GLOBALS['aznet_front_posts'] = 2;
    $GLOBALS['aznet_front_loop'] = 0;
    $GLOBALS['aznet_front_full_content_calls'] = 0;
    $GLOBALS['aznet_front_cards'] = 0;
    $GLOBALS['aznet_front_home_delegations'] = 0;

    function is_home(): bool { return 'posts' === $GLOBALS['aznet_front_mode']; }
    function get_header(): void { echo '<header data-aznet-test="header"></header>'; }
    function get_footer(): void { echo '<footer data-aznet-test="footer"></footer>'; }
    function have_posts(): bool { return $GLOBALS['aznet_front_loop'] < $GLOBALS['aznet_front_posts']; }
    function the_post(): void { $GLOBALS['aznet_front_loop']++; }
    function the_ID(): void { echo $GLOBALS['aznet_front_loop']; }
    function post_class(string $classes = ''): void { echo 'class="' . $classes . '"'; }
    function the_content(): void {
        $GLOBALS['aznet_front_full_content_calls']++;
        echo 'FULL_ARTICLE_BODY_SENTINEL';
    }
    function get_template_part(string $slug, ?string $name = null, array $args = []): void {
        if ('home' === $slug) {
            $GLOBALS['aznet_front_home_delegations']++;
            require dirname(__DIR__, 2) . '/home.php';
            return;
        }
        if ('template-parts/content/card' === $slug) {
            $GLOBALS['aznet_front_cards']++;
            echo '<article class="aznet-theme-card">ARTICLE_CARD_SENTINEL</article>';
            return;
        }
        throw new \RuntimeException('Unexpected template part: ' . $slug);
    }
    function single_post_title(string $prefix = '', bool $display = true): string { return ''; }
    function __(string $text, string $domain = ''): string { return $text; }
    function esc_html__(string $text, string $domain = ''): string { return $text; }
    function esc_attr__(string $text, string $domain = ''): string { return $text; }
    function esc_html_e(string $text, string $domain = ''): void { echo htmlspecialchars($text, ENT_QUOTES); }
    function esc_html(string $text): string { return htmlspecialchars($text, ENT_QUOTES); }
    function esc_attr(string $text): string { return htmlspecialchars($text, ENT_QUOTES); }
    function esc_url(string $url): string { return htmlspecialchars($url, ENT_QUOTES); }
    function home_url(string $path = ''): string { return 'http://fixture.local' . $path; }
    function the_posts_pagination(array $args = []): void { echo '<nav aria-label="Posts pagination">NATIVE_PAGINATION_SENTINEL</nav>'; }

    function fail_posts_front(string $message): never {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }

    function render_front_mode(string $mode, int $posts): string {
        $GLOBALS['aznet_front_mode'] = $mode;
        $GLOBALS['aznet_front_posts'] = $posts;
        $GLOBALS['aznet_front_loop'] = 0;
        $GLOBALS['aznet_front_full_content_calls'] = 0;
        $GLOBALS['aznet_front_cards'] = 0;
        $GLOBALS['aznet_front_home_delegations'] = 0;
        ob_start();
        require dirname(__DIR__, 2) . '/front-page.php';
        return (string) ob_get_clean();
    }

    $posts = render_front_mode('posts', 2);
    if ($GLOBALS['aznet_front_full_content_calls'] !== 0 || str_contains($posts, 'FULL_ARTICLE_BODY_SENTINEL')) {
        fail_posts_front('posts-on-front must not dump full Post bodies');
    }
    if ($GLOBALS['aznet_front_home_delegations'] !== 1 || $GLOBALS['aznet_front_cards'] !== 2) {
        fail_posts_front('posts-on-front must reuse native home.php cards exactly twice');
    }
    if (!str_contains($posts, 'NATIVE_PAGINATION_SENTINEL') || !str_contains($posts, 'aznet-theme-main--posts-page')) {
        fail_posts_front('posts-on-front lost posts index pagination/layout');
    }
    if (substr_count($posts, '<main') !== 1 || substr_count($posts, 'data-aznet-test="header"') !== 1
        || substr_count($posts, 'data-aznet-test="footer"') !== 1) {
        fail_posts_front('posts-on-front must have exactly one Header/Main/Footer shell');
    }

    $static = render_front_mode('page', 1);
    if ($GLOBALS['aznet_front_home_delegations'] !== 0 || $GLOBALS['aznet_front_full_content_calls'] !== 1
        || $GLOBALS['aznet_front_cards'] !== 0 || substr_count($static, 'FULL_ARTICLE_BODY_SENTINEL') !== 1) {
        fail_posts_front('static Front Page must retain exactly one native the_content boundary');
    }
    if (str_contains($static, 'NATIVE_PAGINATION_SENTINEL') || substr_count($static, '<main') !== 1) {
        fail_posts_front('static Front Page was replaced with posts index or duplicated main');
    }

    $empty = render_front_mode('posts', 0);
    if ($GLOBALS['aznet_front_home_delegations'] !== 1 || $GLOBALS['aznet_front_cards'] !== 0
        || $GLOBALS['aznet_front_full_content_calls'] !== 0
        || !str_contains($empty, 'No content is available here yet')) {
        fail_posts_front('empty posts-on-front must use home.php accessible recovery state');
    }
    echo "PASS: native posts-on-front home.php delegation, cards, pagination, empty state; static content unchanged\n";
}
