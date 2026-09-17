<?php
/**
 * Shared native Page presentation.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$page_id       = (int) get_the_ID();
$variant       = \AZnet\Theme\page_variant( $page_id );
$crumbs        = \AZnet\Theme\page_breadcrumb_items( $page_id );
$excerpt       = trim( (string) get_post_field( 'post_excerpt', $page_id ) );
$shell_classes = \AZnet\Theme\content_shell_classes( false );
?>
<div class="<?php echo esc_attr( implode( ' ', $shell_classes ) ); ?>">
    <article <?php post_class( 'aznet-theme-page aznet-theme-page--' . $variant ); ?>>
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
                <figure class="aznet-theme-page__featured"><?php the_post_thumbnail( 'large' ); ?></figure>
            <?php endif; ?>
        </header>
        <div class="aznet-theme-page__content">
            <?php the_content(); ?>
            <?php wp_link_pages(); ?>
        </div>
    </article>
</div>
