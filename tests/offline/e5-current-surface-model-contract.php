<?php

declare(strict_types=1);

namespace {
    define('ABSPATH', __DIR__);

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

    function assert_true(bool $actual, string $message): void
    {
        if (!$actual) {
            fail_test($message);
        }
    }
}

namespace AZnet\Theme\Integrations\RootProfile {
    function provider_available(): bool
    {
        return true;
    }

    function contact(): ?array
    {
        return $GLOBALS['e5_contact_payload'] ?? null;
    }

    function organization(): ?array
    {
        return $GLOBALS['e5_organization_payload'] ?? null;
    }

    function profile_provider_available(): bool
    {
        return true;
    }

    function person_profile(int $entity_id): ?array
    {
        if ($entity_id < 1) {
            return null;
        }
        return $GLOBALS['e5_person_profile_payload'] ?? null;
    }

    function organization_profile(): ?array
    {
        return $GLOBALS['e5_organization_profile_payload'] ?? null;
    }
}

namespace {
    require dirname(__DIR__, 2) . '/inc/theme/contact-surface.php';
    require dirname(__DIR__, 2) . '/inc/theme/profile-surface.php';

    $contact_adapter = 'AZnet\\Theme\\contact_surface_model_from_payload';
    $profile_adapter = 'AZnet\\Theme\\profile_surface_model_from_payload';

    if (!function_exists($contact_adapter)) {
        fail_test('contact_surface_model_from_payload() is not implemented');
    }
    if (!function_exists($profile_adapter)) {
        fail_test('profile_surface_model_from_payload() is not implemented');
    }

    $uuid = '123e4567-e89b-42d3-a456-426614174000';

    $person_payload = [
        'contract' => 'rootprofile.presentation',
        'version' => 2,
        'resource' => 'person_profile',
        'entity' => [
            'uuid' => $uuid,
            'display_name' => 'Nguyen Van A',
            'profile_url' => 'https://example.test/ho-so/nguyen-van-a/',
        ],
        'organization' => [
            'uuid' => '223e4567-e89b-42d3-a456-426614174000',
            'display_name' => 'AZnet Vietnam',
        ],
        'role_context' => [
            'role' => 'Founder',
        ],
        'sections' => [
            [
                'key' => 'intro',
                'label' => 'Gioi thieu',
                'anchor' => 'gioi-thieu',
                'show_in_navigation' => true,
                'section_type' => 'core',
                'origin' => 'rootprofile',
                'data' => ['text' => 'Profile intro'],
            ],
            [
                'key' => 'evidence',
                'label' => 'Bang chung',
                'anchor' => 'bang-chung',
                'show_in_navigation' => false,
                'data' => ['items' => [['title' => 'Evidence A']]],
            ],
            [
                'key' => '',
                'label' => 'Invalid',
                'anchor' => 'invalid',
                'show_in_navigation' => true,
                'data' => [],
            ],
        ],
        'signals' => ['readiness' => 'ready'],
        'updated_at' => '2026-09-03T02:00:00Z',
    ];

    $expected_person_model = [
        'resource' => 'person_profile',
        'entity' => $person_payload['entity'],
        'sections' => [
            [
                'key' => 'intro',
                'label' => 'Gioi thieu',
                'anchor' => 'gioi-thieu',
                'show_in_navigation' => true,
                'data' => ['text' => 'Profile intro'],
                'section_type' => 'core',
                'origin' => 'rootprofile',
            ],
            [
                'key' => 'evidence',
                'label' => 'Bang chung',
                'anchor' => 'bang-chung',
                'show_in_navigation' => false,
                'data' => ['items' => [['title' => 'Evidence A']]],
            ],
        ],
        'navigation' => [
            [
                'label' => 'Gioi thieu',
                'anchor' => 'gioi-thieu',
            ],
        ],
        'signals' => ['readiness' => 'ready'],
        'updated_at' => '2026-09-03T02:00:00Z',
        'organization_context' => [
            'organization' => $person_payload['organization'],
            'role_context' => $person_payload['role_context'],
        ],
    ];

    $person_model = $profile_adapter('person_profile', $person_payload);
    assert_same($expected_person_model, $person_model, 'explicit Person provider payload must preserve resolved model semantics');

    assert_same(null, $profile_adapter('person', $person_payload), 'unsupported Profile resource must be rejected');

    $invalid_person = $person_payload;
    $invalid_person['entity']['profile_url'] = '';
    assert_same(null, $profile_adapter('person_profile', $invalid_person), 'missing authoritative Profile URL must be rejected');

