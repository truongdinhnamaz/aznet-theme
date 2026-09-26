<?php
/**
 * Y2 Professional Page Kits ownership and portability contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function y2_fail(string $message): never {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

$expected = [
    'about'           => 'aznet-theme/page-about',
    'services'        => 'aznet-theme/page-services',
    'team'            => 'aznet-theme/page-team-expertise',
    'contact'         => 'aznet-theme/page-contact',
    'content_landing' => 'aznet-theme/page-content-landing',
];

$helperPath = $root . '/inc/theme/professional-page-kits.php';
if (! is_file($helperPath)) {
    y2_fail('missing inc/theme/professional-page-kits.php');
}

$bootstrapPath = $root . '/inc/theme/bootstrap.php';
$bootstrap = file_get_contents($bootstrapPath);
if (false === $bootstrap || ! str_contains($bootstrap, "require_once __DIR__ . '/professional-page-kits.php';")) {
    y2_fail('bootstrap must load professional-page-kits.php');
}

if (! defined('ABSPATH')) {
    define('ABSPATH', $root . '/');
}

if (! class_exists('WP_Block_Patterns_Registry')) {
    final class WP_Block_Patterns_Registry {
        private static ?self $instance = null;
        /** @var array<string,array<string,mixed>> */
        public array $registered = [];

        public static function get_instance(): self {
            return self::$instance ??= new self();
        }

        /** @return array<string,mixed>|null */
        public function get_registered(string $slug): ?array {
            return $this->registered[$slug] ?? null;
        }
    }
}

require_once $helperPath;

if (! function_exists('AZnet\\Theme\\professional_page_kit_roles')) {
    y2_fail('professional_page_kit_roles() missing');
}
if (! function_exists('AZnet\\Theme\\professional_page_kit_content')) {
    y2_fail('professional_page_kit_content() missing');
}

$actual = \AZnet\Theme\professional_page_kit_roles();
if ($expected !== $actual) {
    y2_fail('professional Page Kit role mapping changed');
}

if ('' !== \AZnet\Theme\professional_page_kit_content('unknown')) {
    y2_fail('unknown Page Kit role must fail soft to empty content');
}

$registry = \WP_Block_Patterns_Registry::get_instance();
foreach ($expected as $role => $slug) {
    $sentinel = '<!-- y2:' . $role . ' -->';
    $registry->registered[$slug] = ['content' => $sentinel];
    if ($sentinel !== \AZnet\Theme\professional_page_kit_content($role)) {
        y2_fail('registry-backed content lookup failed for role ' . $role);
    }
}

$helper = file_get_contents($helperPath);
if (false === $helper) {
    y2_fail('unable to read professional-page-kits.php');
}
foreach (['WP_Block_Patterns_Registry', 'get_registered'] as $required) {
    if (! str_contains($helper, $required)) {
        y2_fail('registry helper missing marker: ' . $required);
    }
}
foreach ([
    'get_option(',
    'get_post_meta(',
    '$wpdb',
    'WP_Query',
    'query_posts(',
    'get_posts(',
    'wp_insert_post(',
    'register_post_type(',
    'choiceguide_',
    'rootprofile_',
    'file_get_contents(',
] as $forbidden) {
    if (str_contains($helper, $forbidden)) {
        y2_fail('forbidden helper ownership/storage marker: ' . $forbidden);
    }
}

$patternFiles = [
    'page-about.php'           => 'aznet-theme/page-about',
    'page-services.php'        => 'aznet-theme/page-services',
    'page-team-expertise.php'  => 'aznet-theme/page-team-expertise',
    'page-contact.php'         => 'aznet-theme/page-contact',
    'page-content-landing.php' => 'aznet-theme/page-content-landing',
];

foreach ($patternFiles as $filename => $slug) {
    $path = $root . '/patterns/' . $filename;
    if (! is_file($path)) {
        y2_fail('missing Page Kit pattern: ' . $filename);
    }

    $content = file_get_contents($path);
    if (false === $content) {
        y2_fail('unable to read Page Kit pattern: ' . $filename);
    }

    foreach (['Title:', 'Slug:', 'Categories:', 'Description:'] as $header) {
        if (! str_contains($content, $header)) {
            y2_fail($filename . ' missing Theme pattern header ' . $header);
        }
    }
    if (! str_contains($content, 'Slug: ' . $slug)) {
        y2_fail($filename . ' has incorrect pattern slug');
    }
    if (! str_contains($content, 'Categories: aznet-theme-pages')) {
        y2_fail($filename . ' must use aznet-theme-pages category');
    }
    if (! str_contains($content, 'Thay bằng')) {
        y2_fail($filename . ' must explicitly instruct editors to replace placeholders');
    }

    foreach ([
        'get_option(',
        'get_post_meta(',
        '$wpdb',
        'choiceguide_',
        'rootprofile_',
        'wp_insert_post(',
        'register_post_type(',
    ] as $forbidden) {
        if (str_contains($content, $forbidden)) {
            y2_fail($filename . ' contains forbidden provider/storage marker: ' . $forbidden);
        }
    }

    if (preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $content)) {
        y2_fail($filename . ' contains a fixed email address');
    }
    if (preg_match('/(?<![\w-])(?:\+?84|0)\d{8,10}(?!\d)/', $content)) {
        y2_fail($filename . ' contains a fixed phone number');
    }
    if (preg_match('/\b\d+\+?\s*(?:năm|years?|khách hàng|customers?|chứng chỉ|certifications?|dự án|projects?)\b/ui', $content)) {
        y2_fail($filename . ' contains an unsupported quantitative client claim');
    }
}


