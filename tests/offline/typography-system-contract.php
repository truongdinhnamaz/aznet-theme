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
$latinRange = 'U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD';
$vietnameseRange = 'U+0102-0103,U+0110-0111,U+0128-0129,U+0168-0169,U+01A0-01A1,U+01AF-01B0,U+0300-0301,U+0303-0304,U+0308-0309,U+0323,U+0329,U+1EA0-1EF9,U+20AB';

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

$faces = $families[0]['fontFace'] ?? [];
assert(6 === count($faces), 'Roboto family must declare paired Latin + Vietnamese faces for approved 400/500/700 weights.');

foreach (['400', '500', '700'] as $weight) {
    $weightFaces = array_values(array_filter(
        $faces,
        static fn(array $face): bool => $weight === (string) ($face['fontWeight'] ?? '')
    ));

    assert(2 === count($weightFaces), "Roboto weight {$weight} must declare exactly two subset faces.");

    $ranges = array_column($weightFaces, 'unicodeRange');
    assert(in_array($latinRange, $ranges, true), "Roboto weight {$weight} is missing the bounded Latin face.");
    assert(in_array($vietnameseRange, $ranges, true), "Roboto weight {$weight} is missing the Vietnamese face.");

    foreach ($weightFaces as $face) {
        assert('swap' === ($face['fontDisplay'] ?? null), "Roboto weight {$weight} must keep font-display: swap.");
        assert('Roboto' === ($face['fontFamily'] ?? null), "Roboto weight {$weight} must keep the canonical family.");
    }
}

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

$fontAssets = [
    'Roboto-Regular.woff2' => null,
    'Roboto-Medium.woff2' => null,
    'Roboto-Bold.woff2' => null,
    'Roboto-Regular-Vietnamese.woff2' => '1cd52dd843f373e9526518a66314329ceea40e2b',
    'Roboto-Medium-Vietnamese.woff2' => 'aacc7a6fdf0b86d533c1ffdf5e8c576012c741f1',
    'Roboto-Bold-Vietnamese.woff2' => '6f142f1e6e7697f01f39f7514daaa97c8459fe25',
];

foreach ($fontAssets as $filename => $expectedGitBlobSha) {
    $fontPath = $root . '/assets/fonts/roboto/' . $filename;
    assert(is_file($fontPath), "Approved Roboto font asset missing: {$fontPath}");

    if (null === $expectedGitBlobSha) {
        continue;
    }

    $bytes = (string) file_get_contents($fontPath);
    $actualGitBlobSha = sha1('blob ' . strlen($bytes) . "\0" . $bytes);
    assert(
        $expectedGitBlobSha === $actualGitBlobSha,
        "Vietnamese Roboto asset provenance mismatch for {$filename}."
    );
}

echo "PASS: Roboto default typography architecture + Vietnamese glyph coverage contract\n";
