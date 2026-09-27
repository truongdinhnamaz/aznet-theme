<?php
/** One-shot Tâm Đức Homepage 1.3.54 staging deployment/runtime contract. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$harness = file_get_contents($root . '/tests/browser/tamduc-homepage-1354-runtime-staging.mjs');
$workflow = file_get_contents($root . '/.github/workflows/_ops-tamduc-homepage-1354-runtime-staging.yml');

foreach ([
    [$harness, "const TARGET_VERSION = '1.3.54';", 'target version marker missing'],
    [$harness, "const ROLLBACK_VERSION = '1.3.1';", 'rollback version marker missing'],
    [$harness, "'front-page-content'", 'native Front Page runtime assertion missing'],
    [$harness, "Sửa các bước", 'Process full-content runtime assertion missing'],
    [$harness, "Sửa câu hỏi", 'FAQ full-content runtime assertion missing'],
    [$harness, "Nguồn & cài đặt nâng cao", 'advanced source runtime assertion missing'],
    [$harness, "new AxeBuilder", 'a11y browser check missing'],
    [$harness, "theme_restored", 'rollback verification missing'],
    [$workflow, "55dda598adc7bd677ea2d5301fb42ecc90eae94a", 'canonical base pin missing'],
    [$workflow, "89cc97599068c57f3446e617d0076e9f67936e35", '1.3.1 rollback source pin missing'],
    [$workflow, "build-release-package.py", 'deterministic package builder missing'],
    [$workflow, "PILOT_WP_USER", 'staging credential binding missing'],
    [$workflow, "PILOT_WP_PASSWORD", 'staging credential binding missing'],
    [$workflow, "tamduc-homepage-1354-runtime-staging.mjs", 'runtime harness invocation missing'],
] as [$haystack, $needle, $message]) {
    if (! is_string($haystack) || ! str_contains($haystack, $needle)) {
        fwrite(STDERR, "FAIL: {$message}.\n");
        exit(1);
    }
}

foreach (['lstamduchn.vn', 'remquocanh.vn'] as $forbiddenTarget) {
    if (is_string($workflow) && str_contains($workflow, $forbiddenTarget)) {
        fwrite(STDERR, "FAIL: staging workflow targets production site {$forbiddenTarget}.\n");
        exit(1);
    }
}

echo "PASS: Tâm Đức Homepage 1.3.54 staging deployment/runtime contract\n";
