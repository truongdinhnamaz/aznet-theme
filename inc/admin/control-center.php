<?php
namespace AZnet\Theme\Admin;

use function AZnet\Theme\settings;
use function AZnet\Theme\settings_defaults;
use function AZnet\Theme\Integrations\WooCommerce\available as woo_available;

if ( ! defined( 'ABSPATH' ) ) { exit; }

function control_center_section(): string {
    $allowed = [ 'overview', 'header', 'footer', 'homepage', 'hero-library', 'provisioning', 'commerce', 'system-health' ];
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
        if ( is_array( $value ) ) {
            foreach ( $value as $item ) {
                echo '<input type="hidden" name="aznet_theme_settings[' . esc_attr( $key ) . '][]" value="' . esc_attr( (string) $item ) . '">';
            }
            continue;
        }
        echo '<input type="hidden" name="aznet_theme_settings[' . esc_attr( $key ) . ']" value="' . esc_attr( is_bool( $value ) ? ( $value ? '1' : '0' ) : (string) $value ) . '">';
    }
}

function render_settings_form( string $section ): void {
    $s = settings();
    $visible_keys = [];
    if ( 'header' === $section ) {
        $visible_keys = [ 'header_preset', 'header_sticky', 'header_search', 'header_utilities' ];
    } elseif ( 'footer' === $section ) {
        $visible_keys = [ 'footer_preset' ];
    } elseif ( 'commerce' === $section ) {
        $visible_keys = [ 'woo_catalog_preset', 'woo_product_card_density', 'woo_product_preset' ];
    }

    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="aznet-theme-panel">';
    echo '<input type="hidden" name="action" value="aznet_theme_save_settings">';
    wp_nonce_field( 'aznet_theme_save_settings' );
    render_hidden_settings( $visible_keys );

    if ( 'header' === $section ) {
        field_select( 'header_preset', __( 'Kiểu Header', 'aznet-theme' ), [ 'standard' => 'Standard', 'compact' => 'Compact', 'commerce' => 'Commerce', 'overlay' => 'Overlay' ], (string) $s['header_preset'] );
        field_select( 'header_sticky', __( 'Sticky', 'aznet-theme' ), [ 'off' => 'Off', 'sticky' => 'Sticky', 'sticky-compact' => 'Sticky Compact' ], (string) $s['header_sticky'] );
        field_checkbox( 'header_search', __( 'Hiển thị tìm kiếm', 'aznet-theme' ), (bool) $s['header_search'] );
        field_checkbox( 'header_utilities', __( 'Hiển thị tiện ích Header', 'aznet-theme' ), (bool) $s['header_utilities'] );
    } elseif ( 'footer' === $section ) {
        field_select( 'footer_preset', __( 'Kiểu Footer', 'aznet-theme' ), [ 'standard' => 'Standard', 'professional' => 'Professional', 'compact' => 'Compact', 'law-01' => 'Law 01' ], (string) $s['footer_preset'] );
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
    field_select( 'visual_preset', __( 'Phong cách', 'aznet-theme' ), [ 'default' => 'Default', 'editorial' => 'Editorial', 'commerce' => 'Commerce', 'curtain-01' => 'Rèm 01', 'industrial-01' => 'Industrial 01' ], (string) $s['visual_preset'] );
    field_select( 'header_preset', __( 'Kiểu Header', 'aznet-theme' ), [ 'standard' => 'Standard', 'compact' => 'Compact', 'commerce' => 'Commerce', 'overlay' => 'Overlay' ], (string) $s['header_preset'] );
    submit_button( __( 'Lưu Quick Setup', 'aznet-theme' ), 'primary', 'submit', false );
    echo '</form>';
}


/**
 * Render a visual Footer template library.
 *
 * Template selection changes Theme-owned presentation only. Footer content is
 * edited separately through WordPress-native identity/menu sources.
 */
function render_footer_template_library(): void {
    $s = settings();
    $current = (string) ( $s['footer_preset'] ?? 'standard' );
    $templates = [
        'standard' => [
            'name'        => __( 'Cân bằng', 'aznet-theme' ),
            'description' => __( 'Bố cục 3 cột rõ ràng cho website doanh nghiệp và nội dung.', 'aznet-theme' ),
        ],
        'professional' => [
            'name'        => __( 'Chuyên nghiệp', 'aznet-theme' ),
            'description' => __( 'Bố cục rộng, nhiều cột, phù hợp website dịch vụ và doanh nghiệp.', 'aznet-theme' ),
        ],
        'compact' => [
            'name'        => __( 'Tối giản', 'aznet-theme' ),
            'description' => __( 'Footer gọn, ít chiều cao, ưu tiên điều hướng và thông tin chính.', 'aznet-theme' ),
        ],
        'law-01' => [
            'name'        => __( 'Cao cấp', 'aznet-theme' ),
            'description' => __( 'Bố cục premium 4 cột với vùng thương hiệu, liên hệ và mạng xã hội nổi bật.', 'aznet-theme' ),
        ],
    ];

    echo '<div class="aznet-theme-panel aznet-theme-footer-library">';
    echo '<div class="aznet-theme-footer-library__heading"><div><h2>' . esc_html__( 'Chọn mẫu Footer', 'aznet-theme' ) . '</h2>';
    echo '<p>' . esc_html__( 'Đổi mẫu chỉ thay bố cục và phong cách hiển thị. Nội dung Footer hiện có được giữ nguyên.', 'aznet-theme' ) . '</p></div>';
    echo '<span class="aznet-theme-footer-library__current">' . esc_html__( 'Đang dùng:', 'aznet-theme' ) . ' ' . esc_html( $templates[ $current ]['name'] ?? $templates['standard']['name'] ) . '</span></div>';

    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
    echo '<input type="hidden" name="action" value="aznet_theme_save_settings">';
    echo '<input type="hidden" name="aznet_theme_return_section" value="footer">';
    wp_nonce_field( 'aznet_theme_save_settings' );
    render_hidden_settings( [ 'footer_preset' ] );
    echo '<fieldset class="aznet-theme-footer-library__fieldset"><legend class="screen-reader-text">' . esc_html__( 'Chọn mẫu Footer', 'aznet-theme' ) . '</legend>';
    echo '<div class="aznet-theme-footer-library__grid">';

    foreach ( $templates as $preset => $template ) {
        $checked = $current === $preset;
        echo '<label class="aznet-theme-footer-template-card">';
        echo '<input type="radio" name="aznet_theme_settings[footer_preset]" value="' . esc_attr( $preset ) . '" ' . checked( $checked, true, false ) . '>';
        echo '<span class="aznet-theme-footer-template-preview aznet-theme-footer-template-preview--' . esc_attr( $preset ) . '" aria-hidden="true">';
        echo '<span class="aznet-theme-footer-template-preview__brand"><i></i><b></b><b></b></span>';
        echo '<span class="aznet-theme-footer-template-preview__column"><b></b><i></i><i></i><i></i></span>';
        echo '<span class="aznet-theme-footer-template-preview__column"><b></b><i></i><i></i><i></i></span>';
        echo '<span class="aznet-theme-footer-template-preview__column aznet-theme-footer-template-preview__column--extra"><b></b><i></i><i></i><i></i></span>';
        echo '</span>';
        echo '<span class="aznet-theme-footer-template-card__meta"><strong>' . esc_html( $template['name'] ) . '</strong><span>' . esc_html( $template['description'] ) . '</span>';
        if ( $checked ) {
            echo '<em>' . esc_html__( 'Đang sử dụng', 'aznet-theme' ) . '</em>';
        }
        echo '</span></label>';
    }

    echo '</div></fieldset>';
    submit_button( __( 'Áp dụng mẫu Footer', 'aznet-theme' ), 'primary', 'submit', false );
    echo '</form></div>';
}

/**
 * Render read-only WordPress-native Homepage setup guidance.
 */

function render_footer_profile_form(): void {
    $values = function_exists( __NAMESPACE__ . '\\footer_profile_from_menus' ) ? footer_profile_from_menus() : [];
    $fields = [
        'location'  => [ 'footer_profile_location', __( 'Địa chỉ', 'aznet-theme' ), 'text', '62 Cửa Bắc, Ba Đình, Hà Nội' ],
        'phone'     => [ 'footer_profile_phone', __( 'Số điện thoại', 'aznet-theme' ), 'text', '024 3716 4123' ],
        'website'   => [ 'footer_profile_website', __( 'Website', 'aznet-theme' ), 'url', 'https://example.com' ],
        'email'     => [ 'footer_profile_email', __( 'Email', 'aznet-theme' ), 'email', 'lienhe@example.com' ],
        'facebook'  => [ 'footer_profile_facebook', __( 'Fanpage Facebook', 'aznet-theme' ), 'url', 'https://facebook.com/...' ],
        'youtube'   => [ 'footer_profile_youtube', __( 'YouTube', 'aznet-theme' ), 'url', 'https://youtube.com/@...' ],
        'linkedin'  => [ 'footer_profile_linkedin', __( 'LinkedIn', 'aznet-theme' ), 'url', 'https://linkedin.com/company/...' ],
        'tiktok'    => [ 'footer_profile_tiktok', __( 'TikTok', 'aznet-theme' ), 'url', 'https://tiktok.com/@...' ],
        'instagram' => [ 'footer_profile_instagram', __( 'Instagram', 'aznet-theme' ), 'url', 'https://instagram.com/...' ],
    ];

    echo '<div class="aznet-theme-panel aznet-theme-footer-content-editor">';
    echo '<div class="aznet-theme-footer-content-editor__heading"><div><h2>' . esc_html__( 'Sửa nội dung Footer', 'aznet-theme' ) . '</h2>';
    echo '<p>' . esc_html__( 'Thông tin liên hệ và mạng xã hội được giữ nguyên khi đổi mẫu. Dữ liệu được lưu bằng Menu WordPress để không phụ thuộc Theme.', 'aznet-theme' ) . '</p></div>';
    echo '<div class="aznet-theme-footer-content-editor__actions">';
    echo '<a class="button" href="' . esc_url( admin_url( 'customize.php?autofocus[control]=custom_logo' ) ) . '">' . esc_html__( 'Sửa Logo', 'aznet-theme' ) . '</a>';
    echo '<a class="button" href="' . esc_url( admin_url( 'options-general.php' ) ) . '">' . esc_html__( 'Sửa tên & mô tả', 'aznet-theme' ) . '</a>';
    echo '<a class="button" href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">' . esc_html__( 'Sửa liên kết Footer', 'aznet-theme' ) . '</a>';
    echo '</div></div>';
    if ( isset( $_GET['footer_saved'] ) && '1' === sanitize_key( wp_unslash( $_GET['footer_saved'] ) ) ) {
        echo '<div class="notice notice-success inline"><p>' . esc_html__( 'Đã lưu thông tin chân trang.', 'aznet-theme' ) . '</p></div>';
    }
    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
    echo '<input type="hidden" name="action" value="aznet_theme_save_footer_profile">';
    wp_nonce_field( 'aznet_theme_save_footer_profile' );
    echo '<div class="aznet-theme-footer-content-editor__grid">';
    foreach ( $fields as $key => $field ) {
        echo '<label class="aznet-theme-footer-content-editor__field" for="' . esc_attr( $field[0] ) . '"><span>' . esc_html( $field[1] ) . '</span>';
        echo '<input class="regular-text" id="' . esc_attr( $field[0] ) . '" type="' . esc_attr( $field[2] ) . '" name="aznet_theme_footer_profile[' . esc_attr( $key ) . ']" value="' . esc_attr( (string) ( $values[ $key ] ?? '' ) ) . '" placeholder="' . esc_attr( $field[3] ) . '">';
        echo '</label>';
    }
    echo '</div>';
    submit_button( __( 'Lưu thông tin chân trang', 'aznet-theme' ), 'primary', 'submit', false );
    echo '</form></div>';
}

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
    $tabs = [ 'overview' => 'Tổng quan', 'header' => 'Header', 'footer' => 'Footer', 'homepage' => 'Trang chủ', 'provisioning' => 'Thiết lập nhanh', 'system-health' => 'System Health' ];
    if ( woo_available() ) {
        $tabs = [ 'overview' => 'Tổng quan', 'header' => 'Header', 'footer' => 'Footer', 'homepage' => 'Trang chủ', 'provisioning' => 'Thiết lập nhanh', 'commerce' => 'Commerce', 'system-health' => 'System Health' ];
    }
    $center_class = 'wrap aznet-theme-control-center' . ( 'homepage' === $section ? ' aznet-theme-control-center--homepage' : '' );
    echo '<div class="' . esc_attr( $center_class ) . '"' . ( 'homepage' === $section ? ' style="max-width:none;width:auto"' : '' ) . '><h1>AZnet Theme</h1><nav class="nav-tab-wrapper">';
    foreach ( $tabs as $slug => $label ) {
        $url = add_query_arg( [ 'page' => 'aznet-theme', 'section' => $slug ], admin_url( 'admin.php' ) );
        echo '<a class="nav-tab ' . ( $section === $slug ? 'nav-tab-active' : '' ) . '" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
    }
    echo '</nav>';

    if ( 'overview' === $section ) {
        render_quick_setup_form();
        render_provisioning_invitation();
        render_homepage_setup_card();
        echo '<div class="aznet-theme-grid">';
        $cards = [
            [ 'Logo', 'Dùng Custom Logo native của WordPress.', admin_url( 'customize.php?autofocus[control]=custom_logo' ) ],
            [ 'Menu', 'Quản lý menu bằng WordPress.', admin_url( 'nav-menus.php' ) ],
            [ 'Header', 'Chọn preset và sticky mode.', add_query_arg( [ 'page' => 'aznet-theme', 'section' => 'header' ], admin_url( 'admin.php' ) ) ],
            [ 'Trang', 'Tạo Page mới bằng Classic Editor.', admin_url( 'post-new.php?post_type=page' ) ],
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
    } elseif ( 'footer' === $section ) {
        render_footer_template_library();
        render_footer_profile_form();
    } elseif ( 'homepage' === $section ) {
        render_homepage_settings();
    } elseif ( 'hero-library' === $section ) {
        render_homepage_hero_library_screen();
    } elseif ( 'provisioning' === $section ) {
        render_provisioning_wizard();
    } elseif ( 'system-health' === $section ) {
        $report = system_health_report();
        $core = $report['standalone_core'] ?? [];
        $optional = $report['optional_integrations'] ?? [];

        echo '<div class="aznet-theme-panel"><h2>' . esc_html__( 'AZnet Theme Core', 'aznet-theme' ) . '</h2>';
        echo '<p><strong>' . esc_html__( 'Standalone Core', 'aznet-theme' ) . ':</strong> <code>' . esc_html( strtoupper( (string) ( $core['status'] ?? 'unknown' ) ) ) . '</code></p>';
        echo '<table class="widefat striped"><tbody>';
        foreach ( [ 'environment', 'theme', 'standalone_core' ] as $group ) {
            foreach ( (array) ( $report[ $group ] ?? [] ) as $key => $value ) {
                if ( 'standalone_core' === $group && 'status' === $key ) { continue; }
                echo '<tr><th>' . esc_html( $group . ' / ' . $key ) . '</th><td><code>' . esc_html( is_bool( $value ) ? ( $value ? 'yes' : 'no' ) : (string) $value ) . '</code></td></tr>';
            }
        }
        echo '</tbody></table></div>';

        $optional_labels = [
            'woocommerce' => __( 'WooCommerce', 'aznet-theme' ),
            'rootprofile_v1' => __( 'RootProfile v1', 'aznet-theme' ),
            'rootprofile_v2' => __( 'RootProfile v2', 'aznet-theme' ),
            'rootprofile_current_surface' => __( 'RootProfile Current Surface', 'aznet-theme' ),
            'convertflow' => __( 'ConvertFlow', 'aznet-theme' ),
        ];
        echo '<div class="aznet-theme-panel"><h2>' . esc_html__( 'Optional Integrations', 'aznet-theme' ) . '</h2><table class="widefat striped"><tbody>';
        foreach ( $optional as $key => $value ) {
            $display = [
                'available' => __( 'Available', 'aznet-theme' ),
                'not_present' => __( 'Not present', 'aznet-theme' ),
                'unknown' => __( 'Unknown', 'aznet-theme' ),
            ][ (string) $value ] ?? (string) $value;
            $label = $optional_labels[ (string) $key ] ?? (string) $key;
            echo '<tr><th>' . esc_html( $label ) . '</th><td><code>' . esc_html( $display ) . '</code></td></tr>';
        }
        echo '</tbody></table></div>';

        $snapshot = wp_json_encode( support_snapshot(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
        echo '<div class="aznet-theme-panel"><h2>' . esc_html__( 'Support Snapshot', 'aznet-theme' ) . '</h2><textarea class="large-text code" rows="16" readonly aria-label="' . esc_attr__( 'Support Snapshot JSON', 'aznet-theme' ) . '">' . esc_textarea( is_string( $snapshot ) ? $snapshot : '{}' ) . '</textarea></div>';
    } else {
        render_settings_form( $section );
    }
    echo '</div>';
}
