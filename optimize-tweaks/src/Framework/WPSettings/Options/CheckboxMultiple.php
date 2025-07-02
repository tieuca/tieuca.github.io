<?php
/**
 * Lớp định nghĩa loại tùy chọn Checkbox Multiple.
 * Cho phép người dùng chọn nhiều giá trị từ một danh sách checkbox.
 *
 * @package     WPSettings
 * @subpackage  Framework/WPSettings/Options
 * @since       1.0.0
 * @author      TieuCA
 */
namespace TieuCA\WPSettings\Options;

use TieuCA\WPSettings\Enqueuer;
use TieuCA\WPSettings\WPSettings;
use TieuCA\WPSettings\Helpers;


class CheckboxMultiple extends OptionAbstract {
    /**
     * Tên file view để render.
     * @var string
     */
    public $view = 'checkbox-multiple';

    /**
     * Hàm khởi tạo.
     * Đăng ký hook để tải script cần thiết.
     * @param object $section Đối tượng section cha.
     * @param array $args Các tham số cấu hình.
     */
    public function __construct($section, $args = []) {
        add_action('az_settings_before_render_settings_page', [$this, 'enqueue']);
        parent::__construct($section, $args);
    }

    /**
     * Sửa đổi thuộc tính 'name' để HTML có thể nhận nhiều giá trị dạng mảng.
     * Ví dụ: 'my_option_name[]'
     * @return string
     */
    public function get_name_attribute() {
        $name = parent::get_name_attribute();
        return "{$name}[]";
    }

    /**
     * Làm sạch giá trị đầu vào, đảm bảo nó luôn là một mảng.
     * @param mixed $value
     * @return array
     */
    public function sanitize($value) {
        return (array) $value;
    }
    
    /**
     * Tải đoạn mã JavaScript cần thiết cho chức năng "Chọn tất cả" / "Bỏ chọn".
     *
     * [FIX] Đã sửa lỗi sử dụng sai script handle.
     * Giờ đây nó sẽ lấy handle chính xác từ đối tượng settings và gắn inline script vào đó.
     */
    public function enqueue() {
        Enqueuer::add('wps-checkbox-multiple', function () {

            $main_script_handle = Helpers::normalizeString(WPSettings::FRAMEWORK_NAME);
            
            $script = "
                jQuery(function($) {
                    $('.select-all').on('click', function(e) {
                        e.preventDefault();
                        $(this).closest('td').find('input[type=\"checkbox\"]').prop('checked', true);
                    });
                    
                    $('.deselect').on('click', function(e) {
                        e.preventDefault();
                        $(this).closest('td').find('input[type=\"checkbox\"]').prop('checked', false);
                    });
                });
            ";
            wp_add_inline_script($main_script_handle, $script);
        });
    }
}
