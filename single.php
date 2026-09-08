<?php
/**
 * Native WordPress single template.
 *
 * Editorial composition is intentionally limited to native Posts. Other
 * single post types retain the generic fallback unless their owner provides
 * a more specific public template path.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$shell_classes  = \AZnet\Theme\content_shell_classes( false );
$is_native_post = is_singular( 'post' );
$main_classes   = 'aznet-theme-main' . ( $is_native_post ? ' aznet-theme-main--article' : '' );
?>
<main id="main" class="<?php echo esc_attr( $main_classes ); ?>">
    <?php if ( have_posts() ) : ?>
        <?php while ( have_posts() ) : ?>
            <?php the_post(); ?>
            <div class="<?php echo esc_attr( implode( ' ', $shell_classes ) ); ?>">
                <?php if ( $is_native_post ) : ?>
                    <?php get_template_part( 'template-parts/content/content', 'single' ); ?>
                <?php else : ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class( 'aznet-theme-entry aznet-theme-entry--post' ); ?>>
                        <header class="aznet-theme-entry__header">
                            <h1 class="aznet-theme-entry__title"><?php the_title(); ?></h1>
                        </header>

                        <div class="aznet-theme-entry__content">
                            <?php the_content(); ?>
                            <?php wp_link_pages(); ?>
                        </div>
                    </article>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else : ?>
        <p><?php esc_html_e( 'No content found.', 'aznet-theme' ); ?></p>
    <?php endif; ?>
</main>
<?php
get_footer();
