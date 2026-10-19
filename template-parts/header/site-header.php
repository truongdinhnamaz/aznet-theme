<?php
/**
 * Theme-owned Header composer.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$context          = \AZnet\Theme\header_context();
$effective_preset = \AZnet\Theme\effective_header_preset();
$sticky_mode      = \AZnet\Theme\header_sticky_mode();
$law01_header     = ! empty( $context['law01_header'] );
$catalog_enabled  = ! empty( $context['catalog_enabled'] );
$header_classes   = [
    'aznet-theme-site-header',
    'aznet-theme-site-header--' . $effective_preset,
    'aznet-theme-site-header--' . $sticky_mode,
];

if ( $law01_header ) {
    $header_classes[] = 'aznet-theme-site-header--law01-' . \AZnet\Theme\homepage_law01_variant();
}
if ( $catalog_enabled ) {
    $header_classes[] = 'aznet-theme-site-header--catalog';
    if ( ! empty( $context['catalog_home_rail'] ) ) {
        $header_classes[] = 'aznet-theme-site-header--catalog-home-rail';
    }
}
?>
<a class="aznet-theme-skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Chuyển đến nội dung', 'aznet-theme' ); ?></a>
<header class="<?php echo esc_attr( implode( ' ', $header_classes ) ); ?>" data-aznet-theme-site-header role="banner">
    <div class="aznet-theme-site-header__inner">
        <?php get_template_part( 'template-parts/header/brand', null, $context ); ?>

        <?php if ( ! empty( $context['catalog_home_rail'] ) ) : ?>
            <div class="aznet-theme-site-header__catalog-rail-label">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" aria-hidden="true" focusable="false">
                    <path d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
                <span><?php esc_html_e( 'Danh mục sản phẩm', 'aznet-theme' ); ?></span>
            </div>
        <?php endif; ?>

        <?php if ( 'commerce' === $effective_preset ) : ?>
            <div class="aznet-theme-site-header__commerce-search">
                <?php get_template_part( 'template-parts/header/search', null, $context ); ?>
            </div>
            <?php if ( ! empty( $context['catalog_menu'] ) ) : ?>
                <?php get_template_part( 'template-parts/header/catalog-navigation', null, $context ); ?>
            <?php endif; ?>
            <?php get_template_part( 'template-parts/header/primary-navigation', null, $context ); ?>
            <div class="aznet-theme-site-header__actions">
                <?php get_template_part( 'template-parts/header/utility-navigation', null, $context ); ?>
                <?php get_template_part( 'template-parts/header/commerce-actions', null, $context ); ?>
            </div>
        <?php else : ?>
            <?php if ( ! empty( $context['catalog_menu'] ) ) : ?>
                <?php get_template_part( 'template-parts/header/catalog-navigation', null, $context ); ?>
            <?php endif; ?>
            <?php get_template_part( 'template-parts/header/primary-navigation', null, $context ); ?>
            <div class="aznet-theme-site-header__actions">
                <?php if ( $law01_header ) : ?>
                    <?php get_template_part( 'template-parts/header/search', null, $context ); ?>
                    <?php get_template_part( 'template-parts/header/law01-consultation', null, $context ); ?>
                <?php else : ?>
                    <?php get_template_part( 'template-parts/header/utility-navigation', null, $context ); ?>
                    <?php get_template_part( 'template-parts/header/search', null, $context ); ?>
                    <?php get_template_part( 'template-parts/header/commerce-actions', null, $context ); ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ( \AZnet\Theme\header_mobile_panel_enabled() ) : ?>
            <?php get_template_part( 'template-parts/header/mobile-panel', null, $context ); ?>
        <?php endif; ?>
    </div>
</header>
