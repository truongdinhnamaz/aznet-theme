<?php
namespace AZnet\Theme\Admin;

use function AZnet\Theme\settings;
use function AZnet\Theme\settings_defaults;
use function AZnet\Theme\visual_preset_choices;
use function AZnet\Theme\Integrations\WooCommerce\available as woo_available;

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! function_exists( __NAMESPACE__ . '\\footer_column_counts' ) ) {
    require_once __DIR__ . '/footer-content.php';
}

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

function render_footer_template_gallery( string $current ): void {
    $templates = [
        'minimal'      => [ __( 'Minimal', 'aznet-theme' ), __( 'Gọn, ưu tiên thương hiệu và các liên kết thiết yếu.', 'aznet-theme' ) ],
        'classic'      => [ __( 'Classic', 'aznet-theme' ), __( 'Bố cục ba cột quen thuộc cho website doanh nghiệp.', 'aznet-theme' ) ],
        'professional' => [ __( 'Professional', 'aznet-theme' ), __( 'Bốn cột rõ ràng cho website dịch vụ và doanh nghiệp.', 'aznet-theme' ) ],
        'split'        => [ __( 'Split', 'aznet-theme' ), __( 'Khối thương hiệu nổi bật bên trái, nội dung ở bên phải.', 'aznet-theme' ) ],
        'centered'     => [ __( 'Centered', 'aznet-theme' ), __( 'Bố cục cân giữa, phù hợp website thương hiệu gọn.', 'aznet-theme' ) ],
        'compact'      => [ __( 'Compact', 'aznet-theme' ), __( 'Footer thấp, tiết kiệm không gian hiển thị.', 'aznet-theme' ) ],
    ];

    echo '<div class="aznet-theme-footer-template-gallery" data-footer-template-gallery>';
    echo '<div class="aznet-theme-footer-template-gallery__heading"><h2>' . esc_html__( 'Chọn mẫu Footer', 'aznet-theme' ) . '</h2><p>' . esc_html__( 'Đổi mẫu chỉ thay cách trình bày. Toàn bộ nội dung Footer dùng chung và được giữ nguyên.', 'aznet-theme' ) . '</p></div>';
    if ( ! isset( $templates[ $current ] ) ) {
        echo '<input type="hidden" name="aznet_theme_settings[footer_preset]" value="' . esc_attr( $current ) . '" data-footer-preset-fallback>';
    }
    echo '<fieldset class="aznet-theme-footer-template-gallery__grid"><legend class="screen-reader-text">' . esc_html__( 'Mẫu Footer', 'aznet-theme' ) . '</legend>';
    foreach ( $templates as $preset => $template ) {
        $checked = checked( $current, $preset, false );
        echo '<label class="aznet-theme-footer-template-card aznet-theme-footer-template-card--' . esc_attr( $preset ) . '" data-footer-template="' . esc_attr( $preset ) . '">';
        echo '<input type="radio" name="aznet_theme_settings[footer_preset]" value="' . esc_attr( $preset ) . '" ' . $checked . '>';
        echo '<span class="aznet-theme-footer-template-card__preview" aria-hidden="true"><span></span><span></span><span></span><span></span></span>';
        echo '<span class="aznet-theme-footer-template-card__meta"><strong>' . esc_html( $template[0] ) . '</strong><small>' . esc_html( $template[1] ) . '</small></span>';
        echo '</label>';
    }
    echo '</fieldset>';

    $preview_preset = isset( $templates[ $current ] ) ? $current : 'classic';
    echo '<div class="aznet-theme-footer-live-preview aznet-theme-footer-live-preview--' . esc_attr( $preview_preset ) . '" data-footer-live-preview data-preset="' . esc_attr( $preview_preset ) . '">';
    echo '<div class="aznet-theme-footer-live-preview__toolbar"><strong>' . esc_html__( 'Xem trước bố cục', 'aznet-theme' ) . '</strong><span data-footer-preview-label>' . esc_html( $templates[ $preview_preset ][0] ) . '</span></div>';
    echo '<div class="aznet-theme-footer-live-preview__canvas" aria-hidden="true"><span class="aznet-theme-footer-live-preview__brand"></span><span></span><span></span><span></span></div>';
    echo '</div>';

    if ( ! isset( $templates[ $current ] ) && 'standard' !== $current ) {
        echo '<p class="description">' . esc_html__( 'Website đang dùng một preset Footer tương thích cũ/chuyên biệt. Chọn một mẫu ở trên để chuyển sang thư viện mẫu chung.', 'aznet-theme' ) . '</p>';
    }
    echo '</div>';
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
        render_footer_template_gallery( (string) $s['footer_preset'] );
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
    field_select( 'visual_preset', __( 'Phong cách', 'aznet-theme' ), visual_preset_choices(), (string) $s['visual_preset'] );
    field_select( 'header_preset', __( 'Kiểu Header', 'aznet-theme' ), [ 'standard' => 'Standard', 'compact' => 'Compact', 'commerce' => 'Commerce', 'overlay' => 'Overlay' ], (string) $s['header_preset'] );
    submit_button( __( 'Lưu Quick Setup', 'aznet-theme' ), 'primary', 'submit', false );
    echo '</form>';
}

