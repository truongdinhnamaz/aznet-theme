<?php
/** L0/L2 contract for isolated 1.3.97 -> 1.3.92 rollback rehearsal. */
declare(strict_types=1);
$root = dirname(__DIR__, 2);
$workflow = $root . '/.github/workflows/rqa-v1397-curtain01-preview.yml';
$script = $root . '/.github/scripts/rqa_v1397_isolated_upgrade_rollback.sh';
function require_fragment(string $body, string $expected, string $label): void {
    if (!str_contains($body, $expected)) {
        fwrite(STDERR, "FAIL: missing {$label}: {$expected}\n");
        exit(1);
    }
}
if (!is_file($workflow)) { fwrite(STDERR, "RED: missing isolated 1.3.97 workflow\n"); exit(1); }
if (!is_file($script)) { fwrite(STDERR, "RED: missing isolated 1.3.97 rollback script\n"); exit(1); }
$wf = file_get_contents($workflow);
$sh = file_get_contents($script);
if ($wf === false || $sh === false) { exit(1); }
foreach ([
    'pull_request:' => 'PR-only trigger',
    'github.event.pull_request.head.sha' => 'exact-head checkout',
    '--version=7.1.3' => 'production-shape WordPress version',
    '--version=11.2.1' => 'production-shape WooCommerce version',
    "php-version: '8.4'" => 'production-shape PHP version',
    'rqa-1397-preview.zip' => 'candidate filename',
    '63df4b7e404df92869a30865fa03f44a0a9cdb99a52571694e6af74c59e5702e' => 'installer SHA',
    '204458e8518096fcf08f01f42c75d0880471f0b6f26ebfbc283f57b894b9b3ed' => 'official rollback SHA',
    'rqa_v1397_isolated_upgrade_rollback.sh upgrade' => 'upgrade invoke',
    'rqa_v1397_isolated_upgrade_rollback.sh rollback' => 'rollback invoke',
    'Run responsive browser and accessibility preview' => 'independent browser validation',
    'No production upload' => 'scope guard',
] as $needle => $label) require_fragment($wf, $needle, $label);
foreach ([
    'rqa-1397-preview.zip' => 'candidate filename',
    '63df4b7e404df92869a30865fa03f44a0a9cdb99a52571694e6af74c59e5702e' => 'installer SHA guard',
    '204458e8518096fcf08f01f42c75d0880471f0b6f26ebfbc283f57b894b9b3ed' => 'official rollback SHA guard',
    '1.3.97' => 'candidate version',
    '1.3.92' => 'rollback version',
    'ownership-before.json' => 'WordPress owner snapshot',
    'cmp -s' => 'byte-identical snapshot check',
    "--post_content=''" => 'clear duplicate CSS body',
    '--post_status=publish' => 'restore native CSS post',
    'RQA_1397_ROLLBACK_STAGE_PASS' => 'rollback status marker',
    'http://127.0.0.1:8080/' => 'isolated browser endpoint',
] as $needle => $label) require_fragment($sh, $needle, $label);
if (str_contains($wf, '50a4189be371cda131eda1eb0dd95ff739c43e4e4228015e1b4d4a958703a266')
    || str_contains($sh, 'EXPECTED_RC_SHA=50a4189be371cda131eda1eb0dd95ff739c43e4e4228015e1b4d4a958703a266')) {
    fwrite(STDERR, "FAIL: old 1.3.96 artifact identity leaked into 1.3.97 rehearsal\n");
    exit(1);
}
if (str_contains($wf, 'wp theme install --activate --force --path=https://remquocanh.vn')) {
    fwrite(STDERR, "FAIL: live site mutation forbidden\n"); exit(1);
}
echo "RQA_1397_REHEARSAL_CONTRACT_PASS\n";
