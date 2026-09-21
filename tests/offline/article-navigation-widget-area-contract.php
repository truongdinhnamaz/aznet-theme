<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);

$fail = static function (string $message): void {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

$required = [
    'inc/theme/widgets.php',
    'template-parts/content/content-single.php',
    'assets/css/components/article.css',
    'inc/theme/bootstrap.php',
];

foreach ($required as $relative) {
    if (!is_file($root . '/' . $relative)) {
        $fail('missing article widget-area artifact: ' . $relative);
    }
}

$widgets = (string) file_get_contents($root . '/inc/theme/widgets.php');
$single = (string) file_get_contents($root . '/template-parts/content/content-single.php');
$css = (string) file_get_contents($root . '/assets/css/components/article.css');
$bootstrap = (string) file_get_contents($root . '/inc/theme/bootstrap.php');

foreach ([
    'function register_widget_areas',
    'register_sidebar(',
    "'id'            => 'article-navigation'",
    "'name'          => __( 'Article Navigation'",
] as $needle) {
    if (!str_contains($widgets, $needle)) {
        $fail('Theme does not register the native article navigation widget area: ' . $needle);
    }
}

foreach ([
    "add_action( 'widgets_init', __NAMESPACE__ . '\\\\register_widget_areas' )",
    "require_once __DIR__ . '/widgets.php';",
] as $needle) {
    if (!str_contains($bootstrap, $needle)) {
        $fail('Theme bootstrap does not wire the WordPress widget area: ' . $needle);
    }
}

foreach ([
    "is_active_sidebar( 'article-navigation' )",
    "dynamic_sidebar( 'article-navigation' )",
    'aznet-theme-article__navigation-sidebar',
    'aznet-theme-article__reading-layout--with-navigation',
] as $needle) {
    if (!str_contains($single, $needle)) {
        $fail('single Post template does not render the native article navigation widget area: ' . $needle);
    }
}

foreach ([
    '.aznet-theme-article__reading-layout--with-navigation',
    '.aznet-theme-article__navigation-sidebar',
    'position: sticky',
    '@media (max-width: 64rem)',
] as $needle) {
    if (!str_contains($css, $needle)) {
        $fail('article CSS does not support the responsive navigation sidebar: ' . $needle);
    }
}

$production = $widgets . "\n" . $single . "\n" . $css;
foreach ([
    'choiceguide_',
    'convertflow_',
    'get_option(',
    'get_post_meta(',
    '$wpdb',
    'preg_match_all',
    'DOMDocument',
] as $forbidden) {
    if (str_contains($production, $forbidden)) {
        $fail('Theme crossed the WordPress widget / ConvertFlow ownership boundary: ' . $forbidden);
    }
}

echo "PASS: native Article Navigation widget area contract\n";
