<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$admin = (string) file_get_contents($root . '/inc/admin/control-center.php');
$contentAdapter = (string) file_get_contents($root . '/inc/admin/footer-content.php');
$settings = (string) file_get_contents($root . '/inc/theme/settings.php');
$footer = (string) file_get_contents($root . '/inc/theme/footer.php');
$template = (string) file_get_contents($root . '/template-parts/footer/site-footer.php');
$script = (string) file_get_contents($root . '/assets/js/admin/footer-template-picker.js');
$adminCss = (string) file_get_contents($root . '/assets/css/admin/control-center.css');
$footerCss = (string) file_get_contents($root . '/assets/css/components/site-footer.css');

$fail = static function (string $message): never {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

foreach ([
    "'minimal'      => 2",
    "'classic'      => 3",
    "'professional' => 4",
    "'split'        => 3",
    "'centered'     => 1",
    "'compact'      => 3",
] as $needle) {
    if (! str_contains($contentAdapter, $needle)) {
        $fail('Footer editor column schema must match the selected template: ' . $needle);
    }
}

foreach ([
    'render_footer_column_editors',
    'data-footer-column-editors',
    'aznet_theme_footer_columns[',
    'aznet_theme_footer_editor_preset',
    'wp_editor(',
] as $needle) {
    if (! str_contains($admin, $needle)) {
        $fail('Footer Control Center must render one editor per selected-template column: ' . $needle);
    }
}

foreach ([
    'footer_column_setting_key',
    'footer_column_editor_value',
    'handle_footer_columns_save',
    'aznet_theme_save_footer_columns',
    "'post_type'    => 'wp_block'",
    'wp_insert_post(',
    'wp_update_post(',
    'wp_kses_post(',
] as $needle) {
    if (! str_contains($contentAdapter, $needle)) {
        $fail('Footer column content adapter missing: ' . $needle);
    }
}

foreach (['minimal', 'classic', 'professional', 'split', 'centered', 'compact'] as $preset) {
    for ($column = 1; $column <= ['minimal'=>2,'classic'=>3,'professional'=>4,'split'=>3,'centered'=>1,'compact'=>3][$preset]; $column++) {
        $key = "footer_{$preset}_column_{$column}_block";
        if (! str_contains($settings, "'{$key}'")) {
            $fail('Theme settings must retain only the WordPress block reference for ' . $key);
        }
    }
}

foreach (['footer_columns_active', 'footer_columns'] as $needle) {
    if (! str_contains($footer, "'{$needle}'")) {
        $fail('Footer context must expose configured column content: ' . $needle);
    }
}
foreach (['footer_columns_active', 'aznet-theme-site-footer__column-content', 'foreach ( $footer_columns as $column_index => $column_content )'] as $needle) {
    if (! str_contains($template, $needle)) {
        $fail('Footer template must render configured column editors as columns: ' . $needle);
    }
}

foreach (['data-footer-column-editors', 'editorGroups', 'editorPanel.hidden'] as $needle) {
    if (! str_contains($script, $needle)) {
        $fail('Footer picker must switch the editor collection with the selected template: ' . $needle);
    }
}

foreach (['.aznet-theme-footer-content-editor__columns', '.aznet-theme-footer-content-editor__column', '.aznet-theme-site-footer__column-content'] as $selector) {
    if (! str_contains($adminCss . "\n" . $footerCss, $selector)) {
        $fail('Footer column editor presentation missing selector: ' . $selector);
    }
}

if (str_contains($admin, "'Soạn nội dung như văn bản bình thường. Bạn có thể chèn liên kết trực tiếp bằng trình soạn thảo; nội dung dùng chung cho mọi mẫu Footer và mọi pilot.'")) {
    $fail('Footer content must no longer be represented by one shared editor across all templates');
}

echo "PASS: Footer content editors follow the selected template column structure\n";
