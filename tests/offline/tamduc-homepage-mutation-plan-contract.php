<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$planPath = $root . '/ops/tamduc-homepage/mutation-plan.json';

assert(is_file($planPath), 'Tâm Đức mutation plan must exist');
$raw = file_get_contents($planPath);
assert(is_string($raw) && '' !== trim($raw), 'Mutation plan must be readable');
$plan = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

assert(($plan['site'] ?? '') === 'https://tamduchanoi.aznet.vn', 'Mutation plan site mismatch');
assert(($plan['baseline_head'] ?? '') === '8ad20ef6329d48c99651867dc7ab506fa0c8d581', 'Mutation plan must bind to fresh baseline head');
assert(is_array($plan['mutations'] ?? null) && [] !== $plan['mutations'], 'Mutation plan must contain at least one sourced change');

$allowedOwners = ['wordpress_core', 'aznet_theme_presentation_config'];
$forbiddenOwners = ['rootprofile_private', 'convertflow_private', 'woocommerce_private', 'seo_private'];

foreach ($plan['mutations'] as $index => $item) {
    assert(is_array($item), "Mutation {$index} must be an object");
    foreach (['owner', 'field', 'before', 'after', 'source', 'reversible', 'rollback'] as $key) {
        assert(array_key_exists($key, $item), "Mutation {$index} missing {$key}");
    }
    assert(in_array($item['owner'], $allowedOwners, true), "Mutation {$index} has invalid owner");
    assert(!in_array($item['owner'], $forbiddenOwners, true), "Mutation {$index} crosses provider-private ownership");
    assert(true === $item['reversible'], "Mutation {$index} must be reversible");
    assert(($item['source']['status'] ?? '') === 'authoritative', "Mutation {$index} requires authoritative source");
    assert(($item['source']['kind'] ?? '') !== 'heuristic', "Mutation {$index} must not use heuristics");
    assert(($item['source']['kind'] ?? '') !== 'unknown', "Mutation {$index} must not use unknown facts");
    assert($item['rollback'] === $item['before'], "Mutation {$index} rollback must restore exact before value");
}

$encoded = json_encode($plan, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
foreach (['phone', 'hotline', 'address', 'credential', 'testimonial', 'award', 'case_result'] as $forbiddenField) {
    assert(!str_contains(strtolower((string) $encoded), '"field":"' . $forbiddenField . '"'), "Unsourced client fact forbidden: {$forbiddenField}");
}

echo "PASS: Tâm Đức sourced mutation plan contract\n";
