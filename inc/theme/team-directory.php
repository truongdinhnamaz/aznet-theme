<?php
namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) { exit; }

function team_directory_parent(): ?\WP_Post {
    $id = (int) homepage_effective_source_value( 'law-01', 'team' );
    return $id > 0 ? homepage_page_reference( $id ) : null;
}

/** @return array<int,\WP_Post> */
function team_directory_members( int $limit = 0 ): array {
    $parent = team_directory_parent();
    if ( ! $parent instanceof \WP_Post ) { return []; }

    $posts = get_posts(
        [
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'post_parent'    => (int) $parent->ID,
            'orderby'        => 'menu_order title',
            'order'          => 'ASC',
            'posts_per_page' => $limit > 0 ? min( 24, $limit ) : -1,
            'no_found_rows'  => true,
        ]
    );

    return is_array( $posts )
        ? array_values( array_filter( $posts, static fn( $post ): bool => $post instanceof \WP_Post ) )
        : [];
}

/** @return array<int,\WP_Post> */
function team_directory_managed_members(): array {
    $parent = team_directory_parent();
    if ( ! $parent instanceof \WP_Post ) { return []; }

    $posts = get_posts(
        [
            'post_type'      => 'page',
            'post_status'    => [ 'publish', 'draft', 'private', 'pending', 'future' ],
            'post_parent'    => (int) $parent->ID,
            'orderby'        => 'menu_order title',
            'order'          => 'ASC',
            'posts_per_page' => -1,
            'no_found_rows'  => true,
        ]
    );

    return is_array( $posts )
        ? array_values( array_filter( $posts, static fn( $post ): bool => $post instanceof \WP_Post ) )
        : [];
}

/** @return array<int,\WP_Post> */
function team_directory_public_members( int $limit = 0 ): array {
    $members = team_directory_members();
    $public = [];

    foreach ( $members as $member ) {
        if ( '' === trim( (string) get_the_title( $member ) ) ) {
            continue;
        }

        $url = get_permalink( $member );
        if ( ! is_string( $url ) || '' === trim( $url ) ) {
            continue;
        }

        $public[] = $member;
        if ( $limit > 0 && count( $public ) >= min( 24, $limit ) ) {
            break;
        }
    }

    return $public;
}

function team_directory_member_is_child( int $post_id ): bool {
    $parent = team_directory_parent();
    if ( ! $parent instanceof \WP_Post || $post_id <= 0 ) { return false; }

    $post = get_post( $post_id );

    return $post instanceof \WP_Post
        && 'page' === $post->post_type
        && (int) $post->post_parent === (int) $parent->ID;
}


/** Read one Team member phone number from the scoped WordPress Page meta. */
function team_member_phone( int $post_id ): string {
    if ( $post_id <= 0 || ! team_directory_member_is_child( $post_id ) ) {
        return '';
    }

    $value = get_post_meta( $post_id, '_aznet_theme_team_phone', true );
    return is_string( $value ) ? trim( $value ) : '';
}

/** Read one Team member Zalo number from the scoped WordPress Page meta. */
function team_member_zalo( int $post_id ): string {
    if ( $post_id <= 0 || ! team_directory_member_is_child( $post_id ) ) {
        return '';
    }

    $value = get_post_meta( $post_id, '_aznet_theme_team_zalo', true );
    return is_string( $value ) ? trim( $value ) : '';
}

/** Normalize a phone-like value to digits with an optional leading plus sign. */
function team_member_normalize_phone( string $value ): string {
    $value = trim( $value );
    if ( '' === $value ) {
        return '';
    }

    $has_plus = str_starts_with( $value, '+' );
    $digits = preg_replace( '/\D+/', '', $value );
    if ( ! is_string( $digits ) || '' === $digits ) {
        return '';
    }

    return $has_plus ? '+' . $digits : $digits;
}

/** Format common Vietnamese local numbers for compact frontend display. */
function team_member_phone_display( string $value ): string {
    $normalized = team_member_normalize_phone( $value );
    if ( 1 === preg_match( '/^0\d{9}$/', $normalized ) ) {
        return substr( $normalized, 0, 4 ) . '.' . substr( $normalized, 4, 3 ) . '.' . substr( $normalized, 7, 3 );
    }

    return $normalized;
}

/** Build a safe tel: href from one phone-like value. */
function team_member_phone_href( string $value ): string {
    $normalized = team_member_normalize_phone( $value );
    return '' !== $normalized ? 'tel:' . $normalized : '';
}

/** Build a Zalo profile URL from a locally entered phone number. */
function team_member_zalo_url( string $value ): string {
    $normalized = ltrim( team_member_normalize_phone( $value ), '+' );
    if ( '' === $normalized ) {
        return '';
    }

    if ( 1 === preg_match( '/^0\d{9}$/', $normalized ) ) {
        $normalized = '84' . substr( $normalized, 1 );
    }

    return 'https://zalo.me/' . $normalized;
}

/** Persist the two explicitly approved Team contact facts on the WordPress Page. */
function save_team_member_contact( int $post_id, string $phone, string $zalo ): void {
    if ( $post_id <= 0 || ! team_directory_member_is_child( $post_id ) ) {
        return;
    }

    $phone = team_member_normalize_phone( $phone );
    $zalo = team_member_normalize_phone( $zalo );

    if ( '' !== $phone ) {
        update_post_meta( $post_id, '_aznet_theme_team_phone', $phone );
    } else {
        delete_post_meta( $post_id, '_aznet_theme_team_phone' );
    }

    if ( '' !== $zalo ) {
        update_post_meta( $post_id, '_aznet_theme_team_zalo', $zalo );
    } else {
        delete_post_meta( $post_id, '_aznet_theme_team_zalo' );
    }
}

function team_directory_next_menu_order(): int {
    $parent = team_directory_parent();
    if ( ! $parent instanceof \WP_Post ) { return 0; }

    $children = get_posts(
        [
            'post_type'      => 'page',
            'post_status'    => 'any',
            'post_parent'    => (int) $parent->ID,
            'orderby'        => 'menu_order',
            'order'          => 'DESC',
            'posts_per_page' => 1,
            'no_found_rows'  => true,
        ]
    );

    $last = is_array( $children ) && isset( $children[0] ) && $children[0] instanceof \WP_Post
        ? (int) $children[0]->menu_order
        : -1;

    return $last + 1;
}
