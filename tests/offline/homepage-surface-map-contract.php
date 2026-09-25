<?php
/** Homepage Map shared effective-surface RED contract. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$surfacePath = $root . '/inc/theme/homepage-surface-map.php';
$composerPath = $root . '/inc/theme/homepage-composer.php';
$adminPath = $root . '/inc/admin/homepage.php';
$bootstrapPath = $root . '/inc/theme/bootstrap.php';

if (! file_exists($surfacePath)) {
    fwrite(STDERR, "FAIL: shared Homepage effective-surface model file is missing.\n");
    exit(1);
}

$surface = file_get_contents($surfacePath);
$composer = file_get_contents($composerPath);
$admin = file_get_contents($adminPath);
$bootstrap = file_get_contents($bootstrapPath);

foreach ([
    [$surface, 'function homepage_effective_surface_map(', 'effective surface resolver missing'],
    [$surface, "'boundary'", 'surface boundary metadata missing'],
    [$surface, "'anchor'", 'surface anchor metadata missing'],
    [$composer, 'homepage_effective_surface_map(', 'frontend composer does not consume shared surface model'],
    [$admin, 'function render_homepage_map(', 'Homepage Map admin renderer missing'],
    [$admin, 'homepage_effective_surface_map(', 'Homepage admin does not consume shared surface model'],
    [$admin, 'Trang chủ đang hiển thị', 'Homepage Map primary heading missing'],
    [$admin, 'Nguồn & cài đặt nâng cao', 'advanced source disclosure missing'],
    [$bootstrap, "require_once __DIR__ . '/homepage-surface-map.php';", 'surface map is not bootstrapped'],
] as [$haystack, $needle, $message]) {
    if (! is_string($haystack) || ! str_contains($haystack, $needle)) {
        fwrite(STDERR, "FAIL: {$message}.\n");
        exit(1);
    }
}

if (is_string($composer) && str_contains($composer, "foreach ( [ 'hero', 'services', 'profile' ] as $section )")) {
    fwrite(STDERR, "FAIL: frontend still owns a duplicated hard-coded Law 01 section list.\n");
    exit(1);
}

echo "PASS: Homepage Map uses one shared effective-surface model\n";
