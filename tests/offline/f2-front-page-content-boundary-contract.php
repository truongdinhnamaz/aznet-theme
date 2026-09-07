<?php

declare(strict_types=1);

define('ABSPATH', __DIR__);

$GLOBALS['f2_loop'] = 0;

function get_header(): void { echo '<header data-f2="header"></header>'; }
function get_footer(): void { echo '<footer data-f2="footer"></footer>'; }
function have_posts(): bool { return $GLOBALS['f2_loop'] < 1; }
function the_post(): void { $GLOBALS['f2_loop']++; }
function the_ID(): void { echo '42'; }
function post_class(string $class = ''): void { echo 'class="' . $class . '"'; }
function the_content(): void { echo '<section data-f2="journey-body">JOURNEY_BODY_SENTINEL</section>'; }
function esc_html_e(string $text, string $domain = ''): void { echo $text; }

function fail_test(string $message): never
{
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

$root = dirname(__DIR__, 2);
$template = $root . '/front-page.php';
if (!is_file($template)) {
    fail_test('front-page.php missing');
}

ob_start();
require $template;
$output = (string) ob_get_clean();

if (1 !== substr_count($output, 'JOURNEY_BODY_SENTINEL')) {
    fail_test('public WordPress content body must render exactly once');
}

$main_open = strpos($output, '<main');
$body = strpos($output, 'JOURNEY_BODY_SENTINEL');
$main_close = strpos($output, '</main>');
if (false === $main_open || false === $body || false === $main_close || !($main_open < $body && $body < $main_close)) {
    fail_test('content body must remain inside the Theme-owned main shell');
}

if (false === strpos($output, 'data-f2="header"') || false === strpos($output, 'data-f2="footer"')) {
    fail_test('Theme header/footer must remain outside the replaceable Page body');
}

if (!(strpos($output, 'data-f2="header"') < $main_open && $main_close < strpos($output, 'data-f2="footer"'))) {
    fail_test('Theme header/main/footer composition order changed');
}

echo "PASS: F2 WordPress body integration boundary contract\n";
