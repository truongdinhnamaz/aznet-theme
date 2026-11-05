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
    >
        <svg class="aznet-theme-site-header__mobile-menu-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" aria-hidden="true" focusable="false">
            <path d="M4 6.5h16M4 12h16M4 17.5h16" />
        </svg>
        <span><?php esc_html_e( 'Menu', 'aznet-theme' ); ?></span>
    </button>
    <div class="aznet-theme-site-header__mobile-backdrop" data-aznet-theme-nav-backdrop hidden aria-hidden="true"></div>
    <div
        id="aznet-theme-mobile-panel"
        class="aznet-theme-site-header__mobile-panel"
        data-aznet-theme-nav-panel
        data-aznet-theme-expand-label="<?php echo esc_attr__( 'Mở mục con:', 'aznet-theme' ); ?>"
        data-aznet-theme-collapse-label="<?php echo esc_attr__( 'Thu gọn mục con:', 'aznet-theme' ); ?>"
        tabindex="-1"
    >
        <div class="aznet-theme-site-header__mobile-panel-header">
            <strong id="aznet-theme-mobile-panel-title"><?php esc_html_e( 'Điều hướng', 'aznet-theme' ); ?></strong>
            <button type="button" class="aznet-theme-site-header__mobile-close" data-aznet-theme-nav-close aria-label="<?php echo esc_attr__( 'Đóng menu', 'aznet-theme' ); ?>">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" aria-hidden="true" focusable="false">
                    <path d="M5 5l14 14M19 5L5 19" />
                </svg>
            </button>
        </div>
        <?php if ( '' !== $mobile_menu ) : ?>
            <nav aria-label="<?php echo esc_attr__( 'Điều hướng di động', 'aznet-theme' ); ?>">
                <?php echo $mobile_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu() output. ?>
            </nav>
        <?php endif; ?>
        <?php if ( ! empty( $args['catalog_mobile_menu'] ) ) : ?>
            <details class="aznet-theme-catalog-navigation aznet-theme-catalog-navigation--mobile">
                <summary><?php esc_html_e( 'Danh mục sản phẩm', 'aznet-theme' ); ?></summary>
                <nav aria-label="<?php echo esc_attr__( 'Danh mục sản phẩm trên di động', 'aznet-theme' ); ?>">
                    <?php echo $args['catalog_mobile_menu']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu() output. ?>
                </nav>
            </details>
        <?php endif; ?>
        <div class="aznet-theme-site-header__mobile-actions">
            <?php if ( ! empty( $args['law01_header'] ) ) : ?>
                <?php get_template_part( 'template-parts/header/search', null, $args ); ?>
                <?php get_template_part( 'template-parts/header/law01-consultation', null, $args ); ?>
            <?php else : ?>
                <?php get_template_part( 'template-parts/header/utility-navigation', null, $args ); ?>
                <?php get_template_part( 'template-parts/header/search', null, $args ); ?>
                <?php get_template_part( 'template-parts/header/commerce-actions', null, $args ); ?>
            <?php endif; ?>
        </div>
    </div>
</div>
