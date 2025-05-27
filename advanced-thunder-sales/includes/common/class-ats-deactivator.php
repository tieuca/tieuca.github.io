<?php
/**
 * File: class-ats-deactivator.php
 *
 * Thực hiện các tác vụ khi plugin Advanced Thunder Sales bị vô hiệu hóa.
 * Ví dụ: flush rewrite rules, dọn dẹp các cron job (nếu có).
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
 * Class ATS_Deactivator.
 *
 * Xử lý các tác vụ khi vô hiệu hóa plugin.
 */
class ATS_Deactivator {

    /**
     * Phương thức chính được gọi khi plugin bị vô hiệu hóa.
     *
     * @since   1.0.1
     */
    public static function deactivate() {
        // Xóa các cron job đã đăng ký bởi plugin (nếu có)
        // wp_clear_scheduled_hook('ats_my_cron_hook');

        flush_rewrite_rules();
    }

} // Kết thúc class ATS_Deactivator

/*
-----------------------------------------------------------------------------------
 Ghi chú phiên bản và cập nhật:
-----------------------------------------------------------------------------------
 *
 * Phiên bản 1.0.1 (25/05/2025)
 * - Tạo file và class ATS_Deactivator.
 * - Thêm hàm flush_rewrite_rules() khi vô hiệu hóa.
 *
 */
