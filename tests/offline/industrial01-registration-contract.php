<?php
/**
 * Industrial 01 MVP registration and lexicon-isolation contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$settings = file_get_contents($root . '/inc/theme/settings.php');
$design = file_get_contents($root . '/inc/theme/design-system.php');
$composer = file_get_contents($root . '/inc/theme/homepage-composer.php');

foreach ([
    [$settings, "'industrial-01'", 'Settings must register Industrial 01.'],
    [$design, "'industrial-01'", 'Visual preset allow-list must register Industrial 01.'],
    [$composer, "'industrial-01'", 'Homepage Composer must register Industrial 01.'],
] as [$source, $needle, $message]) {
    assert(is_string($source) && str_contains($source, $needle), $message);
}

$lexiconPath = $root . '/inc/theme/preset-lexicon.php';
assert(is_file($lexiconPath), 'Preset lexicon adapter must exist.');
$lexicon = file_get_contents($lexiconPath);
assert(is_string($lexicon));
assert(str_contains($lexicon, 'function preset_lexicon('), 'Theme must expose one presentation-only preset lexicon adapter.');
assert(str_contains($lexicon, "'industrial-01'"), 'Industrial 01 lexicon must be present.');
assert(str_contains($lexicon, "'law-01'"), 'Law 01 lexicon must remain isolated.');
assert(str_contains($lexicon, "'curtain-01'"), 'Curtain 01 lexicon must remain isolated.');

foreach ([
    'Danh mục thiết bị',
    'Thiết bị & phụ kiện',
    'Yêu cầu báo giá',
    'Kiến thức kỹ thuật',
] as $industrialTerm) {
    assert(str_contains($lexicon, $industrialTerm), "Industrial 01 term missing: {$industrialTerm}");
}

foreach ([
    'Lĩnh vực hành nghề',
    'Luật sư',
    'Dòng rèm',
] as $foreignTerm) {
    $industrialBlock = preg_match("/'industrial-01'\s*=>\s*\[(.*?)\n\s*\],/s", $lexicon, $matches) ? ($matches[1] ?? '') : '';
    assert(! str_contains($industrialBlock, $foreignTerm), "Foreign preset term leaked into Industrial 01: {$foreignTerm}");
}

$industrialCssPath = $root . '/assets/css/presets/industrial-01.css';
assert(is_file($industrialCssPath), 'Industrial 01 visual preset stylesheet must exist.');
$industrialCss = file_get_contents($industrialCssPath);
assert(is_string($industrialCss) && str_contains($industrialCss, 'body.aznet-theme-preset--industrial-01'));
assert(! str_contains($industrialCss, 'minhnguyen'), 'Client identity must not be hard-coded into the reusable Industrial 01 preset.');

echo "PASS: Industrial 01 registration and lexicon isolation contract\n";