    $organization_profile_payload = [
        'contract' => 'rootprofile.presentation',
        'version' => 2,
        'resource' => 'organization_profile',
        'entity' => [
            'uuid' => $uuid,
            'display_name' => 'AZnet Vietnam',
            'profile_url' => 'https://example.test/gioi-thieu/',
        ],
        'sections' => [],
        'signals' => [],
        'updated_at' => '',
    ];
    $organization_profile_model = $profile_adapter('organization_profile', $organization_profile_payload);
    assert_true(is_array($organization_profile_model), 'Organization provider payload must produce a model');
    assert_true(!array_key_exists('organization_context', $organization_profile_model), 'Organization model must not invent Person organization context');

    $contact_payload = [
        'contract' => 'rootprofile.presentation',
        'version' => 1,
        'resource' => 'contact',
        'entity' => [
            'uuid' => $uuid,
            'display_name' => 'AZnet Vietnam',
            'legal_name' => 'AZnet Vietnam Co., Ltd.',
            'logo_url' => 'https://example.test/logo.png',
            'profile_url' => 'https://example.test/gioi-thieu/',
        ],
        'surface' => [
            'url' => 'https://example.test/lien-he/',
        ],
        'contact' => [
            'website' => 'https://example.test/',
            'address' => ['street' => '1 Main Street'],
            'service_area' => 'Thanh Hoa',
            'points' => [
                ['kind' => 'phone', 'value' => '0123456789'],
            ],
            'opening_hours' => [
                ['label' => 'Mon-Fri', 'value' => '08:00-17:00'],
            ],
        ],
        'social_links' => [
            ['network' => 'facebook', 'url' => 'https://facebook.com/example'],
        ],
        'policies' => [
            ['label' => 'Privacy', 'url' => 'https://example.test/privacy/'],
        ],
        'responsible_people' => [
            ['name' => 'Nguyen Van A'],
        ],
        'signals' => ['verified' => true],
    ];

    $organization_payload = [
        'contract' => 'rootprofile.presentation',
        'version' => 1,
        'resource' => 'organization',
        'entity' => [
            'uuid' => $uuid,
            'display_name' => 'AZnet Vietnam',
            'summary' => 'Trusted organization summary',
            'logo_url' => 'https://example.test/org-logo.png',
            'profile_url' => 'https://example.test/gioi-thieu/',
        ],
    ];

    $contact_model = $contact_adapter($contact_payload, $organization_payload);
    assert_true(is_array($contact_model), 'matching Contact + Organization payloads must produce a model');
    assert_same($uuid, $contact_model['entity']['uuid'] ?? null, 'Contact model must preserve authoritative UUID');
    assert_same('Trusted organization summary', $contact_model['entity']['summary'] ?? null, 'matching Organization payload may enrich summary');
    assert_same($contact_payload['contact']['points'], $contact_model['contact']['points'] ?? null, 'Contact points must preserve provider order and values');

    $mismatched_organization = $organization_payload;
    $mismatched_organization['entity']['uuid'] = '323e4567-e89b-42d3-a456-426614174000';
    assert_same(null, $contact_adapter($contact_payload, $mismatched_organization), 'mismatched Organization UUID must reject enrichment and model');

    $empty_contact = $contact_payload;
    $empty_contact['contact'] = [];
    $empty_contact['social_links'] = [];
    $empty_contact['policies'] = [];
    $empty_contact['responsible_people'] = [];
    assert_same(null, $contact_adapter($empty_contact, $organization_payload), 'Contact payload with no public details must remain rejected');

    $GLOBALS['e5_person_profile_payload'] = $person_payload;
    $GLOBALS['e5_organization_profile_payload'] = $organization_profile_payload;
    $GLOBALS['e5_contact_payload'] = $contact_payload;
    $GLOBALS['e5_organization_payload'] = $organization_payload;

    assert_same(
        $person_model,
        \AZnet\Theme\profile_surface_model('person_profile', 42),
        'provider-derived Person model must remain equivalent to explicit payload adapter'
    );
    assert_same(
        $organization_profile_model,
        \AZnet\Theme\profile_surface_model('organization_profile'),
        'provider-derived Organization model must remain equivalent to explicit payload adapter'
    );
    assert_same(
        $contact_model,
        \AZnet\Theme\contact_surface_model(),
        'provider-derived Contact model must remain equivalent to explicit payload adapter'
    );

    echo "PASS: E5 current-surface payload-to-model adapters\n";
}
