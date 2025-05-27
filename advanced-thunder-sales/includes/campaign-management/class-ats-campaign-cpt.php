<?php
/**
 * File: class-ats-campaign-cpt.php
 *
 * Đăng ký Custom Post Type "Campaign" (Chiến dịch) cho plugin Advanced Thunder Sales.
 * Cũng quản lý việc đăng ký và lưu trữ metaboxes cho CPT này.
 *
 * @package     AdvancedThunderSales
 * @subpackage  Includes/CampaignManagement
 * @since       1.0.1
 * @version     1.0.3.2
 * @lastupdate  26/05/2025 10:30 AM
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class ATS_Campaign_CPT.
 */
class ATS_Campaign_CPT {

    const POST_TYPE = 'ats_campaign';

    public function __construct() {
        // ... (Constructor giữ nguyên như v1.0.3.1) ...
        if ( defined('WP_DEBUG') && WP_DEBUG === true ) {
            error_log('[ATS] ATS_Campaign_CPT constructor called.');
        }
        $this->load_metabox_classes();

        add_action( 'init', array( $this, 'register_post_type' ), 0 );
        add_action( 'add_meta_boxes_' . self::POST_TYPE, array( $this, 'add_campaign_meta_boxes' ) );
        add_action( 'save_post_' . self::POST_TYPE, array( $this, 'save_campaign_meta_data' ), 10, 2 );
    }

    private function load_metabox_classes() {
        // ... (load_metabox_classes giữ nguyên như v1.0.3.1) ...
        if ( defined('WP_DEBUG') && WP_DEBUG === true ) {
            error_log('[ATS] ATS_Campaign_CPT: Loading metabox classes...');
        }
        $metabox_files = array(
            'class-ats-metabox-campaign-details.php',
            'class-ats-metabox-campaign-products.php',
        );

        foreach ( $metabox_files as $file ) {
            $file_path = ATS_PLUGIN_DIR . 'includes/campaign-management/metaboxes/' . $file;
            if ( file_exists( $file_path ) ) {
                require_once $file_path;
                if ( defined('WP_DEBUG') && WP_DEBUG === true ) {
                    error_log('[ATS] ATS_Campaign_CPT: Successfully loaded metabox file ' . $file_path);
                }
            } else {
                if ( defined('WP_DEBUG') && WP_DEBUG === true ) {
                    error_log( '[ATS] ATS_Campaign_CPT Critical Error: Metabox file not found at ' . $file_path );
                }
            }
        }
    }

    public function register_post_type() {
        // ... (register_post_type giữ nguyên như v1.0.3.1) ...
        $labels = array(
            'name'                  => _x( 'Chiến dịch Thunder Sales', 'Post Type General Name', ATS_TEXT_DOMAIN ),
            'singular_name'         => _x( 'Chiến dịch Thunder Sales', 'Post Type Singular Name', ATS_TEXT_DOMAIN ),
            'menu_name'             => __( 'Thunder Sales', ATS_TEXT_DOMAIN ),
            'name_admin_bar'        => __( 'Chiến dịch TS', ATS_TEXT_DOMAIN ),
            'archives'              => __( 'Lưu trữ Chiến dịch', ATS_TEXT_DOMAIN ),
            'attributes'            => __( 'Thuộc tính Chiến dịch', ATS_TEXT_DOMAIN ),
            'parent_item_colon'     => __( 'Chiến dịch cha:', ATS_TEXT_DOMAIN ),
            'all_items'             => __( 'Tất cả Chiến dịch', ATS_TEXT_DOMAIN ),
            'add_new_item'          => __( 'Thêm Chiến dịch mới', ATS_TEXT_DOMAIN ),
            'add_new'               => __( 'Thêm mới', ATS_TEXT_DOMAIN ),
            'new_item'              => __( 'Chiến dịch mới', ATS_TEXT_DOMAIN ),
            'edit_item'             => __( 'Chỉnh sửa Chiến dịch', ATS_TEXT_DOMAIN ),
            'update_item'           => __( 'Cập nhật Chiến dịch', ATS_TEXT_DOMAIN ),
            'view_item'             => __( 'Xem Chiến dịch', ATS_TEXT_DOMAIN ),
            'view_items'            => __( 'Xem các Chiến dịch', ATS_TEXT_DOMAIN ),
            'search_items'          => __( 'Tìm kiếm Chiến dịch', ATS_TEXT_DOMAIN ),
            'not_found'             => __( 'Không tìm thấy chiến dịch nào.', ATS_TEXT_DOMAIN ),
            'not_found_in_trash'    => __( 'Không tìm thấy chiến dịch nào trong thùng rác.', ATS_TEXT_DOMAIN ),
            'featured_image'        => __( 'Ảnh đại diện Chiến dịch', ATS_TEXT_DOMAIN ), 
            'set_featured_image'    => __( 'Đặt ảnh đại diện/Banner', ATS_TEXT_DOMAIN ),
            'remove_featured_image' => __( 'Xóa ảnh đại diện/Banner', ATS_TEXT_DOMAIN ),
            'use_featured_image'    => __( 'Sử dụng làm ảnh đại diện/Banner', ATS_TEXT_DOMAIN ),
            'insert_into_item'      => __( 'Chèn vào chiến dịch', ATS_TEXT_DOMAIN ),
            'uploaded_to_this_item' => __( 'Tải lên cho chiến dịch này', ATS_TEXT_DOMAIN ),
            'items_list'            => __( 'Danh sách Chiến dịch', ATS_TEXT_DOMAIN ),
            'items_list_navigation' => __( 'Điều hướng danh sách Chiến dịch', ATS_TEXT_DOMAIN ),
            'filter_items_list'     => __( 'Lọc danh sách Chiến dịch', ATS_TEXT_DOMAIN ),
        );
        $args = array(
            'label'                 => __( 'Chiến dịch Thunder Sales', ATS_TEXT_DOMAIN ),
            'description'           => __( 'Quản lý các chiến dịch Thunder Sales.', ATS_TEXT_DOMAIN ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'editor', 'thumbnail' ),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 20,
            'menu_icon'             => 'dashicons-megaphone',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => 'thunder-sales',
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'rewrite'               => array( 'slug' => 'thunder-sale-campaign', 'with_front' => true ),
            'capability_type'       => 'post',
            'show_in_rest'          => true,
        );
        register_post_type( self::POST_TYPE, $args );
    }


