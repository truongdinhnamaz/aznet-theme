<?php
/**
 * Native WordPress 404 template.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$shell_classes = \AZnet\Theme\content_shell_classes( false );
?>
<main id="main" class="aznet-theme-main">
    <div class="<?php echo esc_attr( implode( ' ', $shell_classes ) ); ?>">
        <section class="aznet-theme-recovery aznet-theme-recovery--404" aria-labelledby="aznet-theme-404-title">
            <p class="aznet-theme-recovery__code" aria-hidden="true">404</p>
            <h1 id="aznet-theme-404-title" class="aznet-theme-recovery__title">
                <?php esc_html_e( 'Page not found', 'aznet-theme' ); ?>
            </h1>
            <p class="aznet-theme-recovery__description">
                <?php esc_html_e( 'The address may have changed, or the page may no longer be available. You can search the site or return to the homepage.', 'aznet-theme' ); ?>
            </p>
            <div class="aznet-theme-recovery__search">
                <?php get_search_form(); ?>
            </div>
            <div class="aznet-theme-recovery__actions">
                <a class="aznet-theme-recovery__action" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <?php esc_html_e( 'Back to home', 'aznet-theme' ); ?>
                </a>
            </div>
        </section>
    </div>
</main>
<?php
get_footer();
