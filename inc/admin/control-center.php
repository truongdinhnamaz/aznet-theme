<?php
/**
 * AZnet Theme Control Center overview.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme\Admin\ControlCenter;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const MENU_SLUG = 'aznet-theme';

function required_capability(): string {
    return 'edit_theme_options';
}

function register_menu(): void {
    add_menu_page(
        __( 'AZnet Theme', 'aznet-theme' ),
        __( 'AZnet Theme', 'aznet-theme' ),
        required_capability(),
        MENU_SLUG,
        __NAMESPACE__ . '\\render_overview',
        'dashicons-admin-appearance',
        58
    );
}

function is_control_center_screen( ?string $hook_suffix ): bool {
    return 'toplevel_page_' . MENU_SLUG === $hook_suffix;
}

/** @return array<string,array<string,mixed>> */
function overview_status(): array {
    $settings = \AZnet\Theme\Settings\get();

    return [
        'logo' => [
            'configured' => (int) get_theme_mod( 'custom_logo', 0 ) > 0,
        ],
        'primary_menu' => [
            'configured' => has_nav_menu( 'primary' ),
        ],
        'preset' => [
            'configured' => '' !== (string) $settings['preset'],
            'value'      => (string) $settings['preset'],
        ],
        'woocommerce' => [
            'available' => \AZnet\Theme\Integrations\WooCommerce\available(),
        ],
    ];
}

function status_text( bool $is_ready ): string {
    return $is_ready
        ? __( 'Đã cấu hình', 'aznet-theme' )
        : __( 'Chưa cấu hình', 'aznet-theme' );
}

function availability_text( bool $is_available ): string {
    return $is_available
        ? __( 'Sẵn sàng', 'aznet-theme' )
        : __( 'Chưa có', 'aznet-theme' );
}

function render_overview(): void {
    if ( ! current_user_can( required_capability() ) ) {
        wp_die( esc_html__( 'Bạn không có quyền xem AZnet Theme.', 'aznet-theme' ) );
    }

    $status   = overview_status();
    $logo_url = admin_url( 'customize.php?autofocus[control]=custom_logo' );
    $menu_url = admin_url( 'nav-menus.php' );
    ?>
    <div class="wrap aznet-theme-control-center">
        <h1><?php esc_html_e( 'AZnet Theme', 'aznet-theme' ); ?></h1>
        <p>
            <?php
            echo esc_html(
                sprintf(
                    /* translators: %s: current theme version. */
                    __( 'Phiên bản %s — trung tâm cấu hình presentation của website.', 'aznet-theme' ),
                    AZNET_THEME_VERSION
                )
            );
            ?>
        </p>

        <div class="aznet-theme-control-center__grid">
            <section class="aznet-theme-control-center__card">
                <h2><?php esc_html_e( 'Logo', 'aznet-theme' ); ?></h2>
                <p class="aznet-theme-control-center__status"><?php echo esc_html( status_text( (bool) $status['logo']['configured'] ) ); ?></p>
                <p><a class="button" href="<?php echo esc_url( $logo_url ); ?>"><?php esc_html_e( 'Thiết lập Logo', 'aznet-theme' ); ?></a></p>
            </section>

            <section class="aznet-theme-control-center__card">
                <h2><?php esc_html_e( 'Primary Menu', 'aznet-theme' ); ?></h2>
                <p class="aznet-theme-control-center__status"><?php echo esc_html( status_text( (bool) $status['primary_menu']['configured'] ) ); ?></p>
                <p><a class="button" href="<?php echo esc_url( $menu_url ); ?>"><?php esc_html_e( 'Quản lý Menu', 'aznet-theme' ); ?></a></p>
            </section>

            <section class="aznet-theme-control-center__card">
                <h2><?php esc_html_e( 'Design Preset', 'aznet-theme' ); ?></h2>
                <p class="aznet-theme-control-center__status">
                    <?php
                    echo esc_html(
                        (bool) $status['preset']['configured']
                            ? (string) $status['preset']['value']
                            : __( 'Mặc định — Presets sẽ mở ở U1', 'aznet-theme' )
                    );
                    ?>
                </p>
            </section>

            <section class="aznet-theme-control-center__card">
                <h2><?php esc_html_e( 'WooCommerce', 'aznet-theme' ); ?></h2>
                <p class="aznet-theme-control-center__status"><?php echo esc_html( availability_text( (bool) $status['woocommerce']['available'] ) ); ?></p>
            </section>
        </div>
    </div>
    <?php
}
