<?php
/**
 * RQA-PAGE-01: Curtain 01 renders authored About/Projects Page HTML.
 * WordPress owns saved HTML and content filters; Theme owns scoped CSS only.
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$css = file_get_contents($root . '/assets/css/presets/curtain-01.css');
if (! is_string($css) || '' === $css) {
    fwrite(STDERR, "FAIL: Curtain 01 CSS unreadable\n");
    exit(1);
}
$fail = static function (bool $valid, string $reason): void {
    if (! $valid) {
        fwrite(STDERR, "FAIL: RQA-PAGE-01 " . $reason . "\n");
        exit(1);
    }
};
$marker = '/* RQA-PAGE-01: WordPress-authored Curtain 01 About and Projects Page presentation.';
$start = strpos($css, $marker);
$fail(false !== $start, 'authored Page presentation compatibility section missing');
$candidate = substr($css, $start);
$fail(str_contains($candidate, 'body.aznet-theme-preset--curtain-01'),
    'rules must be tied to the opted-in Curtain 01 preset');
foreach ([
    '.rqa-about-hero',
    '.rqa-projects-hero',
    '.rqa-about-hero__media',
    '.rqa-projects-hero__media',
    '.rqa-about-hero__inner',
    '.rqa-projects-hero__inner',
    '.rqa-about-story__grid',
    '.rqa-about-journey__chapters',
    '.rqa-about-values__list',
    '.rqa-about-capability__grid',
    '.rqa-projects-process__list',
    '.rqa-about-cta',
    '.rqa-projects-cta',
    '.rqa-page-tabs',
] as $selector) {
    $fail(str_contains($candidate, $selector), 'required authored HTML class unhandled: ' . $selector);
}
foreach ([
    'position: absolute;',
    'object-fit: cover;',
    'min-height: clamp(',
    'grid-template-columns:',
    '@media (max-width: 47.999rem)',
    'overflow-wrap: anywhere;',
] as $rule) {
    $fail(str_contains($candidate, $rule), 'responsive presentation invariant missing: ' . $rule);
}
foreach (['!important', '@import', 'page-id-', 'post-35', 'post-41',
    'remquocanh.vn', 'get_post_meta', 'choiceguide_', 'rootprofile/', 'woocommerce_'] as $forbidden) {
    $fail(! str_contains($candidate, $forbidden), 'ownership or side effect leaked: ' . $forbidden);
}
$fail(! str_contains($candidate, 'br { display: none; }'),
    'do not hide source-owned HTML broadly instead of correcting editorial markup');
echo "PASS: RQA-PAGE-01 scoped Curtain 01 About/Projects presentation contract\n";
