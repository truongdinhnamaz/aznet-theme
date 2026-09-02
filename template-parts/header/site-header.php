<?php
/**
 * Theme-owned site header presentation.
 *
 * Presentation provenance:
 * - ConvertFlow Core P5.223 SiteHeaderRenderer.php
 * - ConvertFlow Core P5.223 assets/css/site-header.css
 *
 * Domain/settings controllers remain with their source owners. This template
 * consumes WordPress native presentation state and optional public WooCommerce
 * functions only; missing integrations fail soft.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$site_title = trim( (string) get_bloginfo( 'name' ) );
$home_url   = home_url( '/' );
$logo_id    = (int) get_theme_mod( 'custom_logo', 0 );
$logo_html  = '';

if ( $logo_id > 0 && function_exists( 'wp_get_attachment_image' ) ) {
    $logo_html = (string) wp_get_attachment_image(
        $logo_id,
        'full',
        false,
        [
            'class' => 'aznet-theme-site-header__logo',
            'alt'   => '',
        ]
    );
}

$menu_html = static function ( string $menu_id ): string {
    $html = wp_nav_menu(
        [
            'theme_location' => 'primary',
            'container'      => false,
            'fallback_cb'    => false,
            'echo'           => false,
            'menu_class'     => 'aznet-theme-site-header__menu',
            'menu_id'        => $menu_id,
            'depth'          => 2,
        ]
    );

    return is_string( $html ) ? trim( $html ) : '';
};

$primary_menu = $menu_html( 'aznet-theme-primary-menu' );
$mobile_menu  = $menu_html( 'aznet-theme-mobile-menu' );

$account_url = '';
if ( function_exists( 'wc_get_page_permalink' ) ) {
    $candidate = wc_get_page_permalink( 'myaccount' );
    $account_url = is_string( $candidate ) ? trim( $candidate ) : '';
}

$cart_url = '';
if ( function_exists( 'wc_get_cart_url' ) ) {
    $candidate = wc_get_cart_url();
    $cart_url = is_string( $candidate ) ? trim( $candidate ) : '';
}

$render_search = static function () use ( $home_url ): void {
    ?>
    <form class="aznet-theme-site-header__search" role="search" method="get" action="<?php echo esc_url( $home_url ); ?>">
        <label>
            <span class="screen-reader-text"><?php esc_html_e( 'Tìm kiếm', 'aznet-theme' ); ?></span>
            <input type="search" name="s" placeholder="<?php echo esc_attr__( 'Tìm kiếm…', 'aznet-theme' ); ?>">
        </label>
        <button type="submit"><?php esc_html_e( 'Tìm', 'aznet-theme' ); ?></button>
    </form>
    <?php
};

$render_actions = static function () use ( $account_url, $cart_url, $render_search ): void {
    $render_search();
    if ( '' !== $account_url ) {
        ?>
        <a class="aznet-theme-site-header__utility" href="<?php echo esc_url( $account_url ); ?>"><?php esc_html_e( 'Tài khoản', 'aznet-theme' ); ?></a>
        <?php
    }
    if ( '' !== $cart_url ) {
        ?>
        <a class="aznet-theme-site-header__utility" href="<?php echo esc_url( $cart_url ); ?>"><?php esc_html_e( 'Giỏ hàng', 'aznet-theme' ); ?></a>
        <?php
    }
};
?>
<a class="aznet-theme-skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Chuyển đến nội dung', 'aznet-theme' ); ?></a>
<header class="aznet-theme-site-header aznet-theme-site-header--standard aznet-theme-site-header--sticky" data-aznet-theme-site-header role="banner">
    <div class="aznet-theme-site-header__inner">
        <a class="aznet-theme-site-header__brand" href="<?php echo esc_url( $home_url ); ?>" rel="home" aria-label="<?php echo esc_attr( $site_title ); ?>">
            <?php if ( '' !== $logo_html ) : ?>
                <?php echo $logo_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress attachment HTML. ?>
            <?php else : ?>
                <span><?php echo esc_html( $site_title ); ?></span>
            <?php endif; ?>
        </a>

        <?php if ( '' !== $primary_menu ) : ?>
            <nav class="aznet-theme-site-header__nav" aria-label="<?php echo esc_attr__( 'Điều hướng chính', 'aznet-theme' ); ?>">
                <?php echo $primary_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu output. ?>
            </nav>
        <?php endif; ?>

        <div class="aznet-theme-site-header__actions">
            <?php $render_actions(); ?>
        </div>

        <details class="aznet-theme-site-header__mobile">
            <summary><?php esc_html_e( 'Menu', 'aznet-theme' ); ?></summary>
            <div class="aznet-theme-site-header__mobile-panel">
                <?php if ( '' !== $mobile_menu ) : ?>
                    <nav aria-label="<?php echo esc_attr__( 'Điều hướng di động', 'aznet-theme' ); ?>">
                        <?php echo $mobile_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu output. ?>
                    </nav>
                <?php endif; ?>
                <div class="aznet-theme-site-header__mobile-actions">
                    <?php $render_actions(); ?>
                </div>
            </div>
        </details>
    </div>
</header>
