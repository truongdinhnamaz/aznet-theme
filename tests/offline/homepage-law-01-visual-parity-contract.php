<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$css = (string) file_get_contents($root . '/assets/css/components/homepage-law-01-variants.css');

$must = static function (bool $condition, string $message): void {
    assert($condition, $message);
};

// Visual target approved from the supplied law-firm homepage reference:
// balanced light split Hero, compact four-item trust strip, and six-card services row.
$must(
    str_contains($css, 'grid-template-columns: minmax(0, 48%) minmax(0, 52%);'),
    'Law 01 target Hero must use the approved near-balanced 48/52 desktop split.'
);
$must(
    str_contains($css, 'min-height: clamp(29rem, 32vw, 34rem);'),
    'Law 01 target Hero visual must keep the approved desktop image band proportion.'
);
$must(
    str_contains($css, 'min-height: 4.8rem;'),
    'Law 01 target trust strip must stay compact while retaining comfortable icon/text rhythm.'
);
$must(
    str_contains($css, 'grid-template-columns: repeat(6, minmax(0, 1fr));'),
    'Law 01 target services must retain six cards in one desktop row.'
);
$must(
    str_contains($css, 'min-height: 12.25rem;'),
    'Law 01 target service cards must use the approved compact card density.'
);

echo "PASS: Law 01 Hero/Trust/Services visual parity contract\n";
