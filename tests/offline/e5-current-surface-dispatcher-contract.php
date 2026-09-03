<?php

declare(strict_types=1);

namespace {
    define('ABSPATH', __DIR__);
    define('AZNET_THEME_VERSION', '0.1.0-alpha.7');

    $GLOBALS['e5_templates'] = [];
    $GLOBALS['e5_styles'] = [];
    $GLOBALS['e5_provider_payloads'] = [];

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

    function reset_dispatch_calls(): void
    {
        $GLOBALS['e5_templates'] = [];
        $GLOBALS['e5_styles'] = [];
    }

    function has_filter(string $hook): int|false
    {
        return false;
    }

    function apply_filters(string $hook, mixed $value, mixed ...$args): mixed
    {
        if ('rootprofile/presentation/provider/v1' !== $hook) {
            return $value;
        }

        $resource = (string) ($args[0] ?? '');
        return $GLOBALS['e5_provider_payloads'][$resource] ?? null;
    }

    function get_template_part(string $slug, ?string $name = null, array $args = []): void
    {
        $GLOBALS['e5_templates'][] = [
            'slug' => $slug,
            'name' => $name,
            'args' => $args,
        ];
    }

    function wp_enqueue_style(
        string $handle,
        string $src = '',
        array $deps = [],
        string|bool|null $ver = false,
        string $media = 'all'
    ): void {
        $GLOBALS['e5_styles'][] = [
            'handle' => $handle,
            'src' => $src,
            'deps' => $deps,
            'ver' => $ver,
            'media' => $media,
        ];
    }

    function get_theme_file_uri(string $path = ''): string
    {
        return 'https://theme.example.test' . $path;
    }

    $root = dirname(__DIR__, 2);
    $dispatcher = $root . '/inc/theme/rootprofile-current-surface.php';

    require $root . '/inc/integrations/rootprofile.php';
    require $root . '/inc/theme/contact-surface.php';
    require $root . '/inc/theme/profile-surface.php';

    if (!is_file($dispatcher)) {
        fail_test('dispatcher module does not exist');
    }

    require $dispatcher;

    $render = 'AZnet\\Theme\\render_current_rootprofile_surface';
    if (!function_exists($render)) {
        fail_test('render_current_rootprofile_surface() is not implemented');
    }

    $uuid = '123e4567-e89b-42d3-a456-426614174000';

    $person = [
        'surface' => 'person_profile',
        'presentation' => [
            'contract' => 'rootprofile.presentation',
            'version' => 2,
            'resource' => 'person_profile',
            'entity' => [
                'uuid' => $uuid,
                'display_name' => 'Nguyen Van A',
                'profile_url' => 'https://example.test/ho-so/nguyen-van-a/',
            ],
            'sections' => [],
        ],
    ];

    reset_dispatch_calls();
    assert_true($render($person), 'valid Person current-surface context must render');
    assert_same(['aznet-theme-profile-surface'], array_column($GLOBALS['e5_styles'], 'handle'), 'Person must enqueue Profile CSS exactly once');
    assert_same(['template-parts/profile/surface'], array_column($GLOBALS['e5_templates'], 'slug'), 'Person must render Profile template exactly once');

    $organization = [
        'surface' => 'organization_profile',
        'presentation' => [
            'contract' => 'rootprofile.presentation',
            'version' => 2,
            'resource' => 'organization_profile',
            'entity' => [
                'uuid' => $uuid,
                'display_name' => 'AZnet Vietnam',
                'profile_url' => 'https://example.test/gioi-thieu/',
            ],
            'sections' => [],
        ],
    ];

    reset_dispatch_calls();
    assert_true($render($organization), 'valid Organization current-surface context must render');
    assert_same(['aznet-theme-profile-surface'], array_column($GLOBALS['e5_styles'], 'handle'), 'Organization must enqueue Profile CSS exactly once');
    assert_same(['template-parts/profile/surface'], array_column($GLOBALS['e5_templates'], 'slug'), 'Organization must render Profile template exactly once');

    $contact_presentation = [
        'contract' => 'rootprofile.presentation',
        'version' => 1,
        'resource' => 'contact',
        'entity' => [
            'uuid' => $uuid,
            'display_name' => 'AZnet Vietnam',
            'profile_url' => 'https://example.test/gioi-thieu/',
        ],
        'surface' => [
            'url' => 'https://example.test/lien-he/',
        ],
        'contact' => [
            'website' => 'https://example.test/',
            'address' => [],
            'service_area' => '',
            'points' => [],
            'opening_hours' => [],
        ],
        'social_links' => [],
        'policies' => [],
        'responsible_people' => [],
        'signals' => [],
    ];

    $GLOBALS['e5_provider_payloads']['organization'] = [
        'contract' => 'rootprofile.presentation',
        'version' => 1,
        'resource' => 'organization',
        'entity' => [
            'uuid' => $uuid,
            'display_name' => 'AZnet Vietnam',
            'summary' => 'AZnet organization summary',
            'profile_url' => 'https://example.test/gioi-thieu/',
        ],
    ];

    reset_dispatch_calls();
    assert_true($render([
        'surface' => 'contact',
        'presentation' => $contact_presentation,
    ]), 'valid Contact current-surface context must render');
    assert_same(['aznet-theme-contact-surface'], array_column($GLOBALS['e5_styles'], 'handle'), 'Contact must enqueue Contact CSS exactly once');
    assert_same(['template-parts/contact/surface'], array_column($GLOBALS['e5_templates'], 'slug'), 'Contact must render Contact template exactly once');

    reset_dispatch_calls();
    assert_same(false, $render(['surface' => 'person_profile', 'presentation' => null]), 'malformed context must fail soft');
    assert_same([], $GLOBALS['e5_styles'], 'malformed context must not enqueue surface CSS');
    assert_same([], $GLOBALS['e5_templates'], 'malformed context must not render a template');

    reset_dispatch_calls();
    assert_same(false, $render(['surface' => 'unknown', 'presentation' => []]), 'unsupported surface must fail soft');
    assert_same([], $GLOBALS['e5_styles'], 'unsupported surface must not enqueue surface CSS');
    assert_same([], $GLOBALS['e5_templates'], 'unsupported surface must not render a template');

    echo "PASS: E5 dormant current-surface dispatcher\n";
}
