<?php
/**
 * Native WordPress Page template.
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
    <?php if ( have_posts() ) : ?>
        <?php while ( have_posts() ) : ?>
            <?php the_post(); ?>
            <div class="<?php echo esc_attr( implode( ' ', $shell_classes ) ); ?>">
                <article id="post-<?php the_ID(); ?>" <?php post_class( 'aznet-theme-entry aznet-theme-entry--page' ); ?>>
                    <header class="aznet-theme-entry__header">
                        <h1 class="aznet-theme-entry__title"><?php echo esc_html( get_the_title() ); ?></h1>
                    </header>
                    <div class="aznet-theme-entry__content">
                        <?php the_content(); ?>
                        <?php wp_link_pages(); ?>
                    </div>
                </article>
            </div>
        <?php endwhile; ?>
    <?php else : ?>
        <p><?php esc_html_e( 'No content found.', 'aznet-theme' ); ?></p>
    <?php endif; ?>
</main>
<?php
get_footer();
