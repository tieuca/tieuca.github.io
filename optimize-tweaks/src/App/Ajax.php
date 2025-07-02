<?php
/**
 * Xử lý các yêu cầu AJAX dành riêng cho ứng dụng AZ_Settings.
 *
 * @package     AZ_Settings
 * @subpackage  App
 * @since       1.0.0
 */
namespace OXT\App;

class Ajax {

    /**
     * Tham chiếu đến đối tượng WPSettings chính.
     *
     * @var \TieuCA\WPSettings\WPSettings
     */
    private $settings_instance;

    /**
     * Hàm khởi tạo.
     */
    public function __construct() {
        // Lấy capability từ config để kiểm tra quyền
        $this->capability = Config::get('capability', 'manage_options');

        // Đăng ký các hook cho AJAX
        add_action('wp_ajax_OXT_activate_license', [$this, 'handle_activate_license']);
        add_action('wp_ajax_OXT_deactivate_license', [$this, 'handle_deactivate_license']);
    }

    /**
     * Xử lý yêu cầu kích hoạt license qua AJAX.
     */
    public function handle_activate_license() {
        check_ajax_referer('license-nonce', 'nonce');

        if (!current_user_can($this->capability)) {
            wp_send_json_error(['message' => __('Permission denied.', 'optimize-tweaks')], 403);
        }

        $key = isset($_POST['key']) ? sanitize_text_field($_POST['key']) : '';

        if (empty($key)) {
            wp_send_json_error(['message' => __('Please enter a license key.', 'optimize-tweaks')]);
        }

        $is_activated = License::activate($key);

        if ($is_activated) {
            wp_send_json_success([
                'message' => __('License activated successfully!', 'optimize-tweaks'),
                'masked_key' => License::get_masked_key()
            ]);
        } else {
            wp_send_json_error(['message' => __('Invalid or expired license key.', 'optimize-tweaks')]);
        }
    }

    /**
     * Xử lý yêu cầu vô hiệu hóa license qua AJAX.
     */
    public function handle_deactivate_license() {
        check_ajax_referer('license-nonce', 'nonce');
        
        if (!current_user_can($this->capability)) {
            wp_send_json_error(['message' => __('Permission denied.', 'optimize-tweaks')], 403);
        }

        License::deactivate();

        wp_send_json_success(['message' => __('License deactivated.', 'optimize-tweaks')]);
    }
}
