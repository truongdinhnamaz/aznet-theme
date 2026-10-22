<?php
/** D-057: Digital 01 site-specific double-menu Header fails softly. */
declare(strict_types=1);
$root = dirname(__DIR__, 2);
$css = (string) file_get_contents($root . '/assets/css/presets/digital-01.css');
$check = static function (bool $ok, string $reason): void {
    if (!$ok) { fwrite(STDERR, "FAIL D-057 Digital 01 Header: $reason\n"); exit(1); }
};
$check(str_contains($css, '.aznet-theme-site-header:not(.aznet-theme-site-header--nav-only) .aznet-theme-site-header__brand:has(img) .aznet-theme-site-header__brand-copy'),
    'WordPress title fallback must remain when no actual logo image exists');
$check(str_contains($css, '@media (min-width:1501px)'), 'wide Header uses one desktop menu');
$check(str_contains($css, ':has(> .aznet-theme-site-header__actions .aznet-theme-site-header__utility-nav) > .aznet-theme-site-header__nav'),
    'hide duplicate desktop primary only when a native utility menu exists');
$check(str_contains($css, 'flex-wrap:wrap'), 'editor-authored utility links must not overlap');
$check(str_contains($css, '@media (min-width:981px) and (max-width:1500px)'),
    'intermediate viewport must switch to native mobile navigation');
$check(str_contains($css, '.aznet-theme-site-header__mobile {display:block'),
    'native mobile disclosure remains available');
$check(!str_contains($css, 'page-id-18897') && !str_contains($css, 'gioi-thieu'),
    'no site-specific slug or ID routing heuristic');
echo "PASS: D-057 Digital 01 standard Header desktop/tablet contract\n";