$aboutPath = $root . '/patterns/page-about.php';
$about = file_get_contents($aboutPath);
if (false === $about) {
    y2_fail('unable to read About Page Kit pattern');
}
foreach ([
    'aznet-theme-page-kit__intro',
    'aznet-theme-page-kit__story',
    'aznet-theme-page-kit__principles',
    'aznet-theme-page-kit__process',
    'aznet-theme-page-kit__capabilities',
    'aznet-theme-page-kit__team-teaser',
    'aznet-theme-page-kit__trust',
    'aznet-theme-page-kit__cta',
] as $aboutSectionClass) {
    if (! str_contains($about, $aboutSectionClass)) {
        y2_fail('About Page Kit missing approved editorial section class ' . $aboutSectionClass);
    }
}

$pageCss = file_get_contents($root . '/assets/css/components/page.css');
if (false === $pageCss) {
    y2_fail('unable to read Page stylesheet');
}
foreach ([
    '.aznet-theme-page-kit',
    '.aznet-theme-page-kit__section',
    '.aznet-theme-page-kit__card',
    '.aznet-theme-page-kit__cta',
    '.aznet-theme-page-kit--about .aznet-theme-page-kit__story',
    '.aznet-theme-page-kit--about .aznet-theme-page-kit__principles',
    '.aznet-theme-page-kit--about .aznet-theme-page-kit__process',
    '.aznet-theme-page-kit--about .aznet-theme-page-kit__team-teaser',
] as $selector) {
    if (! str_contains($pageCss, $selector)) {
        y2_fail('Page Kit stylesheet missing shared selector ' . $selector);
    }
}
foreach ([
    '.aznet-theme-page-kit--about',
    '--aznet-theme-about-icon-scales',
    '--aznet-theme-about-icon-compass',
    '--aznet-theme-about-icon-shield',
    '--aznet-theme-about-icon-document',
    '.aznet-theme-page-kit--about .aznet-theme-page-kit__trust',
    'mask-image: var(--aznet-theme-about-icon',
] as $aboutPresentationMarker) {
    if (! str_contains($pageCss, $aboutPresentationMarker)) {
        y2_fail('About Page Kit visual refresh missing marker: ' . $aboutPresentationMarker);
    }
}
if (preg_match('/#[0-9a-f]{3,8}\b|rgba?\s*\(/i', $pageCss)) {
    y2_fail('Page Kit presentation must use existing Theme tokens instead of hard-coded brand colors');
}

if (! preg_match(
    '/\\.aznet-theme-page-kit--about \\.aznet-theme-page-kit__capabilities \\.aznet-theme-page-kit__card::after\\s*\\{[^}]*inset-block-start:\\s*var\\(--aznet-theme-space-4\\);[^}]*inset-inline-end:\\s*var\\(--aznet-theme-space-4\\);[^}]*transform:\\s*none;/s',
    $pageCss
)) {
    y2_fail('About capabilities decorative icon must be anchored to the card top-right instead of floating through body copy');
}

foreach ([
    '--aznet-theme-about-principle-icon-size: 2rem;',
    '--aznet-theme-about-trust-icon-size: 1.75rem;',
    '--aznet-theme-about-capability-icon-size: 2.5rem;',
    '--aznet-theme-about-icon-gap: var(--aznet-theme-space-3);',
    'padding-block-start: calc(var(--aznet-theme-space-4) + var(--aznet-theme-about-principle-icon-size) + var(--aznet-theme-about-icon-gap));',
    'width: var(--aznet-theme-about-principle-icon-size);',
    'padding-inline-end: calc(var(--aznet-theme-space-4) + var(--aznet-theme-about-capability-icon-size) + var(--aznet-theme-about-icon-gap));',
    'width: var(--aznet-theme-about-capability-icon-size);',
    'padding-block-start: calc(var(--aznet-theme-space-3) + var(--aznet-theme-about-trust-icon-size) + var(--aznet-theme-about-icon-gap));',
    'width: var(--aznet-theme-about-trust-icon-size);',
] as $iconGeometryMarker) {
    if (! str_contains($pageCss, $iconGeometryMarker)) {
        y2_fail('About Page Kit icon geometry audit missing marker: ' . $iconGeometryMarker);
    }
}

$style = file_get_contents($root . '/style.css');
$functions = file_get_contents($root . '/functions.php');
if (false === $style || false === $functions) {
    y2_fail('unable to read Theme version declarations');
}
if (! preg_match('/^Version:\s*([0-9]+\.[0-9]+\.[0-9]+)\s*$/m', $style, $styleVersion)) {
    y2_fail('Y2 retained contract requires a semantic style.css Theme version');
}
if (! preg_match("/define\(\s*'AZNET_THEME_VERSION'\s*,\s*'([0-9]+\.[0-9]+\.[0-9]+)'\s*\)/", $functions, $functionVersion)) {
    y2_fail('Y2 retained contract requires AZNET_THEME_VERSION');
}
if ($styleVersion[1] !== $functionVersion[1]) {
    y2_fail('Theme version declarations must remain consistent');
}

echo "PASS: Y2 Professional Page Kits ownership/registry/content contract\n";