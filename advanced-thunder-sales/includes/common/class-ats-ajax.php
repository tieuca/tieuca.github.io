<?php
/**
 * File: class-ats-ajax.php
 *
 * Xử lý các request AJAX cho plugin Advanced Thunder Sales.
 *
 * @package     AdvancedThunderSales
 * @subpackage  Includes/Common
 * @since       1.0.0.0
 * @version     1.0.1.2
 * @lastupdate  26/05/2025 10:30 PM
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class ATS_Ajax.
 */
class ATS_Ajax {

    public function __construct() {
        add_action( 'wp_ajax_ats_get_product_details_for_metabox', array( $this, 'get_product_details_for_metabox' ) );
    }

    /**
     * AJAX handler để lấy chi tiết sản phẩm cho metabox "Sản phẩm Flash Sale".
     * Nếu sản phẩm là variable, sẽ lấy thông tin của sản phẩm cha trước, sau đó đến các biến thể con.
     *
     * @since 1.0.0.0
     * @version 1.0.1.2 - Refined data fetching for clarity.
     */
    public function get_product_details_for_metabox() {
        check_ajax_referer( 'ats_get_product_details_nonce', 'nonce' );

        $product_id_input = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
        if ( ! $product_id_input ) {
            wp_send_json_error( array( 'message' => __( 'Product ID is missing.', ATS_TEXT_DOMAIN ) ) );
        }

        $product_to_add = wc_get_product( $product_id_input );
        if ( ! $product_to_add || ! $product_to_add->exists() ) {
            wp_send_json_error( array( 'message' => __( 'Product not found or does not exist.', ATS_TEXT_DOMAIN ) ) );
        }

        $products_data = array();

        if ( $product_to_add->is_type('variable') ) {
            // Sản phẩm được chọn là sản phẩm cha (variable product)
            // Thêm sản phẩm cha vào danh sách trước
            $products_data[] = $this->get_product_item_data( $product_to_add, null, true ); // Đánh dấu là dòng cha chính

            $variations = $product_to_add->get_children(); // Lấy ID các biến thể con
            if ( !empty($variations) ) {
                foreach ( $variations as $variation_id ) {
                    $variation_product = wc_get_product( $variation_id );
                    if ( $variation_product && $variation_product->exists() ) {
                        $products_data[] = $this->get_product_item_data( $variation_product, $product_to_add );
                    }
                }
            }
        } elseif ( $product_to_add->is_type('variation') ) {
            // Sản phẩm được chọn là một biến thể cụ thể
            $parent_product = wc_get_product( $product_to_add->get_parent_id() );
            if ($parent_product && $parent_product->exists()) {
                // Thêm sản phẩm cha của biến thể này trước
                 $products_data[] = $this->get_product_item_data( $parent_product, null, true );
            }
            // Sau đó thêm chính biến thể đó
            $products_data[] = $this->get_product_item_data( $product_to_add, $parent_product );

        } else {
            // Sản phẩm đơn giản
            $products_data[] = $this->get_product_item_data( $product_to_add );
        }

        if (empty($products_data)) {
            wp_send_json_error( array( 'message' => __( 'No valid product data could be prepared.', ATS_TEXT_DOMAIN ) ) );
            return;
        }
        
        // Loại bỏ các sản phẩm trùng lặp theo product_id trước khi gửi (quan trọng nếu logic trên có thể thêm trùng)
        $unique_products_data = array();
        $added_ids = array();
        foreach($products_data as $p_data){
            if(!in_array($p_data['product_id'], $added_ids)){
                $unique_products_data[] = $p_data;
                $added_ids[] = $p_data['product_id'];
            }
        }

        wp_send_json_success( $unique_products_data );
    }

