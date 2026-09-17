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

$style = file_get_contents($root . '/style.css');
$functions = file_get_contents($root . '/functions.php');
if (false === $style || false === $functions) {
    y2_fail('unable to read Theme version declarations');
}
if (! preg_match('/^Version:\s*1\.2\.0\s*$/m', $style)) {
    y2_fail('Y2 must keep style.css version at 1.2.0');
}
if (! str_contains($functions, "define( 'AZNET_THEME_VERSION', '1.2.0' );")) {
    y2_fail('Y2 must keep AZNET_THEME_VERSION at 1.2.0');
}

echo "PASS: Y2 Professional Page Kits ownership/registry/content contract\n";
