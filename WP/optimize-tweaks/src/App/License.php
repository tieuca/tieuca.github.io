<?php
/**
 * License Handler for AZ Settings Plugin using a Hash-based system.
 * This version includes hardened checks to make tampering more difficult.
 *
 * @package     AZ_Settings
 * @subpackage  App
 * @since       1.5.0
 * @version     1.5.0
 * @author      TieuCA
 */
namespace OXT\App;

class License {

    const STATUS_OPTION_NAME = 'OXT_license_status';
    const KEY_OPTION_NAME    = 'OXT_license_key';

    /**
     * [QUAN TRỌNG] Đặt Mã Bí Mật (Secret Salt) của bạn tại đây.
     * @return string The secret salt.
     */
    private static function get_secret_salt() {
        return 'HEROKI';
    }

    /**
     * Tạo key dự kiến dựa trên tên miền và mã bí mật.
     * @return string The expected license key hash.
     */
    private static function generate_expected_key() {
        $domain = strtolower(preg_replace('/^www\./i', '', $_SERVER['SERVER_NAME']));
        $salt = self::get_secret_salt();

        if (empty($domain) || strpos($salt, 'CHANGE-THIS') !== false) {
            error_log('Optimize X Tweaks: Secret Salt is not configured or domain is empty.');
            return '';
        }
        
        $data_to_hash = "{$domain}::{$salt}";
        $hash = hash('sha256', $data_to_hash);
        
        return strtoupper(substr($hash, 0, 25));
    }

    /**
     * Kích hoạt license.
     * @param string $submitted_key Key do người dùng cung cấp.
     * @return bool True nếu kích hoạt thành công.
     */
    public static function activate($submitted_key) {
        if (self::verify_key($submitted_key)) {
            update_option(self::STATUS_OPTION_NAME, 'valid');
            update_option(self::KEY_OPTION_NAME, trim($submitted_key));
            return true;
        }
        
        self::deactivate();
        return false;
    }

    /**
     * [MỚI] Tách logic xác thực key ra một hàm riêng.
     * @param string $key_to_check
     * @return bool
     */
    private static function verify_key($key_to_check) {
        if (empty($key_to_check)) {
            return false;
        }

        $normalized_key = strtoupper(str_replace('-', '', $key_to_check));
        $expected_key = self::generate_expected_key();
        
        if (empty($expected_key)) {
            return false;
        }

        return hash_equals($expected_key, $normalized_key);
    }

    /**
     * Vô hiệu hóa license.
     */
    public static function deactivate() {
        delete_option(self::STATUS_OPTION_NAME);
        delete_option(self::KEY_OPTION_NAME);
    }

    /**
     * [NÂNG CẤP] Kiểm tra trạng thái license một cách an toàn hơn.
     * @return bool
     */
    public static function is_active() {
        // 1. Kiểm tra nhanh trạng thái đã lưu. Nếu không có, chắc chắn là không active.
        if (get_option(self::STATUS_OPTION_NAME) !== 'valid') {
            return false;
        }

        // 2. Lấy key đã lưu trong DB.
        $stored_key = self::get_raw_key();
        if (empty($stored_key)) {
            // Trạng thái là 'valid' nhưng không có key? Bất thường -> Deactivate.
            self::deactivate();
            return false;
        }
        
        // 3. Xác thực lại key đã lưu với domain hiện tại.
        // Đây là bước "hardening", làm cho việc bypass khó hơn.
        if (self::verify_key($stored_key)) {
            return true;
        } else {
            // Key không còn hợp lệ với domain hiện tại (có thể do đổi domain).
            self::deactivate();
            return false;
        }
    }

    private static function get_raw_key() {
        return get_option(self::KEY_OPTION_NAME, '');
    }
    
    public static function get_masked_key() {
        $key = self::get_raw_key();
        if (empty($key)) {
            return '';
        }
        $parts = explode('-', $key);
        if (count($parts) > 2) {
            return $parts[0] . '-...-' . end($parts);
        }
        return substr($key, 0, 5) . '...';
    }
}