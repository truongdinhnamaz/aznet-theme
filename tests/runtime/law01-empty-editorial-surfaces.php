<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    fwrite( STDERR, "FAIL: WordPress runtime is not loaded.\n" );
    exit( 1 );
}

$original = get_theme_mod( 'aznet_theme_settings', [] );
$original = is_array( $original ) ? $original : [];
$created_terms = [];
$failure = null;

try {
    $analysis = wp_insert_term( 'Law 01 Empty Analysis', 'category', [ 'slug' => 'law01-empty-analysis' ] );
    if ( is_wp_error( $analysis ) ) {
        throw new RuntimeException( $analysis->get_error_message() );
    }
    $analysis_id = (int) $analysis['term_id'];
    $created_terms[] = $analysis_id;

    $news = wp_insert_term( 'Law 01 Empty News', 'category', [ 'slug' => 'law01-empty-news' ] );
    if ( is_wp_error( $news ) ) {
        throw new RuntimeException( $news->get_error_message() );
    }
    $news_id = (int) $news['term_id'];
    $created_terms[] = $news_id;

    $settings = $original;
    $settings['homepage_law01_case_analysis_term'] = $analysis_id;
    $settings['homepage_law01_legal_news_term'] = $news_id;
    set_theme_mod( 'aznet_theme_settings', $settings );

    $surfaces = \AZnet\Theme\homepage_effective_surface_map( 'law-01' );
    $keys = array_map(
        static fn ( array $surface ): string => (string) ( $surface['key'] ?? '' ),
        $surfaces
    );

    if ( in_array( 'analysis', $keys, true ) ) {
        throw new RuntimeException( 'FAIL: empty Analysis Category rendered a Homepage surface.' );
    }
    if ( in_array( 'news', $keys, true ) ) {
        throw new RuntimeException( 'FAIL: empty Legal News Category rendered a Homepage surface.' );
    }

    foreach ( [ 'latest', 'topics', 'process', 'faq', 'final-cta' ] as $required ) {
        if ( ! in_array( $required, $keys, true ) ) {
            throw new RuntimeException( 'FAIL: unrelated Homepage surface disappeared while editorial Categories were empty: ' . $required );
        }
    }
} catch ( Throwable $error ) {
    $failure = $error;
} finally {
    set_theme_mod( 'aznet_theme_settings', $original );
    foreach ( $created_terms as $term_id ) {
        wp_delete_term( (int) $term_id, 'category' );
    }
}

if ( $failure instanceof Throwable ) {
    fwrite( STDERR, $failure->getMessage() . "\n" );
    exit( 1 );
}

echo "PASS: empty Law 01 Analysis/News Categories fail soft without affecting unrelated surfaces.\n";
