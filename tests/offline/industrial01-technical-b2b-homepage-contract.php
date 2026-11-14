<?php
/**
 * Industrial 01 opt-in technical B2B homepage contract.
 * Run: php tests/offline/industrial01-technical-b2b-homepage-contract.php
 * No WordPress database, domain fixtures, site mutation, or external provider.
 */
declare(strict_types=1);

namespace AZnet\Theme {
    // Independent stubs avoid booting WordPress when testing pure normalization.
    function template_presentation_ids( string $kind ): array {
        return 'homepage_preset' === $kind || 'visual_preset' === $kind ? [ 'industrial-01' ] : [];
    }
    function template_manifest_ids(): array { return [ 'industrial-01' ]; }
}

namespace {
    define( 'ABSPATH', __DIR__ );
    require_once dirname( __DIR__, 2 ) . '/inc/theme/settings.php';

    $failures = [];
    $check = static function ( bool $ok, string $label ) use ( &$failures ): void {
        if ( ! $ok ) { $failures[] = $label; }
    };
    $base = \AZnet\Theme\normalize_settings( [ 'homepage_preset' => 'industrial-01' ] );
    $technical = \AZnet\Theme\normalize_settings( [
        'homepage_preset' => 'industrial-01',
        'homepage_industrial01_variant' => 'technical-b2b',
    ] );
    $invalid = \AZnet\Theme\normalize_settings( [
        'homepage_preset' => 'industrial-01',
        'homepage_industrial01_variant' => '../../thepphuongbac',
    ] );

    $check( 'standard' === ( $base['homepage_industrial01_variant'] ?? null ), 'Existing Industrial 01 sites keep standard presentation' );
    $check( 'technical-b2b' === ( $technical['homepage_industrial01_variant'] ?? null ), 'Technical B2B variant can be explicitly selected' );
    $check( 'standard' === ( $invalid['homepage_industrial01_variant'] ?? null ), 'Unsupported variant fails closed to standard' );
    unset( $base['homepage_industrial01_variant'], $technical['homepage_industrial01_variant'] );
    $check( $base === $technical, 'Choosing technical layout never mutates source references or other settings' );

    $root = dirname( __DIR__, 2 );
    $load = static function ( string $path ) use ( $root ): string {
        $value = file_get_contents( $root . '/' . $path );
        if ( false === $value ) { throw new \RuntimeException( 'Cannot read ' . $path ); }
        return $value;
    };
    $admin = $load( 'inc/admin/homepage.php' );
    $composer = $load( 'inc/theme/homepage-composer.php' );
    $surface = $load( 'inc/theme/homepage-surface-map.php' );
    $styles = $load( 'assets/css/components/homepage-industrial-01.css' );
    $hero = $load( 'template-parts/homepage/industrial-01/hero.php' );
    $cta = $load( 'template-parts/homepage/industrial-01/cta.php' );
    $products_template = $load( 'template-parts/homepage/industrial-01/products.php' );

    $check( 1 === preg_match( "/field_select\\s*\\(\\s*'homepage_industrial01_variant'\\s*,/", $admin ), 'Editor offers a native Theme variant choice' );
    $check(
        str_contains( $admin, "\$preset_visible[] = 'homepage_industrial01_variant';" )
        && ! str_contains( $admin, "'homepage_law01_variant', 'homepage_industrial01_variant' ]" ),
        'Saving a different Homepage preset must not erase its stored Industrial variant'
    );
    $check( str_contains( $composer, 'aznet-theme-homepage--industrial-01-technical-b2b' ), 'Variant renders within the existing Theme Homepage Composer' );
    $check( str_contains( $surface, '$technical_solutions_surface' ), 'Technical layout uses the mapped WordPress solutions surface after process' );
    $check( str_contains( $surface, 'count( $categories ) > 1' ), 'Single public Woo category does not crowd technical product discovery' );
    $check( str_contains( $styles, '.aznet-theme-homepage--industrial-01-technical-b2b' ), 'Technical CSS is opt-in and isolated from default Industrial 01' );
    $check( str_contains( $styles, 'grid-template-columns: repeat(3, minmax(0, 1fr))' ), 'Three-column product catalogue layout exists' );
    $check( str_contains( $styles, '@media (max-width: 48rem)' ), 'Mobile reflow styles exist' );

    $check(
        str_contains( $cta, '$fallback_to_shop' )
        && str_contains( $cta, 'Integrations\WooCommerce\shop_url' )
        && str_contains( $cta, "'Xem sản phẩm'" ),
        'Technical B2B quote CTA uses public Woo fallback when contact source is absent'
    );
    $check(
        str_contains( $hero, '$secondary_explicit_url' )
        && str_contains( $hero, 'contact_surface_model' )
        && str_contains( $hero, "\$secondary_label = '';" ),
        'Default quote link is suppressed without a mapped WordPress contact source'
    );
    $check( str_contains( $products_template, "'Sản phẩm đang cung cấp'" ), 'Technical B2B avoids equipment-only label' );
    $check(
        str_contains( $styles, 'hero--with-background' ) && str_contains( $styles, 'rgba(7,25,41,.2)' ),
        'Technical Hero allows approved WordPress background media to remain visible'
    );

    foreach ( [ $admin, $composer, $surface, $styles, $hero, $cta, $products_template ] as $file ) {
        $check( ! str_contains( $file, 'thepphuongbac.vn' ) && ! str_contains( $file, '0965435000' ),
            'Theme must not embed site URLs or authoritative contact facts' );
    }

    if ( [] !== $failures ) {
        fwrite( STDERR, "RED: Industrial 01 technical B2B contract failures:\n- " . implode( "\n- ", $failures ) . "\n" );
        exit( 1 );
    }
    echo "PASS: Technical B2B Homepage within AZnet Theme (isolated opt-in, owner-safe)\n";
}
