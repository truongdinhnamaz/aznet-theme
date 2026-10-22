<?php
/** D-060: explicit Gutenberg About Showcase v2 opt-in and isolated presentation. */
declare(strict_types=1);
$root=dirname(__DIR__,2);
$fail=static function(string $why):never {fwrite(STDERR,"FAIL D-060 About V2: {$why}\n");exit(1);};
$expect=static function(bool $condition,string $why)use($fail):void {if(!$condition)$fail($why);};
$loader=(string)file_get_contents($root.'/inc/theme/assets.php');
$cssPath=$root.'/assets/css/components/about-showcase-v2.css';
$expect(str_contains($loader,'function should_enqueue_about_showcase_asset(): bool'),
    'missing explicit Gutenberg presentation opt-in guard');
$expect(str_contains($loader,'function enqueue_about_showcase_asset('),
    'missing dedicated surface asset enqueue');
$expect(str_contains($loader,'parse_blocks(')&&str_contains($loader,"'core/group'"),
    'must consume native Gutenberg block class rather than Page slug or title');
$expect(str_contains($loader,"'aznet-theme-about-showcase'"),
    'missing authored opt-in class');
$expect(str_contains($loader,'should_enqueue_page_assets()')&&str_contains($loader,"'digital-01'"),
    'asset must fail-soft outside non-front Digital 01 native Pages');
$expect(str_contains($loader,"'aznet-theme-about-showcase'")&&str_contains($loader,"'/assets/css/components/about-showcase-v2.css'"),
    'missing scoped style registration');
$expect(str_contains($loader,"'aznet-theme-page'")&&str_contains($loader,"asset_content_version( '/assets/css/components/about-showcase-v2.css'"),
    'asset must depend on Page stylesheet and use content fingerprint');
$expect(is_file($cssPath),'missing isolated About V2 CSS file');
$css=(string)file_get_contents($cssPath);
$expect(str_contains($css,':has(> .aznet-theme-about-showcase)'),
    'all nine sections must require an authored explicit surface opt-in');
$expect(str_contains($css,'> .aznet-theme-about-showcase'),'Hero must be scoped to opted-in Group');
$expect(str_contains($css,'@media (max-width: 640px)'),'mobile layout contract absent');
$expect(str_contains($css,'prefers-reduced-motion'),'reduced-motion fallback absent');
foreach(['page-id-18897','gioi-thieu','rank_math','choiceguide_','rootprofile_','get_post_meta(','get_option('] as $forbidden) {
    $expect(!str_contains($loader,$forbidden) || !in_array($forbidden,['page-id-18897','gioi-thieu'],true),
      'prohibited Page ID/slug heuristics in asset guard');
    $expect(!str_contains($css,$forbidden),'presentation cannot hardcode Page identity/provider internals');
}
$expect(1===preg_match('/function should_enqueue_about_showcase_asset\(\): bool\s*\{[\s\S]*?\$blocks\s*=\s*parse_blocks\(/',$loader),
    'guard must inspect Gutenberg native block attrs');
echo "PASS: D-060 About V2 authored opt-in, isolated asset and responsive presentation contract\n";
