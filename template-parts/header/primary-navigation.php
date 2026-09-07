<?php
/**
 * Header primary-navigation primitive.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$primary_menu = isset( $args['primary_menu'] ) ? (string) $args['primary_menu'] : '';

if ( '' === $primary_menu ) {
    return;
}
?>
<nav class="aznet-theme-site-header__nav" aria-label="<?php echo esc_attr__( 'Điều hướng chính', 'aznet-theme' ); ?>">
    <?php echo $primary_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu() output. ?>
</nav>
