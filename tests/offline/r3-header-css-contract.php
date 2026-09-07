<?php
/**
 * R3 Header preset CSS contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$path = $root . '/assets/css/components/site-header.css';
$css  = file_get_contents($path);

if (false === $css) {
    fwrite(STDERR, "FAIL: unable to read Header stylesheet\n");
    exit(1);
}

foreach ([
    '.aznet-theme-site-header--standard',
    '.aznet-theme-site-header--compact',
    '.aznet-theme-site-header--commerce',
    '.aznet-theme-site-header--overlay',
] as $selector) {
    if (! str_contains($css, $selector)) {
        fwrite(STDERR, "FAIL: missing R3 Header preset selector {$selector}\n");
        exit(1);
    }
}

if (! preg_match('/\.aznet-theme-site-header--compact\s*\{[^}]*--aznet-theme-header-current-height\s*:/s', $css)) {
    fwrite(STDERR, "FAIL: compact preset must define current Header height through a Theme token\n");
    exit(1);
}

if (! preg_match('/\.aznet-theme-site-header--commerce\s+\.aznet-theme-site-header__commerce-search\s*\{[^}]*flex\s*:\s*1\s+1/s', $css)) {
    fwrite(STDERR, "FAIL: commerce preset must provide a flexible search region\n");
    exit(1);
}

if (! preg_match('/\.aznet-theme-site-header--overlay\s*\{([^}]*)\}/s', $css, $overlay_match)) {
    fwrite(STDERR, "FAIL: overlay preset block missing\n");
    exit(1);
}
$overlay = $overlay_match[1];
if (! str_contains($overlay, 'background: var(--aznet-theme-surface-inverse);')) {
    fwrite(STDERR, "FAIL: overlay preset needs an explicit contrast-safe solid fallback background\n");
    exit(1);
}
if (! str_contains($overlay, 'color-mix(')) {
    fwrite(STDERR, "FAIL: overlay preset must layer its translucent enhancement after the solid fallback\n");
    exit(1);
}
if (! str_contains($overlay, 'color: var(--aznet-theme-on-inverse);')) {
    fwrite(STDERR, "FAIL: overlay preset must explicitly preserve inverse text contrast\n");
    exit(1);
}

if (! preg_match('/@media\s*\(max-width:\s*980px\)[\s\S]*?\.aznet-theme-site-header__mobile\s*\{[^}]*display\s*:\s*block/s', $css)) {
    fwrite(STDERR, "FAIL: mobile navigation fallback must remain available without JavaScript\n");
    exit(1);
}

if (preg_match('/\.aznet-theme-site-header__nav[^}]*\b(?:min-)?width\s*:\s*(?:3\d\d|[4-9]\d\d|\d{4,})px/s', $css)) {
    fwrite(STDERR, "FAIL: desktop navigation must not depend on a large fixed pixel width\n");
    exit(1);
}

if (! str_contains($css, '@media (prefers-reduced-motion: reduce)')) {
    fwrite(STDERR, "FAIL: Header stylesheet must retain reduced-motion handling\n");
    exit(1);
}

echo "PASS: R3 Header preset CSS contract\n";
