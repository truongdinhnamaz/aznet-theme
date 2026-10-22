<?php
/** D-060 runtime: only the native FIRST Hero core/group may opt in to V2. */
declare(strict_types=1);

namespace {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
    $GLOBALS['d060_page'] = true;
    $GLOBALS['d060_preset'] = 'digital-01';
    $GLOBALS['d060_post_content'] = '<!-- wp:group -->';
    $GLOBALS['d060_blocks'] = [];
    $GLOBALS['d060_enqueued'] = [];
    function is_page(): bool {return $GLOBALS['d060_page'];}
    function is_front_page(): bool {return false;}
    function get_queried_object(): object {return (object)['post_content'=>$GLOBALS['d060_post_content']];}
    function parse_blocks(string $content): array {return $GLOBALS['d060_blocks'];}
    function get_theme_file_path(string $path): string {return ABSPATH . ltrim($path,'/');}
    function get_theme_file_uri(string $path): string {return 'https://theme.example.test' . $path;}
    function wp_enqueue_style(string $handle,string $src,array $deps=[],?string $version=null): void {
      $GLOBALS['d060_enqueued'][]=compact('handle','src','deps','version');
    }
}
namespace AZnet\Theme {
    function visual_preset(): string {return $GLOBALS['d060_preset'];}
}
namespace {
    require_once ABSPATH.'inc/theme/assets.php';
    $fail=static function(string $message): never {fwrite(STDERR,"FAIL D-060 runtime: {$message}\n");exit(1);};
    $check=static function(bool $valid,string $message)use($fail):void{if(!$valid)$fail($message);};
    $make=static fn(string $name,array $inside=[]):array=>[
       'blockName'=>'core/group','attrs'=>['className'=>$name],'innerBlocks'=>$inside
    ];
    $valid=$make('aznet-theme-page-hero aznet-theme-page-inverse aznet-theme-about-showcase');
    $normal=$make('aznet-theme-page-hero aznet-theme-page-inverse');
    $GLOBALS['d060_blocks']=[$valid];
    $GLOBALS['d060_page']=false;
    $check(!\AZnet\Theme\should_enqueue_about_showcase_asset(),'must not load on non-Page');
    $GLOBALS['d060_page']=true;
    $GLOBALS['d060_preset']='default';
    $check(!\AZnet\Theme\should_enqueue_about_showcase_asset(),'must not load on another preset');
    $GLOBALS['d060_preset']='digital-01';
    $GLOBALS['d060_blocks']=[$normal];
    $check(!\AZnet\Theme\should_enqueue_about_showcase_asset(),'no marker must fail soft');
    $GLOBALS['d060_blocks']=[$make('aznet-theme-about-showcase')];
    $check(!\AZnet\Theme\should_enqueue_about_showcase_asset(),'non-Hero Group must not load Hero V2');
    $GLOBALS['d060_blocks']=[$normal,$valid];
    $check(!\AZnet\Theme\should_enqueue_about_showcase_asset(),'second Group must not activate first-Hero stylesheet');
    $GLOBALS['d060_blocks']=[$make('aznet-theme-page-hero aznet-theme-about-showcase-extra')];
    $check(!\AZnet\Theme\should_enqueue_about_showcase_asset(),'must match class token exactly');
    $GLOBALS['d060_blocks']=[$valid];
    $check(\AZnet\Theme\should_enqueue_about_showcase_asset(),'explicit first Hero Group must activate');
    \AZnet\Theme\enqueue_about_showcase_asset('1.3.96');
    $styles=$GLOBALS['d060_enqueued'];
    $check(count($styles)===1,'enqueues exactly one scoped asset');
    $check($styles[0]['handle']==='aznet-theme-about-showcase'
       && $styles[0]['deps']===['aznet-theme-page']
       && str_ends_with($styles[0]['src'],'/assets/css/components/about-showcase-v2.css')
       && str_starts_with((string)$styles[0]['version'],'1.3.96-'),
       'asset dependency, name or fingerprint mismatch');
    echo "PASS: D-060 native first-Hero V2 gated consumer runtime\n";
}
