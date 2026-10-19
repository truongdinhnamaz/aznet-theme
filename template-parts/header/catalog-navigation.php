<?php
/**
 * Accessible, opt-in WordPress catalog-menu disclosure in the Theme Header.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$catalog_menu = isset( $args['catalog_menu'] ) ? (string) $args['catalog_menu'] : '';
if ( '' === trim( $catalog_menu ) ) {
    return;
}
?>
<details class="aznet-theme-site-header__catalog aznet-theme-catalog-navigation aznet-theme-catalog-navigation--header">
    <summary><span aria-hidden="true">☰</span><?php esc_html_e( 'Danh mục sản phẩm', 'aznet-theme' ); ?><span aria-hidden="true">⌄</span></summary>
    <nav aria-label="<?php echo esc_attr__( 'Điều hướng danh mục sản phẩm', 'aznet-theme' ); ?>">
        <?php echo $catalog_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu() output. ?>
    </nav>
</details>
