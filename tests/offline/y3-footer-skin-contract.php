<?php
/** Historical skin compatibility must not produce different Footer styling. */
declare(strict_types=1);
$root=dirname(__DIR__,2);$helper=(string)file_get_contents($root.'/inc/theme/footer.php');$tpl=(string)file_get_contents($root.'/template-parts/footer/site-footer.php');$css=(string)file_get_contents($root.'/assets/css/components/site-footer.css');
$fail=static function(string $x):never{fwrite(STDERR,"FAIL shared Footer skin: {$x}\n");exit(1);};
if(!str_contains($helper,'function footer_skin(): string {')||!str_contains($helper,"return 'default';"))$fail('No shared skin fallback');
if(!str_contains($helper,"'skin'        => footer_skin()"))$fail('Retained context field missing');
if(!str_contains($tpl,'data-aznet-theme-footer-content-preset'))$fail('Content profile marker missing');
foreach(['aznet-theme-site-footer--skin-','aznet-theme-site-footer--professional','aznet-theme-site-footer--law-01'] as $old){if(str_contains($tpl,$old)||str_contains($css,'.'.$old))$fail('Legacy visual class: '.$old);}
if(!str_contains($css,'--aznet-theme-footer-ink: #f7f8fa;')||!str_contains($css,'background: #111722;'))$fail('Shared neutral palette missing');
echo "PASS: Footer source compatibility with one shared skin\n";
