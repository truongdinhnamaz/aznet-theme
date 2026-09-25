<?php
/** D-040 TD1 Template Library admin presentation contract. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$admin = file_get_contents($root . '/inc/admin/homepage.php');
$bootstrap = file_get_contents($root . '/inc/admin/bootstrap.php');
$css = file_get_contents($root . '/assets/css/admin/control-center.css');
$js = file_get_contents($root . '/assets/js/admin/template-library.js');

foreach ([
    [$admin, 'function homepage_template_library_items()', 'Template Library catalog helper missing'],
    [$admin, 'data-aznet-template-library', 'Template Library root marker missing'],
    [$admin, 'homepage-template-search', 'Template Library search missing'],
    [$admin, 'homepage-template-category', 'Template Library category filter missing'],
    [$admin, "'law-01'", 'Law 01 compatibility missing'],
    [$admin, "'curtain-01'", 'Curtain 01 compatibility missing'],
    [$bootstrap, "'aznet-theme-template-library'", 'Template Library script not enqueued'],
    [$css, '/* D-040 / TD1 Template Library */', 'Template Library scoped CSS missing'],
    [$js, '[data-aznet-template-search]', 'Template Library filtering behavior missing'],
] as [$haystack, $needle, $message]) {
    if (! is_string($haystack) || ! str_contains($haystack, $needle)) {
        fwrite(STDERR, "FAIL: {$message}.\n");
        exit(1);
    }
}

if (is_string($admin) && str_contains($admin, "field_select( 'homepage_preset', __( 'Mẫu', 'aznet-theme' )")) {
    fwrite(STDERR, "FAIL: flat Homepage preset selector is still primary.\n");
    exit(1);
}

foreach (['get_post_meta(', '$wpdb', 'choiceguide_', 'rootprofile_'] as $forbidden) {
    if (is_string($admin) && str_contains($admin, $forbidden)) {
        fwrite(STDERR, "FAIL: Template Library crossed ownership boundary: {$forbidden}.\n");
        exit(1);
    }
}

echo "PASS: D-040 TD1 Template Library admin presentation contract\n";
