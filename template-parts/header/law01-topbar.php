<?php
/** Law 01 reference topbar: native WordPress tagline and existing public menus only. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( empty( $args['law01_homepage'] ) || 'burgundy-gold' !== homepage_law01_variant() ) { return; }
$tagline = trim( (string) get_bloginfo( 'description' ) );
$has_contact = true === setting( 'header_utilities', true ) && has_nav_menu( 'header-utility' );
$has_social = has_nav_menu( 'footer-social' );
if ( '' === $tagline && ! $has_contact && ! $has_social ) { return; }
?>
<div class="aznet-theme-law01-topbar">
    <div class="aznet-theme-law01-topbar__inner">
        <?php if ( '' !== $tagline ) : ?><p class="aznet-theme-law01-topbar__tagline"><?php echo esc_html( $tagline ); ?></p><?php endif; ?>
        <?php if ( $has_contact ) : ?>
            <nav class="aznet-theme-law01-topbar__contact" aria-label="<?php esc_attr_e( 'Liên hệ nhanh', 'aznet-theme' ); ?>">
                <?php wp_nav_menu( [ 'theme_location' => 'header-utility', 'container' => false, 'menu_class' => 'aznet-theme-law01-topbar__menu', 'menu_id' => 'aznet-theme-law01-topbar-contact-menu', 'fallback_cb' => false, 'depth' => 1 ] ); ?>
            </nav>
        <?php endif; ?>
        <?php if ( $has_social ) : ?>
            <nav class="aznet-theme-law01-topbar__social" aria-label="<?php esc_attr_e( 'Kết nối mạng xã hội', 'aznet-theme' ); ?>">
                <?php wp_nav_menu( [ 'theme_location' => 'footer-social', 'container' => false, 'menu_class' => 'aznet-theme-law01-topbar__menu', 'menu_id' => 'aznet-theme-law01-topbar-social-menu', 'fallback_cb' => false, 'depth' => 1 ] ); ?>
            </nav>
        <?php endif; ?>
    </div>
</div>