/**
 * Render read-only WordPress-native Homepage setup guidance.
 */

function render_footer_column_editors( string $current ): void {
    $column_counts = function_exists( __NAMESPACE__ . '\\footer_column_counts' ) ? footer_column_counts() : [];
    $template_names = [
        'minimal'      => __( 'Minimal', 'aznet-theme' ),
        'classic'      => __( 'Classic', 'aznet-theme' ),
        'professional' => __( 'Professional', 'aznet-theme' ),
        'split'        => __( 'Split', 'aznet-theme' ),
        'centered'     => __( 'Centered', 'aznet-theme' ),
        'compact'      => __( 'Compact', 'aznet-theme' ),
    ];

    echo '<div class="aznet-theme-panel aznet-theme-footer-content-editor">';
    echo '<div class="aznet-theme-footer-content-editor__heading"><div><h2>' . esc_html__( 'Sửa nội dung Footer', 'aznet-theme' ) . '</h2>';
    echo '<p>' . esc_html__( 'Mỗi cột của mẫu Footer có một trình soạn thảo riêng. Khi đổi mẫu ở phía trên, bộ trình soạn thảo cũng đổi đúng theo số cột của mẫu đó.', 'aznet-theme' ) . '</p></div>';
    echo '<div class="aznet-theme-footer-content-editor__actions">';
    echo '<a class="button" href="' . esc_url( admin_url( 'customize.php?autofocus[control]=custom_logo' ) ) . '">' . esc_html__( 'Sửa Logo', 'aznet-theme' ) . '</a>';
    echo '<a class="button" href="' . esc_url( admin_url( 'options-general.php' ) ) . '">' . esc_html__( 'Sửa tên & mô tả', 'aznet-theme' ) . '</a>';
    echo '<a class="button" href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">' . esc_html__( 'Sửa Menu Footer', 'aznet-theme' ) . '</a>';
    echo '</div></div>';

    if ( isset( $_GET['footer_saved'] ) && '1' === sanitize_key( wp_unslash( $_GET['footer_saved'] ) ) ) {
        echo '<div class="notice notice-success inline"><p>' . esc_html__( 'Đã lưu nội dung các cột Footer.', 'aznet-theme' ) . '</p></div>';
    }

    foreach ( $column_counts as $preset => $count ) {
        $hidden = $preset === $current ? '' : ' hidden';
        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="aznet-theme-footer-content-editor__preset" data-footer-column-editors="' . esc_attr( $preset ) . '"' . $hidden . '>';
        echo '<input type="hidden" name="action" value="aznet_theme_save_footer_columns">';
        echo '<input type="hidden" name="aznet_theme_footer_editor_preset" value="' . esc_attr( $preset ) . '">';
        wp_nonce_field( 'aznet_theme_save_footer_columns' );

        echo '<div class="aznet-theme-footer-content-editor__preset-heading">';
        echo '<strong>' . esc_html( (string) ( $template_names[ $preset ] ?? ucfirst( $preset ) ) ) . '</strong>';
        echo '<span>' . esc_html( sprintf(
            /* translators: %d: number of Footer columns. */
            _n( '%d cột nội dung', '%d cột nội dung', $count, 'aznet-theme' ),
            $count
        ) ) . '</span>';
        echo '</div>';

        echo '<div class="aznet-theme-footer-content-editor__columns aznet-theme-footer-content-editor__columns--' . esc_attr( (string) $count ) . '">';
        for ( $column = 1; $column <= $count; $column++ ) {
            $content = function_exists( __NAMESPACE__ . '\\footer_column_editor_value' )
                ? footer_column_editor_value( $preset, $column )
                : '';
            $editor_id = 'aznet_theme_footer_' . $preset . '_column_' . $column;

            echo '<section class="aznet-theme-footer-content-editor__column">';
            echo '<h3>' . esc_html( sprintf(
                /* translators: %d: Footer column number. */
                __( 'Cột %d', 'aznet-theme' ),
                $column
            ) ) . '</h3>';

            wp_editor(
                $content,
                $editor_id,
                [
                    'textarea_name' => 'aznet_theme_footer_columns[' . $column . ']',
                    'textarea_rows' => 12,
                    'media_buttons' => true,
                    'teeny'         => false,
                    'quicktags'     => true,
                ]
            );
            echo '</section>';
        }
        echo '</div>';

        submit_button( __( 'Lưu nội dung Footer', 'aznet-theme' ), 'primary', 'submit', false );
        echo '</form>';
    }

    if ( ! isset( $column_counts[ $current ] ) ) {
        echo '<p class="description" data-footer-column-editors-empty>' . esc_html__( 'Hãy chọn một mẫu Footer ở phía trên để mở đúng bộ trình soạn thảo theo cột.', 'aznet-theme' ) . '</p>';
    }

    echo '</div>';
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
        render_settings_form( $section );
        render_footer_column_editors( (string) settings()['footer_preset'] );
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
