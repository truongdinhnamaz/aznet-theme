<?php
/**
 * Contact Surface presentation.
 *
 * Expects a presentation-only model produced by contact_surface_model().
 * Authoritative Organization/contact values remain owned by RootProfile.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$model = is_array( $args['model'] ?? null ) ? $args['model'] : null;
if ( null === $model ) {
    return;
}

$entity = is_array( $model['entity'] ?? null ) ? $model['entity'] : [];
$contact = is_array( $model['contact'] ?? null ) ? $model['contact'] : [];
$points = is_array( $contact['points'] ?? null ) ? $contact['points'] : [];
$opening_hours = is_array( $contact['opening_hours'] ?? null ) ? $contact['opening_hours'] : [];
$social_links = is_array( $model['social_links'] ?? null ) ? $model['social_links'] : [];
$policies = is_array( $model['policies'] ?? null ) ? $model['policies'] : [];
$responsible_people = is_array( $model['responsible_people'] ?? null ) ? $model['responsible_people'] : [];
$address = is_array( $contact['address'] ?? null ) ? $contact['address'] : [];

$display_name = trim( (string) ( $entity['display_name'] ?? '' ) );
$legal_name = trim( (string) ( $entity['legal_name'] ?? '' ) );
$summary = trim( (string) ( $entity['summary'] ?? '' ) );
$logo_url = trim( (string) ( $entity['logo_url'] ?? '' ) );
$profile_url = trim( (string) ( $entity['profile_url'] ?? '' ) );
$website = trim( (string) ( $contact['website'] ?? '' ) );
$service_area = trim( (string) ( $contact['service_area'] ?? '' ) );

$formatted_address = trim( (string) ( $address['formatted'] ?? '' ) );
if ( '' === $formatted_address ) {
    $address_parts = [];
    foreach ( [ 'street', 'locality', 'region', 'postal_code', 'country' ] as $address_key ) {
        $part = trim( (string) ( $address[ $address_key ] ?? '' ) );
        if ( '' !== $part ) {
            $address_parts[] = $part;
        }
    }
    $formatted_address = implode( ', ', $address_parts );
}

$kind_labels = [
    'phone' => __( 'Điện thoại', 'aznet-theme' ),
    'email' => __( 'Email', 'aznet-theme' ),
    'url' => __( 'Liên kết', 'aznet-theme' ),
    'zalo' => __( 'Zalo', 'aznet-theme' ),
    'whatsapp' => __( 'WhatsApp', 'aznet-theme' ),
];
$purpose_labels = [
    'general' => '',
    'sales' => __( 'Kinh doanh', 'aznet-theme' ),
    'support' => __( 'Hỗ trợ', 'aznet-theme' ),
    'warranty' => __( 'Bảo hành', 'aznet-theme' ),
    'complaints' => __( 'Khiếu nại', 'aznet-theme' ),
];
$social_labels = [
    'facebook' => 'Facebook',
    'youtube' => 'YouTube',
    'linkedin' => 'LinkedIn',
    'tiktok' => 'TikTok',
    'instagram' => 'Instagram',
    'x' => 'X',
    'other' => __( 'Kênh chính thức', 'aznet-theme' ),
];
$policy_labels = [
    'privacy' => __( 'Chính sách bảo mật', 'aznet-theme' ),
    'terms' => __( 'Điều khoản sử dụng', 'aznet-theme' ),
    'warranty' => __( 'Chính sách bảo hành', 'aznet-theme' ),
    'shipping' => __( 'Chính sách giao hàng', 'aznet-theme' ),
    'returns' => __( 'Chính sách đổi trả', 'aznet-theme' ),
    'payment' => __( 'Chính sách thanh toán', 'aznet-theme' ),
];
?>
<section class="aznet-theme-contact-surface">
    <header class="aznet-theme-contact-surface__hero">
        <div class="aznet-theme-contact-surface__identity">
            <?php if ( '' !== $logo_url ) : ?>
                <img class="aznet-theme-contact-surface__logo" src="<?php echo esc_url( $logo_url ); ?>" alt="" loading="lazy">
            <?php endif; ?>
            <div>
                <p class="aznet-theme-contact-surface__eyebrow"><?php echo esc_html__( 'Thông tin liên hệ chính thức', 'aznet-theme' ); ?></p>
                <h2 class="aznet-theme-contact-surface__title"><?php echo esc_html( $display_name ); ?></h2>
                <?php if ( '' !== $legal_name && $legal_name !== $display_name ) : ?>
                    <p class="aznet-theme-contact-surface__legal-name"><?php echo esc_html( $legal_name ); ?></p>
                <?php endif; ?>
                <?php if ( '' !== $summary ) : ?>
                    <p class="aznet-theme-contact-surface__summary"><?php echo esc_html( $summary ); ?></p>
                <?php endif; ?>
                <?php if ( '' !== $profile_url ) : ?>
                    <a class="aznet-theme-contact-surface__profile-link" href="<?php echo esc_url( $profile_url ); ?>">
                        <?php echo esc_html__( 'Xem hồ sơ doanh nghiệp', 'aznet-theme' ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div class="aznet-theme-contact-surface__grid">
        <?php if ( [] !== $points || '' !== $website ) : ?>
            <section class="aznet-theme-contact-surface__card">
                <h3><?php echo esc_html__( 'Liên hệ trực tiếp', 'aznet-theme' ); ?></h3>
                <ul class="aznet-theme-contact-surface__list">
                    <?php foreach ( $points as $point ) : ?>
                        <?php
                        if ( ! is_array( $point ) ) {
                            continue;
                        }
                        $kind = strtolower( trim( (string) ( $point['kind'] ?? '' ) ) );
                        $purpose = strtolower( trim( (string) ( $point['purpose'] ?? 'general' ) ) );
                        $value = trim( (string) ( $point['value'] ?? '' ) );
                        if ( '' === $value ) {
                            continue;
                        }
                        $kind_label = $kind_labels[ $kind ] ?? __( 'Liên hệ', 'aznet-theme' );
                        $purpose_label = $purpose_labels[ $purpose ] ?? '';
                        $href = '';
                        if ( 'phone' === $kind ) {
                            $dial = preg_replace( '/[^0-9+]/', '', $value );
                            $href = '' !== $dial ? 'tel:' . $dial : '';
                        } elseif ( 'email' === $kind ) {
                            $href = 'mailto:' . $value;
                        } elseif ( in_array( $kind, [ 'url', 'zalo', 'whatsapp' ], true ) ) {
                            $href = $value;
                        }
                        ?>
                        <?php
                        $escaped_href = in_array( $kind, [ 'url', 'zalo', 'whatsapp' ], true )
                            ? esc_url( $href )
                            : esc_attr( $href );
                        ?>
                        <li class="aznet-theme-contact-surface__item">
                            <span class="aznet-theme-contact-surface__label">
                                <?php echo esc_html( $kind_label ); ?><?php if ( '' !== $purpose_label ) : ?> · <?php echo esc_html( $purpose_label ); ?><?php endif; ?>
                            </span>
                            <?php if ( '' !== $escaped_href ) : ?>
                                <a href="<?php echo $escaped_href; ?>"><?php echo esc_html( $value ); ?></a>
                            <?php else : ?>
                                <span><?php echo esc_html( $value ); ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                    <?php if ( '' !== $website ) : ?>
                        <li class="aznet-theme-contact-surface__item">
                            <span class="aznet-theme-contact-surface__label"><?php echo esc_html__( 'Website', 'aznet-theme' ); ?></span>
                            <a href="<?php echo esc_url( $website ); ?>"><?php echo esc_html( $website ); ?></a>
                        </li>
                    <?php endif; ?>
                </ul>
            </section>
        <?php endif; ?>

        <?php if ( '' !== $formatted_address || '' !== $service_area ) : ?>
            <section class="aznet-theme-contact-surface__card">
                <h3><?php echo esc_html__( 'Địa chỉ & phạm vi phục vụ', 'aznet-theme' ); ?></h3>
                <?php if ( '' !== $formatted_address ) : ?>
                    <p class="aznet-theme-contact-surface__detail">
                        <span class="aznet-theme-contact-surface__label"><?php echo esc_html__( 'Địa chỉ', 'aznet-theme' ); ?></span>
                        <?php echo esc_html( $formatted_address ); ?>
                    </p>
                <?php endif; ?>
                <?php if ( '' !== $service_area ) : ?>
                    <p class="aznet-theme-contact-surface__detail">
                        <span class="aznet-theme-contact-surface__label"><?php echo esc_html__( 'Phạm vi phục vụ', 'aznet-theme' ); ?></span>
                        <?php echo esc_html( $service_area ); ?>
                    </p>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if ( [] !== $opening_hours ) : ?>
            <section class="aznet-theme-contact-surface__card">
                <h3><?php echo esc_html__( 'Thời gian làm việc', 'aznet-theme' ); ?></h3>
                <dl class="aznet-theme-contact-surface__hours">
                    <?php foreach ( $opening_hours as $hours ) : ?>
                        <?php if ( ! is_array( $hours ) ) { continue; } ?>
                        <div>
                            <dt><?php echo esc_html( (string) ( $hours['day'] ?? '' ) ); ?></dt>
                            <dd><?php echo esc_html( (string) ( $hours['time'] ?? '' ) ); ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            </section>
        <?php endif; ?>

        <?php if ( [] !== $social_links ) : ?>
            <section class="aznet-theme-contact-surface__card">
                <h3><?php echo esc_html__( 'Kênh chính thức', 'aznet-theme' ); ?></h3>
                <ul class="aznet-theme-contact-surface__link-list">
                    <?php foreach ( $social_links as $social ) : ?>
                        <?php
                        if ( ! is_array( $social ) ) { continue; }
                        $platform = strtolower( trim( (string) ( $social['platform'] ?? 'other' ) ) );
                        $url = trim( (string) ( $social['url'] ?? '' ) );
                        if ( '' === $url ) { continue; }
                        ?>
                        <li><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $social_labels[ $platform ] ?? $social_labels['other'] ); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endif; ?>

        <?php if ( [] !== $responsible_people ) : ?>
            <section class="aznet-theme-contact-surface__card aznet-theme-contact-surface__card--wide">
                <h3><?php echo esc_html__( 'Người phụ trách', 'aznet-theme' ); ?></h3>
                <div class="aznet-theme-contact-surface__people">
                    <?php foreach ( $responsible_people as $person ) : ?>
                        <?php if ( ! is_array( $person ) ) { continue; } ?>
                        <article class="aznet-theme-contact-surface__person">
                            <h4><?php echo esc_html( (string) ( $person['name'] ?? '' ) ); ?></h4>
                            <?php if ( '' !== trim( (string) ( $person['role'] ?? '' ) ) ) : ?>
                                <p><?php echo esc_html( (string) $person['role'] ); ?></p>
                            <?php endif; ?>
                            <?php if ( '' !== trim( (string) ( $person['scope'] ?? '' ) ) ) : ?>
                                <p><?php echo esc_html( (string) $person['scope'] ); ?></p>
                            <?php endif; ?>
                            <?php if ( '' !== trim( (string) ( $person['profile_url'] ?? '' ) ) ) : ?>
                                <a href="<?php echo esc_url( (string) $person['profile_url'] ); ?>"><?php echo esc_html__( 'Xem hồ sơ', 'aznet-theme' ); ?></a>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if ( [] !== $policies ) : ?>
            <nav class="aznet-theme-contact-surface__card aznet-theme-contact-surface__card--wide" aria-label="<?php echo esc_attr( __( 'Thông tin chính sách', 'aznet-theme' ) ); ?>">
                <h3><?php echo esc_html__( 'Thông tin chính sách', 'aznet-theme' ); ?></h3>
                <ul class="aznet-theme-contact-surface__link-list aznet-theme-contact-surface__link-list--inline">
                    <?php foreach ( $policies as $policy ) : ?>
                        <?php
                        if ( ! is_array( $policy ) ) { continue; }
                        $type = strtolower( trim( (string) ( $policy['type'] ?? '' ) ) );
                        $url = trim( (string) ( $policy['url'] ?? '' ) );
                        if ( '' === $url ) { continue; }
                        $fallback_label = ucwords( str_replace( [ '_', '-' ], ' ', $type ) );
                        ?>
                        <li><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $policy_labels[ $type ] ?? $fallback_label ); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</section>
