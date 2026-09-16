<?php
/**
 * Native WordPress comments template.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( post_password_required() ) {
    return;
}
?>
<section id="comments" class="aznet-theme-comments">
    <?php if ( have_comments() ) : ?>
        <h2 id="aznet-theme-comments-title" class="aznet-theme-comments__title">
            <?php
            printf(
                esc_html( _n( '%s comment', '%s comments', get_comments_number(), 'aznet-theme' ) ),
                esc_html( number_format_i18n( get_comments_number() ) )
            );
            ?>
        </h2>

        <ol class="comment-list aznet-theme-comments__list">
            <?php
            wp_list_comments(
                [
                    'style'       => 'ol',
                    'short_ping'  => true,
                    'avatar_size' => 56,
                ]
            );
            ?>
        </ol>

        <?php
        the_comments_pagination(
            [
                'prev_text' => esc_html__( 'Previous comments', 'aznet-theme' ),
                'next_text' => esc_html__( 'Next comments', 'aznet-theme' ),
            ]
        );
        ?>
    <?php endif; ?>

    <?php if ( ! comments_open() && get_comments_number() ) : ?>
        <p class="aznet-theme-comments__closed"><?php esc_html_e( 'Comments are closed.', 'aznet-theme' ); ?></p>
    <?php endif; ?>

    <?php if ( comments_open() ) : ?>
        <?php
        comment_form(
            [
                'class_container' => 'comment-respond aznet-theme-comments__respond',
                'class_submit'    => 'submit aznet-theme-comments__submit',
            ]
        );
        ?>
    <?php endif; ?>
</section>
