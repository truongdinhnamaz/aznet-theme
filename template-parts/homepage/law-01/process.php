<?php
/** Law 01 Process Page preview; renders authored Core List structure when available. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }

$page = homepage_page_reference( (int) homepage_source_value( 'law-01', 'process' ) );
if ( ! $page instanceof \WP_Post ) { return; }

$summary = trim( (string) get_the_excerpt( $page ) );
$source_block = null;
$queue = parse_blocks( (string) $page->post_content );

while ( [] !== $queue ) {
    $block = array_shift( $queue );
    if ( ! is_array( $block ) ) { continue; }
    if ( 'core/list' === ( $block['blockName'] ?? null ) ) {
        $source_block = $block;
        break;
    }
    foreach ( (array) ( $block['innerBlocks'] ?? [] ) as $inner_block ) {
        $queue[] = $inner_block;
    }
}
?>
<section class="aznet-theme-law01-section aznet-theme-law01-process" aria-labelledby="aznet-law01-process-title">
    <div class="aznet-theme-law01-container aznet-theme-law01-panel">
        <div class="aznet-theme-law01-process__intro">
            <p class="aznet-theme-law01-eyebrow"><?php esc_html_e( 'Quy trình', 'aznet-theme' ); ?></p>
            <h2 id="aznet-law01-process-title"><?php echo esc_html( get_the_title( $page ) ); ?></h2>
            <?php if ( '' !== $summary ) : ?><p><?php echo esc_html( $summary ); ?></p><?php endif; ?>
            <p><a class="aznet-theme-law01-text-link" href="<?php echo esc_url( get_permalink( $page ) ); ?>"><?php esc_html_e( 'Xem quy trình tư vấn', 'aznet-theme' ); ?></a></p>
        </div>
        <?php if ( is_array( $source_block ) ) : ?>
            <div class="aznet-theme-law01-process__source">
                <?php echo render_block( $source_block ); ?>
            </div>
        <?php endif; ?>
    </div>
</section>
