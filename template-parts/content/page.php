<?php
/**
 * Shared native WordPress Page presentation.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$variant = \AZnet\Theme\page_variant( (int) get_the_ID() );
$crumbs  = \AZnet\Theme\page_breadcrumb_items( (int) get_the_ID() );
$excerpt = trim( (string) get_the_excerpt() );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'aznet-theme-page aznet-theme-page--' . $variant ); ?>>
    <header class="aznet-theme-page__header">
        <?php if ( [] !== $crumbs ) : ?>
            <nav class="aznet-theme-page__breadcrumbs" aria-label="<?php echo esc_attr__( 'Breadcrumb', 'aznet-theme' ); ?>">
                <ol>
                    <?php foreach ( $crumbs as $item ) : ?>
                        <li><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a></li>
                    <?php endforeach; ?>
                    <li aria-current="page"><?php the_title(); ?></li>
                </ol>
            </nav>
        <?php endif; ?>

        <h1 class="aznet-theme-page__title"><?php the_title(); ?></h1>

        <?php if ( '' !== $excerpt ) : ?>
            <p class="aznet-theme-page__lead"><?php echo esc_html( $excerpt ); ?></p>
        <?php endif; ?>

        <?php if ( has_post_thumbnail() ) : ?>
            <figure class="aznet-theme-page__featured">
                <?php the_post_thumbnail( 'large' ); ?>
            </figure>
        <?php endif; ?>
    </header>

    <div class="aznet-theme-page__content aznet-theme-entry__content">
        <?php the_content(); ?>
        <?php wp_link_pages(); ?>
    </div>
</article>
