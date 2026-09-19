<?php
/**
 * Law 01 consultation action sourced from the mapped public Contact Page.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if (
    ! function_exists( 'AZnet\\Theme\\setting' ) ||
    ! function_exists( 'AZnet\\Theme\\homepage_page_reference' )
) {
    return;
}

$contact = \AZnet\Theme\homepage_page_reference(
    (int) \AZnet\Theme\setting( 'homepage_contact_page', 0 )
);

if ( ! $contact instanceof \WP_Post ) {
    return;
}
?>
<a class="aznet-theme-site-header__consultation" href="<?php echo esc_url( get_permalink( $contact ) ); ?>">
    <?php esc_html_e( 'Yêu cầu tư vấn', 'aznet-theme' ); ?>
</a>
