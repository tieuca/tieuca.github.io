<?php
/**
 * Plugin Name:       Advanced Thunder Sales
 * Plugin URI:        https://your-plugin-uri.com/
 * Description:       Plugin quản lý các chiến dịch Thunder Sales (Flash Sale) nâng cao và linh hoạt cho WooCommerce.
 * Version:           1.0.6.0
 * Author:            TieuCA
 * Author URI:        https://your-author-uri.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       advanced-thunder-sales
 * Domain Path:       /languages
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * WC requires at least: 6.0
 * WC tested up to: 8.8
 */

/**
 * Advanced Thunder Sales
 *
 * @package AdvancedThunderSales
 * @author TieuCA
 * @version 1.0.6.0
 */

/*
-----------------------------------------------------------------------------------
 Ghi chú phiên bản và cập nhật:
-----------------------------------------------------------------------------------
 *
 * Phiên bản 1.0.6.0 (26/05/2025 02:05 PM)
 * - Bắt đầu triển khai tính năng tự động thêm các biến thể con khi chọn sản phẩm cha.
 * - Cập nhật AJAX handler, JS và template để hỗ trợ hiển thị biến thể.
 * - Cập nhật ghi chú phiên bản với giờ phút.
 *
 * Phiên bản 1.0.5.9 (26/05/2025 11:40 AM)
 * - Cập nhật phiên bản đồng bộ với các sửa lỗi trong ats-admin-metaboxes.js và class-ats-main.php
 * để đảm bảo các chuỗi dịch cho SweetAlert2 được truyền và sử dụng đúng cách.
 *
 * (Toàn bộ lịch sử ghi chú phiên bản cũ được giữ lại bên dưới)
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Phiên bản hiện tại của plugin.
 */
define( 'ATS_VERSION', '1.0.6.0' ); // Cập nhật phiên bản

// ... (Phần còn lại của file giữ nguyên như phiên bản 1.0.5.9) ...
define( 'ATS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ATS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ATS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'ATS_TEXT_DOMAIN', 'advanced-thunder-sales' );
add_action(
    'before_woocommerce_init',
    function() {
        if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'high_performance_order_storage', __FILE__, true );
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        }
    }
);
function ats_buffer_admin_notices_start() { if ( is_admin() ) { ob_start(); } }
add_action( 'admin_notices', 'ats_buffer_admin_notices_start', 0 );
add_action( 'all_admin_notices', 'ats_buffer_admin_notices_start', 0 );
function ats_filter_and_display_admin_notices() {
    if ( ! is_admin() || ! ob_get_level() ) { return; }
    $notices_html = ob_get_clean();
    $hpos_notice_phrase = "Plugin này không tương thích với tính năng WooCommerce đã bật 'Lưu trữ đơn hàng hiệu suất cao'";
    $hpos_notice_phrase_context = "nó không nên được kích hoạt";
    if ( strpos( $notices_html, $hpos_notice_phrase ) !== false && strpos( $notices_html, $hpos_notice_phrase_context ) !== false) {
        $pattern1 = '/<div\b[^>]*class="[^"]*\bnotice\b[^"]*"[^>]*>.*?'. preg_quote($hpos_notice_phrase, '/') .'.*?<\/div>/is';
        $cleaned_html = preg_replace( $pattern1, '', $notices_html );
        if ( $cleaned_html === null || $cleaned_html === $notices_html || (strpos( $cleaned_html, $hpos_notice_phrase ) !== false) ) {
            $pattern2 = '/<div[^>]*>[\s\S]*?'. preg_quote($hpos_notice_phrase, '/') .'[\s\S]*?<\/div>/is';
            $cleaned_html_pass2 = preg_replace( $pattern2, $notices_html );
            if ($cleaned_html_pass2 !== null && $cleaned_html_pass2 !== $notices_html && (strpos( $cleaned_html_pass2, $hpos_notice_phrase ) === false) ) {
                $cleaned_html = $cleaned_html_pass2;
            }
        }
        if (strpos( $cleaned_html, $hpos_notice_phrase ) !== false && strpos( $cleaned_html, $hpos_notice_phrase_context ) !== false) { echo ''; } else { echo $cleaned_html; }
    } else { echo $notices_html; }
}
add_action( 'admin_notices', 'ats_filter_and_display_admin_notices', PHP_INT_MAX );
add_action( 'all_admin_notices', 'ats_filter_and_display_admin_notices', PHP_INT_MAX );
function ats_run_advanced_thunder_sales() {
    if ( defined('WP_DEBUG') && WP_DEBUG === true ) { error_log('[ATS] Running ats_run_advanced_thunder_sales()'); }
    require_once ATS_PLUGIN_DIR . 'includes/common/class-ats-main.php';
    $plugin = ATS_Main::get_instance();
    $plugin->run();
}
add_action( 'plugins_loaded', 'ats_run_advanced_thunder_sales', 20 );
function ats_activate_plugin() {
    if ( defined('WP_DEBUG') && WP_DEBUG === true ) { error_log('[ATS] Running ats_activate_plugin()'); }
    require_once ATS_PLUGIN_DIR . 'includes/common/class-ats-activator.php';
    ATS_Activator::activate();
}
register_activation_hook( __FILE__, 'ats_activate_plugin' );
function ats_deactivate_plugin() {
    if ( defined('WP_DEBUG') && WP_DEBUG === true ) { error_log('[ATS] Running ats_deactivate_plugin()'); }
    require_once ATS_PLUGIN_DIR . 'includes/common/class-ats-deactivator.php';
    ATS_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'ats_deactivate_plugin' );
function ats_load_textdomain_action() {
    load_plugin_textdomain( ATS_TEXT_DOMAIN, false, dirname( ATS_PLUGIN_BASENAME ) . '/languages/' );
}
add_action( 'plugins_loaded', 'ats_load_textdomain_action' );

