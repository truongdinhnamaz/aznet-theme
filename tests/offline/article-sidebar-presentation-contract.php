<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);

$fail = static function (string $message): void {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

$widgets = (string) file_get_contents($root . '/inc/theme/widgets.php');
$single = (string) file_get_contents($root . '/template-parts/content/content-single.php');
$css = (string) file_get_contents($root . '/assets/css/components/article.css');

foreach ([
    "'id'            => 'article-navigation'",
    "'name'          => __( 'Sidebar bài viết'",
    'Vùng tiện ích bên cạnh nội dung Bài viết',
    'aznet-theme-article-sidebar-widget',
] as $needle) {
    if (!str_contains($widgets, $needle)) {
        $fail('Article widget area is not a generic Flatsome-style sidebar: ' . $needle);
    }
}

foreach ([
    'aznet-theme-article__reading-layout--with-sidebar',
    'aznet-theme-article__sidebar',
    "is_active_sidebar( 'article-navigation' )",
    "dynamic_sidebar( 'article-navigation' )",
] as $needle) {
    if (!str_contains($single, $needle)) {
        $fail('Single Post does not consume the generic article sidebar: ' . $needle);
    }
}

foreach ([
    '.aznet-theme-article__reading-layout--with-sidebar',
    '.aznet-theme-article__sidebar',
    '.aznet-theme-article-sidebar-widget__title',
    'position: sticky',
    'order: -1',
] as $needle) {
    if (!str_contains($css, $needle)) {
        $fail('Article CSS does not provide the generic responsive sidebar presentation: ' . $needle);
    }
}

foreach ([
    'Điều hướng bài viết',
    'mục lục hoặc điều hướng nội dung',
    'aznet-theme-article__navigation-sidebar',
    'aznet-theme-article__reading-layout--with-navigation',
    'aznet-theme-article-navigation-widget__title',
] as $forbidden) {
    if (str_contains($widgets . "\n" . $single . "\n" . $css, $forbidden)) {
        $fail('Old navigation-specific widget semantics remain: ' . $forbidden);
    }
}

echo "PASS: generic Article Sidebar presentation contract\n";
