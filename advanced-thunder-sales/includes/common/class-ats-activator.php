<?php
/**
 * File: class-ats-activator.php
 *
 * Thực hiện các tác vụ khi plugin Advanced Thunder Sales được kích hoạt.
 * Ví dụ: flush rewrite rules, tạo các bảng CSDL (nếu cần), thiết lập options mặc định.
 *
 * @package     AdvancedThunderSales
 * @subpackage  Includes/Common
 * @since       1.0.1
 * @version     1.0.1
 * @lastupdate  25/05/2025
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class ATS_Activator.
 *
 * Xử lý các tác vụ khi kích hoạt plugin.
 */
class ATS_Activator {

    /**
     * Phương thức chính được gọi khi plugin được kích hoạt.
     *
     * @since   1.0.1
     */
    public static function activate() {
        // Đảm bảo Custom Post Type đã được đăng ký (thường là trên hook 'init')
        // trước khi flush rewrite rules.
        // Việc gọi flush ở đây giúp các permalink của CPT mới hoạt động ngay.
        // Tuy nhiên, CPT nên được đăng ký trong class riêng và hook vào 'init'.
        // Nếu CPT 'ats_campaign' được đăng ký bởi ATS_Campaign_CPT::register_post_type() trên 'init',
        // thì việc flush ở đây là hợp lý.

        // Thiết lập options mặc định nếu cần
        // update_option('ats_some_default_option', 'defaultValue');

        flush_rewrite_rules();
    }

} // Kết thúc class ATS_Activator

/*
-----------------------------------------------------------------------------------
 Ghi chú phiên bản và cập nhật:
-----------------------------------------------------------------------------------
 *
 * Phiên bản 1.0.1 (25/05/2025)
 * - Tạo file và class ATS_Activator.
 * - Thêm hàm flush_rewrite_rules() khi kích hoạt để hỗ trợ CPT mới.
 *
 */
