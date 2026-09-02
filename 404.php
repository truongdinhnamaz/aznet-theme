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
        <section class="aznet-theme-entry aznet-theme-entry--error" aria-labelledby="aznet-theme-404-title">
            <header class="aznet-theme-entry__header">
                <h1 id="aznet-theme-404-title" class="aznet-theme-entry__title"><?php esc_html_e( '404', 'aznet-theme' ); ?></h1>
            </header>

            <div class="aznet-theme-entry__content">
                <p><?php esc_html_e( 'Page not found.', 'aznet-theme' ); ?></p>
                <p>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'aznet-theme' ); ?></a>
                </p>
                <?php get_search_form(); ?>
            </div>
        </section>
    </div>
</main>
<?php
get_footer();
