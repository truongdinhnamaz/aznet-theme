<?php
/**
 * Shared service-card presentation.
 *
 * WordPress owns the service Page data. The Theme only renders the public
 * projection supplied by the existing mapped Services relationship.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$service_page = $args['service_page'] ?? null;
$index        = (int) ( $args['index'] ?? 0 );

if ( ! $service_page instanceof \WP_Post ) {
    return;
}

$service_url = get_permalink( $service_page );
if ( ! is_string( $service_url ) || '' === $service_url ) {
    return;
}

$service_excerpt = trim( (string) $service_page->post_excerpt );
$service_index   = str_pad( (string) max( 1, $index ), 2, '0', STR_PAD_LEFT );
?>
<article class="aznet-theme-service-card">
    <div class="aznet-theme-service-card__top">
        <span class="aznet-theme-service-card__index" aria-hidden="true"><?php echo esc_html( $service_index ); ?></span>
        <span class="aznet-theme-service-card__icon" aria-hidden="true"></span>
    </div>

    <h3 class="aznet-theme-service-card__title">
        <a href="<?php echo esc_url( $service_url ); ?>"><?php echo esc_html( get_the_title( $service_page ) ); ?></a>
    </h3>

    <?php if ( '' !== $service_excerpt ) : ?>
        <p class="aznet-theme-service-card__excerpt"><?php echo esc_html( $service_excerpt ); ?></p>
    <?php endif; ?>

    <a class="aznet-theme-service-card__link" href="<?php echo esc_url( $service_url ); ?>">
        <?php esc_html_e( 'Xem dịch vụ', 'aznet-theme' ); ?> <span aria-hidden="true">→</span>
    </a>
</article>
