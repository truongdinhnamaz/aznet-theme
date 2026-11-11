<?php
declare(strict_types=1);
$root = dirname(__DIR__, 2);
$assert = static function (bool $pass, string $note): void {
    if (!$pass) { fwrite(STDERR, "FAIL: " . $note . "\n"); exit(1); }
};
$src = $root . '/inc/theme/floating-contact.php';
$css = $root . '/assets/css/components/floating-contact.css';
$bootstrap = $root . '/inc/theme/bootstrap.php';
$assert(is_file($src) && is_file($css) && is_file($bootstrap), 'required assets exist');
$php = (string) file_get_contents($src);
$style = (string) file_get_contents($css);
$boot = (string) file_get_contents($bootstrap);
$assert(str_contains($boot, "require_once __DIR__ . '/floating-contact.php';"), 'Theme bootstrap owns component');
foreach (['render_floating_contact', 'enqueue_floating_contact_assets', 'floating_contact_body_classes'] as $callback) {
    $assert(str_contains($boot, $callback), 'hook wired: ' . $callback);
}
$assert(!str_contains($php, '0987584394'), 'no site data hardcoded in Theme');
foreach (['position: fixed', ':focus-visible', '@media (max-width: 767px)', 'prefers-reduced-motion', 'safe-area-inset-bottom', 'min-width: 48px'] as $cssMarker) {
    $assert(str_contains($style, $cssMarker), 'CSS marker: ' . $cssMarker);
}
define('ABSPATH', '/');
$GLOBALS['test_enabled'] = false;
$GLOBALS['test_locations'] = ['footer-contact'=>15];
$GLOBALS['test_items'] = [
    (object) ['post_status'=>'publish','url'=>'tel:+84987584394','title'=>'Hotline: 0987.58.4394'],
    (object) ['post_status'=>'publish','url'=>'https://zalo.me/0987584394','title'=>'Zalo Van Anh'],
    (object) ['post_status'=>'publish','url'=>'https://evil.example.test','title'=>'Unrelated'],
];
$GLOBALS['test_styles'] = [];
function get_theme_mod($key,$default=false) { return $key==='aznet_theme_floating_contact_enabled' ? $GLOBALS['test_enabled'] : $default; }
function get_nav_menu_locations() { return $GLOBALS['test_locations']; }
function wp_get_nav_menu_items($id) { return $id===15 ? $GLOBALS['test_items'] : []; }
function esc_url($x) { return htmlspecialchars((string)$x,ENT_QUOTES,'UTF-8'); }
function esc_attr($x) { return htmlspecialchars((string)$x,ENT_QUOTES,'UTF-8'); }
function esc_html($x) { return htmlspecialchars((string)$x,ENT_QUOTES,'UTF-8'); }
function __($value,$domain) { return $value; }
function get_theme_file_uri($path) { return 'https://theme.example.test'.$path; }
function wp_enqueue_style(...$a) { $GLOBALS['test_styles'][] = $a; }
require_once $src;
$assert(\AZnet\Theme\floating_contact_links()===[], 'default off');
ob_start(); \AZnet\Theme\render_floating_contact();
$assert(ob_get_clean()==='', 'no output if disabled');
$GLOBALS['test_enabled']='1';
$links = \AZnet\Theme\floating_contact_links();
$assert(count($links)===2 && isset($links['phone'],$links['zalo']), 'two links only');
$assert($links['phone']['url']==='tel:+84987584394', 'phone read from WordPress menu');
$assert($links['zalo']['url']==='https://zalo.me/0987584394', 'Zalo read from WordPress menu');
ob_start(); \AZnet\Theme\render_floating_contact(); $html=ob_get_clean();
$assert(substr_count($html,'<a ')===2, 'single rail with two links');
$assert(str_contains($html,'data-aznet-theme-floating-contact'), 'rail marker');
$assert(str_contains($html,'aria-label=') && str_contains($html,'aria-hidden="true"'), 'accessible labels/icons');
$assert(!str_contains($html,'evil.example.test'), 'unrelated menu links excluded');
\AZnet\Theme\enqueue_floating_contact_assets();
$assert(count($GLOBALS['test_styles'])===1 && $GLOBALS['test_styles'][0][0]==='aznet-theme-floating-contact', 'scoped style');
$GLOBALS['test_items'][1]->url='https://evil.example.test/zalo.me/0987584394';
$assert(\AZnet\Theme\floating_contact_links()===[], 'invalid Zalo rejected');
$GLOBALS['test_items'][1]->url='https://zalo.me/0987584394';
$GLOBALS['test_locations']=[];
$assert(\AZnet\Theme\floating_contact_links()===[], 'missing menu fail soft');
echo "PASS: floating contact menu contract\n";
