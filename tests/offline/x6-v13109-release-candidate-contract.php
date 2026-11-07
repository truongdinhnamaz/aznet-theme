<?php
/**
 * AZnet Theme 1.3.109: bounded metadata candidate for the merged
 * WooCommerce Single Product breadcrumb container alignment repair.
 *
 * Technical candidate only; this does not authorize tag, GitHub Release
 * or production deployment.
 *
 * @package AZnetTheme
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$read = static function (string $path) use ($root): string {
    $value = @file_get_contents($root . '/' . $path);
    if (! is_string($value)) {
        fwrite(STDERR, "FAIL X6 1.3.109: missing {$path}\n");
        exit(1);
    }
    return $value;
};

$style    = $read('style.css');
$boot     = $read('functions.php');
$verify   = $read('scripts/verify-v1-core.sh');
$workflow = $read('.github/workflows/x6-cross-surface-release.yml');
$product  = $read('assets/css/components/woocommerce-product.css');
$assets   = $read('inc/theme/assets.php');
$contract = $read('tests/offline/r4-woo-product-breadcrumb-alignment-contract.php');
$browser  = $read('tests/browser/r4-woocommerce-l4.mjs');

$fail = static function (string $message): never {
    fwrite(STDERR, "FAIL X6 1.3.109 release candidate: {$message}\n");
    exit(1);
};

if (! preg_match('/^Version:\s*1\.3\.109\s*$/m', $style)
    || ! str_contains($boot, "define( 'AZNET_THEME_VERSION', '1.3.109' );")) {
    $fail('style.css and functions.php must declare identical 1.3.109 metadata');
}
if (! str_contains($verify, '  1.3.108)')
    || ! str_contains($verify, 'tests/offline/x6-v13108-release-candidate-contract.php')
    || ! str_contains($verify, '  1.3.109)')
    || ! str_contains($verify, 'tests/offline/x6-v13109-release-candidate-contract.php')
    || ! str_contains($verify, 'FAIL: unsupported Theme release contract version')) {
    $fail('strict dispatcher must retain 1.3.108, add 1.3.109 and fail-closed default');
}
if (! str_contains($product, '.single-product #main > .woocommerce-breadcrumb')
    || ! str_contains($product, 'width: min(100%, var(--aznet-theme-container-shell, 77.5rem));')
    || ! str_contains($product, 'margin-inline: auto;')
    || ! str_contains($contract, 'breadcrumb shares product container geometry')
    || ! str_contains($browser, 'breadcrumbLayout')
    || ! str_contains($browser, 'breadcrumb/content shell misalignment')
    || ! str_contains($assets, "'aznet-theme-woocommerce-product'")
    || ! str_contains($assets, 'should_enqueue_woocommerce_product_assets()')) {
    $fail('merged breadcrumb presentation, scoped asset or retained tests missing');
}
if (str_contains($product, '#main > .woocommerce-breadcrumb {\n    margin-left:')
    || str_contains($product, '.woocommerce-breadcrumb {\n    margin-left:')) {
    $fail('fixed viewport margin must not replace responsive container geometry');
}

$start = strrpos($workflow, '            1.3.109)');
$end = false === $start ? false : strpos($workflow, '            *)', $start);
if (false === $start || false === $end || $end <= $start) {
    $fail('X6 1.3.109 metadata case missing or past fail-closed default');
}
if (! str_contains($workflow, '            1.3.108)')
    || ! str_contains($workflow, 'FAIL: unsupported X6 Theme version state')) {
    $fail('historical version or fail-closed X6 guard missing');
}
$block = substr($workflow, $start, $end - $start);
foreach ([
    'metadata_changes',
    'grep -Fxc -- "$expected"',
    'FAIL: X6 promoted metadata diff exceeds approved 1.3.109 boundary',
    '-Version: 1.3.108',
    '+Version: 1.3.109',
    "-    define( 'AZNET_THEME_VERSION', '1.3.108' );",
    "+    define( 'AZNET_THEME_VERSION', '1.3.109' );",
] as $marker) {
    if (! str_contains($block, $marker)) {
        $fail('X6 exact metadata allowlist missing: ' . $marker);
    }
}
$regexStart = strpos($block, 'grep -Ev');
$regexEnd = false === $regexStart ? false : strpos($block, '|| true)', $regexStart);
if (false === $regexStart || false === $regexEnd) {
    $fail('X6 exact metadata rejection regex missing');
}
$allowedPattern = str_replace(chr(92), '', substr($block, $regexStart, $regexEnd - $regexStart));
if (! str_contains($allowedPattern, '1.3.108')
    || ! str_contains($allowedPattern, '1.3.109')
    || str_contains($allowedPattern, '1.3.107')) {
    $fail('X6 promotion must only allow metadata 1.3.108 to 1.3.109');
}

echo "PASS: AZnet Theme 1.3.109 bounded breadcrumb release candidate and retained history\n";
