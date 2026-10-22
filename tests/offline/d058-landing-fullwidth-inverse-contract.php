<?php
/** D-058: Authored Gutenberg Landing Groups must not inherit media alignfull breakouts. */
declare(strict_types=1);
$root=dirname(__DIR__,2);
$css=(string)file_get_contents($root.'/assets/css/components/page.css');
$assert=static function(bool $ok,string $why):void{
  if(!$ok){fwrite(STDERR,"FAIL D-058 Landing page: {$why}\n");exit(1);}
};
$assert(
  1===preg_match('/\.aznet-theme-page--landing \.aznet-theme-page__content-inner > \.wp-block-group,\s*\.aznet-theme-page--landing \.aznet-theme-page__content-inner > \.wp-block-cover\s*\{[^}]*\btransform:\s*none\s*;/s',$css),
  'full-width Landing groups must override generic media alignment translation'
);
$assert(str_contains($css,'.aznet-theme-page-inverse > .wp-block-heading.has-text-color'),
  'explicit WordPress-authored inverse block must render heading in readable foreground');
$assert(str_contains($css,'.aznet-theme-page-inverse > .wp-block-paragraph.has-text-color'),
  'explicit WordPress-authored inverse block must render paragraph in readable foreground');
$assert(str_contains($css,'!important;'),
  'explicit inverse foreground must supersede legacy Gutenberg text-color precedence');
$assert(!str_contains($css,'page-id-18897')&&!str_contains($css,'gioi-thieu'),
  'no WordPress title/slug/Page-ID routing heuristic');
echo "PASS: D-058 Landing native group full-width and inverse opt-in CSS\n";
