<?php
namespace AZnet\Theme\Admin;

use function AZnet\Theme\settings;
use function AZnet\Theme\settings_defaults;
use function AZnet\Theme\Integrations\WooCommerce\available as woo_available;

if ( ! defined( 'ABSPATH' ) ) { exit; }

function control_center_section(): string {
    $allowed = [ 'overview', 'design', 'header', 'commerce', 'system-health' ];
    $value = isset( $_GET['section'] ) ? sanitize_key( wp_unslash( $_GET['section'] ) ) : 'overview';
    if ( 'commerce' === $value && ! woo_available() ) { return 'overview'; }
    return in_array( $value, $allowed, true ) ? $value : 'overview';
}

function field_select( string $name, string $label, array $choices, string $current ): void {
    echo '<label class="aznet-theme-field"><span>' . esc_html( $label ) . '</span><select name="aznet_theme_settings[' . esc_attr( $name ) . ']">';
    foreach ( $choices as $value => $text ) {
        echo '<option value="' . esc_attr( $value ) . '" ' . selected( $current, $value, false ) . '>' . esc_html( $text ) . '</option>';
    }
    echo '</select></label>';
}

function render_settings_form( string $section ): void {
    $s = settings();
    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="aznet-theme-panel">';
    echo '<input type="hidden" name="action" value="aznet_theme_save_settings">';
    wp_nonce_field( 'aznet_theme_save_settings' );
    foreach ( settings_defaults() as $key => $default ) {
        if ( 'schema_version' === $key ) { continue; }
        echo '<input type="hidden" name="aznet_theme_settings[' . esc_attr( $key ) . ']" value="' . esc_attr( is_bool( $s[$key] ?? $default ) ? ( ( $s[$key] ?? $default ) ? '1' : '0' ) : (string) ( $s[$key] ?? $default ) ) . '">';
    }
    if ( 'design' === $section ) {
        field_select( 'visual_preset', __( 'Phong cách', 'aznet-theme' ), [ 'default' => 'Default', 'editorial' => 'Editorial', 'commerce' => 'Commerce' ], (string) $s['visual_preset'] );
    } elseif ( 'header' === $section ) {
        field_select( 'header_preset', __( 'Kiểu Header', 'aznet-theme' ), [ 'standard' => 'Standard', 'compact' => 'Compact', 'commerce' => 'Commerce', 'overlay' => 'Overlay' ], (string) $s['header_preset'] );
        field_select( 'header_sticky', __( 'Sticky', 'aznet-theme' ), [ 'off' => 'Off', 'sticky' => 'Sticky', 'sticky-compact' => 'Sticky Compact' ], (string) $s['header_sticky'] );
    } elseif ( 'commerce' === $section ) {
        field_select( 'woo_catalog_preset', __( 'Catalog', 'aznet-theme' ), [ 'grid' => 'Grid', 'compact-grid' => 'Compact Grid', 'editorial' => 'Editorial' ], (string) $s['woo_catalog_preset'] );
        field_select( 'woo_product_card_density', __( 'Mật độ thẻ sản phẩm', 'aznet-theme' ), [ 'comfortable' => 'Comfortable', 'balanced' => 'Balanced', 'compact' => 'Compact' ], (string) $s['woo_product_card_density'] );
        field_select( 'woo_product_preset', __( 'Trang sản phẩm', 'aznet-theme' ), [ 'classic' => 'Classic', 'focus' => 'Focus', 'story' => 'Story' ], (string) $s['woo_product_preset'] );
    }
    submit_button( __( 'Lưu thiết lập', 'aznet-theme' ) );
    echo '</form>';
}

function render_control_center(): void {
    if ( ! current_user_can( 'edit_theme_options' ) ) { return; }
    $section = control_center_section();
    $tabs = [ 'overview' => 'Tổng quan', 'design' => 'Thiết kế', 'header' => 'Header', 'system-health' => 'System Health' ];
    if ( woo_available() ) {
        $tabs = [ 'overview' => 'Tổng quan', 'design' => 'Thiết kế', 'header' => 'Header', 'commerce' => 'Commerce', 'system-health' => 'System Health' ];
    }
    echo '<div class="wrap aznet-theme-control-center"><h1>AZnet Theme</h1><nav class="nav-tab-wrapper">';
    foreach ( $tabs as $slug => $label ) {
        $url = add_query_arg( [ 'page' => 'aznet-theme', 'section' => $slug ], admin_url( 'admin.php' ) );
        echo '<a class="nav-tab ' . ( $section === $slug ? 'nav-tab-active' : '' ) . '" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
    }
    echo '</nav>';

    if ( 'overview' === $section ) {
        echo '<div class="aznet-theme-grid">';
        $cards = [
            [ 'Phong cách', 'Chọn visual preset toàn Theme.', add_query_arg( [ 'page' => 'aznet-theme', 'section' => 'design' ], admin_url( 'admin.php' ) ) ],
            [ 'Logo', 'Dùng Custom Logo native của WordPress.', admin_url( 'customize.php?autofocus[control]=custom_logo' ) ],
            [ 'Menu', 'Quản lý menu bằng WordPress.', admin_url( 'nav-menus.php' ) ],
            [ 'Header', 'Chọn preset và sticky mode.', add_query_arg( [ 'page' => 'aznet-theme', 'section' => 'header' ], admin_url( 'admin.php' ) ) ],
            [ 'Patterns', 'Mở Page editor rồi Block Inserter → Patterns.', admin_url( 'post-new.php?post_type=page' ) ],
        ];
        foreach ( $cards as $card ) {
            echo '<a class="aznet-theme-card" href="' . esc_url( $card[2] ) . '"><strong>' . esc_html( $card[0] ) . '</strong><span>' . esc_html( $card[1] ) . '</span></a>';
        }
        echo '</div>';
        echo '<div class="aznet-theme-panel"><h2>Portable settings</h2>';
        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="aznet_theme_export_settings">';
        wp_nonce_field( 'aznet_theme_export_settings' ); submit_button( 'Xuất JSON', 'secondary', 'submit', false ); echo '</form>';
        echo '<form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="aznet_theme_import_settings">';
        wp_nonce_field( 'aznet_theme_import_settings' ); echo '<input type="file" name="aznet_theme_settings_file" accept="application/json,.json" required> '; submit_button( 'Nhập JSON', 'secondary', 'submit', false ); echo '</form></div>';
        echo '<div class="aznet-theme-panel aznet-theme-danger"><h2>Đặt lại thiết lập Theme</h2><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="aznet_theme_reset_settings"><input type="hidden" name="confirm_reset" value="1">';
        wp_nonce_field( 'aznet_theme_reset_settings' ); submit_button( 'Đặt lại', 'delete', 'submit', false ); echo '</form></div>';
    } elseif ( 'system-health' === $section ) {
        $report = system_health_report();
        echo '<div class="aznet-theme-panel"><h2>System Health</h2><table class="widefat striped"><tbody>';
        foreach ( $report as $group => $items ) {
            foreach ( $items as $key => $value ) {
                echo '<tr><th>' . esc_html( $group . ' / ' . $key ) . '</th><td><code>' . esc_html( is_bool( $value ) ? ( $value ? 'yes' : 'no' ) : (string) $value ) . '</code></td></tr>';
            }
        }
        echo '</tbody></table></div>';
    } else {
        render_settings_form( $section );
    }
    echo '</div>';
}
