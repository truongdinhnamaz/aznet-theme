<?php
/** D-050C Theme consumer RED: optional public-safe provider bridge (L2 only). */
declare(strict_types=1);
const ABSPATH = '/mock/';
$GLOBALS['projection'] = null;
$GLOBALS['explode_provider'] = false;
$GLOBALS['checked_nonces'] = [];
function ck(bool $ok,string $reason):void{if(!$ok){fwrite(STDERR,"FAIL: {$reason}\n");exit(1);}}
function apply_filters(string $hook,$fallback){ck($hook==='aznet_site_guard/v1/admin_projection','approved provider v1 public filter only');if($GLOBALS['explode_provider'])throw new RuntimeException('Simulated provider error');return $GLOBALS['projection'];}
function admin_url(string $path):string{return 'https://example.test/wp-admin/'.$path;}
function esc_html(string $s):string{return htmlspecialchars($s,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function esc_url(string $s):string{return esc_html($s);}
function esc_attr(string $s):string{return esc_html($s);}
function wp_nonce_field(string $name):void{$GLOBALS['checked_nonces'][]=$name;echo '<input name="_wpnonce" value="test">';}
function submit_button(string $text,string $style='primary',string $name='submit',bool $wrap=true):void{echo '<button>'.esc_html($text).'</button>';}
function p():array{return [
 'contract'=>'aznet_site_guard_admin','major'=>1,'provider_version'=>'0.1.0-dev',
 'enforcement'=>'active','operator_authorized'=>false,'actor_mode'=>'locked',
 'active_scope'=>null,'remaining_seconds'=>0,
 'scopes'=>['theme_install'=>'Install Theme','theme_switch'=>'Switch Theme','theme_delete'=>'Delete Theme','homepage_critical'=>'Protect Homepage']
];}
function html():string{ob_start();\AZnet\Theme\Admin\render_site_guard_admin_controls();return (string)ob_get_clean();}
$path=dirname(__DIR__,2).'/inc/admin/site-guard-consumer.php';
if(is_file($path))require $path;
ck(function_exists('AZnet\\Theme\\Admin\\render_site_guard_admin_controls'),'Theme-owned v1 consumer presenter must exist');
ck(str_contains(html(),'CHƯA XÁC MINH')&&!str_contains(html(),'name="guard_scope"'),'provider missing fail soft');
$GLOBALS['projection']=p();$GLOBALS['projection']['major']=2;
ck(!str_contains(html(),'name="guard_scope"'),'unsupported major denies controls');
$GLOBALS['projection']=p();$GLOBALS['projection']['active_scope']='theme_switch';
ck(!str_contains(html(),'name="guard_scope"'),'contradictory status invalid');
$GLOBALS['projection']=p();$GLOBALS['projection']['unreviewed']='bad';
ck(!str_contains(html(),'name="guard_scope"'),'extra field invalid');
$GLOBALS['projection']=p();$GLOBALS['explode_provider']=true;
ck(!str_contains(html(),'name="guard_scope"'),'provider exception fails closed');
$GLOBALS['explode_provider']=false;
ck(!str_contains(html(),'name="guard_scope"'),'ordinary administrator cannot operate');
$GLOBALS['projection']['operator_authorized']=true;
$locked=html();
ck(str_contains($locked,'name="guard_scope"')&&str_contains($locked,'name="guard_confirm"')&&str_contains($locked,'name="guard_return_to"'),'owner field names in form');
ck(str_contains($locked,'aznet_site_guard_unlock')&&in_array('aznet_site_guard_unlock',$GLOBALS['checked_nonces'],true),'owner unlock action/nonce');
ck(str_contains($locked,'theme_install')&&str_contains($locked,'homepage_critical'),'all four owner-defined scopes');
ck(str_contains($locked,'https://example.test/wp-admin/admin.php?page=aznet-theme&amp;section=danger-zone'),'canonical return');
$GLOBALS['projection']=array_reverse($GLOBALS['projection'],true);
ck(str_contains(html(),'name="guard_scope"'),'record field order irrelevant');
$GLOBALS['projection']=p();$GLOBALS['projection']['operator_authorized']=true;
$GLOBALS['projection']['actor_mode']='temporarily_unlocked';$GLOBALS['projection']['active_scope']='theme_switch';$GLOBALS['projection']['remaining_seconds']=600;
$open=html();
ck(str_contains($open,'aznet_site_guard_lock')&&in_array('aznet_site_guard_lock',$GLOBALS['checked_nonces'],true),'owner lock action/nonce');
ck(!str_contains($open,'name="guard_scope"'),'no second unlock form');
$GLOBALS['projection']['remaining_seconds']=601;ck(!str_contains(html(),'aznet_site_guard_lock'),'invalid TTL locked out');
$GLOBALS['projection']=p();$GLOBALS['projection']['operator_authorized']=true;$GLOBALS['projection']['enforcement']='emergency';
ck(str_contains(html(),'KHẨN CẤP')&&!str_contains(html(),'name="guard_scope"'),'host emergency visibly unsafe');
$GLOBALS['projection']=p();$GLOBALS['projection']['operator_authorized']=true;$GLOBALS['projection']['scopes']['theme_install']='<script>alert(1)</script>';
$xss=html();ck(!str_contains($xss,'<script>')&&str_contains($xss,'&lt;script&gt;'),'untrusted public label escaped');
$src=(string)file_get_contents($path);
foreach(['Guard::','aznet_site_guard_audit_v1','get_option(','get_transient(','get_post_meta(','$wpdb','class_exists('] as $forbidden){ck(!str_contains($src,$forbidden),'no private API/storage access: '.$forbidden);}
echo "PASS: Theme D050C optional public consumer + nonce + fail-soft states (L2)\n";
