<?php
/**
 * Header mobile-panel primitive.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$mobile_menu = isset( $args['mobile_menu'] ) ? (string) $args['mobile_menu'] : '';
?>
<details class="aznet-theme-site-header__mobile">
    <summary><?php esc_html_e( 'Menu', 'aznet-theme' ); ?></summary>
    <div class="aznet-theme-site-header__mobile-panel">
        <?php if ( '' !== $mobile_menu ) : ?>
            <nav aria-label="<?php echo esc_attr__( 'Điều hướng di động', 'aznet-theme' ); ?>">
                <?php echo $mobile_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu() output. ?>
            </nav>
        <?php endif; ?>
        <div class="aznet-theme-site-header__mobile-actions">
            <?php get_template_part( 'template-parts/header/search', null, $args ); ?>
            <?php get_template_part( 'template-parts/header/commerce-actions', null, $args ); ?>
        </div>
    </div>
</details>
