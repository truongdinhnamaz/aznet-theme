<?php
declare(strict_types=1);
if(!defined('ABSPATH')) define('ABSPATH',__DIR__.'/');
require_once dirname(__DIR__,2).'/inc/theme/provisioning-blueprints.php';
$module=dirname(__DIR__,2).'/inc/theme/provisioning-plan.php';
if(!is_file($module)){fwrite(STDERR,"FAIL: provisioning plan module missing\n");exit(1);} require_once $module;
$discovery=[
 'show_on_front'=>'posts','front_page_id'=>0,'homepage_preset'=>'off','primary_menu_id'=>0,
 'homepage_slots'=>['services'=>0,'about'=>0,'team'=>0,'knowledge'=>[],'case_analysis'=>0,'legal_news'=>0,'process'=>0,'faq'=>0,'contact'=>0],
 'pages'=>[['id'=>42,'title'=>'Existing About','status'=>'publish'],['id'=>51,'title'=>'Existing Contact','status'=>'publish']],
 'categories'=>[['id'=>17,'name'=>'Dân sự']],
];
$selections=[
 'pages'=>[
  'home'=>['action'=>'create','object_id'=>0],
  'about'=>['action'=>'reuse','object_id'=>42],
  'services'=>['action'=>'create','object_id'=>0],
  'contact'=>['action'=>'reuse','object_id'=>51],
 ],
 'categories'=>['case_analysis'=>['action'=>'create','object_id'=>0],'legal_news'=>['action'=>'skip','object_id'=>0]],
 'menu'=>['action'=>'create','menu_id'=>0],
 'front_page'=>['action'=>'set_to_role','role'=>'home'],
];
$plan=\AZnet\Theme\provisioning_build_plan('law01-v1',$selections,$discovery);
assert($plan['schema_version']===1);assert($plan['blueprint']==='law01-v1');assert(str_starts_with($plan['state_fingerprint'],'sha256:'));
$types=array_column($plan['operations'],'type');
foreach(['reuse_page','create_page','create_term','create_menu','set_front_page','map_homepage_sources','set_homepage_preset'] as $type){assert(in_array($type,$types,true),"Missing {$type}");}
foreach($plan['operations'] as $op){assert(isset($op['id'],$op['type'],$op['role'],$op['effect']));}
$validation=\AZnet\Theme\provisioning_validate_plan($plan);assert($validation['ok']===true,implode(';',$validation['errors']));
$f1=\AZnet\Theme\provisioning_plan_fingerprint($plan);
$d2=$discovery;$d2['front_page_id']=99;$p2=\AZnet\Theme\provisioning_build_plan('law01-v1',$selections,$d2);$f2=\AZnet\Theme\provisioning_plan_fingerprint($p2);assert($f1!==$f2);
$bad=$plan;$bad['operations'][]=['id'=>'evil','type'=>'call_php','role'=>'x','effect'=>'x'];assert(\AZnet\Theme\provisioning_validate_plan($bad)['ok']===false);
$src=file_get_contents(dirname(__DIR__,2).'/inc/theme/provisioning-plan.php');foreach(['eval(','call_user_func(','$wpdb','SELECT ','INSERT INTO'] as $badText){assert(!str_contains($src,$badText),"Plan must be pure: {$badText}");}
echo "PASS: Provisioning explicit change-plan contract\n";
