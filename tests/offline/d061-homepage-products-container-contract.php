<?php
/** D-061: published Gutenberg Products nested Section must be contained once. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$css = (string) file_get_contents($root . '/assets/css/components/homepage-digital-01.css');
$selector = 'body.aznet-theme-preset--digital-01 .aznet-digital01__products > .wp-block-group.aznet-digital01__section';
$pattern = '~' . preg_quote($selector, '~') . '\\s*\\{([^}]*)\\}~';
if (!preg_match($pattern, $css, $matches)) {
    fwrite(STDERR, "FAIL: Products nested section lacks own bounded-width/padding guard.\n");
    exit(1);
}
$rule = preg_replace('/\\s+/', '', $matches[1]);
$requirements = [
    'width:min(var(--d01-max),calc(100%-48px))',
    'margin-inline:auto!important',
    'padding-block:0',
];
foreach ($requirements as $requirement) {
    if (!str_contains($rule, $requirement)) {
        fwrite(STDERR, "FAIL: Products nested section missing {$requirement}.\n");
        exit(1);
    }
}
if (!str_contains($css, '.aznet-digital01__products-grid')
    || !str_contains($css, 'grid-template-columns:repeat(2,minmax(0,1fr))')) {
    fwrite(STDERR, "FAIL: Existing Products grid styling regressed.\n");
    exit(1);
}
echo "PASS: Products nested section is a single-spaced, width-bounded presentation island.\n";
