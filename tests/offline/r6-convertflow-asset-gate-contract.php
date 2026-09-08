<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$assets = (string) file_get_contents($root . '/inc/theme/assets.php');
$roadmap = (string) file_get_contents($root . '/docs/source/AZT-04-roadmap-qa-decisions.md');
$r5Evidence = (string) file_get_contents($root . '/docs/evidence/R5_CONTROL_CENTER_L4.md');

function fail_r6_convertflow_gate(string $message): never {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

if (! str_contains($assets, "'aznet-theme-convertflow-contract'")) {
    fail_r6_convertflow_gate('safe Theme-owned ConvertFlow bridge fallback is missing');
}

if (! str_contains($assets, "'/assets/css/integrations/convertflow.css'")) {
    fail_r6_convertflow_gate('Theme-owned ConvertFlow bridge path is missing');
}

$forbiddenDetection = [
    'ChoiceGuide\\',
    'CHOICEGUIDE_',
    'choiceguide_',
    'get_option(',
    'get_post_meta(',
    '$wpdb',
    'is_plugin_active(',
    'active_plugins',
    'plugin_basename(',
];

foreach ($forbiddenDetection as $forbidden) {
    if (str_contains($assets, $forbidden)) {
        fail_r6_convertflow_gate("private/heuristic provider detection entered Theme asset loading: {$forbidden}");
    }
}

$requiredSourceMarkers = [
    'O-008 ConvertFlow projection asset gating: OPEN/BLOCKED unless a documented public/versioned Theme-consumable capability exists; private detection is forbidden.',
    'R6 may execute safe pre-promotion work, but it must stop before the metadata/version promotion gate unless explicitly approved.',
];

foreach ($requiredSourceMarkers as $marker) {
    if (! str_contains($roadmap, $marker)) {
        fail_r6_convertflow_gate("authoritative R6 source marker missing: {$marker}");
    }
}

if (! str_contains($r5Evidence, 'ConvertFlow capability reported as `unknown` because no Theme-consumable public capability is established')) {
    fail_r6_convertflow_gate('latest retained R5 evidence no longer records the public-capability gap');
}

echo "PASS: R6 ConvertFlow projection asset gate = BLOCKED_EXTERNAL_CONTRACT; safe bridge retained without private detection\n";