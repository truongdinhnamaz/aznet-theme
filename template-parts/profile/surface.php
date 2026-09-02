<?php
/**
 * Profile Surface presentation.
 *
 * Section presence, order, labels, anchors and public-safe data are supplied by
 * RootProfile Provider v2. This template only decides Theme-owned composition.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$model = is_array( $args['model'] ?? null ) ? $args['model'] : [];
$entity = is_array( $model['entity'] ?? null ) ? $model['entity'] : [];
$sections = is_array( $model['sections'] ?? null ) ? $model['sections'] : [];
$navigation = is_array( $model['navigation'] ?? null ) ? $model['navigation'] : [];
$organization_context = is_array( $model['organization_context'] ?? null ) ? $model['organization_context'] : [];
$organization = is_array( $organization_context['organization'] ?? null ) ? $organization_context['organization'] : [];
$role_context = is_array( $organization_context['role_context'] ?? null ) ? $organization_context['role_context'] : [];
$is_person = 'person_profile' === ( $model['resource'] ?? '' );

if ( '' === trim( (string) ( $entity['display_name'] ?? '' ) ) ) {
    return;
}

$render_rich_text = static function ( mixed $value ): void {
    if ( ! is_string( $value ) || '' === trim( $value ) ) {
        return;
    }
    echo '<div class="aznet-theme-profile-surface__rich-text">' . wp_kses_post( $value ) . '</div>';
};

$render_link_list = static function ( array $links ): void {
    $valid = [];
    foreach ( $links as $link ) {
        if ( is_string( $link ) && '' !== trim( $link ) ) {
            $valid[] = [ 'label' => $link, 'url' => $link ];
        } elseif ( is_array( $link ) ) {
            $url = trim( (string) ( $link['url'] ?? $link['profile_url'] ?? $link['source_url'] ?? '' ) );
            if ( '' === $url ) {
                continue;
            }
            $valid[] = [
                'label' => trim( (string) ( $link['label'] ?? $link['platform'] ?? $link['title'] ?? $url ) ),
                'url' => $url,
            ];
        }
    }
    if ( [] === $valid ) {
        return;
    }
    echo '<ul class="aznet-theme-profile-surface__links">';
    foreach ( $valid as $link ) {
        echo '<li><a href="' . esc_url( $link['url'] ) . '">' . esc_html( $link['label'] ) . '</a></li>';
    }
    echo '</ul>';
};

$render_cards = static function ( array $items ): void {
    $cards = array_values( array_filter( $items, 'is_array' ) );
    if ( [] === $cards ) {
        return;
    }
    echo '<div class="aznet-theme-profile-surface__cards">';
    foreach ( $cards as $item ) {
        $title = trim( (string) ( $item['title'] ?? $item['name'] ?? $item['label'] ?? $item['statement'] ?? '' ) );
        $url = trim( (string) ( $item['url'] ?? $item['profile_url'] ?? $item['source_url'] ?? $item['website'] ?? '' ) );
        $image = trim( (string) ( $item['image_url'] ?? $item['photo_url'] ?? $item['logo_url'] ?? '' ) );
        $description = trim( (string) ( $item['description'] ?? $item['excerpt'] ?? $item['bio'] ?? $item['claim_statement'] ?? $item['caption'] ?? $item['role'] ?? '' ) );
        echo '<article class="aznet-theme-profile-surface__card">';
        if ( '' !== $image ) {
            echo '<img class="aznet-theme-profile-surface__card-image" src="' . esc_url( $image ) . '" alt="" loading="lazy">';
        }
        if ( '' !== $title ) {
            echo '<h3 class="aznet-theme-profile-surface__card-title">';
            if ( '' !== $url ) {
                echo '<a href="' . esc_url( $url ) . '">' . esc_html( $title ) . '</a>';
            } else {
                echo esc_html( $title );
            }
            echo '</h3>';
        }
        if ( '' !== $description ) {
            echo '<div class="aznet-theme-profile-surface__card-copy">' . wp_kses_post( $description ) . '</div>';
        }
        foreach ( [ 'date', 'year', 'issuer', 'scope', 'limitations', 'quality_note' ] as $meta_field ) {
            $meta_value = trim( (string) ( $item[ $meta_field ] ?? '' ) );
            if ( '' !== $meta_value ) {
                echo '<p class="aznet-theme-profile-surface__card-meta">' . esc_html( $meta_value ) . '</p>';
            }
        }
        if ( array_key_exists( 'value', $item ) && ( is_scalar( $item['value'] ) || null === $item['value'] ) ) {
            $value = trim( (string) $item['value'] );
            $unit = trim( (string) ( $item['unit'] ?? '' ) );
            if ( '' !== $value ) {
                echo '<p class="aznet-theme-profile-surface__card-meta">' . esc_html( trim( $value . ' ' . $unit ) ) . '</p>';
            }
        }
        if ( isset( $item['expertise'] ) && is_array( $item['expertise'] ) && [] !== $item['expertise'] ) {
            echo '<ul class="aznet-theme-profile-surface__tags" aria-label="' . esc_attr__( 'Chuyên môn', 'aznet-theme' ) . '">';
            foreach ( $item['expertise'] as $tag ) {
                if ( is_string( $tag ) && '' !== trim( $tag ) ) {
                    echo '<li>' . esc_html( $tag ) . '</li>';
                }
            }
            echo '</ul>';
        }
        echo '</article>';
    }
    echo '</div>';
};

$render_contact = static function ( array $contact ) use ( $render_link_list ): void {
    $website = trim( (string) ( $contact['website'] ?? '' ) );
    $address = is_array( $contact['address'] ?? null ) ? $contact['address'] : [];
    $formatted = trim( (string) ( $address['formatted'] ?? '' ) );
    $service_area = trim( (string) ( $contact['service_area'] ?? '' ) );
    $points = is_array( $contact['points'] ?? null ) ? $contact['points'] : [];
    $hours = is_array( $contact['opening_hours'] ?? null ) ? $contact['opening_hours'] : [];

    if ( '' !== $website || '' !== $formatted || '' !== $service_area ) {
        echo '<dl class="aznet-theme-profile-surface__facts">';
        if ( '' !== $website ) {
            echo '<div><dt>' . esc_html__( 'Website', 'aznet-theme' ) . '</dt><dd><a href="' . esc_url( $website ) . '">' . esc_html( $website ) . '</a></dd></div>';
        }
        if ( '' !== $formatted ) {
            echo '<div><dt>' . esc_html__( 'Địa chỉ', 'aznet-theme' ) . '</dt><dd>' . esc_html( $formatted ) . '</dd></div>';
        }
        if ( '' !== $service_area ) {
            echo '<div><dt>' . esc_html__( 'Khu vực phục vụ', 'aznet-theme' ) . '</dt><dd>' . esc_html( $service_area ) . '</dd></div>';
        }
        echo '</dl>';
    }

    if ( [] !== $points ) {
        $links = [];
        foreach ( $points as $point ) {
            if ( ! is_array( $point ) ) {
                continue;
            }
            $value = trim( (string) ( $point['value'] ?? '' ) );
            $href = trim( (string) ( $point['href'] ?? '' ) );
            if ( '' !== $value && '' !== $href ) {
                $links[] = [ 'label' => $value, 'url' => $href ];
            }
        }
        $render_link_list( $links );
    }

    if ( [] !== $hours ) {
        echo '<dl class="aznet-theme-profile-surface__hours">';
        foreach ( $hours as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }
            $day = trim( (string) ( $row['day'] ?? '' ) );
            $time = trim( (string) ( $row['time'] ?? '' ) );
            if ( '' !== $day && '' !== $time ) {
                echo '<div><dt>' . esc_html( $day ) . '</dt><dd>' . esc_html( $time ) . '</dd></div>';
            }
        }
        echo '</dl>';
    }
};

$render_section_data = static function ( array $data ) use ( $entity, $render_rich_text, $render_link_list, $render_cards, $render_contact ): void {
    if ( 'profile' === ( $data['body'] ?? null ) ) {
        $body = trim( (string) ( $entity['full_bio'] ?? $entity['full_description'] ?? $entity['summary'] ?? '' ) );
        $render_rich_text( $body );
        $scope_note = trim( (string) ( $entity['scope_note'] ?? '' ) );
        if ( '' !== $scope_note ) {
            echo '<p class="aznet-theme-profile-surface__note">' . esc_html( $scope_note ) . '</p>';
        }
    } elseif ( isset( $data['body'] ) && is_string( $data['body'] ) ) {
        $render_rich_text( $data['body'] );
    }

    foreach ( [ 'intro', 'summary', 'full_description', 'legacy_text', 'vision', 'mission', 'narrative' ] as $field ) {
        if ( isset( $data[ $field ] ) ) {
            $render_rich_text( $data[ $field ] );
        }
    }

    if ( isset( $data['expertise'] ) && is_array( $data['expertise'] ) && [] !== $data['expertise'] ) {
        echo '<ul class="aznet-theme-profile-surface__tags">';
        foreach ( $data['expertise'] as $tag ) {
            if ( is_string( $tag ) && '' !== trim( $tag ) ) {
                echo '<li>' . esc_html( $tag ) . '</li>';
            }
        }
        echo '</ul>';
    }

    if ( isset( $data['claims'] ) && is_array( $data['claims'] ) ) {
        $render_cards( $data['claims'] );
    }
    if ( isset( $data['evidence'] ) && is_array( $data['evidence'] ) ) {
        $render_cards( $data['evidence'] );
    }
    if ( isset( $data['items'] ) && is_array( $data['items'] ) ) {
        $render_cards( $data['items'] );
    }
    foreach ( [ 'steps', 'values', 'events', 'principles' ] as $collection ) {
        if ( isset( $data[ $collection ] ) && is_array( $data[ $collection ] ) ) {
            $render_cards( $data[ $collection ] );
        }
    }
    if ( isset( $data['links'] ) && is_array( $data['links'] ) ) {
        $render_link_list( $data['links'] );
    }
    if ( isset( $data['social_links'] ) && is_array( $data['social_links'] ) ) {
        $render_link_list( $data['social_links'] );
    }
    if ( isset( $data['contact'] ) && is_array( $data['contact'] ) ) {
        $render_contact( $data['contact'] );
    }
    if ( isset( $data['responsibility'] ) && is_array( $data['responsibility'] ) ) {
        $responsibility = $data['responsibility'];
        $updated = trim( (string) ( $responsibility['updated_at'] ?? '' ) );
        if ( '' !== $updated ) {
            echo '<p class="aznet-theme-profile-surface__note">' . esc_html__( 'Cập nhật:', 'aznet-theme' ) . ' ' . esc_html( $updated ) . '</p>';
        }
        if ( isset( $responsibility['people'] ) && is_array( $responsibility['people'] ) ) {
            $render_cards( $responsibility['people'] );
        }
        if ( isset( $responsibility['policy_urls'] ) && is_array( $responsibility['policy_urls'] ) ) {
            $render_link_list( $responsibility['policy_urls'] );
        }
    }
    if ( isset( $data['updated_at'] ) && is_string( $data['updated_at'] ) && '' !== trim( $data['updated_at'] ) ) {
        echo '<p class="aznet-theme-profile-surface__note">' . esc_html__( 'Cập nhật:', 'aznet-theme' ) . ' ' . esc_html( $data['updated_at'] ) . '</p>';
    }
};
?>
<section class="aznet-theme-profile-surface aznet-theme-profile-surface--<?php echo esc_attr( $is_person ? 'person' : 'organization' ); ?>">
    <header class="aznet-theme-profile-surface__hero">
        <div class="aznet-theme-profile-surface__identity">
            <?php $image = trim( (string) ( $entity[ $is_person ? 'photo_url' : 'logo_url' ] ?? '' ) ); ?>
            <?php if ( '' !== $image ) : ?>
                <img class="aznet-theme-profile-surface__portrait" src="<?php echo esc_url( $image ); ?>" alt="" loading="eager">
            <?php endif; ?>
            <div class="aznet-theme-profile-surface__identity-copy">
                <p class="aznet-theme-profile-surface__eyebrow"><?php echo esc_html( $is_person ? __( 'Hồ sơ cá nhân', 'aznet-theme' ) : __( 'Hồ sơ doanh nghiệp', 'aznet-theme' ) ); ?></p>
                <h1 class="aznet-theme-profile-surface__title"><?php echo esc_html( (string) $entity['display_name'] ); ?></h1>
                <?php if ( $is_person && '' !== trim( (string) ( $entity['job_title'] ?? '' ) ) ) : ?>
                    <p class="aznet-theme-profile-surface__subtitle"><?php echo esc_html( (string) $entity['job_title'] ); ?></p>
                <?php elseif ( ! $is_person && '' !== trim( (string) ( $entity['legal_name'] ?? '' ) ) ) : ?>
                    <p class="aznet-theme-profile-surface__subtitle"><?php echo esc_html( (string) $entity['legal_name'] ); ?></p>
                <?php endif; ?>

                <?php if ( $is_person && '' !== trim( (string) ( $organization['display_name'] ?? '' ) ) ) : ?>
                    <p class="aznet-theme-profile-surface__context">
                        <?php if ( '' !== trim( (string) ( $organization['profile_url'] ?? '' ) ) ) : ?>
                            <a href="<?php echo esc_url( (string) $organization['profile_url'] ); ?>"><?php echo esc_html( (string) $organization['display_name'] ); ?></a>
                        <?php else : ?>
                            <?php echo esc_html( (string) $organization['display_name'] ); ?>
                        <?php endif; ?>
                        <?php if ( '' !== trim( (string) ( $role_context['role'] ?? '' ) ) ) : ?>
                            <span aria-hidden="true"> · </span><?php echo esc_html( (string) $role_context['role'] ); ?>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>

                <?php if ( '' !== trim( (string) ( $entity['summary'] ?? '' ) ) ) : ?>
                    <div class="aznet-theme-profile-surface__summary"><?php echo wp_kses_post( (string) $entity['summary'] ); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <?php if ( [] !== $navigation ) : ?>
        <nav class="aznet-theme-profile-surface__nav" aria-label="<?php echo esc_attr__( 'Mục lục hồ sơ', 'aznet-theme' ); ?>">
            <ul>
                <?php foreach ( $navigation as $item ) : ?>
                    <?php
                    if ( ! is_array( $item ) ) { continue; }
                    $label = trim( (string) ( $item['label'] ?? '' ) );
                    $anchor = trim( (string) ( $item['anchor'] ?? '' ) );
                    if ( '' === $label || '' === $anchor ) { continue; }
                    ?>
                    <li><a href="#<?php echo esc_attr( $anchor ); ?>"><?php echo esc_html( $label ); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
    <?php endif; ?>

    <div class="aznet-theme-profile-surface__sections">
        <?php foreach ( $sections as $section ) : ?>
            <?php
            if ( ! is_array( $section ) ) { continue; }
            $key = trim( (string) ( $section['key'] ?? '' ) );
            $label = trim( (string) ( $section['label'] ?? '' ) );
            $anchor = trim( (string) ( $section['anchor'] ?? '' ) );
            $type = trim( (string) ( $section['section_type'] ?? $key ) );
            $data = is_array( $section['data'] ?? null ) ? $section['data'] : [];
            if ( '' === $key || '' === $label || '' === $anchor ) { continue; }
            ?>
            <section id="<?php echo esc_attr( $anchor ); ?>" class="aznet-theme-profile-surface__section aznet-theme-profile-surface__section--<?php echo esc_attr( sanitize_html_class( $type, 'generic' ) ); ?>">
                <h2 class="aznet-theme-profile-surface__section-title"><?php echo esc_html( $label ); ?></h2>
                <div class="aznet-theme-profile-surface__section-body">
                    <?php $render_section_data( $data ); ?>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
</section>
