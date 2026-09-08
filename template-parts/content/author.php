<?php
/**
 * WordPress-native author presentation fallback.
 *
 * This does not reconstruct RootProfile identity semantics.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$author_id          = (int) get_the_author_meta( 'ID' );
$author_description = trim( (string) get_the_author_meta( 'description' ) );
$author_url         = get_author_posts_url( $author_id );
?>
<aside class="aznet-theme-article-author" aria-label="<?php esc_attr_e( 'About the author', 'aznet-theme' ); ?>">
    <div class="aznet-theme-article-author__media" aria-hidden="true">
        <?php echo wp_kses_post( get_avatar( $author_id, 72, '', '', [ 'class' => 'aznet-theme-article-author__avatar' ] ) ); ?>
    </div>
    <div class="aznet-theme-article-author__body">
        <h2 class="aznet-theme-article-author__title">
            <a href="<?php echo esc_url( $author_url ); ?>"><?php echo esc_html( get_the_author() ); ?></a>
        </h2>
        <?php if ( '' !== $author_description ) : ?>
            <p class="aznet-theme-article-author__description"><?php echo esc_html( $author_description ); ?></p>
        <?php endif; ?>
    </div>
</aside>
