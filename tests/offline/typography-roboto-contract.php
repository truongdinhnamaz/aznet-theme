<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);

$theme_json_path = $root . '/theme.json';
$tokens_path = $root . '/assets/css/tokens.css';
$law_base_path = $root . '/assets/css/components/homepage-law-01.css';
$law_variant_path = $root . '/assets/css/components/homepage-law-01-variants.css';

foreach ([$theme_json_path, $tokens_path, $law_base_path, $law_variant_path] as $path) {
    assert(is_file($path), "Typography contract input missing: {$path}");
}

$theme_json = json_decode((string) file_get_contents($theme_json_path), true, 512, JSON_THROW_ON_ERROR);
$tokens = (string) file_get_contents($tokens_path);
$law_base = (string) file_get_contents($law_base_path);
$law_variant = (string) file_get_contents($law_variant_path);

assert(str_contains($tokens, '--aznet-theme-font-family-base: Roboto, Arial, sans-serif;'));
assert(str_contains($tokens, '--aznet-theme-font-family-heading: var(--aznet-theme-font-family-base);'));

$families = $theme_json['settings']['typography']['fontFamilies'] ?? [];
$roboto = array_values(array_filter(
    $families,
    static fn(array $family): bool => 'roboto' === ($family['slug'] ?? null)
));
assert(1 === count($roboto), 'theme.json must expose exactly one Roboto family.');
assert('Roboto, Arial, sans-serif' === ($roboto[0]['fontFamily'] ?? null));

$faces = $roboto[0]['fontFace'] ?? [];
$weights = array_map(static fn(array $face): string => (string) ($face['fontWeight'] ?? ''), $faces);
foreach (['400', '500', '700'] as $weight) {
    assert(in_array($weight, $weights, true), "Roboto fontFace missing weight {$weight}.");
}
foreach ($faces as $face) {
    assert('swap' === ($face['fontDisplay'] ?? null), 'Roboto fontFace must use font-display swap.');
    foreach ((array) ($face['src'] ?? []) as $src) {
        assert(! str_contains((string) $src, 'fonts.googleapis.com'));
        assert(! str_contains((string) $src, 'fonts.gstatic.com'));
        assert(str_contains((string) $src, 'assets/fonts/roboto/'));
    }
}

$global_family = $theme_json['styles']['typography']['fontFamily'] ?? null;
assert('var:preset|font-family|roboto' === $global_family, 'theme.json global typography must use the Roboto preset.');

assert(! str_contains($law_base, "Georgia, 'Times New Roman', serif"));
assert(! str_contains($law_variant, "Georgia, 'Times New Roman', serif"));
assert(str_contains($law_base, 'var(--aznet-theme-font-family-heading)'));
assert(str_contains($law_variant, 'var(--aznet-theme-font-family-heading)'));

echo "PASS: Roboto default typography contract\n";
