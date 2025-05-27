<?php
/**
 * File: class-ats-main.php
 *
 * Class chính điều phối hoạt động của plugin Advanced Thunder Sales.
 *
 * @package     AdvancedThunderSales
 * @subpackage  Includes/Common
 * @since       1.0.0
 * @version     1.0.4.8
 * @lastupdate  26/05/2025 10:42 PM
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class ATS_Main.
 */
class ATS_Main {

    private static $instance;
    protected $modules = array();

    private function __construct() {
        // ... (constructor giữ nguyên) ...
        if ( defined('WP_DEBUG') && WP_DEBUG === true ) { error_log('[ATS] ATS_Main constructor called.'); }
        $this->load_dependencies();
        $this->initialize_modules();
        $this->define_hooks();
    }

    public static function get_instance() {
        // ... (get_instance giữ nguyên) ...
        if ( null === self::$instance ) { self::$instance = new self(); } return self::$instance;
    }

    private function load_dependencies() {
        // ... (load_dependencies giữ nguyên) ...
        if ( defined('WP_DEBUG') && WP_DEBUG === true ) { error_log('[ATS] ATS_Main: Loading dependencies...'); }
        $base_dir = ATS_PLUGIN_DIR . 'includes/';
        $files_to_load = array( 'common/class-ats-ajax.php', 'campaign-management/class-ats-campaign-cpt.php',);
        foreach ( $files_to_load as $file_rel_path ) {
            $file_abs_path = $base_dir . $file_rel_path;
            if ( file_exists( $file_abs_path ) ) { require_once $file_abs_path;
            } else { if ( defined('WP_DEBUG') && WP_DEBUG === true ) { error_log( '[ATS] ATS_Main Critical Error: File not found at ' . $file_abs_path ); } }
        }
    }

    private function initialize_modules() {
        // ... (initialize_modules giữ nguyên) ...
        if ( defined('WP_DEBUG') && WP_DEBUG === true ) { error_log('[ATS] ATS_Main: Initializing modules...'); }
        if ( class_exists( 'ATS_Campaign_CPT' ) ) { $this->modules['campaign_cpt'] = new ATS_Campaign_CPT(); }
        if ( class_exists( 'ATS_Ajax' ) ) { $this->modules['ajax'] = new ATS_Ajax(); }
    }

