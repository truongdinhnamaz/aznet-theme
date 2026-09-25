<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$homepage = file_get_contents($root . '/inc/admin/homepage.php');
$control = file_get_contents($root . '/inc/admin/control-center.php');
$hero = file_get_contents($root . '/inc/admin/homepage-hero.php');

if (false === $homepage || false === $control || false === $hero) {
    fwrite(STDERR, "FAIL: unable to read Hero/Homepage admin sources\n");
    exit(2);
}

foreach ([
    "section' => 'hero-library'",
    "Thiết kế Hero",
    "function render_homepage_hero_library_screen",
    "Quay lại Trang chủ",
    "Hero mở khu thiết kế riêng",
    "'hero' !== \$slot && [] !== \$shared_uses",
] as $needle) {
    if (! str_contains($homepage, $needle)) {
        fwrite(STDERR, "FAIL: missing Hero Library separation contract: {$needle}\n");
        exit(1);
    }
}

if (preg_match("/render_homepage_settings\\(\\).*?render_homepage_hero_library\\(/s", $homepage)) {
    fwrite(STDERR, "FAIL: Homepage settings still render Hero Library inline.\n");
    exit(1);
}

if (! str_contains($control, "'hero-library'") || ! str_contains($control, "render_homepage_hero_library_screen()")) {
    fwrite(STDERR, "FAIL: Control Center has no dedicated Hero Library route.\n");
    exit(1);
}

if (str_contains($hero, "'section' => 'homepage', 'hero_form'") || str_contains($hero, "'section'=>'homepage','hero'")) {
    fwrite(STDERR, "FAIL: Hero authoring still redirects back to the Homepage Map.\n");
    exit(1);
}

echo "PASS: Hero Library is separate and Homepage Hero card exposes one design path.\n";
