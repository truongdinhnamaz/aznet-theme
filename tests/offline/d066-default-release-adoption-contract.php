<?php
/**
 * D-066: versioned presentation defaults must be visible after Theme upgrade.
 * Does not mutate native WordPress menus or saved Theme mods.
 */
declare(strict_types=1);

if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}
$GLOBALS['d066_saved'] = [];
$GLOBALS['d066_read_count'] = 0;
function get_theme_mod(string $name, mixed $default = false): mixed {
    $GLOBALS['d066_read_count']++;
    return 'aznet_theme_settings' === $name ? $GLOBALS['d066_saved'] : $default;
}
function set_theme_mod(): never {
    throw new RuntimeException('Theme setting reads may not mutate site storage.');
}

$root = dirname(__DIR__, 2);
require_once $root . '/inc/theme/settings.php';
require_once $root . '/inc/theme/header.php';

$fail = static function (string $message): never {
    fwrite(STDERR, "FAIL D-066 default release adoption: {$message}\n");
    exit(1);
};
$assert = static function (bool $good, string $message) use ($fail): void {
    if (! $good) {
        $fail($message);
    }
};

$defaults = AZnet\Theme\settings_defaults();
$assert(($defaults['header_mobile_layout'] ?? null) === 'drawer', 'new Theme installation must use the shipped light Drawer');
$assert(AZnet\Theme\normalize_settings([])['header_mobile_layout'] === 'drawer', 'empty settings must inherit new shipped default');

$preserved = [
    'schema_version' => 3,
    'visual_preset' => 'editorial',
    'header_preset' => 'compact',
    'header_sticky' => 'sticky-compact',
    'header_search' => false,
    'footer_preset' => 'minimal',
];
$GLOBALS['d066_saved'] = $preserved;
$before = $GLOBALS['d066_saved'];
$assert(AZnet\Theme\header_mobile_layout() === 'drawer', 'saved settings without mobile key must inherit shipped Drawer without an admin visit');
$after = $GLOBALS['d066_saved'];
$assert($before === $after, 'reading effective layout must not insert or rewrite stored Theme options');
$normalized = AZnet\Theme\settings();
foreach (['visual_preset','header_preset','header_sticky','header_search','footer_preset'] as $field) {
    $assert($normalized[$field] === $preserved[$field], "unrelated site presentation lost on Theme upgrade: {$field}");
}
$assert(($normalized['schema_version'] ?? null) === 3, 'Theme settings schema must remain backward compatible');

foreach (['popover','drawer'] as $explicit) {
    $GLOBALS['d066_saved'] = $preserved + ['header_mobile_layout' => $explicit];
    $assert(AZnet\Theme\header_mobile_layout() === $explicit, "explicit owner mobile override must survive upgrades: {$explicit}");
}
$GLOBALS['d066_saved'] = $preserved + ['header_mobile_layout' => 'unsupported'];
$assert(AZnet\Theme\header_mobile_layout() === 'drawer', 'invalid mobile layout must fail soft to current default');
$assert(AZnet\Theme\normalize_settings(['header_mobile_layout'=>'popover'])['header_mobile_layout'] === 'popover', 'compatibility popover remains selectable');

$policy = file_get_contents($root . '/docs/source/AZT-04-roadmap-qa-decisions.md');
$verify = file_get_contents($root . '/scripts/verify-v1-core.sh');
$r3 = file_get_contents($root . '/.github/workflows/r3-header-browser.yml');
$x6 = file_get_contents($root . '/.github/workflows/x6-cross-surface-release.yml');
foreach ([
    'D-066 — Versioned Presentation Default Adoption Gate',
    'Fresh WordPress',
    'Saved settings without layout',
    'Explicit popover',
    'Exact installed ZIP',
] as $marker) {
    $assert(is_string($policy) && str_contains($policy, $marker), "D-066 authoritative rollout/upgrade rule missing: {$marker}");
}
$assert(is_string($verify) && str_contains($verify, 'tests/offline/d066-default-release-adoption-contract.php'), 'V1 chain must run D-066 regression');
foreach (['default-unset-drawer','upgrade-missing-drawer','standard-clean'] as $scenario) {
    $assert(is_string($r3) && str_contains($r3, $scenario), "WordPress 6.9 R3 default/legacy matrix missing: {$scenario}");
}
$assert(is_string($x6) && str_contains($x6, "grep -F 'aznet-theme-site-header--mobile-drawer' /tmp/x6/package-post-before.html"), 'X6 must prove default Drawer from exact installed ZIP');
$assert(is_string($x6) && str_contains($x6, "grep -F 'aznet-theme-site-header--mobile-drawer' /tmp/x6/package-post-after.html"), 'X6 must prove inherited default survived Theme switch/return');

echo "PASS: D-066 fresh/default-on, saved-missing auto adoption, explicit legacy preservation and exact-package guard\n";