    private function define_hooks() {
        // ... (define_hooks giữ nguyên) ...
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    public function enqueue_public_styles() {
        // ... (enqueue_public_styles giữ nguyên) ...
        wp_enqueue_style( ATS_TEXT_DOMAIN . '-public-styles', ATS_PLUGIN_URL . 'assets/css/ats-styles.css', array(), ATS_VERSION, 'all' );
        wp_enqueue_style( 'sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css', array(), '11.10.8' );
    }

    public function enqueue_public_scripts() {
        // ... (enqueue_public_scripts giữ nguyên) ...
        wp_enqueue_script( ATS_TEXT_DOMAIN . '-public-scripts', ATS_PLUGIN_URL . 'assets/js/ats-scripts.js', array( 'jquery' ), ATS_VERSION, true );
        wp_enqueue_script( 'sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js', array(), '11.10.8', true );
        wp_localize_script( ATS_TEXT_DOMAIN . '-public-scripts', 'ats_public_params', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'ats_public_ajax_nonce' ) ) );
    }

    public function enqueue_admin_assets( $hook ) {
        global $post_type, $pagenow;
        // ... (phần đầu của enqueue_admin_assets giữ nguyên) ...

        $is_ats_campaign_edit_page = ( ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) && isset( $post_type ) && class_exists('ATS_Campaign_CPT') && ATS_Campaign_CPT::POST_TYPE === $post_type );

        if ( $is_ats_campaign_edit_page ) {
            // ... (phần enqueue styles và scripts dependencies giữ nguyên như v1.0.4.7) ...
            wp_enqueue_style( ATS_TEXT_DOMAIN . '-admin-metaboxes-styles', ATS_PLUGIN_URL . 'assets/css/ats-admin-metaboxes.css', array(), ATS_VERSION,'all');
            wp_enqueue_style( 'flatpickr-css', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css', array(), '4.6.13' );
            if ( class_exists('WooCommerce') ) { wp_enqueue_style( 'wc-select2', WC()->plugin_url() . '/assets/css/select2.css', array(), defined('WC_VERSION') ? WC_VERSION : null );}
            wp_enqueue_script( 'jquery' ); wp_enqueue_script( 'jquery-ui-sortable'); 
            wp_enqueue_script( 'flatpickr-js', 'https://cdn.jsdelivr.net/npm/flatpickr', array('jquery'), '4.6.13', true );
            $flatpickr_locale_handle = 'flatpickr-l10n-vi';
            wp_enqueue_script( $flatpickr_locale_handle, 'https://npmcdn.com/flatpickr/dist/l10n/vn.js', array('flatpickr-js'), '4.6.13', true );
            wp_add_inline_script( 'flatpickr-js', "if(typeof flatpickr !== 'undefined' && typeof flatpickr.l10ns !== 'undefined' && typeof flatpickr.l10ns.vn !== 'undefined') { flatpickr.localize(flatpickr.l10ns.vn); } else { console.warn('ATS: Flatpickr or Vietnamese locale not ready for default localization.'); }", 'after' );
            wp_enqueue_script( 'select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0-rc.0', true );
            wp_enqueue_script( 'wp-util' ); 
            $dependencies = array( 'jquery', 'flatpickr-js', $flatpickr_locale_handle, 'wp-util', 'jquery-ui-sortable', 'select2-js' );
            wp_enqueue_script( ATS_TEXT_DOMAIN . '-admin-metaboxes-scripts', ATS_PLUGIN_URL . 'assets/js/ats-admin-metaboxes.js', $dependencies, ATS_VERSION, true );
            wp_enqueue_style( 'sweetalert2-admin', 'https://cdn.jsdelivr.net/npm/sweetalert2@11.10.8/dist/sweetalert2.min.css', array(), '11.10.8' );
            wp_enqueue_script( 'sweetalert2-admin', 'https://cdn.jsdelivr.net/npm/sweetalert2@11.10.8/dist/sweetalert2.all.min.js', array(), '11.10.8', true );

            $localized_text_strings = array(
                // ... (các chuỗi dịch cũ giữ nguyên) ...
                'error_title'   => __( 'Lỗi!', ATS_TEXT_DOMAIN ),
                'success_title' => __( 'Thành công!', ATS_TEXT_DOMAIN ),
                'warning_title' => __( 'Cảnh báo!', ATS_TEXT_DOMAIN ),
                'info_title'    => __( 'Thông báo', ATS_TEXT_DOMAIN ),
                'confirm_delete_product_title'      => __( 'Bạn chắc chắn?', ATS_TEXT_DOMAIN ),
                'confirm_delete_product_text'       => __( 'Bạn sẽ không thể hoàn tác hành động này!', ATS_TEXT_DOMAIN ),
                'confirm_delete_parent_product_text'=> __( 'Hành động này sẽ xóa sản phẩm cha và tất cả các biến thể con của nó. Bạn có chắc chắn?', ATS_TEXT_DOMAIN ),
                'confirm_delete_selected_title'   => __( 'Xóa các sản phẩm đã chọn?', ATS_TEXT_DOMAIN ),
                'confirm_delete_selected_text'    => __( 'Các sản phẩm đã chọn (bao gồm cả biến thể nếu sản phẩm cha được chọn) sẽ bị xóa.', ATS_TEXT_DOMAIN ),
                'confirm_button_text'             => __( 'Vâng, xóa nó!', ATS_TEXT_DOMAIN ), 
                'cancel_button_text'              => __( 'Không, hủy bỏ', ATS_TEXT_DOMAIN ),   
                'deleted_successfully_title'      => __( 'Đã xóa!', ATS_TEXT_DOMAIN ),
                'deleted_successfully_text'       => __( 'Sản phẩm đã được xóa.', ATS_TEXT_DOMAIN ),
                'selected_products_deleted_text'  => __( 'Các sản phẩm đã chọn đã được xóa.', ATS_TEXT_DOMAIN ),
                'no_product_selected'             => __( 'Vui lòng chọn một sản phẩm.', ATS_TEXT_DOMAIN ),
                'product_already_added'           => __( 'Sản phẩm này đã có trong chiến dịch.', ATS_TEXT_DOMAIN ),
                'error_fetching_product_details'  => __( 'Lỗi khi lấy chi tiết sản phẩm.', ATS_TEXT_DOMAIN ),
                'ajax_error'                      => __( 'Lỗi AJAX. Vui lòng thử lại.', ATS_TEXT_DOMAIN ),
                'no_products_in_campaign'         => __( 'Chưa có sản phẩm nào trong chiến dịch này.', ATS_TEXT_DOMAIN ),
                'select2_error_loading'           => __('Không thể tải kết quả.', ATS_TEXT_DOMAIN),
                'select2_input_too_short'         => __('Vui lòng nhập %s ký tự trở lên', ATS_TEXT_DOMAIN),
                'select2_loading_more'            => __('Đang tải thêm kết quả…', ATS_TEXT_DOMAIN),
                'select2_no_results'              => __('Không tìm thấy kết quả nào.', ATS_TEXT_DOMAIN),
                'select2_searching'               => __('Đang tìm…', ATS_TEXT_DOMAIN), // Dùng cho Select2
                'add_product_button'              => __('Thêm sản phẩm', ATS_TEXT_DOMAIN),
                'select2_placeholder'             => __('Tìm sản phẩm...', ATS_TEXT_DOMAIN),
                'template_error'                  => __('Lỗi Giao Diện: Không thể hiển thị sản phẩm.', ATS_TEXT_DOMAIN),
                'adding_product_loading'          => __('Đang thêm sản phẩm...', ATS_TEXT_DOMAIN), // Chuỗi mới
            );

            wp_localize_script(
                ATS_TEXT_DOMAIN . '-admin-metaboxes-scripts', 
                'ats_admin_params',
                array(
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'wc_product_search_nonce' => wp_create_nonce('search-products'), 
                    'product_details_nonce' => wp_create_nonce('ats_get_product_details_nonce'),
                    'flatpickr_locale' => 'vn',
                    'text'     => $localized_text_strings 
                )
            );
        }
    }

    public function run() {
        // ... 
    }

}
/*
-----------------------------------------------------------------------------------
 Ghi chú phiên bản và cập nhật:
-----------------------------------------------------------------------------------
 *
 * Phiên bản 1.0.4.8 (26/05/2025 10:42 PM)
 * - Thêm chuỗi dịch 'adding_product_loading' vào ats_admin_params.text.
 * - Cập nhật ghi chú phiên bản với giờ phút.
 *
 * Phiên bản 1.0.4.7 (26/05/2025 11:45 PM)
 * - Thêm chuỗi dịch 'confirm_delete_parent_product_text' vào ats_admin_params.text.
 *
 * (Các phiên bản cũ hơn được lược bỏ)
 */
