<?php
/**
 * Industrial 01 Solutions presentation derived from the mapped WordPress Page.
 *
 * WordPress owns the authored title, copy, media and anchor. The Theme only
 * projects a bounded view of native Gutenberg blocks, with a legacy fallback.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$page = homepage_page_reference( (int) homepage_effective_source_value( 'industrial-01', 'solutions' ) );
if ( ! $page instanceof \WP_Post ) {
    return;
}

$raw = trim( (string) $page->post_content );
if ( '' === $raw ) {
    return;
}

$solution_cards  = [];
$intro_heading   = '';
$intro_copy      = '';
$intro_eyebrow   = '';
$media_id        = 0;
$has_situation_anchor = false;

$block_text = static function ( array $block ): string {
    $text = html_entity_decode(
        wp_strip_all_tags( (string) ( $block['innerHTML'] ?? '' ) ),
        ENT_QUOTES | ENT_HTML5,
        'UTF-8'
    );
    return trim( (string) preg_replace( '/\s+/u', ' ', $text ) );
};

// Native WordPress block structure is a public presentation source, not a
// second domain store. Select the authored "tinh-huong" group explicitly.
$visit_blocks = static function (
    array $blocks,
    bool $within_hero = false,
    bool $within_hero_media = false,
    bool $within_situations = false
) use (
    &$visit_blocks,
    &$solution_cards,
    &$intro_heading,
    &$intro_copy,
    &$intro_eyebrow,
    &$media_id,
    &$has_situation_anchor,
    $block_text
): void {
    foreach ( $blocks as $block ) {
        if ( ! is_array( $block ) ) {
            continue;
        }

        $attributes = is_array( $block['attrs'] ?? null ) ? $block['attrs'] : [];
        $classes = preg_split( '/\s+/', trim( (string) ( $attributes['className'] ?? '' ) ), -1, PREG_SPLIT_NO_EMPTY ) ?: [];
        $name = (string) ( $block['blockName'] ?? '' );
        $is_hero = $within_hero || in_array( 'aznet-theme-industrial01-solution-hero', $classes, true );
        $is_hero_media = $within_hero_media || in_array( 'aznet-theme-industrial01-solution-hero__media', $classes, true );
        $is_situation = $within_situations || 'tinh-huong' === (string) ( $attributes['anchor'] ?? '' );

        if ( 'tinh-huong' === (string) ( $attributes['anchor'] ?? '' ) ) {
            $has_situation_anchor = true;
        }

        if ( $is_hero && '' === $intro_heading && 'core/heading' === $name && 2 === (int) ( $attributes['level'] ?? 2 ) ) {
            $intro_heading = $block_text( $block );
        }
        if ( $is_hero && '' === $intro_copy && 'core/paragraph' === $name && in_array( 'aznet-theme-industrial01-solution-lead', $classes, true ) ) {
            $intro_copy = $block_text( $block );
        }
        if ( $is_hero && '' === $intro_eyebrow && 'core/paragraph' === $name && in_array( 'aznet-theme-industrial01-solution-eyebrow', $classes, true ) ) {
            $intro_eyebrow = $block_text( $block );
        }
        if ( $is_hero_media && 0 === $media_id && 'core/image' === $name ) {
            $media_id = max( 0, (int) ( $attributes['id'] ?? 0 ) );
        }

        if ( $is_situation && count( $solution_cards ) < 4 && in_array( 'aznet-theme-industrial01-solution-card', $classes, true ) ) {
            $title = '';
            $description = '';
            foreach ( (array) ( $block['innerBlocks'] ?? [] ) as $child ) {
                if ( ! is_array( $child ) ) {
                    continue;
                }
                $child_name = (string) ( $child['blockName'] ?? '' );
                $child_attributes = is_array( $child['attrs'] ?? null ) ? $child['attrs'] : [];
                $child_classes = preg_split( '/\s+/', trim( (string) ( $child_attributes['className'] ?? '' ) ), -1, PREG_SPLIT_NO_EMPTY ) ?: [];
                if ( 'core/heading' === $child_name && 3 === (int) ( $child_attributes['level'] ?? 2 ) ) {
                    $title = $block_text( $child );
                }
                if ( 'core/paragraph' === $child_name && '' === $description && ! in_array( 'aznet-theme-industrial01-solution-card__index', $child_classes, true ) ) {
                    $description = $block_text( $child );
                }
            }
            if ( '' !== $title ) {
                $solution_cards[] = [ 'title' => $title, 'description' => $description ];
            }
        }

        if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
            $visit_blocks( $block['innerBlocks'], $is_hero, $is_hero_media, $is_situation );
        }
    }
};

if ( function_exists( 'parse_blocks' ) ) {
    $visit_blocks( parse_blocks( $raw ) );
}

// Existing unstructured WordPress content remains supported, without
// inventing descriptions, imagery, anchor identity or product relationships.
if ( [] === $solution_cards ) {
    $rendered = trim( (string) apply_filters( 'the_content', $raw ) );
    if ( '' !== $rendered && preg_match_all( '/<h3[^>]*>(.*?)<\/h3>/is', $rendered, $matches ) ) {
        foreach ( array_slice( (array) ( $matches[1] ?? [] ), 0, 4 ) as $heading ) {
            $title = trim( wp_strip_all_tags( (string) $heading ) );
            if ( '' !== $title ) {
                $solution_cards[] = [ 'title' => $title, 'description' => '' ];
            }
        }
    }
}

if ( [] === $solution_cards ) {
    return;
}

$image_html = '';
if ( $media_id > 0 && function_exists( 'wp_get_attachment_image' ) ) {
    $image_html = (string) wp_get_attachment_image(
        $media_id,
        'large',
        false,
        [
            'class'    => 'aznet-theme-industrial01-solutions__image',
            'loading'  => 'lazy',
            'decoding' => 'async',
            'sizes'    => '(max-width: 48rem) 100vw, (max-width: 64rem) 90vw, 460px',
        ]
    );
}
$page_url = get_permalink( $page );
$situation_url = $has_situation_anchor ? $page_url . '#tinh-huong' : $page_url;
?>
<section id="aznet-homepage-industrial-solutions" data-aznet-homepage-surface="solutions" class="aznet-theme-industrial01-section aznet-theme-industrial01-solutions<?php echo '' === $image_html ? ' aznet-theme-industrial01-solutions--no-media' : ''; ?>" aria-labelledby="aznet-industrial01-solutions-title">
    <div class="aznet-theme-industrial01-shell">
        <div class="aznet-theme-industrial01-solutions__intro">
            <div class="aznet-theme-industrial01-solutions__intro-heading">
                <p class="aznet-theme-industrial01-kicker"><?php esc_html_e( 'Theo nhu cầu sản xuất', 'aznet-theme' ); ?></p>
                <h2 id="aznet-industrial01-solutions-title"><?php echo esc_html( get_the_title( $page ) ); ?></h2>
            </div>
            <div class="aznet-theme-industrial01-solutions__intro-aside">
                <?php if ( '' !== $intro_copy ) : ?>
                    <p class="aznet-theme-industrial01-solutions__lead"><?php echo esc_html( $intro_copy ); ?></p>
                <?php endif; ?>
                <a class="aznet-theme-industrial01-solutions__all" href="<?php echo esc_url( $page_url ); ?>">
                    <?php esc_html_e( 'Khám phá tất cả giải pháp', 'aznet-theme' ); ?>
                    <span aria-hidden="true">↗</span>
                </a>
            </div>
        </div>
        <div class="aznet-theme-industrial01-solutions__layout">
            <?php if ( '' !== $image_html ) : ?>
                <div class="aznet-theme-industrial01-solutions__visual">
                    <div class="aznet-theme-industrial01-solutions__visual-media">
                        <?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Native WordPress attachment HTML. ?>
                    </div>
                    <?php if ( '' !== $intro_eyebrow || '' !== $intro_heading ) : ?>
                        <div class="aznet-theme-industrial01-solutions__visual-caption">
                            <?php if ( '' !== $intro_eyebrow ) : ?>
                                <span class="aznet-theme-industrial01-solutions__visual-kicker"><?php echo esc_html( $intro_eyebrow ); ?></span>
                            <?php endif; ?>
                            <?php if ( '' !== $intro_heading ) : ?>
                                <p class="aznet-theme-industrial01-solutions__visual-heading"><?php echo esc_html( $intro_heading ); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="aznet-theme-industrial01-solutions__grid">
                <?php foreach ( $solution_cards as $index => $solution ) : ?>
                    <a class="aznet-theme-industrial01-solutions__card" href="<?php echo esc_url( $situation_url ); ?>">
                        <span class="aznet-theme-industrial01-solutions__card-top" aria-hidden="true">
                            <span class="aznet-theme-industrial01-solutions__card-number"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
                            <span class="aznet-theme-industrial01-solutions__card-arrow">↗</span>
                        </span>
                        <strong class="aznet-theme-industrial01-solutions__card-title"><?php echo esc_html( $solution['title'] ); ?></strong>
                        <?php if ( '' !== $solution['description'] ) : ?>
                            <p class="aznet-theme-industrial01-solutions__card-description"><?php echo esc_html( $solution['description'] ); ?></p>
                        <?php endif; ?>
                        <span class="aznet-theme-industrial01-solutions__card-action" aria-hidden="true"><?php esc_html_e( 'Tìm hiểu giải pháp', 'aznet-theme' ); ?> <span>→</span></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
