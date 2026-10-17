<?php
/**
 * Published Curtain 01 CSS source synchronization and peer-preset isolation.
 *
 * Rèm Quốc Anh page presentation uses explicit WordPress-authored class markers.
 * Neither the shared Page component nor Law/Industrial presets may inherit it.
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$page = file_get_contents($root . '/assets/css/components/page.css');
$curtain = file_get_contents($root . '/assets/css/presets/curtain-01.css');
$law = file_get_contents($root . '/assets/css/presets/editorial.css');
$industrial = file_get_contents($root . '/assets/css/presets/industrial-01.css');
if (! is_string($page) || ! is_string($curtain) || ! is_string($law) || ! is_string($industrial)) {
    fwrite(STDERR, "FAIL: a required preset/page stylesheet is unreadable\n");
    exit(1);
}

foreach (['.page-id-726', '.page-id-45', '.rqa-quote-', '.rqa-contact-'] as $forbidden) {
    if (str_contains($page, $forbidden)) {
        fwrite(STDERR, "FAIL: shared Page CSS still contains Curtain pilot-only selector {$forbidden}\n");
        exit(2);
    }
}

foreach ([
    '.aznet-theme-page--landing',
    '.aznet-theme-latest-posts-cards.wp-block-latest-posts.is-grid',
    '.aznet-theme-latest-posts-cards .wp-block-latest-posts__featured-image',
    'grid-template-columns: repeat(2, minmax(0, 1fr));',
    '.aznet-theme-page__content',
] as $required) {
    if (! str_contains($page, $required)) {
        fwrite(STDERR, "FAIL: a reusable Page presentation rule was lost: {$required}\n");
        exit(3);
    }
}

foreach ([
    'body.aznet-theme-preset--curtain-01:has(.rqa-quote-trust)',
    'body.aznet-theme-preset--curtain-01:has(.rqa-contact-hero)',
    '.rqa-quote-factors',
    '.rqa-quote-trust',
    '.rqa-contact-main',
    '.rqa-contact-form',
    '.rqa-contact-cta',
] as $required) {
    if (! str_contains($curtain, $required)) {
        fwrite(STDERR, "FAIL: a published Curtain authored-Page rule is absent: {$required}\n");
        exit(4);
    }
}

foreach (['.page-id-', 'get_post_meta', 'get_option', 'choiceguide_', 'rootprofile_'] as $forbidden) {
    if (str_contains(strtolower($curtain), strtolower($forbidden))) {
        fwrite(STDERR, "FAIL: Curtain preset depends on private/domain data or a Page ID: {$forbidden}\n");
        exit(5);
    }
}

if (str_contains($law, '.rqa-') || str_contains($industrial, '.rqa-')) {
    fwrite(STDERR, "FAIL: another pilot preset contains Curtain-only presentation selectors\n");
    exit(6);
}

/**
 * Split a CSS selector group at top-level commas (not inside :has/:where).
 * A scoped stylesheet must not silently match a Law/Industrial page.
 */
function aznet_r6_selector_parts(string $selector): array
{
    $parts = [];
    $depth = 0;
    $offset = 0;
    $quote = null;
    $length = strlen($selector);
    for ($i = 0; $i < $length; ++$i) {
        $char = $selector[$i];
        if (null !== $quote) {
            if ('\\' === $char) { ++$i; continue; }
            if ($quote === $char) { $quote = null; }
            continue;
        }
        if ('"' === $char || "'" === $char) { $quote = $char; continue; }
        if ('(' === $char) { ++$depth; continue; }
        if (')' === $char) { --$depth; continue; }
        if (',' === $char && 0 === $depth) {
            $parts[] = trim(substr($selector, $offset, $i - $offset));
            $offset = $i + 1;
        }
    }
    $parts[] = trim(substr($selector, $offset));
    return $parts;
}

$withoutComments = preg_replace('~/\*.*?\*/~s', '', $curtain);
if (! is_string($withoutComments)) {
    fwrite(STDERR, "FAIL: CSS comment normalization failed\n");
    exit(7);
}

preg_match_all('/([^{}]+)\{/s', $withoutComments, $found);
$declarations = 0;
foreach ($found[1] as $selectorGroup) {
    $selectorGroup = trim($selectorGroup);
    if ('' === $selectorGroup || str_starts_with($selectorGroup, '@')) { continue; }
    foreach (aznet_r6_selector_parts($selectorGroup) as $selector) {
        if (! str_starts_with($selector, 'body.aznet-theme-preset--curtain-01') &&
            ! str_starts_with($selector, '.editor-styles-wrapper')) {
            fwrite(STDERR, "FAIL: Curtain CSS selector escapes the visual preset: {$selector}\n");
            exit(8);
        }
        ++$declarations;
    }
}
if ($declarations < 130) {
    fwrite(STDERR, "FAIL: expected the published Curtain visual + authored-Page rule set\n");
    exit(9);
}

echo "PASS: published Curtain 01 CSS is preset-scoped; generic Page, Law and Industrial presentation stays isolated\n";
