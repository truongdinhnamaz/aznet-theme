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

function field_checkbox( string $name, string $label, bool $current ): void {
    echo '<label class="aznet-theme-field"><span>' . esc_html( $label ) . '</span><span>';
    echo '<input type="hidden" name="aznet_theme_settings[' . esc_attr( $name ) . ']" value="0">';
    echo '<input type="checkbox" name="aznet_theme_settings[' . esc_attr( $name ) . ']" value="1" ' . checked( $current, true, false ) . '>';
    echo '</span></label>';
}

function render_hidden_settings( array $visible_keys ): void {
    $s = settings();
    foreach ( settings_defaults() as $key => $default ) {
        if ( 'schema_version' === $key || in_array( $key, $visible_keys, true ) ) { continue; }
        $value = $s[ $key ] ?? $default;
        echo '<input type="hidden" name="aznet_theme_settings[' . esc_attr( $key ) . ']" value="' . esc_attr( is_bool( $value ) ? ( $value ? '1' : '0' ) : (string) $value ) . '">';
    }
}

function render_settings_form( string $section ): void {
    $s = settings();
    $visible_keys = [];
    if ( 'design' === $section ) {
        $visible_keys = [ 'visual_preset' ];
    } elseif ( 'header' === $section ) {
        $visible_keys = [ 'header_preset', 'header_sticky', 'header_search', 'header_utilities' ];
    } elseif ( 'commerce' === $section ) {
        $visible_keys = [ 'woo_catalog_preset', 'woo_product_card_density', 'woo_product_preset' ];
    }

    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="aznet-theme-panel">';
    echo '<input type="hidden" name="action" value="aznet_theme_save_settings">';
    wp_nonce_field( 'aznet_theme_save_settings' );
    render_hidden_settings( $visible_keys );

    if ( 'design' === $section ) {
        field_select( 'visual_preset', __( 'Phong cách', 'aznet-theme' ), [ 'default' => 'Default', 'editorial' => 'Editorial', 'commerce' => 'Commerce' ], (string) $s['visual_preset'] );
    } elseif ( 'header' === $section ) {
        field_select( 'header_preset', __( 'Kiểu Header', 'aznet-theme' ), [ 'standard' => 'Standard', 'compact' => 'Compact', 'commerce' => 'Commerce', 'overlay' => 'Overlay' ], (string) $s['header_preset'] );
        field_select( 'header_sticky', __( 'Sticky', 'aznet-theme' ), [ 'off' => 'Off', 'sticky' => 'Sticky', 'sticky-compact' => 'Sticky Compact' ], (string) $s['header_sticky'] );
        field_checkbox( 'header_search', __( 'Hiển thị tìm kiếm', 'aznet-theme' ), (bool) $s['header_search'] );
        field_checkbox( 'header_utilities', __( 'Hiển thị tiện ích Header', 'aznet-theme' ), (bool) $s['header_utilities'] );
    } elseif ( 'commerce' === $section ) {
        field_select( 'woo_catalog_preset', __( 'Catalog', 'aznet-theme' ), [ 'grid' => 'Grid', 'compact-grid' => 'Compact Grid', 'editorial' => 'Editorial' ], (string) $s['woo_catalog_preset'] );
        field_select( 'woo_product_card_density', __( 'Mật độ thẻ sản phẩm', 'aznet-theme' ), [ 'comfortable' => 'Comfortable', 'balanced' => 'Balanced', 'compact' => 'Compact' ], (string) $s['woo_product_card_density'] );
        field_select( 'woo_product_preset', __( 'Trang sản phẩm', 'aznet-theme' ), [ 'classic' => 'Classic', 'focus' => 'Focus', 'story' => 'Story' ], (string) $s['woo_product_preset'] );
    }
    submit_button( __( 'Lưu thiết lập', 'aznet-theme' ) );
    echo '</form>';
}

function render_quick_setup_form(): void {
    $s = settings();
    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="aznet-theme-panel">';
    echo '<input type="hidden" name="action" value="aznet_theme_save_settings">';
    wp_nonce_field( 'aznet_theme_save_settings' );
    render_hidden_settings( [ 'visual_preset', 'header_preset' ] );
    echo '<h2>' . esc_html__( 'Quick Setup', 'aznet-theme' ) . '</h2>';
    field_select( 'visual_preset', __( 'Phong cách', 'aznet-theme' ), [ 'default' => 'Default', 'editorial' => 'Editorial', 'commerce' => 'Commerce' ], (string) $s['visual_preset'] );
    field_select( 'header_preset', __( 'Kiểu Header', 'aznet-theme' ), [ 'standard' => 'Standard', 'compact' => 'Compact', 'commerce' => 'Commerce', 'overlay' => 'Overlay' ], (string) $s['header_preset'] );
    submit_button( __( 'Lưu Quick Setup', 'aznet-theme' ), 'primary', 'submit', false );
    echo '</form>';
}

