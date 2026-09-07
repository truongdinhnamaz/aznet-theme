<?php
/**
 * Header commerce-actions primitive.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$utilities_enabled = isset( $args['utilities_enabled'] ) ? true === $args['utilities_enabled'] : true;
$account_url       = isset( $args['account_url'] ) ? (string) $args['account_url'] : '';
$cart_url          = isset( $args['cart_url'] ) ? (string) $args['cart_url'] : '';

if ( ! $utilities_enabled ) {
    return;
}

if ( '' !== $account_url ) :
    ?>
    <a class="aznet-theme-site-header__utility aznet-theme-site-header__utility--account" href="<?php echo esc_url( $account_url ); ?>"><?php esc_html_e( 'Tài khoản', 'aznet-theme' ); ?></a>
    <?php
endif;

if ( '' !== $cart_url ) :
    ?>
    <a class="aznet-theme-site-header__utility aznet-theme-site-header__utility--cart" href="<?php echo esc_url( $cart_url ); ?>"><?php esc_html_e( 'Giỏ hàng', 'aznet-theme' ); ?></a>
    <?php
endif;
