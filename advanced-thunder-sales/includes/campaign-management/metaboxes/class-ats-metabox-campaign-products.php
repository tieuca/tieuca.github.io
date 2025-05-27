<?php
/**
 * File: class-ats-metabox-campaign-products.php
 *
 * Class để render metabox "Sản phẩm Flash Sale" cho CPT Campaign.
 *
 * @package     AdvancedThunderSales
 * @subpackage  Includes/CampaignManagement/Metaboxes
 * @since       1.0.0.0
 * @version     1.0.1.4
 * @lastupdate  26/05/2025 11:28 PM
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class ATS_Metabox_Campaign_Products.
 */
class ATS_Metabox_Campaign_Products {

    /**
     * Render HTML cho metabox sản phẩm trong chiến dịch.
     *
     * @since 1.0.0.0
     * @param WP_Post $post Đối tượng post hiện tại.
     */
    public static function render_metabox( $post ) {
        wp_nonce_field( 'ats_save_campaign_products_data', 'ats_campaign_products_nonce' );

        $campaign_products_meta = get_post_meta( $post->ID, '_ats_campaign_products', true );
        if ( ! is_array( $campaign_products_meta ) ) {
            $campaign_products_meta = array();
        }
        ?>
        <div id="ats_campaign_products_wrapper">
            <div class="ats-product-search-wrapper">
                <p>
                    <label for="ats_product_search"><?php esc_html_e( 'Gõ tên sản phẩm hoặc biến thể để tìm kiếm:', ATS_TEXT_DOMAIN ); ?></label>
                    <select id="ats_product_search"
                            name="ats_product_search_temp"
                            class="wc-product-search" 
                            style="width: 400px;"
                            data-placeholder="<?php esc_attr_e( 'Tìm sản phẩm theo tên, SKU, hoặc ID...', ATS_TEXT_DOMAIN ); ?>"
                            data-action="woocommerce_json_search_products_and_variations" 
                            data-nonce="<?php echo esc_attr( wp_create_nonce( 'search-products' ) ); ?>"
                            data-multiple="false"
                            data-exclude_type="grouped,external,bundle">
                        <option value=""><?php esc_html_e( 'Tìm sản phẩm...', ATS_TEXT_DOMAIN ); ?></option>
                    </select>
                </p>
            </div>

            <table class="wp-list-table widefat fixed striped ats-campaign-products-table">
                <thead>
                    <tr>
                        <th scope="col" class="ats-col-checkbox manage-column column-cb check-column"><input type="checkbox" title="<?php esc_attr_e('Chọn tất cả', ATS_TEXT_DOMAIN); ?>"/></th>
                        <th scope="col" class="ats-col-image"><?php esc_html_e( 'Ảnh', ATS_TEXT_DOMAIN ); ?></th>
                        <th scope="col" class="ats-col-name"><?php esc_html_e( 'Tên sản phẩm', ATS_TEXT_DOMAIN ); ?></th>
                        <th scope="col" class="ats-col-sku"><?php esc_html_e( 'Mã SP', ATS_TEXT_DOMAIN ); ?></th>
                        <th scope="col" class="ats-col-price"><?php esc_html_e( 'Giá bán', ATS_TEXT_DOMAIN ); ?></th>
                        <th scope="col" class="ats-col-fs-price"><?php esc_html_e( 'Giá FS', ATS_TEXT_DOMAIN ); ?></th>
                        <th scope="col" class="ats-col-fs-stock"><?php esc_html_e( 'Số lượng FS', ATS_TEXT_DOMAIN ); ?></th>
                        <th scope="col" class="ats-col-virtual-sold-toggle"><?php esc_html_e( 'SL ảo', ATS_TEXT_DOMAIN ); ?></th>
                        <th scope="col" class="ats-col-sold"><?php esc_html_e( 'Đã bán', ATS_TEXT_DOMAIN ); ?></th>
                        <th scope="col" class="ats-col-actions"><?php esc_html_e( 'Hành động', ATS_TEXT_DOMAIN ); ?></th>
                    </tr>
                </thead>
                <tbody id="ats_campaign_products_list">
                    <?php
                    if ( empty( $campaign_products_meta ) ) {
                        echo '<tr class="no-items"><td colspan="10">' . esc_html__( 'Chưa có sản phẩm nào trong chiến dịch này.', ATS_TEXT_DOMAIN ) . '</td></tr>';
                    } else {
                        $rendered_parents = array(); // Theo dõi ID của sản phẩm cha đã được render

                        foreach ( $campaign_products_meta as $product_item_data ) {
                            $product_id = isset( $product_item_data['id'] ) ? intval( $product_item_data['id'] ) : 0;
                            if ( ! $product_id ) continue;

                            $_product = wc_get_product( $product_id );
                            if ( ! $_product ) continue;

                            $is_parent_from_meta = isset( $product_item_data['is_parent'] ) && $product_item_data['is_parent'] === 'true';
                            $is_variation = $_product->is_type( 'variation' );
                            $actual_parent_id = $is_variation ? $_product->get_parent_id() : 0;

                            // Bước 1: Nếu là sản phẩm cha (is_parent_from_meta) hoặc là sản phẩm đơn giản
                            if ( $is_parent_from_meta || ( !$is_variation && !$actual_parent_id ) ) {
                                if (in_array($product_id, $rendered_parents)) {
                                    continue; // Bỏ qua nếu sản phẩm cha này đã được render
                                }
                                $rendered_parents[] = $product_id; // Đánh dấu là đã render

                                $image_id   = $_product->get_image_id();
                                $image_src  = $image_id ? wp_get_attachment_image_src( $image_id, 'thumbnail' ) : false;
                                $image_html = $image_src ? '<img src="' . esc_url( $image_src[0] ) . '" />' : '<span class="dashicons dashicons-format-image"></span>';
                                $edit_link  = get_edit_post_link( $product_id );
                                $row_class  = 'ats-parent-row';
                                $is_variable_product = $_product->is_type('variable');
                                ?>
                                <tr data-product_id="<?php echo esc_attr($product_id); ?>" class="<?php echo esc_attr($row_class); ?>">
                                    <td class="ats-col-checkbox check-column">
                                        <?php if (!$is_variable_product): // Chỉ cho phép chọn sản phẩm đơn giản ở dòng cha ?>
                                            <input type="checkbox" class="ats-product-item-checkbox" name="ats_campaign_products_select[<?php echo esc_attr($product_id); ?>]" value="<?php echo esc_attr($product_id); ?>" />
                                        <?php endif; ?>
                                    </td>
                                    <td class="ats-col-image"><?php echo $image_html; ?></td>
                                    <td class="ats-col-name">
                                        <?php if ($edit_link): ?><a href="<?php echo esc_url($edit_link); ?>" target="_blank"><strong><?php echo esc_html($_product->get_name()); ?></strong></a><?php else: ?><strong><?php echo esc_html($_product->get_name()); ?></strong><?php endif; ?>
                                        <span class="ats-product-stock-status <?php echo esc_attr($_product->get_stock_status()); ?>"><?php echo wp_kses_post(wc_get_stock_html($_product)); ?></span>
                                    </td>
                                    <td class="ats-col-sku"><?php echo esc_html($_product->get_sku() ? $_product->get_sku() : 'N/A'); ?></td>
                                    <td class="ats-col-price"><?php echo wp_kses_post($_product->get_price_html()); ?></td>
                                    <?php if (!$is_variable_product): // Input cho sản phẩm đơn giản ?>
                                        <td class="ats-col-fs-price"><input type="text" name="ats_campaign_products[<?php echo esc_attr($product_id); ?>][fs_price]" value="<?php echo esc_attr(isset($product_item_data['fs_price']) ? $product_item_data['fs_price'] : ''); ?>" class="short ats-input-price" /></td>
                                        <td class="ats-col-fs-stock"><input type="number" name="ats_campaign_products[<?php echo esc_attr($product_id); ?>][fs_stock]" value="<?php echo esc_attr(isset($product_item_data['fs_stock']) ? $product_item_data['fs_stock'] : ''); ?>" placeholder="0" class="short ats-input-qty" min="0" step="1" /></td>
                                        <td class="ats-col-virtual-sold-toggle"><label class="ats-toggle-switch"><input type="checkbox" class="ats-virtual-sold-checkbox" name="ats_campaign_products[<?php echo esc_attr($product_id); ?>][is_virtual_sold]" value="on" <?php checked( isset($product_item_data['is_virtual_sold']) && $product_item_data['is_virtual_sold'] === 'on' ); ?>><span class="ats-toggle-slider"></span></label></td>
                                        <td class="ats-col-sold"><input type="number" name="ats_campaign_products[<?php echo esc_attr($product_id); ?>][sold_count]" value="<?php echo esc_attr(isset($product_item_data['sold_count']) ? $product_item_data['sold_count'] : 0); ?>" class="short ats-input-sold-count" min="0" step="1" <?php echo (isset($product_item_data['is_virtual_sold']) && $product_item_data['is_virtual_sold'] === 'on') ? '' : 'readonly'; ?> /></td>
                                    <?php else: // Để trống cho sản phẩm cha của biến thể ?>
                                        <td class="ats-col-fs-price"></td> <td class="ats-col-fs-stock"></td> <td class="ats-col-virtual-sold-toggle"></td> <td class="ats-col-sold"></td>
                                    <?php endif; ?>
                                    <td class="ats-col-actions">
                                        <input type="hidden" name="ats_campaign_products[<?php echo esc_attr($product_id); ?>][id]" value="<?php echo esc_attr($product_id); ?>" />
                                        <?php if ($is_variable_product): ?>
                                            <input type="hidden" name="ats_campaign_products[<?php echo esc_attr($product_id); ?>][is_parent]" value="true" />
                                        <?php endif; ?>
                                        <button type="button" class="button button-link-delete ats-remove-product-row <?php echo $is_variable_product ? 'ats-remove-parent-and-variations' : ''; ?>"><span class="dashicons dashicons-trash"></span><span class="screen-reader-text"><?php esc_html_e( 'Xóa', ATS_TEXT_DOMAIN ); ?></span></button>
                                    </td>
                                </tr>
                                <?php
                                // Bây giờ render các biến thể con của sản phẩm cha này (nếu có)
                                if ($is_variable_product) {
                                    foreach ($campaign_products_meta as $variation_item_data) {
                                        $variation_id = isset($variation_item_data['id']) ? intval($variation_item_data['id']) : 0;
                                        $_variation_product = wc_get_product($variation_id);
                                        if (!$_variation_product || !$_variation_product->is_type('variation') || $_variation_product->get_parent_id() !== $product_id) {
                                            continue; // Bỏ qua nếu không phải biến thể của cha hiện tại
                                        }
                                        // Render dòng biến thể (code tương tự như trước)
                                        $var_image_id   = $_variation_product->get_image_id() ? $_variation_product->get_image_id() : $image_id; // Fallback to parent image
                                        $var_image_src  = $var_image_id ? wp_get_attachment_image_src( $var_image_id, 'thumbnail' ) : false;
                                        $var_image_html = $var_image_src ? '<img src="' . esc_url( $var_image_src[0] ) . '" />' : '<span class="dashicons dashicons-format-image"></span>';
                                        $var_edit_link = get_edit_post_link( $variation_id );
                                        $var_fs_price = isset( $variation_item_data['fs_price'] ) ? $variation_item_data['fs_price'] : '';
                                        $var_fs_stock = isset( $variation_item_data['fs_stock'] ) ? $variation_item_data['fs_stock'] : '';
                                        $var_is_virtual_sold = isset( $variation_item_data['is_virtual_sold'] ) && $variation_item_data['is_virtual_sold'] === 'on';
                                        $var_sold_count = isset( $variation_item_data['sold_count'] ) ? intval($variation_item_data['sold_count']) : 0;
                                        ?>
                                        <tr data-product_id="<?php echo esc_attr($variation_id); ?>" class="ats-variation-row ats-child-of-<?php echo esc_attr($product_id); ?>" data-parent_id="<?php echo esc_attr($product_id); ?>">
                                            <th scope="row" class="ats-col-checkbox check-column"><input type="checkbox" class="ats-product-item-checkbox" name="ats_campaign_products_select[<?php echo esc_attr($variation_id); ?>]" value="<?php echo esc_attr($variation_id); ?>" /></th>
                                            <td class="ats-col-image"><?php echo $var_image_html; ?></td>
                                            <td class="ats-col-name">
                                                <?php if ($var_edit_link): ?><a href="<?php echo esc_url($var_edit_link); ?>" target="_blank"><?php echo wp_kses_post($_variation_product->get_formatted_name()); ?></a><?php else: echo wp_kses_post($_variation_product->get_formatted_name()); endif; ?>
                                                <span class="ats-product-stock-status <?php echo esc_attr($_variation_product->get_stock_status()); ?>"><?php echo wp_kses_post(wc_get_stock_html($_variation_product)); ?></span>
                                            </td>
                                            <td class="ats-col-sku"><?php echo esc_html($_variation_product->get_sku() ? $_variation_product->get_sku() : 'N/A'); ?></td>
                                            <td class="ats-col-price"><?php echo wp_kses_post($_variation_product->get_price_html()); ?></td>
                                            <td class="ats-col-fs-price"><input type="text" name="ats_campaign_products[<?php echo esc_attr($variation_id); ?>][fs_price]" value="<?php echo esc_attr($var_fs_price); ?>" class="short ats-input-price" /></td>
                                            <td class="ats-col-fs-stock"><input type="number" name="ats_campaign_products[<?php echo esc_attr($variation_id); ?>][fs_stock]" value="<?php echo esc_attr($var_fs_stock); ?>" placeholder="0" class="short ats-input-qty" min="0" step="1" /></td>
                                            <td class="ats-col-virtual-sold-toggle"><label class="ats-toggle-switch"><input type="checkbox" class="ats-virtual-sold-checkbox" name="ats_campaign_products[<?php echo esc_attr($variation_id); ?>][is_virtual_sold]" value="on" <?php checked( $var_is_virtual_sold ); ?>><span class="ats-toggle-slider"></span></label></td>
                                            <td class="ats-col-sold"><input type="number" name="ats_campaign_products[<?php echo esc_attr($variation_id); ?>][sold_count]" value="<?php echo esc_attr($var_sold_count); ?>" class="short ats-input-sold-count" min="0" step="1" <?php echo $var_is_virtual_sold ? '' : 'readonly'; ?> /></td>
                                            <td class="ats-col-actions">
                                                <input type="hidden" name="ats_campaign_products[<?php echo esc_attr($variation_id); ?>][id]" value="<?php echo esc_attr($variation_id); ?>" />
                                                <input type="hidden" name="ats_campaign_products[<?php echo esc_attr($variation_id); ?>][parent_id]" value="<?php echo esc_attr($product_id); ?>" />
                                                <button type="button" class="button button-link-delete ats-remove-product-row"><span class="dashicons dashicons-trash"></span><span class="screen-reader-text"><?php esc_html_e( 'Xóa', ATS_TEXT_DOMAIN ); ?></span></button>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                }
                            } // end if ($is_parent_from_meta || simple product)
                        } // end foreach $campaign_products_meta
                    } 
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="10"> 
                            <button type="button" class="button ats-remove-selected-products" <?php echo empty($campaign_products_meta) ? 'disabled' : ''; ?>><?php esc_html_e( 'Xóa sản phẩm đã chọn', ATS_TEXT_DOMAIN ); ?></button>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <script type="text/html" id="tmpl-ats-campaign-product-row">
            <# // Template này sẽ được dùng cho cả sản phẩm cha và biến thể. 
               // Logic phân biệt sẽ dựa vào data.is_parent_row và data.is_variation #>
            <tr data-product_id="{{data.product_id}}" class="{{ data.is_parent_row ? 'ats-parent-row' : (data.is_variation ? 'ats-variation-row ats-child-of-' + data.parent_id : '') }}" <# if (data.is_variation) { #> data-parent_id="{{data.parent_id}}" <# } #> >
                <# if ( data.is_parent_row ) { #>
                    <td class="ats-col-checkbox check-column"></td> 
                    <td class="ats-col-image">{{{data.image}}}</td>
                    <td class="ats-col-name"> 
                        <# if ( data.edit_link ) { #>
                            <a href="{{data.edit_link}}" target="_blank"><strong>{{{data.name}}}</strong></a>
                        <# } else { #>
                            <strong>{{{data.name}}}</strong>
                        <# } #>
                         <span class="ats-product-stock-status {{data.stock_status_class}}">{{{data.stock_html}}}</span>
                    </td>
                    <td class="ats-col-sku">{{data.sku}}</td> 
                    <td class="ats-col-price">{{{data.price_html}}}</td> 
                    <td class="ats-col-fs-price"></td> 
                    <td class="ats-col-fs-stock"></td> 
                    <td class="ats-col-virtual-sold-toggle"></td> 
                    <td class="ats-col-sold"></td> 
                    <td class="ats-col-actions">
                         <input type="hidden" name="ats_campaign_products[{{data.product_id}}][id]" value="{{data.product_id}}" />
                         <input type="hidden" name="ats_campaign_products[{{data.product_id}}][is_parent]" value="true" />
                         <button type="button" class="button button-link-delete ats-remove-product-row ats-remove-parent-and-variations"><span class="dashicons dashicons-trash"></span><span class="screen-reader-text"><?php esc_html_e( 'Xóa cha & con', ATS_TEXT_DOMAIN ); ?></span></button>
                    </td>
                <# } else { #>
                    <th scope="row" class="ats-col-checkbox check-column"><input type="checkbox" class="ats-product-item-checkbox" name="ats_campaign_products_select[{{data.product_id}}]" value="{{data.product_id}}" /></th>
                    <td class="ats-col-image">{{{data.image}}}</td>
                    <td class="ats-col-name">
                        <# if ( data.edit_link ) { #>
                            <a href="{{data.edit_link}}" target="_blank">{{{data.name}}}</a>
                        <# } else { #>
                            {{{data.name}}}
                        <# } #>
                        <span class="ats-product-stock-status {{data.stock_status_class}}">{{{data.stock_html}}}</span>
                    </td>
                    <td class="ats-col-sku">{{data.sku}}</td>
                    <td class="ats-col-price">{{{data.price_html}}}</td>
                    <td class="ats-col-fs-price">
                        <input type="text" name="ats_campaign_products[{{data.product_id}}][fs_price]" value="" class="short ats-input-price" />
                    </td>
                    <td class="ats-col-fs-stock">
                        <input type="number" name="ats_campaign_products[{{data.product_id}}][fs_stock]" value="" placeholder="0" class="short ats-input-qty" min="0" step="1" />
                    </td>
                    <td class="ats-col-virtual-sold-toggle">
                        <label class="ats-toggle-switch">
                            <input type="checkbox" class="ats-virtual-sold-checkbox" name="ats_campaign_products[{{data.product_id}}][is_virtual_sold]" value="on">
                            <span class="ats-toggle-slider"></span>
                        </label>
                    </td>
                    <td class="ats-col-sold">
                        <input type="number" name="ats_campaign_products[{{data.product_id}}][sold_count]" value="0" class="short ats-input-sold-count" min="0" step="1" readonly />
                    </td>
                    <td class="ats-col-actions">
                        <input type="hidden" name="ats_campaign_products[{{data.product_id}}][id]" value="{{data.product_id}}" />
                        <input type="hidden" name="ats_campaign_products[{{data.product_id}}][parent_id]" value="{{data.parent_id}}" />
                        <button type="button" class="button button-link-delete ats-remove-product-row"><span class="dashicons dashicons-trash"></span><span class="screen-reader-text"><?php esc_html_e( 'Xóa', ATS_TEXT_DOMAIN ); ?></span></button>
                    </td>
                <# } #>
            </tr>
        </script>
        <?php
    }
}
/*
-----------------------------------------------------------------------------------
 Ghi chú phiên bản và cập nhật:
-----------------------------------------------------------------------------------
 *
 * Phiên bản 1.0.1.3 (26/05/2025 11:18 PM)
 * - Cải thiện logic render sản phẩm đã lưu trong PHP để xử lý chính xác hơn việc hiển thị sản phẩm cha và biến thể,
 * đảm bảo dòng sản phẩm cha có đủ 10 cột <td> và không bị lặp lại.
 * - Sắp xếp lại mảng sản phẩm trước khi render để ưu tiên sản phẩm cha.
 * - Cập nhật ghi chú thời gian.
 *
 * (Các phiên bản cũ hơn được lược bỏ)
 */
