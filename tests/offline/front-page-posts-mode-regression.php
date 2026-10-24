<?php
/** Regression for WordPress posts-on-front versus configured static Front Page. */
declare(strict_types=1);

define('ABSPATH', __DIR__);
$GLOBALS['state'] = ['posts_index' => true, 'posts' => 2, 'loop' => 0, 'content_calls' => 0, 'cards' => 0];

function is_home(): bool { return $GLOBALS['state']['posts_index']; }
function get_header(): void { echo '<header>HEADER</header>'; }
function get_footer(): void { echo '<footer>FOOTER</footer>'; }
function have_posts(): bool { return $GLOBALS['state']['loop'] < $GLOBALS['state']['posts']; }
function the_post(): void { $GLOBALS['state']['loop']++; }
function the_ID(): void { echo $GLOBALS['state']['loop']; }
function post_class(string $class = ''): void { echo 'class="' . $class . '"'; }
function the_content(): void { $GLOBALS['state']['content_calls']++; echo 'FULL_POST_CONTENT'; }
function get_template_part(string $path): void {
    if ($path !== 'template-parts/content/card') { throw new RuntimeException('Unexpected template part ' . $path); }
    $GLOBALS['state']['cards']++;
    echo '<article class="aznet-theme-card">CARD</article>';
}
function the_posts_pagination(array $args = []): void { echo '<nav>PAGE_LINKS</nav>'; }
function esc_html_e(string $s, string $domain = ''): void { echo htmlspecialchars($s, ENT_QUOTES); }
function fail_test(string $message): never { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); }
function render_front(string $mode, int $posts): string {
    $GLOBALS['state'] = ['posts_index' => $mode === 'posts', 'posts' => $posts, 'loop' => 0, 'content_calls' => 0, 'cards' => 0];
    ob_start();
    require dirname(__DIR__, 2) . '/front-page.php';
    return (string) ob_get_clean();
}

$posts = render_front('posts', 2);
if ($GLOBALS['state']['content_calls'] !== 0) { fail_test('posts-on-front incorrectly renders full article content'); }
if ($GLOBALS['state']['cards'] !== 2 || substr_count($posts, 'CARD') !== 2) { fail_test('posts-on-front did not render exactly two card templates'); }
if (!str_contains($posts, 'aznet-theme-listing__grid') || !str_contains($posts, 'PAGE_LINKS')) { fail_test('posts-on-front missing grid or native pagination'); }
if (substr_count($posts, '<main') !== 1 || !str_contains($posts, 'HEADER') || !str_contains($posts, 'FOOTER')) { fail_test('posts-on-front missing shell landmarks'); }

$static = render_front('page', 1);
if ($GLOBALS['state']['content_calls'] !== 1 || $GLOBALS['state']['cards'] !== 0) { fail_test('static Front Page must preserve exactly one WordPress content boundary'); }
if (substr_count($static, 'FULL_POST_CONTENT') !== 1 || str_contains($static, 'PAGE_LINKS')) { fail_test('static Front Page content replaced or duplicated'); }

$empty = render_front('posts', 0);
if ($GLOBALS['state']['content_calls'] !== 0 || $GLOBALS['state']['cards'] !== 0 || !str_contains($empty, 'No content found.')) { fail_test('posts-on-front empty state broken'); }

echo "PASS: posts-on-front cards/pagination, static Front Page public content, empty state\n";
