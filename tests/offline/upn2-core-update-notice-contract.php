<?php
/**
 * UPN2 isolated test of notification-only update filter.
 * Run: php tests/offline/upn2-core-update-notice-contract.php
 */
declare(strict_types=1);
namespace AZnet\Theme;

const HOUR_IN_SECONDS = 3600;
$GLOBALS['upn2_cache'] = false;
$GLOBALS['upn2_response'] = null;
$GLOBALS['upn2_calls'] = 0;
function add_filter($name, $callback, $priority, $args) {
    $GLOBALS['upn2_registered'] = [$name, $callback, $priority, $args];
}
function get_transient($key) { return $GLOBALS['upn2_cache']; }
function set_transient($key, $value, $ttl) { $GLOBALS['upn2_cache'] = $value; return true; }
function wp_remote_get($url, $options) {
    ++$GLOBALS['upn2_calls'];
    if ($url !== 'https://api.github.com/repos/truongdinhnamaz/aznet-theme/releases/latest'
        || $options['redirection'] !== 0 || $options['timeout'] > 3) {
        throw new \RuntimeException('Unapproved endpoint or network options');
    }
    return $GLOBALS['upn2_response'];
}
function is_wp_error($value) { return $value instanceof \WP_Error; }
function wp_remote_retrieve_response_code($r) { return $r['code'] ?? 0; }
function wp_remote_retrieve_body($r) { return $r['body'] ?? ''; }
\class_alias(\stdClass::class, 'WP_Error');

require dirname(__DIR__, 2) . '/inc/theme/core-update-notice.php';

function assert_upn2($ok, $message) {
    if (!$ok) { fwrite(STDERR, 'FAIL ' . $message . PHP_EOL); exit(1); }
    echo 'PASS ' . $message . PHP_EOL;
}
function release_data($tag, $draft=false, $prerelease=false, $url=null) {
    return ['code'=>200,'body'=>json_encode([
        'tag_name'=>$tag, 'draft'=>$draft, 'prerelease'=>$prerelease,
        'html_url'=>$url ?? 'https://github.com/truongdinhnamaz/aznet-theme/releases/tag/'.$tag
    ])];
}
function check_notice($installed='1.3.98', $stylesheet='aznet-theme', $uri='https://github.com/truongdinhnamaz/aznet-theme') {
    return core_theme_update_notice(false,['UpdateURI'=>$uri,'Version'=>$installed],$stylesheet,[]);
}
function reset_case($response) {
    $GLOBALS['upn2_cache'] = false; $GLOBALS['upn2_response'] = $response; $GLOBALS['upn2_calls'] = 0;
}
assert_upn2($GLOBALS['upn2_registered'][0] === 'update_themes_github.com', 'WordPress host hook');
reset_case(release_data('v1.3.99'));
$r=check_notice();
assert_upn2(is_array($r) && $r['version']==='1.3.99' && !isset($r['package']) && $r['autoupdate']===false,'newer stable release yields notice without installer');
assert_upn2($GLOBALS['upn2_calls']===1,'one network request');
$r=check_notice();
assert_upn2(is_array($r) && $GLOBALS['upn2_calls']===1,'cached metadata');
reset_case(release_data('v1.3.98'));
assert_upn2(false===check_notice(),'same version no update');
reset_case(release_data('v1.3.97'));
assert_upn2(false===check_notice(),'lower version no update');
reset_case(release_data('v1.3.99',true));
assert_upn2(false===check_notice(),'draft ignored');
reset_case(release_data('v1.3.99',false,true));
assert_upn2(false===check_notice(),'prerelease ignored');
reset_case(release_data('v1.3.99',false,false,'https://evil.example/release'));
assert_upn2(false===check_notice(),'untrusted URL ignored');
reset_case(release_data('not-a-version'));
assert_upn2(false===check_notice(),'malformed tag ignored');
reset_case(['code'=>503,'body'=>'']);
assert_upn2(false===check_notice(),'HTTP outage fails soft');
reset_case(release_data('v1.3.99'));
assert_upn2(false===check_notice('1.3.98','other-theme'),'unrelated theme ignored');
assert_upn2($GLOBALS['upn2_calls']===0,'unrelated theme avoids request');
assert_upn2(false===check_notice('1.3.98','aznet-theme','https://other.example/theme'),'different UpdateURI ignored');
echo "UPN2 isolated contract PASS; WordPress runtime/integration remains UNKNOWN.\n";
