<?php
/** Curtain 01 must use the shared effective Homepage Map end-to-end. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$surface = file_get_contents($root . '/inc/theme/homepage-surface-map.php');
$composer = file_get_contents($root . '/inc/theme/homepage-composer.php');
$admin = file_get_contents($root . '/inc/admin/homepage.php');

foreach ([
    [$surface, "'curtain-01' === \$preset", 'Curtain 01 shared surface branch missing'],
    [$surface, "'category-showcase'", 'Curtain 01 category showcase surface missing'],
    [$surface, "'catalogue'", 'Curtain 01 catalogue surface missing'],
    [$surface, "'projects'", 'Curtain 01 projects surface missing'],
    [$surface, "'knowledge'", 'Curtain 01 knowledge surface missing'],
    [$composer, "homepage_effective_surface_map( 'curtain-01' )", 'Curtain 01 frontend composition does not consume shared surface map'],
    [$admin, "in_array( \$preset, [ 'law-01', 'curtain-01' ], true )", 'Homepage Map does not support Curtain 01'],
    [$admin, "'Rèm 01'", 'Curtain 01 Homepage Map preset label missing'],
] as [$haystack, $needle, $message]) {
    if (! is_string($haystack) || ! str_contains($haystack, $needle)) {
        fwrite(STDERR, "FAIL: {$message}.\n");
        exit(1);
    }
}

if (str_contains((string) $composer, "foreach ( [ 'about', 'category-showcase', 'catalogue', 'process', 'projects', 'knowledge', 'final-cta' ] as \$section )")) {
    fwrite(STDERR, "FAIL: Curtain 01 frontend still owns a duplicated hard-coded section order.\n");
    exit(1);
}

$anchors = [
    'hero.php'              => 'data-aznet-homepage-surface="hero"',
    'proof-strip.php'       => 'data-aznet-homepage-surface="proof"',
    'about.php'             => 'data-aznet-homepage-surface="about"',
    'category-showcase.php' => 'data-aznet-homepage-surface="category-showcase"',
    'catalogue.php'         => 'data-aznet-homepage-surface="catalogue"',
    'process.php'           => 'data-aznet-homepage-surface="process"',
    'projects.php'          => 'data-aznet-homepage-surface="projects"',
    'knowledge.php'         => 'data-aznet-homepage-surface="knowledge"',
    'final-cta.php'         => 'data-aznet-homepage-surface="final-cta"',
];

foreach ($anchors as $file => $needle) {
    $source = file_get_contents($root . '/template-parts/homepage/curtain-01/' . $file);
    if (! is_string($source) || ! str_contains($source, $needle)) {
        fwrite(STDERR, "FAIL: Curtain 01 surface marker missing in {$file}.\n");
        exit(1);
    }
}

echo "PASS: Curtain 01 shared Homepage Map contract\n";
