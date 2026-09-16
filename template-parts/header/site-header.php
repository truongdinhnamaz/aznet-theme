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
$header_classes   = [
    'aznet-theme-site-header',
    'aznet-theme-site-header--' . $effective_preset,
    'aznet-theme-site-header--' . $sticky_mode,
];
if ( function_exists( 'AZnet\\Theme\\homepage_composer_active' ) && \AZnet\Theme\homepage_composer_active() ) {
    $header_classes[] = 'aznet-theme-site-header--law01-' . \AZnet\Theme\homepage_law01_variant();
}
?>
<a class="aznet-theme-skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Chuyển đến nội dung', 'aznet-theme' ); ?></a>
<header class="<?php echo esc_attr( implode( ' ', $header_classes ) ); ?>" data-aznet-theme-site-header role="banner">
    <div class="aznet-theme-site-header__inner">
        <?php get_template_part( 'template-parts/header/brand', null, $context ); ?>

        <?php if ( 'commerce' === $effective_preset ) : ?>
            <div class="aznet-theme-site-header__commerce-search">
                <?php get_template_part( 'template-parts/header/search', null, $context ); ?>
            </div>
            <?php get_template_part( 'template-parts/header/primary-navigation', null, $context ); ?>
            <div class="aznet-theme-site-header__actions">
                <?php get_template_part( 'template-parts/header/utility-navigation', null, $context ); ?>
                <?php get_template_part( 'template-parts/header/commerce-actions', null, $context ); ?>
            </div>
        <?php else : ?>
            <?php get_template_part( 'template-parts/header/primary-navigation', null, $context ); ?>
            <div class="aznet-theme-site-header__actions">
                <?php get_template_part( 'template-parts/header/utility-navigation', null, $context ); ?>
                <?php get_template_part( 'template-parts/header/search', null, $context ); ?>
                <?php get_template_part( 'template-parts/header/commerce-actions', null, $context ); ?>
            </div>
        <?php endif; ?>

        <?php if ( \AZnet\Theme\header_mobile_panel_enabled() ) : ?>
            <?php get_template_part( 'template-parts/header/mobile-panel', null, $context ); ?>
        <?php endif; ?>
    </div>
</header>
