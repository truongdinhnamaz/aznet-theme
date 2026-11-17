<?php
declare(strict_types=1);

// D-040/TPL3 bounded, local-only test harness. No WordPress content mutations.
define('ABSPATH', __DIR__ . '/');
define('AZNET_THEME_VERSION', '1.3.110');
$root = dirname(__DIR__, 2);
if ( ! is_file( $root . '/inc/theme/template-registry.php' ) ) { $root .= '/aznet-theme'; }
$GLOBALS['tpl_test_options'] = [];
$GLOBALS['tpl_test_root'] = sys_get_temp_dir() . '/aznet-tpl3-' . bin2hex(random_bytes(5));
mkdir($GLOBALS['tpl_test_root'], 0755, true);
function get_option($key, $default = false) { return $GLOBALS['tpl_test_options'][$key] ?? $default; }
function update_option($key, $value, $autoload = null) { $GLOBALS['tpl_test_options'][$key] = $value; return true; }
function wp_upload_dir($time = null, $create_dir = true) { return ['basedir' => $GLOBALS['tpl_test_root'], 'baseurl' => 'https://example.test/wp-content/uploads', 'error' => '']; }
function wp_mkdir_p($dir) { return is_dir($dir) || mkdir($dir, 0755, true); }
function get_bloginfo($name) { return '6.9'; }
function get_theme_file_path($path) { return $GLOBALS['theme_base'] . '/' . ltrim($path, '/'); }
function get_theme_file_uri($path) { return 'https://example.test/wp-content/themes/aznet-theme/' . ltrim($path, '/'); }
function wp_enqueue_style($handle, $url, $deps = [], $version = null) { $GLOBALS['tpl_styles'][$handle] = ['url' => $url, 'version' => $version]; }
function wp_enqueue_script($handle, $url, $deps = [], $version = null, $footer = false) { $GLOBALS['tpl_scripts'][$handle] = ['url' => $url, 'version' => $version]; }
function is_front_page() { return true; }
$GLOBALS['theme_base'] = $root;
require $root . '/inc/theme/template-registry.php';
require $root . '/inc/theme/template-loader.php';
require $root . '/inc/theme/local-template-packages.php';
require $root . '/inc/theme/assets.php';

function check_tpl(bool $ok, string $message): void { if (!$ok) { fwrite(STDERR, "FAIL: $message\n"); exit(1); } }
function package_fixture(array $manifest, array $files): array {
    return ['package_contract_version' => 1, 'requires' => ['theme' => '1.3.110', 'wordpress' => '6.9', 'php' => '8.1'], 'files' => $files];
}
$summary = \AZnet\Theme\load_local_template_manifests();
check_tpl(in_array('curtain-01', $summary['loaded'], true), 'Curtain 01 bundled manifest missing');
$base = \AZnet\Theme\template_manifest('curtain-01');
check_tpl(is_array($base), 'bundled Curtain 01 manifest missing');
$path = 'assets/css/components/homepage-curtain-01.css';
$raw = file_get_contents($root . '/' . $path);
$manifest = $base;
$manifest['version'] = '1.0.1';
$manifest['assets']['homepage'] = [$base['assets']['homepage'][0]];
$package = package_fixture($manifest, [$path => hash('sha256', $raw)]);
$good = \AZnet\Theme\local_template_package_validate($manifest, $package);
check_tpl(true === $good, 'existing bundled Curtain 01 CSS-only update must validate');

$bad = $manifest;
$bad['homepage']['hero']['source_type'] = 'private_table';
check_tpl(true !== \AZnet\Theme\local_template_package_validate($bad, $package), 'cannot override bundled template logic');
$bad = $manifest;
$bad['assets']['homepage'][0]['path'] = '/assets/js/arbitrary.php';
check_tpl(true !== \AZnet\Theme\local_template_package_validate($bad, $package), 'no PHP path in template package');
$badPackage = $package;
$badPackage['files']['inc/bad.php'] = str_repeat('a', 64);
check_tpl(true !== \AZnet\Theme\local_template_package_validate($manifest, $badPackage), 'PHP package injection must fail');
$badPackage = $package;
$badPackage['requires']['wordpress'] = '99.0';
check_tpl(true !== \AZnet\Theme\local_template_package_validate($manifest, $badPackage), 'incompatible WordPress must fail');

