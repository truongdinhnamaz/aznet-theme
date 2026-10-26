<?php
/** Law 01 FAQ Page preview; renders authored Core Details blocks when available. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }

$page = homepage_page_reference( (int) homepage_source_value( 'law-01', 'faq' ) );
if ( ! $page instanceof \WP_Post ) {
    $page = homepage_page_reference( (int) setting( 'homepage_faq_page', 0 ) );
}
if ( ! $page instanceof \WP_Post ) { return; }

$summary = trim( (string) get_the_excerpt( $page ) );
$details = [];
$queue = parse_blocks( (string) $page->post_content );

while ( [] !== $queue && count( $details ) < 5 ) {
    $block = array_shift( $queue );
    if ( ! is_array( $block ) ) { continue; }
    if ( 'core/details' === ( $block['blockName'] ?? null ) ) {
        $details[] = $block;
        continue;
    }
    foreach ( (array) ( $block['innerBlocks'] ?? [] ) as $inner_block ) {
        $queue[] = $inner_block;
    }
}

// A Classic Editor Page may author native <details> HTML rather than Core blocks.
// Preview only the already mapped WordPress Page; never project its unrelated
// Page Hero/H1 or mutate/duplicate the source content in Theme storage.
$classic_details = [];
$content = (string) $page->post_content;
if ( [] === $details && str_contains( $content, '<details' )
    && strlen( $content ) <= 262144 && class_exists( \DOMDocument::class ) ) {
    $document = new \DOMDocument();
    $prior_libxml_errors = libxml_use_internal_errors( true );
    try {
        $loaded = $document->loadHTML(
            '<?xml encoding="utf-8" ?><html><body>' . $content . '</body></html>',
            LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING
        );
        if ( $loaded ) {
            foreach ( $document->getElementsByTagName( 'details' ) as $element ) {
                if ( count( $classic_details ) >= 5 ) {
                    break;
                }
                if ( ! $element instanceof \DOMElement
                    || 0 === $element->getElementsByTagName( 'summary' )->length ) {
                    continue;
                }
                // Nested details belong to the outer item, not another card.
                $parent = $element->parentNode;
                $nested = false;
                while ( $parent instanceof \DOMElement ) {
                    if ( 'details' === strtolower( $parent->tagName ) ) {
                        $nested = true;
                        break;
                    }
                    $parent = $parent->parentNode;
                }
                if ( $nested ) {
                    continue;
                }
                $existing_classes = preg_split( '/\s+/', trim( $element->getAttribute( 'class' ) ) ) ?: [];
                if ( ! in_array( 'wp-block-details', $existing_classes, true ) ) {
                    $existing_classes[] = 'wp-block-details';
                }
                $element->setAttribute( 'class', trim( implode( ' ', $existing_classes ) ) );
                $raw_markup = $document->saveHTML( $element );
                if ( ! is_string( $raw_markup ) || '' === $raw_markup ) {
                    continue;
                }
                $safe_markup = wp_kses_post( $raw_markup );
                if ( str_contains( $safe_markup, '<details' ) && str_contains( $safe_markup, '<summary' ) ) {
                    $classic_details[] = $safe_markup;
                }
            }
        }
    } finally {
        libxml_clear_errors();
        libxml_use_internal_errors( $prior_libxml_errors );
    }
}
?>
<section id="aznet-homepage-faq" data-aznet-homepage-surface="faq" class="aznet-theme-law01-section aznet-theme-law01-faq" aria-labelledby="aznet-law01-faq-title">
    <div class="aznet-theme-law01-container">
        <div class="aznet-theme-law01-faq__intro">
            <p class="aznet-theme-law01-eyebrow"><?php esc_html_e( 'Hỏi đáp', 'aznet-theme' ); ?></p>
            <h2 id="aznet-law01-faq-title"><?php echo esc_html( get_the_title( $page ) ); ?></h2>
            <?php if ( '' !== $summary ) : ?><p class="aznet-theme-law01-lede"><?php echo esc_html( $summary ); ?></p><?php endif; ?>
        </div>
        <?php if ( [] !== $details || [] !== $classic_details ) : ?>
            <div class="aznet-theme-law01-faq__items">
                <?php foreach ( $details as $detail ) : ?>
                    <?php echo render_block( $detail ); ?>
                <?php endforeach; ?>
                <?php foreach ( $classic_details as $classic_detail ) : ?>
                    <?php echo wp_kses_post( $classic_detail ); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <p class="aznet-theme-law01-faq__action"><a class="aznet-theme-law01-button aznet-theme-law01-button--secondary" href="<?php echo esc_url( get_permalink( $page ) ); ?>"><?php esc_html_e( 'Xem câu hỏi thường gặp', 'aznet-theme' ); ?></a></p>
    </div>
</section>
