<?php
/**
 * D-043 C6 regression: do not propose an unrelated provisioning blueprint when
 * the active Theme-owned Homepage preset has no registered provisioning recipe.
 * The presence of an official presentation template is not a provisioning contract.
 */
declare(strict_types=1);

namespace AZnet\Theme {
    function homepage_preset(): string {
        return (string) ($GLOBALS['aznet_d043_test_preset'] ?? 'off');
    }
}

namespace {
    $root = dirname(__DIR__, 2);
    if (!defined('ABSPATH')) {
        define('ABSPATH', $root . '/');
    }

    require_once $root . '/inc/theme/template-registry.php';
    require_once $root . '/inc/theme/template-loader.php';
    require_once $root . '/inc/theme/provisioning-professional-services.php';
    require_once $root . '/inc/theme/provisioning-blueprints.php';
    require_once $root . '/inc/admin/provisioning.php';

    $store =& \AZnet\Theme\template_manifest_store();
    $store = [];
    $loaded = \AZnet\Theme\load_local_template_manifests();
    if ([] !== ($loaded['rejected'] ?? [])) {
        fwrite(STDERR, "FAIL: template manifests rejected before test\n");
        exit(1);
    }

    $choices = \AZnet\Theme\Admin\provisioning_wizard_blueprint_choices();
    if (($choices['law01-v1-2'] ?? null) !== 'Luật 01'
        || !array_key_exists('professional-services-v1', $choices)) {
        fwrite(STDERR, "FAIL: existing approved blueprint choices changed\n");
        exit(1);
    }

    foreach ([
        'law-01' => 'law01-v1-2',
        'curtain-01' => '',
        'industrial-01' => '',
        'off' => 'law01-v1-2',
        'unregistered-template' => '',
    ] as $preset => $expected) {
        $GLOBALS['aznet_d043_test_preset'] = $preset;
        $actual = \AZnet\Theme\Admin\provisioning_wizard_default_blueprint();
        if ($expected !== $actual) {
            fwrite(STDERR, "FAIL: {$preset} recommended {$actual}; expected " .
                ('' === $expected ? 'no unrelated blueprint' : $expected) . "\n");
            exit(1);
        }
    }

    // An industry template without an accepted provisioning recipe must fail soft.
    $unavailable = [
        'contract_version' => 1,
        'id' => 'fourth-preview-only',
        'name' => 'Fourth Preview',
        'version' => '1.0.0',
        'capabilities' => ['homepage'],
        'presentation' => ['homepage_preset' => 'fourth-preview-only'],
        'provisioning' => [],
    ];
    if (!\AZnet\Theme\register_template_manifest($unavailable)) {
        fwrite(STDERR, "FAIL: non-provisionable fourth template rejected\n");
        exit(1);
    }
    $GLOBALS['aznet_d043_test_preset'] = 'fourth-preview-only';
    if ('' !== \AZnet\Theme\Admin\provisioning_wizard_default_blueprint()) {
        fwrite(STDERR, "FAIL: fourth template without blueprint fell back to Law\n");
        exit(1);
    }

    $src = (string) file_get_contents($root . '/inc/admin/provisioning.php');
    foreach ([
        'Chưa có blueprint Thiết lập nhanh',
        'aznet-theme-provision-unavailable',
        'name="blueprint" required',
    ] as $marker) {
        if (!str_contains($src, $marker)) {
            fwrite(STDERR, "FAIL: missing explicit fail-soft wizard UI: {$marker}\n");
            exit(1);
        }
    }
    echo "PASS: D-043 wizard proposes only an available, template-matching blueprint\n";
}
