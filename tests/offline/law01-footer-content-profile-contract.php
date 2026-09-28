<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$footer = (string) file_get_contents($root . '/inc/theme/footer.php');
$template = (string) file_get_contents($root . '/template-parts/footer/site-footer.php');
$admin = (string) file_get_contents($root . '/inc/admin/control-center.php');
$adminBootstrap = (string) file_get_contents($root . '/inc/admin/bootstrap.php');
$css = (string) file_get_contents($root . '/assets/css/components/site-footer.css');

$fail = static function (string $message): never {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

foreach (['about_intro', 'services'] as $needle) {
    if (! str_contains($footer, "'{$needle}'")) {
        $fail("Footer context missing source-backed {$needle}");
    }
}
foreach (["homepage_source_value( 'law-01', 'about' )", "homepage_source_value( 'law-01', 'services' )", 'homepage_renderable_child_pages'] as $needle) {
    if (! str_contains($footer, $needle)) {
        $fail("Footer must consume mapped WordPress sources: {$needle}");
    }
}
foreach (['Dịch vụ chính', 'aznet-theme-site-footer__services', 'about_intro', 'aznet-theme-site-footer__channel-link', 'aria-label', 'title='] as $needle) {
    if (! str_contains($template, $needle)) {
        $fail("Law 01 Footer template missing {$needle}");
    }
}
if (! str_contains($template, "array_slice( $services, 0, 4 )")) {
    $fail('Footer must cap the primary-service column at four mapped services');
}
foreach (['Thông tin chân trang', 'footer_profile_phone', 'footer_profile_email', 'footer_profile_facebook', 'footer_profile_tiktok', 'footer_profile_instagram'] as $needle) {
    if (! str_contains($admin, $needle)) {
        $fail("Overview Footer profile form missing {$needle}");
    }
}
foreach (['handle_footer_profile_save', 'footer_profile_from_menus'] as $needle) {
    if (! str_contains($adminBootstrap, $needle) && ! str_contains($admin, $needle)) {
        $fail("Footer profile WordPress-menu adapter missing {$needle}");
    }
}
foreach (['aznet-theme-site-footer__services', 'grid-template-columns: minmax(280px, 1.3fr) repeat(3, minmax(160px, .75fr))', 'aznet-theme-site-footer__channel-link', 'aznet-theme-site-footer__channel-icon', 'aznet-theme-footer-field-facebook', 'aznet-theme-footer-field-tiktok', 'aznet-theme-footer-field-instagram'] as $needle) {
    if (! str_contains($css, $needle)) {
        $fail("Footer CSS missing {$needle}");
    }
}
$profile = (string) file_get_contents($root . '/inc/admin/footer-profile.php');
foreach ([
    "'menu-item-title'   => $display_title",
    "'tel' === $definition['type'] || 'email' === $definition['type']",
] as $needle) {
    if (! str_contains($profile, $needle)) {
        $fail("Footer profile must render phone/email values rather than generic labels: {$needle}");
    }
}

echo "PASS: Law 01 Footer renders saved contact values and icon channel buttons\n";
