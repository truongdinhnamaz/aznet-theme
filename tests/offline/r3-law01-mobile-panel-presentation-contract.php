<?php
/**
 * Law 01 mobile navigation presentation regression contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$path = $root . '/assets/css/components/header-law-01.css';
$css  = file_get_contents($path);

if (false === $css) {
    fwrite(STDERR, "FAIL: unable to read Law 01 Header stylesheet\n");
    exit(1);
}

$fail = static function (string $message): void {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

$panel_selector = '.aznet-theme-site-header--law01-burgundy-gold .aznet-theme-site-header__mobile-panel';
if (! preg_match(
    '/@media\s*\(max-width:\s*980px\)[\s\S]*?' . preg_quote($panel_selector, '/') . '\s*\{([^}]*)\}/s',
    $css,
    $panel_match
)) {
    $fail('Law 01 needs an explicit mobile-panel presentation override.');
}

$panel = $panel_match[1];
foreach ([
    'background: #fffdf8;',
    'color: #241d19;',
    'overflow-y: auto;',
    'overscroll-behavior: contain;',
    'max-height:',
] as $needle) {
    if (! str_contains($panel, $needle)) {
        $fail("Law 01 mobile panel is missing {$needle}");
    }
}

$link_selector = '.aznet-theme-site-header--law01-burgundy-gold .aznet-theme-site-header__mobile-panel .aznet-theme-site-header__menu a';
if (! preg_match(
    '/' . preg_quote($link_selector, '/') . '\s*\{([^}]*)\}/s',
    $css,
    $link_match
)) {
    $fail('Law 01 mobile menu links need a dedicated high-contrast row treatment.');
}

$link = $link_match[1];
foreach ([
    'color: #2c241f;',
    'width: 100%;',
    'min-height: 48px;',
] as $needle) {
    if (! str_contains($link, $needle)) {
        $fail("Law 01 mobile menu links are missing {$needle}");
    }
}

$trigger_selector = '.aznet-theme-site-header--law01-burgundy-gold .aznet-theme-site-header__mobile-trigger';
if (! preg_match(
    '/' . preg_quote($trigger_selector, '/') . '\s*\{([^}]*)\}/s',
    $css,
    $trigger_match
)) {
    $fail('Law 01 mobile trigger needs a scoped light-surface treatment.');
}

$trigger = $trigger_match[1];
foreach ([
    'min-height: 44px;',
    'background: #fffdf8;',
    'color: #241d19;',
] as $needle) {
    if (! str_contains($trigger, $needle)) {
        $fail("Law 01 mobile trigger is missing {$needle}");
    }
}

echo "PASS: Law 01 mobile navigation keeps a compact high-contrast light panel.\n";
