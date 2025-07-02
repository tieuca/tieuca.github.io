<?php
/**
 * Custom HTML Option Type.
 *
 * @package     AZ_Settings
 * @subpackage  Framework/WPSettings/Options
 * @since       1.1.0
 * @version     1.0.0
 * @author      TieuCA
 *
 * Date: 2025-06-16
 */
namespace TieuCA\WPSettings\Options;

class HTML extends OptionAbstract
{
    /**
     * Ghi đè phương thức render để thực thi một callback tùy chỉnh.
     *
     * Phương thức này không sử dụng tệp view. Thay vào đó, nó tìm một
     * callback có tên 'render' trong mảng tham số và thực thi nó.
     * Điều này cho phép Plugin mẹ có thể "tiêm" mã HTML tùy chỉnh vào trang
     * cài đặt một cách linh hoạt.
     *
     * @since 1.1.0
     */
    public function render()
    {
        // Lấy callback từ mảng tham số
        $render_callback = $this->get_arg('render');

        // Kiểm tra xem nó có phải là một hàm hợp lệ hay không trước khi gọi
        if (is_callable($render_callback)) {
            // Gọi hàm và truyền đối tượng option hiện tại vào
            call_user_func($render_callback, $this);
        }
    }
}

/*
 * --- CHANGELOG ---
 *
 * 1.0.0 (2025-06-16):
 * - Initial creation of the HTML option type.
 * - Implemented a `render` method that executes a custom callback.
 */
