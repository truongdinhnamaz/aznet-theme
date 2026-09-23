<?php
/** Curtain 01 cinematic Hero motion contract. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$assetsPath = $root . '/inc/theme/assets.php';
$heroPath   = $root . '/template-parts/homepage/curtain-01/hero.php';
$cssPath    = $root . '/assets/css/components/homepage-curtain-01.css';
$jsPath     = $root . '/assets/js/homepage-curtain-01.js';

$fail = static function (string $message): void {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

foreach ([$assetsPath, $heroPath, $cssPath] as $path) {
    if (!is_file($path)) {
        $fail('missing required Curtain 01 artifact: ' . basename($path));
    }
}

if (!is_file($jsPath)) {
    $fail('Curtain 01 cinematic Hero JS is missing.');
}

$assets = (string) file_get_contents($assetsPath);
$hero   = (string) file_get_contents($heroPath);
$css    = (string) file_get_contents($cssPath);
$js     = (string) file_get_contents($jsPath);

foreach ([
    "aznet-theme-homepage-curtain-01-motion",
    "/assets/js/homepage-curtain-01.js",
    "enqueue_homepage_curtain01_asset",
] as $needle) {
    if (!str_contains($assets, $needle)) {
        $fail('Curtain 01 motion asset is not surface-scoped: ' . $needle);
    }
}

if (!str_contains($hero, 'data-aznet-curtain-cinematic')) {
    $fail('Curtain 01 Hero lacks an explicit cinematic enhancement hook.');
}

foreach ([
    "data-aznet-curtain-cinematic",
    "prefers-reduced-motion: reduce",
    "pointermove",
    "requestAnimationFrame",
    "--aznet-curtain-shift-x",
    "--aznet-curtain-shift-y",
    "--aznet-curtain-scroll-shift",
    "is-cinematic-ready",
    "data-aznet-curtain-slide",
    "aznet-theme-curtain01-hero__dots",
    "setActiveSlide",
    "scheduleNextSlide",
    "clearTimeout",
    "visibilitychange",
] as $needle) {
    if (!str_contains($js, $needle)) {
        $fail('Curtain 01 cinematic JS missing behavior marker: ' . $needle);
    }
}

foreach ([
    ".aznet-theme-curtain01-hero[data-aznet-curtain-cinematic]",
    ".is-cinematic-ready",
    "::after",
    "@keyframes aznet-curtain01-hero-reveal",
    "@keyframes aznet-curtain01-hero-breathe",
    "@media (prefers-reduced-motion: reduce)",
    ".aznet-theme-curtain01-hero__slide",
    ".aznet-theme-curtain01-hero__slide.is-active",
    ".aznet-theme-curtain01-hero__dots",
] as $needle) {
    if (!str_contains($css, $needle)) {
        $fail('Curtain 01 cinematic CSS missing presentation marker: ' . $needle);
    }
}

foreach ([
    "setInterval(",
    "gsap",
    "swiper",
    "slick",
] as $forbidden) {
    if (stripos($js, $forbidden) !== false) {
        $fail('Curtain 01 cinematic motion must remain lightweight: ' . $forbidden);
    }
}

if (!str_contains($js, '5000') || !str_contains($css, 'opacity 1400ms')) {
    $fail('Curtain 01 crossfade must advance every 5 seconds with the existing soft fade.');
}

foreach ([
    "hero.addEventListener('pointerenter', clearSlideTimer",
    "hero.addEventListener('pointerleave', scheduleNextSlide",
] as $forbiddenPause) {
    if (str_contains($js, $forbiddenPause)) {
        $fail('Curtain 01 autoplay must not pause merely because the pointer is over the Hero: ' . $forbiddenPause);
    }
}

foreach ([
    '.aznet-theme-curtain01-hero__slide-stage {',
    'width: 100%;',
    'max-width: none !important;',
    '.aznet-theme-curtain01-hero__slide-stage > .aznet-theme-curtain01-hero__slide {',
    'width: 100%;',
    'margin: 0 !important;',
] as $needle) {
    if (!str_contains($css, $needle)) {
        $fail('Crossfade stage must override WordPress constrained-layout width/margins: ' . $needle);
    }
}

if (str_contains($js, "setAttribute('aria-hidden'") || str_contains($css, 'visibility: hidden')) {
    $fail('Crossfade must not hide the stable Hero heading from the accessibility tree.');
}

foreach ([
    '.aznet-theme-curtain01-hero__dot {',
    'min-width: 2.75rem;',
    'min-height: 2.75rem;',
    '.aznet-theme-curtain01-hero__dot::before {',
] as $targetSizeNeedle) {
    if (!str_contains($css, $targetSizeNeedle)) {
        $fail('Curtain 01 Hero slide controls must expose a >=44px pointer target while keeping the visual indicator compact: ' . $targetSizeNeedle);
    }
}

foreach ([
    "data-aznet-curtain-slide-count",
    "WP_HTML_Tag_Processor",
    "wp_get_attachment_image_srcset",
    "wp_get_attachment_image_sizes",
    "fetchpriority",
    "loading",
    "decoding",
] as $performanceNeedle) {
    if (!str_contains($hero, $performanceNeedle)) {
        $fail('Curtain 01 Hero must expose stable pre-paint slide geometry and responsive loading metadata: ' . $performanceNeedle);
    }
}

foreach ([
    ".aznet-theme-curtain01-hero[data-aznet-curtain-slide-count] .aznet-theme-curtain01-hero__library > .wp-block-group {",
    ".aznet-theme-curtain01-hero[data-aznet-curtain-slide-count] .aznet-theme-curtain01-hero__library > .wp-block-group > .wp-block-cover {",
    ".aznet-theme-curtain01-hero[data-aznet-curtain-slide-count]:not(.is-cinematic-ready) .aznet-theme-curtain01-hero__library > .wp-block-group > .wp-block-cover:not(:first-child) {",
    "grid-area: 1 / 1;",
    "opacity: 0;",
] as $prepaintNeedle) {
    if (!str_contains($css, $prepaintNeedle)) {
        $fail('Curtain 01 Hero must avoid JS-induced layout shift before cinematic initialization: ' . $prepaintNeedle);
    }
}

echo "PASS: Curtain 01 cinematic Hero supports three-slide crossfade, scoped motion and reduced-motion safety\n";
