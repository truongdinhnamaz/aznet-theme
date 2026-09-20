<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$themeJsonPath = $root . '/theme.json';
$tokensPath = $root . '/assets/css/tokens.css';

assert(is_file($themeJsonPath));
assert(is_file($tokensPath));

$themeJson = json_decode((string) file_get_contents($themeJsonPath), true, 512, JSON_THROW_ON_ERROR);
$tokens = (string) file_get_contents($tokensPath);

$robotoStack = 'Roboto, Arial, sans-serif';

assert(str_contains(
    $tokens,
    '--aznet-theme-font-family-base: ' . $robotoStack . ';'
));

assert(str_contains($tokens, '--aznet-theme-line-height-small: 1.6;'));

foreach ([
    '--aznet-theme-text-display:',
    '--aznet-theme-text-h1:',
    '--aznet-theme-text-h2:',
    '--aznet-theme-text-h3:',
    '--aznet-theme-text-h4:',
    '--aznet-theme-text-lead:',
    '--aznet-theme-text-body:',
    '--aznet-theme-text-small:',
    '--aznet-theme-text-navigation:',
    '--aznet-theme-text-button:',
    '--aznet-theme-text-label:',
    '--aznet-theme-text-meta:',
    '--aznet-theme-text-caption:',
    '--aznet-theme-text-form:',
    '--aznet-theme-text-price:',
] as $token) {
    assert(str_contains($tokens, $token), "Missing semantic typography token {$token}");
}

$families = $themeJson['settings']['typography']['fontFamilies'] ?? [];
assert(1 === count($families), 'theme.json must expose one canonical Roboto family.');
assert('roboto' === ($families[0]['slug'] ?? null));
assert($robotoStack === ($families[0]['fontFamily'] ?? null));
assert(3 === count($families[0]['fontFace'] ?? []), 'Roboto family must declare the approved 400/500/700 self-hosted faces.');

assert(
    'var:preset|font-family|roboto' === ($themeJson['styles']['typography']['fontFamily'] ?? null),
    'Global theme.json typography must use the Roboto preset.'
);


foreach ([
    'h1' => ['--aznet-theme-text-h1', '--aznet-theme-line-height-h1'],
    'h2' => ['--aznet-theme-text-h2', '--aznet-theme-line-height-h2'],
    'h3' => ['--aznet-theme-text-h3', '--aznet-theme-line-height-h3'],
    'h4' => ['--aznet-theme-text-h4', '--aznet-theme-line-height-h4'],
] as $element => [$sizeToken, $lineHeightToken]) {
    $typography = $themeJson['styles']['elements'][$element]['typography'] ?? [];
    assert(
        "var({$sizeToken})" === ($typography['fontSize'] ?? null),
        "theme.json {$element} fontSize must mirror {$sizeToken}."
    );
    assert(
        "var({$lineHeightToken})" === ($typography['lineHeight'] ?? null),
        "theme.json {$element} lineHeight must mirror {$lineHeightToken}."
    );
}

foreach ([
    $root . '/assets/fonts/roboto/Roboto-Regular.woff2',
    $root . '/assets/fonts/roboto/Roboto-Medium.woff2',
    $root . '/assets/fonts/roboto/Roboto-Bold.woff2',
] as $fontPath) {
    assert(is_file($fontPath), "Approved Roboto font asset missing: {$fontPath}");
}

echo "PASS: Roboto default typography architecture contract\n";