$new = [
    'contract_version' => 2, 'id' => 'sample-native-01', 'name' => 'Sample Native 01',
    'version' => '1.0.0', 'category' => 'Sample', 'description' => 'WordPress native',
    'capabilities' => ['homepage'],
    'presentation' => ['homepage_preset' => 'sample-native-01'],
    'assets' => ['homepage' => [['type' => 'style', 'handle' => 'aznet-theme-homepage-sample-native-01', 'path' => '/assets/css/sample.css', 'dependencies' => ['aznet-theme-tokens']]]],
    'homepage' => ['composition' => ['mode' => 'native'], 'authoring' => ['sections' => ['content']]],
    'provisioning' => [],
];
check_tpl(true === \AZnet\Theme\local_template_package_validate($new, package_fixture($new, ['assets/css/sample.css' => str_repeat('b', 64)])), 'new v2 native template should be installable without Core renderer changes');
$new['homepage']['composition']['mode'] = 'mapped';
check_tpl(true !== \AZnet\Theme\local_template_package_validate($new, package_fixture($new, ['assets/css/sample.css' => str_repeat('b', 64)])), 'unimplemented arbitrary mapping must not become installable');

if (class_exists('ZipArchive')) {
    $zip = new ZipArchive();
    $filename = $GLOBALS['tpl_test_root'] . '/curtain.zip';
    check_tpl(true === $zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE), 'create fixture ZIP');
    $zip->addFromString('manifest.json', json_encode($manifest, JSON_UNESCAPED_SLASHES));
    $zip->addFromString('package.json', json_encode($package, JSON_UNESCAPED_SLASHES));
    $zip->addFromString($path, $raw);
    $zip->close();
    $result = \AZnet\Theme\local_template_package_install($filename, hash_file('sha256', $filename));
    check_tpl(true === ($result['ok'] ?? null), 'install CSS-only Curtain 01 package, reason ' . json_encode($result));
    $style = \AZnet\Theme\local_template_package_asset('curtain-01', '/' . $path);
    check_tpl(is_array($style) && str_contains($style['url'], '/aznet-theme-templates/'), 'installed CSS should resolve to external URL');
    check_tpl(\AZnet\Theme\local_template_package_rollback('curtain-01'), 'rollback installed Curtain 01 to bundled');
    check_tpl(null === \AZnet\Theme\local_template_package_asset('curtain-01', '/' . $path), 'rollback must serve Theme CSS again');
    $badHash = \AZnet\Theme\local_template_package_install($filename, str_repeat('0', 64));
    check_tpl(false === ($badHash['ok'] ?? null), 'unexpected ZIP hash must reject');
    $zip = new ZipArchive();
    $invalid = $GLOBALS['tpl_test_root'] . '/invalid.zip';
    $zip->open($invalid, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFromString('manifest.json', json_encode($manifest));
    $zip->addFromString('package.json', json_encode($package));
    $zip->addFromString($path, $raw);
    $zip->addFromString('evil.php', '<?php system("id");');
    $zip->close();
    $denied = \AZnet\Theme\local_template_package_install($invalid, hash_file('sha256', $invalid));
    check_tpl(false === ($denied['ok'] ?? null), 'executable PHP in ZIP must reject');
    echo "PASS: D-040 local ZIP install, rollback, SHA rejection, executable-code rejection\n";
} else {
    echo "SKIP: ZipArchive unavailable in local PHP; pure contract checks still exercised\n";
}
echo "PASS: D-040 local template manifest/compatibility/ownership contracts\n";
