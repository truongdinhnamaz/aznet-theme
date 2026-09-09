<?php
declare(strict_types=1);

namespace {
    if ( ! defined( 'ABSPATH' ) ) { define( 'ABSPATH', __DIR__ . '/' ); }
    if ( ! class_exists( 'WP_Post' ) ) {
        class WP_Post {
            public int $ID;
            public string $post_title;
            public string $post_status;
            public string $post_type = 'page';
            public function __construct( int $id, string $title, string $status = 'publish', string $type = 'page' ) {
                $this->ID = $id; $this->post_title = $title; $this->post_status = $status; $this->post_type = $type;
            }
        }
    }
    if ( ! class_exists( 'WP_Term' ) ) {
        class WP_Term {
            public int $term_id;
            public string $name;
            public string $taxonomy = 'category';
            public function __construct( int $id, string $name, string $taxonomy = 'category' ) {
                $this->term_id = $id; $this->name = $name; $this->taxonomy = $taxonomy;
            }
        }
    }
    $GLOBALS['aznet_rec_posts'] = [
        10 => new WP_Post( 10, 'Giới thiệu' ),
        11 => new WP_Post( 11, 'Dịch vụ pháp lý' ),
        12 => new WP_Post( 12, 'Liên hệ' ),
    ];
    $GLOBALS['aznet_rec_terms'] = [
        18 => new WP_Term( 18, 'Dân sự' ),
        19 => new WP_Term( 19, 'Hình sự' ),
    ];
    function get_post( $id ) { return $GLOBALS['aznet_rec_posts'][ (int) $id ] ?? null; }
    function get_term( $id, $taxonomy = '' ) { return $GLOBALS['aznet_rec_terms'][ (int) $id ] ?? null; }
    function is_wp_error( $value ) { return false; }
    function get_posts( $args = [] ) { return []; }
    function get_terms( $args = [] ) { return []; }
}

namespace AZnet\Theme {
    if ( ! function_exists( __NAMESPACE__ . '\\settings' ) ) {
        function settings(): array { return []; }
    }
}

namespace {
    require_once dirname( __DIR__, 2 ) . '/inc/theme/provisioning-blueprints.php';
    if ( is_file( dirname( __DIR__, 2 ) . '/inc/theme/provisioning-recommendations.php' ) ) {
        require_once dirname( __DIR__, 2 ) . '/inc/theme/provisioning-recommendations.php';
    }

    assert( function_exists( 'AZnet\\Theme\\provisioning_site_mode' ), 'provisioning_site_mode must exist' );
    assert( function_exists( 'AZnet\\Theme\\provisioning_recommendations' ), 'provisioning_recommendations must exist' );
    assert( function_exists( 'AZnet\\Theme\\provisioning_normalize_label' ), 'provisioning_normalize_label must exist' );

    assert( 'dân sự' === \AZnet\Theme\provisioning_normalize_label( "  DÂN   SỰ  " ) );

    $new = [
        'prior_blueprints' => [], 'show_on_front' => 'posts', 'front_page_id' => 0,
        'primary_menu_id' => 0, 'published_post_count' => 0, 'page_count' => 2,
    ];
    assert( 'NEW_OR_MOSTLY_EMPTY' === \AZnet\Theme\provisioning_site_mode( $new ) );

    $active = $new;
    $active['published_post_count'] = 1;
    assert( 'EXISTING_ACTIVE' === \AZnet\Theme\provisioning_site_mode( $active ) );

    $previous = $active;
    $previous['prior_blueprints'] = [ 'law01-v1' ];
    assert( 'PREVIOUSLY_PROVISIONED' === \AZnet\Theme\provisioning_site_mode( $previous ) );

    $discovery = $active + [
        'homepage_slots' => [
            'about' => 10,
            'services' => 0,
            'contact' => 0,
            'team' => 0,
            'knowledge' => [],
            'case_analysis' => 0,
            'legal_news' => 0,
            'process' => 0,
            'faq' => 0,
        ],
        'pages' => [
            [ 'id' => 10, 'title' => 'Giới thiệu', 'status' => 'publish' ],
            [ 'id' => 11, 'title' => 'Dịch vụ pháp lý', 'status' => 'publish' ],
            [ 'id' => 12, 'title' => 'Liên hệ', 'status' => 'publish' ],
        ],
        'categories' => [
            [ 'id' => 18, 'name' => 'Dân sự' ],
            [ 'id' => 19, 'name' => 'Hình sự' ],
        ],
    ];
    $recommendations = \AZnet\Theme\provisioning_recommendations( 'law01-v1-1', $discovery );
    assert( 'CURRENT' === $recommendations['pages']['about']['state'] );
    assert( 10 === $recommendations['pages']['about']['object_id'] );
    assert( 'SUGGESTED_REUSE' === $recommendations['pages']['services']['state'] );
    assert( 11 === $recommendations['pages']['services']['object_id'] );
    assert( 'SUGGESTED_REUSE' === $recommendations['categories']['knowledge_civil']['state'] );
    assert( 18 === $recommendations['categories']['knowledge_civil']['object_id'] );
    assert( 'SUGGESTED_CREATE' === $recommendations['categories']['legal_news']['state'] );

    $src = is_file( dirname( __DIR__, 2 ) . '/inc/theme/provisioning-recommendations.php' )
        ? file_get_contents( dirname( __DIR__, 2 ) . '/inc/theme/provisioning-recommendations.php' )
        : '';
    foreach ( [ 'sanitize_title(', 'get_page_by_path(', 'url_to_postid(', "get_term_by( 'slug'", 'similar_text(', 'levenshtein(' ] as $bad ) {
        assert( ! str_contains( (string) $src, $bad ), 'Recommendations must not use forbidden semantic heuristic: ' . $bad );
    }

    echo "PASS: Provisioning recommendations are proposal-only and exact-label bounded\n";
}
