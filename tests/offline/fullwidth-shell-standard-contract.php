<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);

$fail = static function (string $message): void {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

$style = (string) file_get_contents($root . '/style.css');
$curtain = (string) file_get_contents($root . '/assets/css/components/homepage-curtain-01.css');
$roadmap = (string) file_get_contents($root . '/docs/source/AZT-04-roadmap-qa-decisions.md');
$spec = (string) file_get_contents($root . '/docs/superpowers/specs/2026-09-21-curtain01-pilot-design.md');

if (!str_contains($roadmap, 'D-035') || !str_contains($roadmap, 'full-bleed outer presentation shell')) {
    $fail('D-035 full-bleed shell standard is not accepted in AZT-04');
}

if (!str_contains($spec, 'the first Hero starts flush immediately below the Header')) {
    $fail('Curtain 01 spec does not codify flush Hero geometry');
}

if (!preg_match('/\.aznet-theme-main--front-page\s*\{([^}]*)\}/s', $style, $mainMatch)) {
    $fail('global Front Page shell rule is missing');
}

foreach ([
    'width: 100%;',
    'max-width: none;',
    'margin-inline: 0;',
    'padding-block: 0;',
    'overflow-x: clip;',
] as $needle) {
    if (!str_contains($mainMatch[1], $needle)) {
        $fail('Front Page shell is not full-bleed/flush: ' . $needle);
    }
}

if (!preg_match('/\.aznet-theme-curtain01-hero\s*\{([^}]*)\}/s', $curtain, $heroMatch)
    || !str_contains($heroMatch[1], 'padding: 0;')) {
    $fail('Curtain 01 Hero still owns top/outer spacing');
}

foreach ([
    '.aznet-theme-curtain01-hero__library {',
    'width: 100%;',
    'max-width: none;',
    'margin-inline: 0;',
    'border-radius: 0;',
    'box-shadow: none;',
    '.aznet-theme-curtain01-hero__library > .wp-block-cover {',
] as $needle) {
    if (!str_contains($curtain, $needle)) {
        $fail('Curtain 01 synced Hero is not full-bleed: ' . $needle);
    }
}

if (!preg_match('/\.aznet-theme-curtain01-hero__grid\s*\{([^}]*)\}/s', $curtain, $gridMatch)) {
    $fail('Curtain 01 fallback Hero grid rule is missing');
}

foreach ([
    'width: 100%;',
    'max-width: none;',
    'margin-inline: 0;',
    'border-radius: 0;',
    'box-shadow: none;',
] as $needle) {
    if (!str_contains($gridMatch[1], $needle)) {
        $fail('Curtain 01 fallback Hero remains boxed: ' . $needle);
    }
}

if (!str_contains($curtain, '.aznet-theme-curtain01-shell {')
    || !str_contains($curtain, 'width: var(--aznet-theme-curtain-shell);')) {
    $fail('inner content shell constraint was accidentally removed');
}

echo "PASS: AZnet outer presentation is full-bleed and first Curtain 01 Hero is flush below Header while inner content stays constrained\n";
