<?php
/**
 * Header search primitive.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$search_enabled = isset( $args['search_enabled'] ) ? true === $args['search_enabled'] : true;
$home_url       = isset( $args['home_url'] ) ? (string) $args['home_url'] : home_url( '/' );

if ( ! $search_enabled ) {
    return;
}
?>
<form class="aznet-theme-site-header__search" role="search" method="get" action="<?php echo esc_url( $home_url ); ?>">
    <label>
        <span class="screen-reader-text"><?php esc_html_e( 'Tìm kiếm', 'aznet-theme' ); ?></span>
        <input type="search" name="s" placeholder="<?php echo esc_attr__( 'Tìm kiếm…', 'aznet-theme' ); ?>">
    </label>
    <button type="submit"><?php esc_html_e( 'Tìm', 'aznet-theme' ); ?></button>
</form>