    /**
     * Helper function to get formatted data for a single product/variation.
     * @param WC_Product $product_object
     * @param WC_Product|null $parent_product_object (optional, for variations)
     * @param bool $is_explicit_parent (optional, to mark the main parent row)
     * @return array
     */
    private function get_product_item_data( $product_object, $parent_product_object = null, $is_explicit_parent = false ) {
        $image_size = apply_filters( 'ats_metabox_product_image_size', 'thumbnail' );
        $image_id   = $product_object->get_image_id();
        
        if ( ! $image_id && $product_object->is_type('variation') && $parent_product_object ) {
            $image_id = $parent_product_object->get_image_id();
        }
        $image_src  = $image_id ? wp_get_attachment_image_src( $image_id, $image_size ) : false;
        $image_html = $image_src ? '<img src="' . esc_url( $image_src[0] ) . '" width="' . esc_attr( $image_src[1] ) . '" height="' . esc_attr( $image_src[2] ) . '" alt="'.esc_attr($product_object->get_name()).'" />' : '<span class="dashicons dashicons-format-image"></span>';
        
        $edit_post_link = get_edit_post_link( $product_object->get_id(), 'raw' );

        // Đối với sản phẩm cha, chúng ta không cần giá cụ thể, chỉ cần tên và trạng thái tồn kho tổng thể
        // Đối với biến thể và sản phẩm đơn giản, lấy giá đầy đủ
        $price_html = ( $is_explicit_parent && $product_object->is_type('variable') ) ? '' : $product_object->get_price_html();

        return array(
            'product_id'        => $product_object->get_id(),
            'name'              => $is_explicit_parent ? $product_object->get_name() : $product_object->get_formatted_name(),
            'edit_link'         => $edit_post_link,
            'sku'               => $product_object->get_sku() ? $product_object->get_sku() : 'N/A',
            'image'             => $image_html,
            'price_html'        => $price_html, // Giá bán đầy đủ (có thể có giá gốc gạch đi)
            'stock_html'        => wc_get_stock_html( $product_object ),
            'stock_status_class'=> $product_object->get_stock_status(),
            'is_variation'      => $product_object->is_type('variation'),
            'is_parent_row'     => $is_explicit_parent, // Đánh dấu đây là dòng sản phẩm cha chính
            'parent_id'         => $parent_product_object ? $parent_product_object->get_id() : ( $product_object->is_type('variation') ? $product_object->get_parent_id() : 0 ),
        );
    }

}
/*
-----------------------------------------------------------------------------------
 Ghi chú phiên bản và cập nhật:
-----------------------------------------------------------------------------------
 *
 * Phiên bản 1.0.1.2 (26/05/2025 10:30 PM)
 * - Cập nhật `get_product_details_for_metabox`:
 * + Xử lý rõ ràng hơn trường hợp người dùng chọn sản phẩm cha hoặc chọn một biến thể cụ thể.
 * + Đảm bảo sản phẩm cha luôn được thêm vào trước các biến thể của nó.
 * + Loại bỏ các sản phẩm/biến thể trùng lặp trước khi trả về.
 * - Cập nhật `get_product_item_data`:
 * + Giá của dòng sản phẩm cha chính (variable product) sẽ không hiển thị giá cụ thể (price_html để trống).
 * - Cập nhật ghi chú phiên bản với giờ phút.
 *
 * Phiên bản 1.0.1.1 (26/05/2025 02:10 PM)
 * - Cập nhật `get_product_details_for_metabox`:
 * + Nếu sản phẩm được chọn là variable, trả về thông tin sản phẩm cha trước, sau đó là các biến thể con.
 * + Nếu người dùng chọn một biến thể cụ thể, vẫn trả về cha và biến thể đó.
 * + Đảm bảo loại bỏ các sản phẩm trùng lặp trước khi gửi JSON.
 * - Cập nhật `get_product_item_data`:
 * + Thêm tham số `$is_explicit_parent` để đánh dấu dòng sản phẩm cha chính.
 * + `name` sẽ là `get_name()` cho sản phẩm cha chính, và `get_formatted_name()` cho biến thể.
 * + `parent_id` được gán đúng cách cho cả biến thể.
 * + Thêm `is_parent_row` vào dữ liệu trả về.
 *
 * (Các phiên bản cũ hơn được lược bỏ)
 */
