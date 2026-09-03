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

function enqueue_assets( string $hook_suffix ): void {
    if ( ! is_control_center_screen( $hook_suffix ) ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-control-center',
        get_template_directory_uri() . '/assets/css/admin/control-center.css',
        [],
        AZNET_THEME_VERSION
    );
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

        <?php
        $notice = isset( $_GET['aznet_theme_notice'] )
            ? sanitize_key( wp_unslash( $_GET['aznet_theme_notice'] ) )
            : '';
        if ( 'saved' === $notice ) :
            ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Đã lưu thiết lập nền AZnet Theme.', 'aznet-theme' ); ?></p></div>
        <?php elseif ( 'reset' === $notice ) : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Đã đặt lại thiết lập AZnet Theme.', 'aznet-theme' ); ?></p></div>
        <?php elseif ( 'confirm-reset' === $notice ) : ?>
            <div class="notice notice-warning"><p><?php esc_html_e( 'Cần xác nhận trước khi đặt lại thiết lập Theme.', 'aznet-theme' ); ?></p></div>
        <?php endif; ?>

        <div class="aznet-theme-control-center__actions">
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="aznet_theme_save_u0_settings">
                <?php wp_nonce_field( 'aznet_theme_save_u0_settings' ); ?>
                <?php submit_button( __( 'Lưu thiết lập nền', 'aznet-theme' ), 'primary', 'submit', false ); ?>
            </form>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="aznet_theme_reset_settings">
                <?php wp_nonce_field( 'aznet_theme_reset_settings' ); ?>
                <label>
                    <input type="checkbox" name="confirm_reset" value="1" required>
                    <?php esc_html_e( 'Tôi xác nhận đặt lại thiết lập presentation của AZnet Theme.', 'aznet-theme' ); ?>
                </label>
                <?php submit_button( __( 'Đặt lại thiết lập AZnet Theme', 'aznet-theme' ), 'delete', 'submit', false ); ?>
            </form>
        </div>
    </div>
    <?php
}
