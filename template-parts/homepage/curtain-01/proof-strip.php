<?php
/**
 * Curtain 01 quick-proof strip.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$proof_block = homepage_block_reference( (int) homepage_source_value( 'curtain-01', 'proof' ) );
if ( ! $proof_block instanceof \WP_Post ) {
    return;
}

$raw = trim( (string) $proof_block->post_content );
if ( '' === $raw ) {
    return;
}

$proof_html = do_blocks( $raw );
if ( '' === trim( $proof_html ) ) {
    return;
}
?>
<section class="aznet-theme-curtain01-proof-strip" aria-label="<?php echo esc_attr__( 'Bằng chứng nhanh', 'aznet-theme' ); ?>">
    <div class="aznet-theme-curtain01-shell aznet-theme-curtain01-proof-strip__inner">
        <?php echo wp_kses_post( $proof_html ); ?>
    </div>
</section>
