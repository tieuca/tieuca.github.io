<?php
/**
 * File: class-ats-metabox-campaign-details.php
 *
 * Class để render metabox "Chi Tiết Chiến Dịch" cho CPT Campaign.
 * Sử dụng toggle switch cho trạng thái và Flatpickr cho chọn ngày giờ.
 * CSS và JS được load từ file riêng.
 *
 * @package     AdvancedThunderSales
 * @subpackage  Includes/CampaignManagement/Metaboxes
 * @since       1.0.0.0
 * @version     1.0.2.0
 * @lastupdate  25/05/2025
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class ATS_Metabox_Campaign_Details.
 */
class ATS_Metabox_Campaign_Details {

    /**
     * Render HTML cho metabox chi tiết chiến dịch.
     *
     * @since 1.0.0.0
     * @param WP_Post $post Đối tượng post hiện tại.
     */
    public static function render_metabox( $post ) {
        // Thêm nonce field để bảo mật
        wp_nonce_field( 'ats_save_campaign_details_data', 'ats_campaign_details_nonce' );

        // Lấy các giá trị meta đã lưu (nếu có)
        $status           = get_post_meta( $post->ID, '_ats_campaign_status', true );
        $start_datetime   = get_post_meta( $post->ID, '_ats_start_datetime', true );
        $end_datetime     = get_post_meta( $post->ID, '_ats_end_datetime', true );
        $button_text      = get_post_meta( $post->ID, '_ats_button_text', true );
        $button_url       = get_post_meta( $post->ID, '_ats_button_url', true );
        $is_recurring     = get_post_meta( $post->ID, '_ats_is_recurring', true );

        $display_start_datetime = $start_datetime ? date_i18n('d/m/Y H:i', strtotime($start_datetime)) : '';
        $display_end_datetime   = $end_datetime ? date_i18n('d/m/Y H:i', strtotime($end_datetime)) : '';

        ?>
        <table class="form-table ats-metabox-table">
            <tbody>
                <tr>
                    <th><label for="_ats_campaign_status_toggle"><?php esc_html_e( 'Trạng thái chiến dịch:', ATS_TEXT_DOMAIN ); ?></label></th>
                    <td>
                        <label class="ats-toggle-switch">
                            <input type="checkbox" id="_ats_campaign_status_toggle" name="_ats_campaign_status" value="on" <?php checked( $status, 'on' ); ?>>
                            <span class="ats-toggle-slider"></span>
                        </label>
                        <p class="description"><?php esc_html_e( 'Bật để kích hoạt chiến dịch này.', ATS_TEXT_DOMAIN ); ?></p>
                    </td>
                </tr>

                <tr> <th><label for="_ats_start_datetime"><?php esc_html_e( 'Thời gian bắt đầu:', ATS_TEXT_DOMAIN ); ?></label></th>
                    <td>
                        <input type="text" id="_ats_start_datetime" name="_ats_start_datetime" value="<?php echo esc_attr( $display_start_datetime ); ?>" class="ats-datetimepicker medium-text" placeholder="dd/mm/yyyy HH:MM">
                        <p class="description"><?php esc_html_e( 'Chọn ngày và giờ bắt đầu chiến dịch.', ATS_TEXT_DOMAIN ); ?></p>
                    </td>
                </tr>
                <tr> <th><label for="_ats_end_datetime"><?php esc_html_e( 'Thời gian kết thúc:', ATS_TEXT_DOMAIN ); ?></label></th>
                    <td>
                        <input type="text" id="_ats_end_datetime" name="_ats_end_datetime" value="<?php echo esc_attr( $display_end_datetime ); ?>" class="ats-datetimepicker medium-text" placeholder="dd/mm/yyyy HH:MM">
                        <p class="description"><?php esc_html_e( 'Chọn ngày và giờ kết thúc chiến dịch.', ATS_TEXT_DOMAIN ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th><label for="_ats_button_text"><?php esc_html_e( 'Chữ trên nút:', ATS_TEXT_DOMAIN ); ?></label></th>
                    <td>
                        <input type="text" id="_ats_button_text" name="_ats_button_text" value="<?php echo esc_attr( $button_text ); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e( 'Văn bản hiển thị trên nút kêu gọi hành động (ví dụ: Xem tất cả).', ATS_TEXT_DOMAIN ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th><label for="_ats_button_url"><?php esc_html_e( 'Đường dẫn nút:', ATS_TEXT_DOMAIN ); ?></label></th>
                    <td>
                        <input type="url" id="_ats_button_url" name="_ats_button_url" value="<?php echo esc_attr( $button_url ); ?>" class="regular-text" placeholder="https://">
                        <p class="description"><?php esc_html_e( 'Đường dẫn khi người dùng nhấp vào nút.', ATS_TEXT_DOMAIN ); ?></p>
                    </td>
                </tr>

                <tr class="ats-recurring-field">
                    <th><label for="_ats_is_recurring"><?php esc_html_e( 'Tự động lặp lại:', ATS_TEXT_DOMAIN ); ?></label></th>
                    <td>
                        <label class="ats-toggle-switch">
                             <input type="checkbox" id="_ats_is_recurring" name="_ats_is_recurring" value="on" <?php checked( $is_recurring, 'on' ); ?>>
                             <span class="ats-toggle-slider"></span>
                        </label>
                        <p class="description"><?php esc_html_e( 'Nếu được chọn, sau khi chiến dịch kết thúc, nó sẽ được lên lịch lại.', ATS_TEXT_DOMAIN ); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }
}
/*
-----------------------------------------------------------------------------------
 Ghi chú phiên bản và cập nhật:
-----------------------------------------------------------------------------------
 *
 * Phiên bản 1.0.2.0 (25/05/2025)
 * - Loại bỏ class 'ats-datetime-fields-fixed' khỏi các <tr> chứa trường Thời gian bắt đầu/kết thúc
 * để đảm bảo chúng luôn hiển thị, khắc phục lỗi bị ẩn do CSS.
 *
 * Phiên bản 1.0.1.1 (25/05/2025)
 * - Thêm trường "Chữ trên nút" (_ats_button_text) và "Đường dẫn nút" (_ats_button_url) dựa trên hình ảnh.
 * - Loại bỏ trường "Loại Lịch trình" và các trường thời gian hàng ngày (_ats_start_time_daily, _ats_end_time_daily)
 * để phù hợp với giao diện mới chỉ có Thời gian bắt đầu và Thời gian kết thúc.
 * - Cập nhật định dạng hiển thị cho Flatpickr thành 'd/m/Y H:i' trong PHP (giá trị thực tế của input).
 * - Đổi tên label "Lặp lại chiến dịch" thành "Tự động lặp lại".
 *
 * (Các phiên bản cũ hơn được lược bỏ trong ghi chú này)
 */
