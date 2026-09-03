<?php

declare(strict_types=1);

define('ABSPATH', __DIR__);

$GLOBALS['aznet_test_filters'] = [];

function has_filter(string $hook): int|false
{
    return array_key_exists($hook, $GLOBALS['aznet_test_filters']) ? 10 : false;
}

function apply_filters(string $hook, mixed $value, mixed ...$args): mixed
{
    if (!array_key_exists($hook, $GLOBALS['aznet_test_filters'])) {
        return $value;
    }

    return ($GLOBALS['aznet_test_filters'][$hook])($value, ...$args);
}

function fail_test(string $message): never
{
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

function assert_same(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        fail_test($message . "\nExpected: " . var_export($expected, true) . "\nActual: " . var_export($actual, true));
    }
}

function set_current_surface_filter(?callable $filter): void
{
    $hook = 'rootprofile/presentation/current-surface/v1';
    if ($filter === null) {
        unset($GLOBALS['aznet_test_filters'][$hook]);
        return;
    }
    $GLOBALS['aznet_test_filters'][$hook] = $filter;
}

require dirname(__DIR__, 2) . '/inc/integrations/rootprofile.php';

$function = 'AZnet\\Theme\\Integrations\\RootProfile\\current_surface_context';
if (!function_exists($function)) {
    fail_test('current_surface_context() is not implemented');
}

$current_surface_context = $function;

set_current_surface_filter(null);
assert_same(null, $current_surface_context(), 'missing current-surface hook must fail soft');

set_current_surface_filter(static function (): never {
    throw new RuntimeException('provider failure');
});
assert_same(null, $current_surface_context(), 'throwing current-surface hook must fail soft');

$uuid = '123e4567-e89b-42d3-a456-426614174000';

$person_presentation = [
    'contract' => 'rootprofile.presentation',
    'version' => 2,
    'resource' => 'person_profile',
    'entity' => [
        'uuid' => $uuid,
        'display_name' => 'Nguyen Van A',
        'profile_url' => 'https://example.test/ho-so/nguyen-van-a/',
    ],
    'sections' => [],
];

$organization_presentation = [
    'contract' => 'rootprofile.presentation',
    'version' => 2,
    'resource' => 'organization_profile',
    'entity' => [
        'uuid' => $uuid,
        'display_name' => 'AZnet Vietnam',
        'profile_url' => 'https://example.test/gioi-thieu/',
    ],
    'sections' => [],
];

$contact_presentation = [
    'contract' => 'rootprofile.presentation',
    'version' => 1,
    'resource' => 'contact',
    'entity' => [
        'uuid' => $uuid,
        'display_name' => 'AZnet Vietnam',
    ],
    'contact' => [
        'points' => [
            ['kind' => 'phone', 'purpose' => 'general', 'value' => '0123456789'],
        ],
    ],
];

$valid_cases = [
    'person_profile' => $person_presentation,
    'organization_profile' => $organization_presentation,
    'contact' => $contact_presentation,
];

foreach ($valid_cases as $surface => $presentation) {
    $candidate = [
        'contract' => 'rootprofile.current_surface',
        'version' => 1,
        'surface' => $surface,
        'presentation' => $presentation,
    ];
    set_current_surface_filter(static fn(): array => $candidate);
    assert_same(
        ['surface' => $surface, 'presentation' => $presentation],
        $current_surface_context(),
        "valid {$surface} context must be accepted"
    );
}

$invalid_cases = [];
$invalid_cases['wrong current contract'] = [
    'contract' => 'rootprofile.current_surface.bad',
    'version' => 1,
    'surface' => 'person_profile',
    'presentation' => $person_presentation,
];
$invalid_cases['wrong current version'] = [
    'contract' => 'rootprofile.current_surface',
    'version' => 2,
    'surface' => 'person_profile',
    'presentation' => $person_presentation,
];
$invalid_cases['unsupported surface'] = [
    'contract' => 'rootprofile.current_surface',
    'version' => 1,
    'surface' => 'person',
    'presentation' => $person_presentation,
];
$invalid_cases['missing presentation'] = [
    'contract' => 'rootprofile.current_surface',
    'version' => 1,
    'surface' => 'person_profile',
];
$bad_person_version = $person_presentation;
$bad_person_version['version'] = 1;
$invalid_cases['wrong nested provider version'] = [
    'contract' => 'rootprofile.current_surface',
    'version' => 1,
    'surface' => 'person_profile',
    'presentation' => $bad_person_version,
];
$bad_contact_resource = $contact_presentation;
$bad_contact_resource['resource'] = 'organization';
$invalid_cases['wrong nested provider resource'] = [
    'contract' => 'rootprofile.current_surface',
    'version' => 1,
    'surface' => 'contact',
    'presentation' => $bad_contact_resource,
];

foreach ($invalid_cases as $label => $candidate) {
    set_current_surface_filter(static fn(): array => $candidate);
    assert_same(null, $current_surface_context(), $label . ' must be rejected');
}

echo "PASS: E5 current-surface consumer contract\n";
