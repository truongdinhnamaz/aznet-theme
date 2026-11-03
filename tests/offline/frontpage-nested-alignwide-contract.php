<?php
/**
 * Front Page nested Gutenberg wide alignment: prevent a second translateX
 * from moving already-centred, WordPress-owned nested Columns off-screen.
 *
 * The Theme fixes presentation only; never rewrites native post_content.
 */
declare(strict_types=1);

$root = dirname( __DIR__, 2 );
$css = (string) file_get_contents( $root . '/assets/css/components/media.css' );
$front = (string) file_get_contents( $root . '/front-page.php' );
$base = (string) file_get_contents( $root . '/style.css' );
$fail = static function ( bool $condition, string $message ): void {
    if ( ! $condition ) {
        fwrite( STDERR, 'FAIL: ' . $message . PHP_EOL );
        exit( 1 );
    }
};

// Keep ordinary top-level wide-block centering and the intentional full-bleed
// surface: remove double-translation only for nested Gutenberg wide blocks.
$fail( str_contains( $css, '.aznet-theme-entry__content .alignwide {' ),
    'Top-level Gutenberg alignwide presentation must remain' );
$fail( str_contains( $base, 'overflow-x: clip;' ),
    'Existing front-page full-bleed shell is not loosened to mask clipping' );
$fail( str_contains( $front, 'the_content();' ),
    'Native WordPress Page content stays owned and rendered by WordPress' );

// At desktop widths, the 768px native Article must remain centred in the
// full-bleed 1440px shell. Industrial01 otherwise overrides generic margins.
$articleSelector = '.aznet-theme-main--front-page .aznet-theme-entry.aznet-theme-entry--front-page';
$articlePattern = '/' . preg_quote( $articleSelector, '/' ) . '\\s*\\{([^}]*)\\}/';
$fail( preg_match( $articlePattern, $css, $articleMatches ) === 1
    && str_contains( (string) ( $articleMatches[1] ?? '' ), 'margin-inline: auto;' ),
    'Native front-page Article must be centred before applying wide-block alignment' );

$selector = '.aznet-theme-main--front-page .aznet-theme-entry__content .alignwide .alignwide';
$pattern = '/' . preg_quote( $selector, '/' ) . '\\s*\\{([^}]*)\\}/';
$fail( preg_match( $pattern, $css, $matches ) === 1,
    'Nested Gutenberg alignwide inside native front-page content must have scoped CSS fix' );
$block = (string) ( $matches[1] ?? '' );
foreach ( [ 'width: 100%;', 'max-width: 100%;', 'margin-inline: 0;', 'transform: none;' ] as $required ) {
    $fail( str_contains( $block, $required ), 'Nested alignwide clipping fix missing ' . $required );
}
$fail( ! str_contains( $block, 'display: none' ) && ! str_contains( $block, 'overflow: hidden' ),
    'Clipping fix must not conceal Gutenberg content' );
echo "PASS: nested Gutenberg wide alignment remains in viewport without changing WordPress-owned content\n";
