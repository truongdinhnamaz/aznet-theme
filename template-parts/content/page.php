<?php
/**
 * Shared native WordPress Page presentation.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$variant = \AZnet\Theme\page_variant( (int) get_the_ID() );
$crumbs  = \AZnet\Theme\page_breadcrumb_items( (int) get_the_ID() );
$excerpt = \AZnet\Theme\page_excerpt( (int) get_the_ID() );
$is_service_detail = \AZnet\Theme\service_page_is_detail( (int) get_the_ID() );
$service_contact_url = $is_service_detail ? \AZnet\Theme\service_page_contact_url() : '';
$service_siblings = $is_service_detail ? \AZnet\Theme\service_page_siblings( (int) get_the_ID() ) : [];
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'aznet-theme-page aznet-theme-page--' . $variant . ( $is_service_detail ? ' aznet-theme-page--service-detail' : '' ) ); ?>>
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

        <?php if ( $is_service_detail ) : ?>
            <div class="aznet-theme-page__service-actions">
                <?php if ( '' !== $service_contact_url ) : ?>
                    <a class="aznet-theme-page__service-primary" href="<?php echo esc_url( $service_contact_url ); ?>"><?php esc_html_e( 'Trao đổi nhu cầu', 'aznet-theme' ); ?></a>
                <?php endif; ?>
                <?php if ( [] !== $crumbs ) : ?>
                    <?php $services_crumb = end( $crumbs ); ?>
                    <?php if ( is_array( $services_crumb ) && ! empty( $services_crumb['url'] ) ) : ?>
                        <a class="aznet-theme-page__service-secondary" href="<?php echo esc_url( (string) $services_crumb['url'] ); ?>"><?php esc_html_e( 'Xem tất cả dịch vụ', 'aznet-theme' ); ?></a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ( has_post_thumbnail() ) : ?>
            <figure class="aznet-theme-page__featured">
                <?php the_post_thumbnail( 'large' ); ?>
            </figure>
        <?php endif; ?>
    </header>

    <div class="aznet-theme-page__content aznet-theme-entry__content">
        <?php the_content(); ?>
        <?php wp_link_pages(); ?>
    </div>

    <?php if ( $is_service_detail && [] !== $service_siblings ) : ?>
        <aside class="aznet-theme-page__service-siblings" aria-labelledby="aznet-theme-service-siblings-title">
            <div class="aznet-theme-page__service-siblings-header">
                <p class="aznet-theme-page__service-eyebrow"><?php esc_html_e( 'Dịch vụ pháp lý', 'aznet-theme' ); ?></p>
                <h2 id="aznet-theme-service-siblings-title"><?php esc_html_e( 'Các dịch vụ khác', 'aznet-theme' ); ?></h2>
            </div>
            <div class="aznet-theme-page__service-siblings-grid">
                <?php foreach ( $service_siblings as $service_page ) : ?>
                    <?php $service_url = get_permalink( $service_page ); ?>
                    <?php if ( ! is_string( $service_url ) || '' === $service_url ) { continue; } ?>
                    <article class="aznet-theme-page__service-card">
                        <h3><a href="<?php echo esc_url( $service_url ); ?>"><?php echo esc_html( get_the_title( $service_page ) ); ?></a></h3>
                        <?php $service_excerpt = trim( (string) $service_page->post_excerpt ); ?>
                        <?php if ( '' !== $service_excerpt ) : ?>
                            <p><?php echo esc_html( $service_excerpt ); ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </aside>
    <?php endif; ?>
</article>