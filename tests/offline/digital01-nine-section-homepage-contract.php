<?php
/** Digital 01 reusable full agency homepage: content roles only, no aznet.vn data authority. */
declare(strict_types=1);
$root = dirname(__DIR__, 2);
$path = $root . '/patterns/homepage-digital-01.php';
$pattern = (string) file_get_contents($path);
$roles = [
    'hero', 'trust', 'why', 'services', 'process',
    'about', 'products', 'articles', 'faq', 'cta',
];
$previous = -1;
foreach ($roles as $role) {
    $needle = 'aznet-digital01__' . $role;
    $pos = strpos($pattern, $needle);
    if (false === $pos || $pos <= $previous) {
        fwrite(STDERR, "FAIL: Digital 01 reusable Homepage missing/incorrect section order: {$role}\n");
        exit(1);
    }
    $previous = $pos;
}
if (!str_contains($pattern, '<!-- wp:query') || !str_contains($pattern, 'wp:post-template')) {
    fwrite(STDERR, "FAIL: WordPress-owned Query Loop must remain native in Classic-ready pattern.\n");
    exit(1);
}
if (substr_count($pattern, '<!-- wp:heading {"level":1}') !== 1) {
    fwrite(STDERR, "FAIL: Hero must be the only primary heading.\n");
    exit(1);
}
foreach (['wp_insert_post(', 'get_post_meta(', 'get_option(', 'get_user_meta(', '$wpdb', 'choiceguide_', 'rootprofile_'] as $forbidden) {
    if (str_contains($pattern, $forbidden)) {
        fwrite(STDERR, "FAIL: Theme pattern accessed forbidden domain/storage API: {$forbidden}\n");
        exit(1);
    }
}
$css = (string) file_get_contents($root . '/assets/css/components/homepage-digital-01.css');
foreach (['__hero', '__trust', '__why', '__services', '__process', '__about', '__products', '__articles', '__faq', '__cta'] as $suffix) {
    if (!str_contains($css, 'aznet-digital01' . $suffix)) {
        fwrite(STDERR, "FAIL: Digital 01 presentation CSS lacks section role {$suffix}.\n");
        exit(1);
    }
}
echo "PASS: Digital 01 full Homepage ten-role presentation, native content authority and scoped CSS\n";
