<?php
/** D-050: Theme-only privileged action state must fail closed per user/session. */
declare(strict_types=1);

define('ABSPATH', __DIR__);
$GLOBALS['d050'] = [
    'user' => 1, 'caps' => ['manage_options' => true], 'multisite' => false,
    'super' => false, 'session' => 'browser-one-session-secret', 'transients' => [],
    'options' => [], 'events' => [],
];

function current_user_can(string $cap): bool { return !empty($GLOBALS['d050']['caps'][$cap]); }
function is_multisite(): bool { return $GLOBALS['d050']['multisite']; }
function is_super_admin(): bool { return $GLOBALS['d050']['super']; }
function get_current_user_id(): int { return (int)$GLOBALS['d050']['user']; }
function wp_get_session_token(): string { return (string)$GLOBALS['d050']['session']; }
function get_transient(string $key): mixed { return $GLOBALS['d050']['transients'][$key]['value'] ?? false; }
function set_transient(string $key, mixed $value, int $ttl): bool {
    $GLOBALS['d050']['transients'][$key] = ['value' => $value, 'ttl' => $ttl];
    return true;
}
function delete_transient(string $key): bool { unset($GLOBALS['d050']['transients'][$key]); return true; }
function get_option(string $key, mixed $default = false): mixed { return $GLOBALS['d050']['options'][$key] ?? $default; }
function update_option(string $key, mixed $value, bool $autoload = false): bool {
    $GLOBALS['d050']['options'][$key] = $value; return true;
}
function wp_die(string $message): never { throw new RuntimeException($message); }
function esc_html__(string $s, string $domain = ''): string { return $s; }
function fail_d050(string $message): never { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); }
function ok_d050(bool $condition, string $message): void { if (!$condition) fail_d050($message); }

require dirname(__DIR__, 2) . '/inc/admin/danger-zone.php';

$allowed = 'AZnet\\Theme\\Admin\\danger_zone_is_authorized';
$unlocked = 'AZnet\\Theme\\Admin\\danger_zone_is_unlocked';
$start = 'AZnet\\Theme\\Admin\\danger_zone_start_unlock';
$finish = 'AZnet\\Theme\\Admin\\danger_zone_complete_action';
$key = 'AZnet\\Theme\\Admin\\danger_zone_transient_key';
$audit = 'AZnet\\Theme\\Admin\\danger_zone_audit_event';

ok_d050(!$allowed(), 'normal administrator must not inherit the danger capability');
ok_d050(!$unlocked(), 'default must be locked');
ok_d050(!$start('Try to unlock'), 'no-capability user must not unlock');
ok_d050(count($GLOBALS['d050']['transients']) === 0, 'denied unlock must not mutate state');

$GLOBALS['d050']['caps']['aznet_theme_manage_danger_zone'] = true;
ok_d050($allowed(), 'specifically granted manager must be authorized');
ok_d050(!$unlocked(), 'newly authorized owner must still be locked');
ok_d050($start('Approved maintenance window'), 'authorized owner must be able to unlock');
ok_d050($unlocked(), 'authorized owner should have a current unlock');
$ownKey = $key();
ok_d050(is_string($ownKey) && !str_contains($ownKey, $GLOBALS['d050']['session']), 'session token leaked into transient key');
ok_d050($GLOBALS['d050']['transients'][$ownKey]['ttl'] === 600, 'unlock must have exactly 600 s TTL');

$GLOBALS['d050']['user'] = 2;
ok_d050(!$unlocked(), 'another administrator must not share an unlock');
$GLOBALS['d050']['user'] = 1;
$GLOBALS['d050']['session'] = 'another-login-session';
ok_d050(!$unlocked(), 'same user in another session must not share unlock');
$GLOBALS['d050']['session'] = 'browser-one-session-secret';
ok_d050($unlocked(), 'original session must remain unlocked');
$GLOBALS['d050']['transients'][$ownKey]['value']['expires_at'] = time() - 1;
ok_d050(!$unlocked(), 'expired unlock must fail closed even if transient is retained');
ok_d050($start('Another maintenance window'), 'may unlock again after expiry');
$finish('reset');
ok_d050(!$unlocked(), 'successful dangerous action must consume unlock');

$GLOBALS['d050']['session'] = '';
ok_d050(!$start('Empty session'), 'missing session must fail closed');
$GLOBALS['d050']['multisite'] = true;
$GLOBALS['d050']['super'] = false;
ok_d050(!$allowed(), 'multisite non-Super-Admin must be denied even with a custom capability');
$GLOBALS['d050']['super'] = true;
ok_d050($allowed(), 'multisite Super Admin must be authorized');
$GLOBALS['d050']['caps']['manage_options'] = false;
ok_d050(!$allowed(), 'super administrator missing WordPress management capability must not bypass');
$GLOBALS['d050']['caps']['manage_options'] = true;

for ($i = 0; $i < 40; $i++) { $audit('sample', 'Event ' . $i); }
$history = get_option('aznet_theme_danger_audit', []);
ok_d050(is_array($history) && count($history) === 25, 'Theme-local audit must remain bounded to 25');
ok_d050(!str_contains(json_encode($history) ?: '', 'browser-one-session-secret'), 'audit must not expose the login session token');

echo "PASS: D-050 capability, default lock, actor/session isolation, TTL, relock, bounded audit\n";
