<?php
/**
 * Industrial 01 product showcase: eight public Woo products, picture-safe
 * catalogue composition and mobile/accessibility presentation contract.
 *
 * Offline consumer fixture only. The Theme must not become a product owner.
 */
declare(strict_types=1);

namespace {
    define( 'ABSPATH', '/test/' );

    final class Industrial01ProductFixture {
        public function __construct(
            private int $id,
            private string $name,
            private string $price = ''
        ) {}
        public function get_id(): int { return $this->id; }
        public function get_name(): string { return $this->name; }
        public function get_permalink(): string { return 'https://example.test/product/' . $this->id . '/'; }
        public function get_price_html(): string { return $this->price; }
    }

    function __( string $text, string $domain = '' ): string { return $text; }
    function esc_html_e( string $text, string $domain = '' ): void { echo htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' ); }
    function esc_html( string $text ): string { return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' ); }
    function esc_attr( string $text ): string { return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' ); }
    function esc_url( string $text ): string { return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' ); }
    function wp_kses_post( string $text ): string { return $text; }
    function has_post_thumbnail( int $id ): bool { return 108 !== $id; }
    function get_the_post_thumbnail( int $id, string $size, array $attributes = [] ): string {
        return '<img src="/native-media/' . $id . '.jpg" alt="Image ' . $id . '" loading="lazy">';
    }
}

namespace AZnet\Theme {
    function preset_term( string $role, string $fallback, string $preset ): string { return $fallback; }
}

namespace AZnet\Theme\Integrations\WooCommerce {
    function homepage_products( int $limit = 8 ): array {
        return array_slice( $GLOBALS['products_fixture'] ?? [], 0, $limit );
    }
    function shop_url(): string { return $GLOBALS['shop_fixture'] ?? 'https://example.test/shop/'; }
}

namespace {
    $root = dirname( __DIR__, 2 );
    $template = $root . '/template-parts/homepage/industrial-01/products.php';
    $css_path = $root . '/assets/css/components/homepage-industrial-01.css';
    $fail = static function ( bool $ok, string $message ): void {
        if ( ! $ok ) { fwrite( STDERR, "FAIL: {$message}\n" ); exit( 1 ); }
    };
    $render = static function () use ( $template ): string {
        ob_start();
        require $template;
        return (string) ob_get_clean();
    };
    $GLOBALS['products_fixture'] = [];
    for ( $i = 101; $i <= 108; $i++ ) {
        $GLOBALS['products_fixture'][] = new Industrial01ProductFixture(
            $i,
            'Máy chế biến gỗ nguồn Woo số ' . $i . ' với tiêu đề dài để kiểm tra hiển thị',
            102 === $i ? '<span class="amount">12.000.000 đ</span>' : ''
        );
    }

    $html = $render();
    $fail( str_contains( $html, 'aria-labelledby="aznet-industrial01-products-title"' ), 'Product section must expose a stable labelled heading' );
    $fail( str_contains( $html, 'aznet-theme-industrial01-products__heading' ), 'Product editorial heading must match Industrial 01' );
    $fail( str_contains( $html, 'aznet-theme-industrial01-products__all' ), 'Public shop CTA is required, without hard-coded website URL' );
    $fail( str_contains( $html, 'https://example.test/shop/' ), 'Shop CTA must come from Woo public adapter' );
    $fail( substr_count( $html, 'class="aznet-theme-industrial01-card aznet-theme-industrial01-product-card"' ) === 8,
        'Preserve eight source products, no decorative ninth card or altered product query' );
    $fail( substr_count( $html, 'aznet-theme-industrial01-product-card__index' ) === 8,
        'Every product has a purely decorative item index' );
    $fail( substr_count( $html, 'aznet-theme-industrial01-product-card__arrow' ) === 8,
        'Every product has an accessible link with presentation-only arrow' );
    $fail( str_contains( $html, 'aznet-theme-industrial01-product-card__media--empty' ),
        'Product without a picture must render a stable labelled clickable media fallback' );
    for ( $i = 101; $i <= 108; $i++ ) {
        $fail( str_contains( $html, 'Máy chế biến gỗ nguồn Woo số ' . $i ), 'Full native product title must survive: ' . $i );
        $fail( substr_count( $html, 'https://example.test/product/' . $i . '/' ) >= 2, 'Keep source permalink: ' . $i );
        if ( 108 !== $i ) {
            $fail( str_contains( $html, '/native-media/' . $i . '.jpg' ), 'Preserve WordPress native image: ' . $i );
        }
    }
    $fail( str_contains( $html, '12.000.000 đ' ), 'Show genuine provider price HTML when nonempty' );
    $fail( substr_count( $html, 'aznet-theme-industrial01-product-card__price' ) === 1, 'Only source-supplied price may be displayed' );
    $fail( ! str_contains( $html, 'đánh giá' ), 'Do not invent star ratings or reviews' );

    $GLOBALS['products_fixture'] = [];
    $fail( '' === trim( $render() ), 'Woo absent or empty must omit product section completely' );

    $css = (string) file_get_contents( $css_path );
    foreach ( [
        '.aznet-theme-industrial01-products__heading',
        '.aznet-theme-industrial01-products__all',
        '.aznet-theme-industrial01-products .aznet-theme-industrial01-product-card',
        '.aznet-theme-industrial01-product-card__index',
        '.aznet-theme-industrial01-product-card__arrow',
        '.aznet-theme-industrial01-product-card__media--empty',
        'object-fit: contain',
        ':focus-visible',
        '@media (max-width: 64rem)',
        '@media (max-width: 48rem)',
        '@media (prefers-reduced-motion: reduce)',
    ] as $needle ) {
        $fail( str_contains( $css, $needle ), 'Missing product presentation CSS: ' . $needle );
    }
    foreach ( [ 'get_post_meta(', 'get_option(', 'wc_get_products(', 'get_posts(', 'get_terms(', 'update_post_meta(' ] as $forbidden ) {
        $fail( ! str_contains( (string) file_get_contents( $template ), $forbidden ),
            'Do not bypass the product owner or create parallel query: ' . $forbidden );
    }
    $fail( ! str_contains( $css, 'minhnguyen.vn' ), 'Theme CSS must remain site-agnostic' );

    echo "PASS: Industrial 01 source-safe, image-preserving, accessible and responsive Woo product showcase\n";
}
