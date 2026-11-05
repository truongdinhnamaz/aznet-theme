<?php
/** Historical Industrial premium visual regression now asserts shared Footer ownership. */
declare(strict_types=1);
$root=dirname(__DIR__,2);
$preset=(string)file_get_contents($root.'/assets/css/presets/industrial-01.css');
$css=(string)file_get_contents($root.'/assets/css/components/site-footer.css');
$tpl=(string)file_get_contents($root.'/template-parts/footer/site-footer.php');
$helper=(string)file_get_contents($root.'/inc/theme/footer.php');
$assets=(string)file_get_contents($root.'/inc/theme/assets.php');
$fail=static function(string $x):never{fwrite(STDERR,"FAIL Industrial shared Footer: {$x}\n");exit(1);};
if(str_contains($preset,'.aznet-theme-site-footer'))$fail('Industry preset still owns Footer CSS');
foreach(['.aznet-theme-site-footer {','.aznet-theme-site-footer__main {','.aznet-theme-site-footer__rich-content {','.aznet-theme-site-footer__bottom {','.aznet-theme-site-footer__logo {',':focus-visible','@media (max-width: 48rem)'] as $rule){if(!str_contains($css,$rule))$fail('Shared CSS missing '.$rule);}
if(!str_contains($tpl,'footer_context()')||!str_contains($tpl,'$column_content')||!str_contains($helper,'wp_kses_post('))$fail('WordPress-owned rich content missing');
if(!str_contains($assets,"asset_content_version( '/assets/css/components/site-footer.css', \$version )"))$fail('Shared CSS cache busting missing');
foreach(['minhnguyen.vn','facebook.com/','tiktok.com'] as $value){if(str_contains($preset.$css,$value))$fail('Hard-coded website data '.$value);}
echo "PASS: Industrial uses universal Footer and WordPress source data\n";
