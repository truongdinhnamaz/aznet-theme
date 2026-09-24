<?php
declare(strict_types=1);
$root = dirname(__DIR__, 2);
$source = (string) file_get_contents($root . '/inc/admin/homepage.php');
foreach ([
    'homepage_source_value( $preset, $slot, $s )',
    'homepage_source_descriptor( $preset, $slot )',
    "homepage_source_value( 'law-01', 'hero_page', \$s )",
    "homepage_source_value( \$preset, 'projects', \$s )",
] as $needle) {
    assert(str_contains($source, $needle), "Scoped diagnostics missing: {$needle}");
}
$diagnostics = substr($source, 0, (int) strpos($source, '/** Render one Page selector. */'));
foreach ([
    "\$s['homepage_hero_block']",
    "\$s['homepage_services_page']",
    "\$s['homepage_knowledge_terms']",
    "\$s['homepage_case_analysis_term']",
    "\$s['homepage_legal_news_term']",
] as $forbidden) {
    assert(! str_contains($diagnostics, $forbidden), "Diagnostics still direct-read legacy key: {$forbidden}");
}
assert(str_contains($source, '$active_preset ='));
echo "PASS: D-038 scoped Homepage diagnostics contract\n";
