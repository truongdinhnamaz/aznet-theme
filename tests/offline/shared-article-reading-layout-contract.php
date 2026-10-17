<?php
/**
 * Shared native Post article reading layout across AZnet Theme presets.
 *
 * Law 01 is the reference for composition, not a source of plugin TOC data
 * or a palette to copy into every site.
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$template = (string) file_get_contents($root . '/template-parts/content/content-single.php');
$css = (string) file_get_contents($root . '/assets/css/components/article.css');

$fail = static function (string $reason): never {
    fwrite(STDERR, "FAIL: shared article layout " . $reason . "\n");
    exit(1);
};

$at = static function (string $needle) use ($template, $fail): int {
    $position = strpos($template, $needle);
    if (false === $position) {
        $fail("missing template marker: " . $needle);
    }
    return $position;
};

$positions = [
    $at('class="<?php echo esc_attr( $reading_layout_class ); ?>"'),
    $at('<header class="aznet-theme-entry__header aznet-theme-article__header">'),
    $at('<div class="aznet-theme-article__reading-main">'),
    $at('<figure class="aznet-theme-article__featured-media">'),
    $at('the_content();'),
    $at('<aside class="aznet-theme-article__sidebar"'),
];

if ($positions !== array_values(array_unique($positions)) || $positions !== array_values(array_unique(array_values($positions)))) {
    $fail('expected one native article component of each kind');
}
if ($positions !== array_values(array_merge([], $positions)) || $positions !== (function (array $x): array { sort($x); return $x; })($positions)) {
    $fail('header must be inside reading layout, before featured media and WordPress content; sidebar after main');
}
if (1 !== substr_count($template, 'the_content();')) {
    $fail('exactly one WordPress the_content() is required');
}
if (!str_contains($template, "is_active_sidebar( 'article-navigation' )") || !str_contains($template, "dynamic_sidebar( 'article-navigation' )")) {
    $fail('sidebar must remain an optional native WordPress widget area');
}
foreach ([
    '.aznet-theme-article:not(.aznet-theme-article--law01) .aznet-theme-article__reading-layout',
    '.aznet-theme-article:not(.aznet-theme-article--law01) .aznet-theme-article__header',
    '.aznet-theme-article:not(.aznet-theme-article--law01) .aznet-theme-article__reading-main',
    '.aznet-theme-article:not(.aznet-theme-article--law01) .aznet-theme-article__sidebar',
    '.aznet-theme-article:not(.aznet-theme-article--law01) .aznet-theme-article__content h2',
    'var(--aznet-theme-accent)',
    '@media (max-width: 64rem)',
    'order: 2;',
    'order: 3;',
] as $marker) {
    if (!str_contains($css, $marker)) {
        $fail('default article CSS missing: ' . $marker);
    }
}
foreach (['get_post_meta(', 'get_option(', 'DOMDocument', 'preg_match_all', 'choiceguide_', 'convertflow_'] as $forbidden) {
    if (str_contains($template . "\n" . $css, $forbidden)) {
        $fail('ownership violation: ' . $forbidden);
    }
}
// A no-sidebar native Post must also remain responsive on narrow devices.
if (substr_count($css, 'grid-template-columns: minmax(0, min(100%, 50rem));') < 3) {
    $fail('shared default/no-widget article reading grid is not width-safe');
}

// Some native widgets intentionally render no markup for a short Post.
// The presentation must not reserve a blank desktop navigation column.
$emptySidebarLayoutSelector = '.aznet-theme-article .aznet-theme-article__reading-layout--with-sidebar:not(:has(> .aznet-theme-article__sidebar > *))';
$emptySidebarSelector = '.aznet-theme-article .aznet-theme-article__sidebar:not(:has(> *))';
foreach ([
    [$emptySidebarLayoutSelector, 'grid-template-columns:\\s*minmax\\(0,\\s*min\\(100%,\\s*50rem\\)\\);'],
    [$emptySidebarSelector, 'display:\\s*none;'],
] as [$selector, $declarationPattern]) {
    $pattern = '/' . preg_quote($selector, '/') . '\\s*\\{[^}]*' . $declarationPattern . '/s';
    if (!preg_match($pattern, $css)) {
        $fail('an empty WordPress article widget must collapse its grid area: ' . $selector);
    }
}

// The approved Law 01 desktop two-column grid must not override its
// mobile/tablet one-column rule through later source-order declarations.
$lawDesktopTwoColumn = ".aznet-theme-article--law01 .aznet-theme-article__reading-layout--with-sidebar {\n    grid-template-columns: minmax(0, 50rem) minmax(15rem, 18rem);\n}";
$lawMobileOneColumn = "@media (max-width: 64rem) {\n    .aznet-theme-article--law01 .aznet-theme-article__reading-layout--with-sidebar {\n        grid-template-columns: minmax(0, 1fr);\n    }";
$lawDesktopOffset = strrpos($css, $lawDesktopTwoColumn);
$lawMobileOffset = strrpos($css, $lawMobileOneColumn);
if (false === $lawDesktopOffset || false === $lawMobileOffset || $lawMobileOffset <= $lawDesktopOffset) {
    $fail('Law 01 populated article sidebar must collapse to one column at 64rem and below');
}

echo "PASS: shared native-Post Law 01 reading composition across presets\n";
