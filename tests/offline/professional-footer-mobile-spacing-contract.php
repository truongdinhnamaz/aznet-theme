<?php
/** Generic mobile Footer spacing — no preset-specific visual variant. */
declare(strict_types=1);
$root=dirname(__DIR__,2);$css=(string)file_get_contents($root.'/assets/css/components/site-footer.css');$fail=static function(string $x):never{fwrite(STDERR,"FAIL shared Footer mobile: {$x}\n");exit(1);};
$i=strpos($css,'@media (max-width: 48rem)');if(false===$i)$fail('Missing breakpoint');$mobile=substr($css,$i);
foreach(['.aznet-theme-site-footer__main {','grid-template-columns: minmax(0, 1fr);','.aznet-theme-site-footer__main > :not(:first-child) {','border-top: 1px solid var(--aznet-theme-footer-border);','border-inline-start: 0;','.aznet-theme-site-footer__bottom {','flex-direction: column;'] as $rule){if(!str_contains($mobile,$rule))$fail('Missing mobile rule '.$rule);}
foreach(['.aznet-theme-site-footer__rich-content {','line-height: 1.7;','.aznet-theme-site-footer__brand {'] as $rule){if(!str_contains($css,$rule))$fail('Shared typography missing '.$rule);}
if(preg_match('/\\.aznet-theme-site-footer--(?:professional|law-01|compact)\\b/',$css))$fail('Forbidden visual variant');
echo "PASS: shared Footer mobile layout and typography\n";
