<?php
/** Optional WordPress-native header utility navigation. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! \AZnet\Theme\setting( 'header_utilities', true ) ) { return; }
if ( ! has_nav_menu( 'header-utility' ) ) { return; }
?>
<nav class="aznet-theme-site-header__utility-nav" aria-label="<?php echo esc_attr__( 'Liên hệ nhanh', 'aznet-theme' ); ?>">
    <?php
    wp_nav_menu(
        [
            'theme_location' => 'header-utility',
            'container'      => false,
            'menu_class'     => 'aznet-theme-site-header__utility-menu',
            'fallback_cb'    => false,
            'depth'          => function_exists( 'AZnet\\Theme\\digital01_brand2026_page_active' )
                && \\AZnet\\Theme\\digital01_brand2026_page_active() ? 2 : 1,
        ]
    );
    ?>
</nav>
