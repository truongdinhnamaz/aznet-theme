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
?>
<section class="aznet-theme-law01-section aznet-theme-law01-faq" aria-labelledby="aznet-law01-faq-title">
    <div class="aznet-theme-law01-container">
        <div class="aznet-theme-law01-faq__intro">
            <p class="aznet-theme-law01-eyebrow"><?php esc_html_e( 'Hỏi đáp', 'aznet-theme' ); ?></p>
            <h2 id="aznet-law01-faq-title"><?php echo esc_html( get_the_title( $page ) ); ?></h2>
            <?php if ( '' !== $summary ) : ?><p class="aznet-theme-law01-lede"><?php echo esc_html( $summary ); ?></p><?php endif; ?>
        </div>
        <?php if ( [] !== $details ) : ?>
            <div class="aznet-theme-law01-faq__items">
                <?php foreach ( $details as $detail ) : ?>
                    <?php echo render_block( $detail ); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <p class="aznet-theme-law01-faq__action"><a class="aznet-theme-law01-button aznet-theme-law01-button--secondary" href="<?php echo esc_url( get_permalink( $page ) ); ?>"><?php esc_html_e( 'Xem câu hỏi thường gặp', 'aznet-theme' ); ?></a></p>
    </div>
</section>
