<?php
/** Curtain 01 C4 visual coexistence contract. */
declare(strict_types=1);

if (! defined('ABSPATH')) { define('ABSPATH', __DIR__ . '/'); }

$root = dirname(__DIR__, 2);
require_once $root . '/inc/theme/settings.php';

$normalized = AZnet\Theme\normalize_settings([
    'visual_preset' => 'curtain-01',
]);

if (($normalized['visual_preset'] ?? null) !== 'curtain-01') {
    fwrite(STDERR, "FAIL: Curtain 01 visual preset is not accepted by strict settings normalization.\n");
    exit(1);
}

$control = file_get_contents($root . '/inc/admin/control-center.php');
$presetPath = $root . '/assets/css/presets/curtain-01.css';

if (! in_array('curtain-01', AZnet\Theme\visual_preset_ids(), true)) {
    fwrite(STDERR, "FAIL: Template Registry does not expose Curtain 01 visual preset.\n");
    exit(1);
}

if ('Rèm 01' !== (AZnet\Theme\visual_preset_choices()['curtain-01'] ?? null)) {
    fwrite(STDERR, "FAIL: Control Center choices do not expose Curtain 01 from Template Registry metadata.\n");
    exit(1);
}
if (! is_string($control) || ! str_contains($control, 'visual_preset_choices()')) {
    fwrite(STDERR, "FAIL: Control Center does not consume manifest-driven visual preset choices.\n");
    exit(1);
}

if (! is_file($presetPath)) {
    fwrite(STDERR, "FAIL: Curtain 01 visual preset stylesheet is missing.\n");
    exit(1);
}

$css = file_get_contents($presetPath);
foreach ([
    'body.aznet-theme-preset--curtain-01',
    '--aznet-theme-color-primary:',
    '--aznet-theme-color-surface:',
    '--aznet-theme-color-text:',
    '--aznet-theme-color-border:',
    '--aznet-theme-surface-inverse:',
    '--aznet-theme-on-inverse:',
] as $needle) {
    if (! str_contains((string) $css, $needle)) {
        fwrite(STDERR, "FAIL: Curtain 01 preset token missing: {$needle}\n");
        exit(1);
    }
}

foreach (['rqa-', 'flatsome', 'ux_', 'choiceguide_', '_choiceguide_', 'get_post_meta', 'get_option'] as $forbidden) {
    if (str_contains(strtolower((string) $css), strtolower($forbidden))) {
        fwrite(STDERR, "FAIL: Curtain 01 visual preset contains forbidden domain/provider coupling: {$forbidden}\n");
        exit(1);
    }
}

$assets = file_get_contents($root . '/inc/theme/assets.php');
if (! str_contains((string) $assets, "get_theme_file_uri( '/assets/css/presets/' . \$preset . '.css' )")) {
    fwrite(STDERR, "FAIL: Visual preset must continue through the existing surface-aware asset loader.\n");
    exit(1);
}

echo "PASS: Curtain 01 C4 visual coexistence contract\n";
