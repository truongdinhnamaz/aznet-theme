<?php

declare(strict_types=1);

define('ABSPATH', __DIR__);

$GLOBALS['f5_loop'] = 0;

function get_header(): void { echo '<header>NATIVE_HEADER</header>'; }
function get_footer(): void { echo '<footer>NATIVE_FOOTER</footer>'; }
function have_posts(): bool { return $GLOBALS['f5_loop'] < 1; }
function the_post(): void { $GLOBALS['f5_loop']++; }
function the_ID(): void { echo '7'; }
function post_class(string $class = ''): void { echo 'class="' . $class . '"'; }
function the_content(): void { echo 'NATIVE_PAGE_CONTENT'; }
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

foreach (['NATIVE_HEADER', 'NATIVE_PAGE_CONTENT', 'NATIVE_FOOTER'] as $needle) {
    if (false === strpos($output, $needle)) {
        fail_test("native fallback missing {$needle}");
    }
}

if (false !== strpos($output, 'choiceguide_') || false !== strpos($output, 'ConvertFlow')) {
    fail_test('native fallback leaked a ConvertFlow dependency');
}

echo "PASS: F5 Homepage absent/native fail-soft contract\n";
