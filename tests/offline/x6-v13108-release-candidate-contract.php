<?php
/**
 * AZnet Theme 1.3.108: bounded metadata candidate for the scoped Curtain 01
 * WordPress-authored About/Projects Page presentation repair.
 *
 * Technical verification only. No tag, GitHub Release or production deploy
 * is authorized by this candidate.
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$style = (string) file_get_contents($root . '/style.css');
$boot = (string) file_get_contents($root . '/functions.php');
$verify = (string) file_get_contents($root . '/scripts/verify-v1-core.sh');
$workflow = (string) file_get_contents($root . '/.github/workflows/x6-cross-surface-release.yml');
$pageCss = (string) file_get_contents($root . '/assets/css/components/page.css');
$curtainPageCss = (string) file_get_contents($root . '/assets/css/components/page-curtain-01-authored.css');
$presetCss = (string) file_get_contents($root . '/assets/css/presets/curtain-01.css');
$assets = (string) file_get_contents($root . '/inc/theme/assets.php');

$fail = static function (string $message): never {
    fwrite(STDERR, "FAIL X6 1.3.108 release candidate: {$message}\n");
    exit(1);
};

if (! preg_match('/^Version:\\s*1\\.3\\.108\\s*$/m', $style)
    || ! str_contains($boot, "define( 'AZNET_THEME_VERSION', '1.3.108' );")) {
    $fail('style.css and functions.php must declare identical 1.3.108 metadata');
}
if (! str_contains($verify, '  1.3.108)')
    || ! str_contains($verify, 'tests/offline/x6-v13108-release-candidate-contract.php')
    || ! str_contains($verify, '  1.3.107)')
    || ! str_contains($verify, 'tests/offline/x6-v13107-release-candidate-contract.php')
    || ! str_contains($verify, 'FAIL: unsupported Theme release contract version')) {
    $fail('strict dispatcher must retain historical 1.3.107 plus 1.3.108 and fail-closed default');
}
if (! str_contains($verify, 'tests/offline/page-native-alignwide-form-layout-contract.php')
    || ! str_contains($pageCss, '.aznet-theme-page__content-inner .wp-block-group.alignwide > .wp-block-columns.alignwide')) {
    $fail('1.3.107 native Page compatibility guard was lost');
}
if (! str_contains($verify, 'tests/offline/curtain01-rqa-landing-css-contract.php')
    || ! str_contains($curtainPageCss, 'RQA-PAGE-01:')
    || ! str_contains($curtainPageCss, '.rqa-about-hero')
    || ! str_contains($curtainPageCss, '.rqa-projects-hero')
    || ! str_contains($curtainPageCss, '.rqa-projects-process__list')
    || ! str_contains($assets, "'curtain-01' === visual_preset()")
    || ! str_contains($assets, "'aznet-theme-page-curtain-01-authored'")
    || ! str_contains($assets, "function enqueue_page_assets(")
    || ! str_contains($assets, "if ( ! should_enqueue_page_assets() )")) {
    $fail('RQA Page-only visual repair or opt-in source/surface guard missing');
}
if (str_contains($presetCss, 'RQA-PAGE-01:') || str_contains($curtainPageCss, '!important')) {
    $fail('specific Page repair must not be added globally or use important overrides');
}

$start = strrpos($workflow, '            1.3.108)');
$end = false === $start ? false : strpos($workflow, '            *)', $start);
if (false === $start || false === $end || $end <= $start) {
    $fail('X6 1.3.108 metadata case missing or placed past fail-closed default');
}
if (! str_contains($workflow, '            1.3.107)')
    || ! str_contains($workflow, 'FAIL: unsupported X6 Theme version state')) {
    $fail('historical X6 release case or fail-closed default missing');
}
$block = substr($workflow, $start, $end - $start);
foreach ([
    'metadata_changes',
    'grep -Fxc -- "$expected"',
    'FAIL: X6 promoted metadata diff exceeds approved 1.3.108 boundary',
    '-Version: 1.3.107',
    '+Version: 1.3.108',
    "-    define( 'AZNET_THEME_VERSION', '1.3.107' );",
    "+    define( 'AZNET_THEME_VERSION', '1.3.108' );",
] as $marker) {
    if (! str_contains($block, $marker)) {
        $fail('X6 release metadata exact allowlist missing ' . $marker);
    }
}
$regexStart = strpos($block, 'grep -Ev');
$regexEnd = false === $regexStart ? false : strpos($block, '|| true)', $regexStart);
if (false === $regexStart || false === $regexEnd) {
    $fail('X6 version metadata rejection regex missing');
}
$allowedPattern = str_replace(chr(92), '', substr($block, $regexStart, $regexEnd - $regexStart));
if (! str_contains($allowedPattern, '1.3.107')
    || ! str_contains($allowedPattern, '1.3.108')
    || str_contains($allowedPattern, '1.3.106')) {
    $fail('X6 metadata allowlist must promote only 1.3.107 -> 1.3.108');
}

echo "PASS: AZnet Theme 1.3.108 bounded metadata candidate, RQA Page fix and history retained\n";
