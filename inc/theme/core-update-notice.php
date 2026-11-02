<?php
/**
 * Core Theme update discovery: official GitHub Release metadata, notification only.
 *
 * No update package, automatic installation, license state, site identity or
 * domain data is transmitted or owned by this Theme module.
 *
 * @package AZnetTheme
 */
namespace AZnet\Theme;

function core_theme_update_notice( $update, array $theme_data, string $theme_stylesheet, array $locales ) {
    unset( $locales );

    $source_uri = 'https://github.com/truongdinhnamaz/aznet-theme';
    if ( 'aznet-theme' !== $theme_stylesheet || ( $theme_data['UpdateURI'] ?? '' ) !== $source_uri ) {
        return $update;
    }

    $installed = $theme_data['Version'] ?? '';
    if ( ! is_string( $installed ) || ! preg_match( '/^\d+\.\d+\.\d+$/D', $installed ) ) {
        return false;
    }

    $cache_key = 'aznet_theme_core_release_notice_v1';
    $metadata = get_transient( $cache_key );
    if ( false === $metadata ) {
        $response = wp_remote_get(
            'https://api.github.com/repos/truongdinhnamaz/aznet-theme/releases/latest',
            [
                'timeout'     => 3,
                'redirection' => 0,
                'headers'     => [ 'Accept' => 'application/vnd.github+json' ],
                'user-agent'  => 'AZnetTheme-UpdateCheck',
            ]
        );

        $metadata = [];
        if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
            $data = json_decode( wp_remote_retrieve_body( $response ), true );
            if ( is_array( $data )
                && false === ( $data['draft'] ?? true )
                && false === ( $data['prerelease'] ?? true )
                && is_string( $data['tag_name'] ?? null )
                && preg_match( '/^v(\d+\.\d+\.\d+)$/D', $data['tag_name'], $matches )
                && ( $data['html_url'] ?? null ) === $source_uri . '/releases/tag/' . $data['tag_name'] ) {
                $metadata = [
                    'version' => $matches[1],
                    'url'     => $data['html_url'],
                ];
            }
        }
        set_transient( $cache_key, $metadata, 6 * HOUR_IN_SECONDS );
    }

    if ( ! is_array( $metadata )
        || ! is_string( $metadata['version'] ?? null )
        || ! preg_match( '/^\d+\.\d+\.\d+$/D', $metadata['version'] )
        || version_compare( $metadata['version'], $installed, '<=' )
        || ! is_string( $metadata['url'] ?? null )
        || $metadata['url'] !== $source_uri . '/releases/tag/v' . $metadata['version'] ) {
        return false;
    }

    return [
        'theme'      => 'aznet-theme',
        'version'    => $metadata['version'],
        'url'        => $metadata['url'],
        'autoupdate' => false,
        // Deliberately omit package: install/upgrade is a separate QA/release gate.
    ];
}

add_filter( 'update_themes_github.com', __NAMESPACE__ . '\\core_theme_update_notice', 10, 4 );