/**
 * Render read-only WordPress-native Homepage setup guidance.
 */
function render_homepage_setup_card(): void {
    $show_on_front = (string) get_option( 'show_on_front', 'posts' );
    $front_page_id = (int) get_option( 'page_on_front', 0 );

    echo '<div class="aznet-theme-panel aznet-theme-homepage-setup">';
    echo '<h2>' . esc_html__( 'Homepage Setup', 'aznet-theme' ) . '</h2>';

    if ( 'page' === $show_on_front && $front_page_id > 0 ) {
        $edit_link = get_edit_post_link( $front_page_id, 'raw' );
        echo '<p>' . esc_html__( 'Trang chủ tĩnh đã được cấu hình. Trang chủ dùng Classic Editor giống các Page WordPress thông thường.', 'aznet-theme' ) . '</p>';
        echo '<p>' . esc_html__( 'Bấm nút dưới để sửa nội dung Trang chủ bằng Classic Editor.', 'aznet-theme' ) . '</p>';
        if ( is_string( $edit_link ) && '' !== $edit_link ) {
            echo '<p><a class="button button-primary" href="' . esc_url( $edit_link ) . '">' . esc_html__( 'Sửa Trang chủ', 'aznet-theme' ) . '</a></p>';
        }
    } else {
        echo '<p>' . esc_html__( 'Hãy tạo hoặc chọn một Page bằng WordPress rồi cấu hình Page đó làm Trang chủ. Page được sửa bằng Classic Editor.', 'aznet-theme' ) . '</p>';
        echo '<p><a class="button" href="' . esc_url( admin_url( 'options-reading.php' ) ) . '">' . esc_html__( 'Mở Cài đặt đọc', 'aznet-theme' ) . '</a></p>';
    }

    echo '<p class="description">' . esc_html__( 'AZnet Theme không tự tạo, ghi đè hoặc xuất bản nội dung Trang chủ.', 'aznet-theme' ) . '</p>';
    echo '</div>';
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
        render_quick_setup_form();
        render_homepage_setup_card();
        echo '<div class="aznet-theme-grid">';
        $cards = [
            [ 'Phong cách', 'Chọn visual preset toàn Theme.', add_query_arg( [ 'page' => 'aznet-theme', 'section' => 'design' ], admin_url( 'admin.php' ) ) ],
            [ 'Logo', 'Dùng Custom Logo native của WordPress.', admin_url( 'customize.php?autofocus[control]=custom_logo' ) ],
            [ 'Menu', 'Quản lý menu bằng WordPress.', admin_url( 'nav-menus.php' ) ],
            [ 'Header', 'Chọn preset và sticky mode.', add_query_arg( [ 'page' => 'aznet-theme', 'section' => 'header' ], admin_url( 'admin.php' ) ) ],
            [ 'Trang', 'Quản lý và sửa Page bằng Classic Editor.', admin_url( 'edit.php?post_type=page' ) ],
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
        echo '<div class="aznet-theme-panel aznet-theme-danger"><h2>Đặt lại thiết lập Theme</h2><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="aznet_theme_reset_settings">';
        wp_nonce_field( 'aznet_theme_reset_settings' );
        echo '<label><input type="checkbox" name="confirm_reset" value="1" required> ' . esc_html__( 'Tôi xác nhận chỉ đặt lại thiết lập trình bày của AZnet Theme.', 'aznet-theme' ) . '</label> ';
        submit_button( 'Đặt lại', 'delete', 'submit', false ); echo '</form></div>';
    } elseif ( 'system-health' === $section ) {
        $report = system_health_report();
        echo '<div class="aznet-theme-panel"><h2>System Health</h2><table class="widefat striped"><tbody>';
        foreach ( $report as $group => $items ) {
            foreach ( $items as $key => $value ) {
                echo '<tr><th>' . esc_html( $group . ' / ' . $key ) . '</th><td><code>' . esc_html( is_bool( $value ) ? ( $value ? 'yes' : 'no' ) : (string) $value ) . '</code></td></tr>';
            }
        }
        echo '</tbody></table></div>';
        $snapshot = wp_json_encode( support_snapshot(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
        echo '<div class="aznet-theme-panel"><h2>' . esc_html__( 'Support Snapshot', 'aznet-theme' ) . '</h2><textarea class="large-text code" rows="16" readonly aria-label="' . esc_attr__( 'Support Snapshot JSON', 'aznet-theme' ) . '">' . esc_textarea( is_string( $snapshot ) ? $snapshot : '{}' ) . '</textarea></div>';
    } else {
        render_settings_form( $section );
    }
    echo '</div>';
}
