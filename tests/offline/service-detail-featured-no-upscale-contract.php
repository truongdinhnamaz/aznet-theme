<?php
/**
 * Service Page featured-image intrinsic sizing regression (#522).
 *
 * The native WordPress featured image can be a 400px logo or a wider
 * landscape image. Theme presentation must not upscale the bitmap to
 * the full service hero width. WordPress owns image selection/metadata.
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$service_css = (string) file_get_contents($root . '/assets/css/components/service-page.css');
$generic_css = (string) file_get_contents($root . '/assets/css/components/page.css');
$template = (string) file_get_contents($root . '/template-parts/content/page.php');

$fail = static function (string $reason): never {
    fwrite(STDERR, "FAIL: Service featured-image intrinsic sizing: {$reason}\n");
    exit(1);
};

$selector = '.aznet-theme-page--service-detail .aznet-theme-page__featured img';
if (!preg_match('/' . preg_quote($selector, '/') . '\s*\{([^}]*)\}/s', $service_css, $match)) {
    $fail('missing service-detail-scoped intrinsic-width override; native 400px bitmap is stretched by generic width:100%');
}

$declarations = $match[1];
foreach ([
    'width' => 'auto',
    'max-width' => '100%',
    'height' => 'auto',
] as $property => $value) {
    $pattern = '/(?:^|;)\s*' . preg_quote($property, '/') . '\s*:\s*' . preg_quote($value, '/') . '\s*(?:;|$)/i';
    if (!preg_match($pattern, $declarations)) {
        $fail("service image must declare {$property}: {$value}");
    }
}
if (preg_match('/!\s*important/i', $declarations)) {
    $fail('intrinsic sizing must not use !important');
}
if (!preg_match('/\.aznet-theme-page__featured\s+img\s*\{[^}]*\bwidth\s*:\s*100%\s*;/s', $generic_css)) {
    $fail('unrelated generic Page image presentation must be retained');
}
foreach ([
    '$is_service_detail',
    'aznet-theme-page--service-detail',
    "the_post_thumbnail( 'large' )",
] as $marker) {
    if (!str_contains($template, $marker)) {
        $fail('native WordPress Page/featured-image contract changed: ' . $marker);
    }
}

echo "PASS: service image intrinsic sizing without WordPress media mutation\n";
