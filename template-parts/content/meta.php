<?php
/**
 * Native WordPress publication metadata for a single Post.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$author_id      = (int) get_the_author_meta( 'ID' );
$author_url     = get_author_posts_url( $author_id );
$published_unix = (int) get_the_time( 'U' );
$modified_unix  = (int) get_the_modified_time( 'U' );
$show_modified  = $modified_unix > $published_unix;
?>
<div class="aznet-theme-article__meta">
    <span class="aznet-theme-article__meta-item aznet-theme-article__author-link">
        <span class="screen-reader-text"><?php esc_html_e( 'Author:', 'aznet-theme' ); ?></span>
        <a href="<?php echo esc_url( $author_url ); ?>"><?php echo esc_html( get_the_author() ); ?></a>
    </span>

    <span class="aznet-theme-article__meta-item">
        <span class="screen-reader-text"><?php esc_html_e( 'Published:', 'aznet-theme' ); ?></span>
        <time datetime="<?php echo esc_attr( get_the_time( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
    </span>

    <?php if ( $show_modified ) : ?>
        <span class="aznet-theme-article__meta-item aznet-theme-article__modified">
            <span><?php esc_html_e( 'Updated:', 'aznet-theme' ); ?></span>
            <time datetime="<?php echo esc_attr( get_the_modified_time( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_modified_date() ); ?></time>
        </span>
    <?php endif; ?>
</div>
