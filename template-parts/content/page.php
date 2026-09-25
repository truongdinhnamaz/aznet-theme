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
$is_law01_service_detail = $is_service_detail
    && function_exists( 'AZnet\\Theme\\header_law01_active' )
    && \AZnet\Theme\header_law01_active();
$is_contact_page = \AZnet\Theme\contact_page_presentation_active( (int) get_the_ID() );
$is_services_page = \AZnet\Theme\services_page_presentation_active( (int) get_the_ID() );
$is_team_page = \AZnet\Theme\team_page_presentation_active( (int) get_the_ID() );
$service_contact_url = $is_service_detail ? \AZnet\Theme\service_page_contact_url() : '';
$service_siblings = $is_service_detail ? \AZnet\Theme\service_page_siblings( (int) get_the_ID() ) : [];
$service_positions = [];
if ( $is_service_detail ) {
    foreach ( \AZnet\Theme\services_page_children() as $service_index => $service_page ) {
        $service_positions[ (int) $service_page->ID ] = $service_index + 1;
    }
}
$page_classes = 'aznet-theme-page aznet-theme-page--full-bleed aznet-theme-page--' . $variant;
if ( $is_service_detail ) {
    $page_classes .= ' aznet-theme-page--service-detail';
}
if ( $is_law01_service_detail ) {
    $page_classes .= ' aznet-theme-page--service-detail-law01';
}
if ( $is_contact_page ) {
    $page_classes .= ' aznet-theme-page--contact';
}
if ( $is_services_page ) {
    $page_classes .= ' aznet-theme-page--services-root';
}
if ( $is_team_page ) {
    $page_classes .= ' aznet-theme-page--team-directory';
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( $page_classes ); ?>>
    <?php if ( $is_contact_page ) : ?>
        <?php
        get_template_part(
            'template-parts/contact/page',
            null,
            [
                'excerpt' => $excerpt,
            ]
        );
        ?>
    <?php elseif ( $is_services_page ) : ?>
        <?php
        get_template_part(
            'template-parts/services/page',
            null,
            [
                'excerpt' => $excerpt,
            ]
        );
        ?>
    <?php elseif ( $is_team_page ) : ?>
        <?php
        get_template_part(
            'template-parts/team/page',
            null,
            [
                'excerpt' => $excerpt,
            ]
        );
        ?>
    <?php else : ?>
    <header class="aznet-theme-page__header">
        <div class="aznet-theme-page__section-inner aznet-theme-page__section-inner--header">
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
        </div>
    </header>

    <section class="aznet-theme-page__content-section">
        <div class="aznet-theme-page__section-inner aznet-theme-page__content aznet-theme-page__content-inner aznet-theme-entry__content">
            <?php the_content(); ?>
            <?php wp_link_pages(); ?>
        </div>
    </section>

    <?php if ( $is_service_detail && [] !== $service_siblings ) : ?>
        <aside class="aznet-theme-page__service-siblings" aria-labelledby="aznet-theme-service-siblings-title">
            <div class="aznet-theme-page__section-inner aznet-theme-page__section-inner--siblings">
            <div class="aznet-theme-page__service-siblings-header">
                <p class="aznet-theme-page__service-eyebrow"><?php esc_html_e( 'Dịch vụ pháp lý', 'aznet-theme' ); ?></p>
                <h2 id="aznet-theme-service-siblings-title"><?php esc_html_e( 'Các dịch vụ khác', 'aznet-theme' ); ?></h2>
            </div>
            <div class="aznet-theme-page__service-siblings-grid aznet-theme-service-card-grid">
                <?php foreach ( $service_siblings as $service_page ) : ?>
                    <?php
                    if ( ! isset( $service_positions[ (int) $service_page->ID ] ) ) {
                        continue;
                    }
                    get_template_part(
                        'template-parts/services/card',
                        null,
                        [
                            'service_page' => $service_page,
                            'index'        => $service_positions[ (int) $service_page->ID ],
                        ]
                    );
                    ?>
                <?php endforeach; ?>
            </div>
            </div>
        </aside>
    <?php endif; ?>
    <?php endif; ?>
</article>