    public function add_campaign_meta_boxes( $post ) {
        // ... (add_campaign_meta_boxes giữ nguyên như v1.0.3.1) ...
        if ( defined('WP_DEBUG') && WP_DEBUG === true ) {
            error_log('[ATS] ATS_Campaign_CPT: add_campaign_meta_boxes called for post ID ' . $post->ID);
        }
        if ( class_exists('ATS_Metabox_Campaign_Details') ) {
            add_meta_box(
                'ats_campaign_details_metabox',
                __( 'Cài đặt chiến dịch Flash Sale', ATS_TEXT_DOMAIN ),
                array( 'ATS_Metabox_Campaign_Details', 'render_metabox' ),
                self::POST_TYPE,
                'normal',
                'high'
            );
        } 
        if ( class_exists('ATS_Metabox_Campaign_Products') ) {
            add_meta_box(
                'ats_campaign_products_metabox',
                __( 'Sản phẩm Flash Sale', ATS_TEXT_DOMAIN ),
                array( 'ATS_Metabox_Campaign_Products', 'render_metabox' ),
                self::POST_TYPE,
                'normal', 
                'core'    
            );
        }
    }

    public function save_campaign_meta_data( $post_id, $post ) {
        // --- Lưu metabox Chi Tiết Chiến Dịch ---
        if ( isset( $_POST['ats_campaign_details_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ats_campaign_details_nonce'] ) ) , 'ats_save_campaign_details_data' ) ) {
            if ( current_user_can( 'edit_post', $post_id ) && ! ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) && self::POST_TYPE === $post->post_type ) {
                // ... (Phần lưu chi tiết chiến dịch giữ nguyên như v1.0.3.1) ...
                if ( isset( $_POST['_ats_campaign_status'] ) && 'on' === $_POST['_ats_campaign_status'] ) {
                    update_post_meta( $post_id, '_ats_campaign_status', 'on' );
                } else {
                    delete_post_meta( $post_id, '_ats_campaign_status' );
                }
                if ( isset( $_POST['_ats_start_datetime'] ) ) {
                    $start_datetime_input = sanitize_text_field( wp_unslash( $_POST['_ats_start_datetime'] ) );
                    if ( ! empty( $start_datetime_input ) ) {
                        $start_datetime_obj = DateTime::createFromFormat( 'd/m/Y H:i', $start_datetime_input );
                        if ( $start_datetime_obj ) {
                            update_post_meta( $post_id, '_ats_start_datetime', $start_datetime_obj->format( 'Y-m-d H:i:s' ) );
                        } else { delete_post_meta( $post_id, '_ats_start_datetime' ); }
                    } else { delete_post_meta( $post_id, '_ats_start_datetime' ); }
                }
                if ( isset( $_POST['_ats_end_datetime'] ) ) {
                    $end_datetime_input = sanitize_text_field( wp_unslash( $_POST['_ats_end_datetime'] ) );
                    if ( ! empty( $end_datetime_input ) ) {
                        $end_datetime_obj = DateTime::createFromFormat( 'd/m/Y H:i', $end_datetime_input );
                        if ( $end_datetime_obj ) {
                            update_post_meta( $post_id, '_ats_end_datetime', $end_datetime_obj->format( 'Y-m-d H:i:s' ) );
                        } else { delete_post_meta( $post_id, '_ats_end_datetime' ); }
                    } else { delete_post_meta( $post_id, '_ats_end_datetime' ); }
                }
                $button_text = isset( $_POST['_ats_button_text'] ) ? sanitize_text_field( wp_unslash( $_POST['_ats_button_text'] ) ) : '';
                update_post_meta( $post_id, '_ats_button_text', $button_text );
                $button_url = isset( $_POST['_ats_button_url'] ) ? esc_url_raw( wp_unslash( $_POST['_ats_button_url'] ) ) : '';
                update_post_meta( $post_id, '_ats_button_url', $button_url );
                if ( isset( $_POST['_ats_is_recurring'] ) && 'on' === $_POST['_ats_is_recurring'] ) {
                    update_post_meta( $post_id, '_ats_is_recurring', 'on' );
                } else {
                    delete_post_meta( $post_id, '_ats_is_recurring' );
                }
            }
        }

        // --- Lưu metabox Sản Phẩm Chiến Dịch ---
        if ( isset( $_POST['ats_campaign_products_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ats_campaign_products_nonce'] ) ), 'ats_save_campaign_products_data' ) ) {
            if ( current_user_can( 'edit_post', $post_id ) && ! ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) && self::POST_TYPE === $post->post_type ) {
                
                $products_data_to_save = array();
                if ( isset( $_POST['ats_campaign_products'] ) && is_array( $_POST['ats_campaign_products'] ) ) {
                    foreach ( $_POST['ats_campaign_products'] as $product_id_key => $product_item_data ) {
                        $clean_product_id = intval( $product_id_key );
                        if ( $clean_product_id > 0 && isset($product_item_data['id']) && intval($product_item_data['id']) === $clean_product_id ) {
                            $sanitized_item = array(
                                'id' => $clean_product_id,
                                'fs_price' => isset( $product_item_data['fs_price'] ) ? sanitize_text_field( wp_unslash( $product_item_data['fs_price'] ) ) : '',
                                'fs_stock' => isset( $product_item_data['fs_stock'] ) ? intval( $product_item_data['fs_stock'] ) : 0,
                                'is_virtual_sold' => isset( $product_item_data['is_virtual_sold'] ) && $product_item_data['is_virtual_sold'] === 'on' ? 'on' : '',
                                'sold_count' => isset( $product_item_data['sold_count'] ) ? intval( $product_item_data['sold_count'] ) : 0,
                            );
                            // Nếu không phải là số lượng ảo, không lưu sold_count từ input (nó sẽ được tính toán thực tế sau)
                            if ( $sanitized_item['is_virtual_sold'] !== 'on' ) {
                                // Lấy sold_count đã lưu trước đó nếu có, hoặc để là 0
                                $current_meta = get_post_meta($post_id, '_ats_campaign_products', true);
                                $existing_sold_count = 0;
                                if(is_array($current_meta)){
                                    foreach($current_meta as $p_meta){
                                        if(isset($p_meta['id']) && intval($p_meta['id']) === $clean_product_id && isset($p_meta['sold_count'])){
                                            $existing_sold_count = intval($p_meta['sold_count']);
                                            break;
                                        }
                                    }
                                }
                                $sanitized_item['sold_count'] = $existing_sold_count; // Giữ lại số lượng đã bán thực tế
                            }
                            $products_data_to_save[] = $sanitized_item;
                        }
                    }
                }
                update_post_meta( $post_id, '_ats_campaign_products', $products_data_to_save );
                if ( defined('WP_DEBUG') && WP_DEBUG === true ) {
                    error_log('[ATS] Saved campaign products data for post ID ' . $post_id . ': ' . print_r($products_data_to_save, true));
                }
            }
        }
    }

}
/*
-----------------------------------------------------------------------------------
 Ghi chú phiên bản và cập nhật:
-----------------------------------------------------------------------------------
 *
 * Phiên bản 1.0.3.2 (26/05/2025 10:30 AM)
 * - Thêm logic lưu trữ cơ bản cho các trường mới trong metabox sản phẩm:
 * fs_price, fs_stock, is_virtual_sold, sold_count.
 * - Dữ liệu sản phẩm được lưu dưới dạng một mảng trong meta key '_ats_campaign_products'.
 * - Nếu 'is_virtual_sold' không được chọn, sold_count từ input sẽ không được lưu (sẽ giữ giá trị cũ hoặc 0).
 *
 * Phiên bản 1.0.3.1 (26/05/2025)
 * - Di chuyển việc load các file class của metabox vào hàm load_metabox_classes() được gọi trong constructor.
 * - Thêm nhiều error_log để theo dõi quá trình khởi tạo và đăng ký metabox.
 *
 * (Các phiên bản cũ hơn được lược bỏ)
 */
