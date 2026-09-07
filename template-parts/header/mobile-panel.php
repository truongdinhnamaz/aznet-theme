<?php
/**
 * Header mobile-panel primitive.
 *
 * The panel is intentionally visible in server markup so primary navigation
 * remains reachable when JavaScript is unavailable. The enhancement script
 * hides it only after successful initialization.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$mobile_menu = isset( $args['mobile_menu'] ) ? (string) $args['mobile_menu'] : '';
?>
<div class="aznet-theme-site-header__mobile">
    <button
        type="button"
        class="aznet-theme-site-header__mobile-trigger"
        data-aznet-theme-nav-trigger
        aria-expanded="false"
        aria-controls="aznet-theme-mobile-panel"
    ><?php esc_html_e( 'Menu', 'aznet-theme' ); ?></button>
    <div
        id="aznet-theme-mobile-panel"
        class="aznet-theme-site-header__mobile-panel"
        data-aznet-theme-nav-panel
        tabindex="-1"
    >
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
</div>
