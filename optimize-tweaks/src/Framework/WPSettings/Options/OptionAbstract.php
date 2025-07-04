<?php
/**
 * Lớp trừu tượng cơ sở cho tất cả các loại tùy chọn.
 * Chứa các logic chung về việc lấy giá trị, tên, id, và các thuộc tính khác.
 *
 * @package     AZ_Settings
 * @subpackage  Framework/WPSettings/Options
 * @since       1.0.0
 * @author      TieuCA
 */
namespace TieuCA\WPSettings\Options;

use Adbar\Dot;
use TieuCA\WPSettings\Helpers;

abstract class OptionAbstract
{
    /**
     * Tham chiếu đến đối tượng Section cha.
     * @since 1.0.0
     * @var \TieuCA\WPSettings\Section
     */
    public $section;

    /**
     * Mảng chứa các tham số cấu hình cho tùy chọn.
     * @since 1.0.0
     * @var array
     */
    public $args = [];

    /**
     * Tên của file view sẽ được sử dụng để render tùy chọn.
     * @since 1.0.0
     * @var string
     */
    public $view;

    /**
     * Hàm khởi tạo cho lớp tùy chọn.
     * @since 1.0.0
     * @param \TieuCA\WPSettings\Section $section Đối tượng Section cha.
     * @param array $args Mảng các tham số cấu hình.
     */
    public function __construct($section, $args = []) {
        $this->section = $section;
        $this->args = $args;
    }

    /**
     * Render HTML cho tùy chọn bằng cách gọi file view tương ứng.
     * @since 1.0.0
     * @return string HTML output.
     */
    public function render() {
        ob_start();
        Helpers::view('options/' . $this->view, ['option' => $this]);
        $field_content = ob_get_clean();

        return Helpers::view('options/field-wrapper', ['option' => $this, 'field_content' => $field_content]);
    }

    /**
     * Get a sanitized, kebab-case CSS class name for the option type.
     * Combines high performance with WordPress standards for safety.
     *
     * @return string
     */
    public function get_type_class(): string {
        $fullClass = get_class($this);
        $className = substr($fullClass, strrpos($fullClass, '\\') + 1);
        $kebab = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $className));
        return 'type-' . sanitize_html_class($kebab);
    }

    /**
     * Hàm tiện ích để render mô tả của trường.
     */
    public function render_description()
    {
        if ($description = $this->get_arg('description')) {
            // Xử lý trường hợp đặc biệt cho loại 'number' (đơn vị thường đi liền sau)
            if ($this->get_arg('type') === 'number') {
                 echo ' ' . wp_kses_post($description);
            } else {
                echo '<p class="description">' . wp_kses_post($description) . '</p>';
            }
        }
    }
    
    /**
     * Hàm tiện ích để render thông báo lỗi của trường.
     */
    public function render_error()
    {
        if ($error = $this->has_error()) {
            echo '<div class="wps-error-feedback">' . esc_html($error) . '</div>';
        }
    }
    
    /**
     * Kiểm tra xem tùy chọn này có thông báo lỗi nào không.
     * @since 1.0.0
     * @return string|void Thông báo lỗi nếu có.
     */
    public function has_error() {
        return $this->section->tab->settings->errors->get($this->get_arg('name'));
    }

    /**
     * Làm sạch giá trị của tùy chọn trước khi lưu vào database.
     * Mặc định sử dụng sanitize_text_field. Các lớp con có thể ghi đè phương thức này.
     * @since 1.0.0
     * @param mixed $value Giá trị cần làm sạch.
     * @return string Giá trị đã được làm sạch.
     */
    public function sanitize($value) {
        return sanitize_text_field($value);
    }

    /**
     * Xác thực giá trị của tùy chọn.
     * @since 1.0.0
     * @param mixed $value Giá trị cần xác thực.
     * @return bool True nếu hợp lệ.
     */
    public function validate($value) {
        return true;
    }

    /**
     * Lấy một tham số cấu hình cụ thể từ mảng $args.
     * @since 1.0.0
     * @param string $key Khóa của tham số cần lấy.
     * @param mixed $fallback Giá trị trả về mặc định nếu không tìm thấy.
     * @return mixed Giá trị của tham số.
     */
    public function get_arg($key, $fallback = null) {
        if (empty($this->args[$key])) {
            return $fallback;
        }

        if (\is_callable($this->args[$key])) {
            return $this->args[$key]();
        }

        return $this->args[$key];
    }

    /**
     * Lấy nhãn (label) của tùy chọn.
     * @since 1.0.0
     * @return string Nhãn đã được làm sạch.
     */
    public function get_label() {
        $label = \esc_attr($this->get_arg('label'));
        
        // Tự động thêm nhãn PRO vào cuối label nếu đây là tính năng trả phí.
        if ($this->is_pro_feature()) {
            $label .= ' <pro>PRO</pro>';
        }

        return $label;
    }

    /**
     * Lấy tên (key) của tùy chọn.
     * @since 1.0.0
     * @return string
     */
    public function get_name() {
        return $this->get_arg('name');
    }

    /**
     * Lấy mảng cấu hình CSS.
     * @since 1.0.0
     * @return array
     */
    public function get_css() {
        return $this->get_arg('css', []);
    }

    /**
     * Xây dựng đường dẫn (path) để truy cập giá trị của tùy chọn trong mảng options lớn.
     * Sử dụng "dot notation". Ví dụ: 'tab_slug.section_slug.option_name'
     * @since 1.0.0
     * @return string
     */
    public function get_option_key_path() {
        $keys = [];

        if ($this->section->tab->is_option_level()) {
            $keys[] = str_replace('-', '_', $this->section->tab->slug);
        }

        if ($this->section->is_option_level()) {
            $keys[] = str_replace('-', '_', $this->section->slug);
        }

        $keys[] = $this->get_arg('name');
        return implode('.', $keys);
    }
    
    /**
     * Lấy thuộc tính 'name' cho thẻ input, đã được định dạng cho mảng HTML.
     * Ví dụ: 'option_group[tab_slug][section_slug][option_name]'
     * @since 1.0.0
     * @return string
     */
    public function get_name_attribute() {
        $keys = explode('.', $this->get_option_key_path());
        $wrapped = array_map(function ($key) {
            return '['.$key.']';
        }, $keys);
        $inputName = implode('', $wrapped);
        return $this->section->tab->settings->option_name.$inputName;
    }
    
    /**
     * Lấy thuộc tính ID cho thẻ HTML.
     * @since 1.0.0
     * @return string
     */
    public function get_id_attribute(): string {
        if ($this->get_arg('id')) {
            return sanitize_html_class($this->get_arg('id'));
        }
    
        $name = $this->get_name_attribute(); // ví dụ: optimize-tweaks_option[meta][signature]
        $id = str_replace(['[', ']', '.'], '_', $name); // chuyển về id-friendly
        return sanitize_html_class(rtrim($id, '_'));
    }

    /**
     * Lấy thuộc tính class cho thẻ input.
     * @since 1.0.0
     * @return string|null
     */
    public function get_input_class_attribute() {
        $class = $this->get_css()['input_class'] ?? null;
        return ! empty($class) ? esc_attr($class) : null;
    }

    /**
     * Lấy thuộc tính class cho thẻ label.
     * @since 1.0.0
     * @return string|null
     */
    public function get_label_class_attribute() {
        $classes = [];
        $css_args = $this->get_css();

        // Lấy class tùy chỉnh từ mảng 'css'
        if (!empty($css_args['label_class'])) {
            $classes[] = $css_args['label_class'];
        }

        // Kiểm tra nếu trường được khai báo là 'required' => true
        if ($this->get_arg('required') === true) {
            $classes[] = 'is-required'; // Dùng chung class để có dấu * đỏ
        }
        
        // Trả về chuỗi class nếu có
        return !empty($classes) ? esc_attr(implode(' ', $classes)) : null;
    }

    /**
     * Tạo chuỗi class cho thẻ <tr>.
     * Phương thức này giờ sẽ tổng hợp tất cả các class cần thiết một cách chính xác.
     *
     * @return string The space-separated class names.
     */
    public function get_row_classes() {
        $classes = [];

        // Lấy class tùy chỉnh từ mảng 'css'
        if (!empty($this->get_css()['class'])) {
            $classes[] = $this->get_css()['class'];
        }

        // Kiểm tra nếu trường được khai báo là 'pro' => true
        if ($this->is_pro_feature() && !$this->is_pro_user()) {
            return 'pro';
        }
        
        return esc_attr(implode(' ', $classes));
    }

    /**
     * Hàm kiểm tra xem tùy chọn này có được đánh dấu là PRO hay không.
     * @return bool
     */
    public function is_pro_feature() {
        return $this->get_arg('is_pro', false) === true;
    }
    
    /**
     * Hàm kiểm tra xem người dùng có phải là người dùng PRO hay không.
     * @return bool
     */
    public function is_pro_user() {
        return $this->section->tab->settings->config['is_pro'] ?? false;
    }

    /**
     * Lấy giá trị mặc định của tùy chọn.
     * @since 1.0.0
     * @return mixed
     */
    public function get_default_value() {
        return $this->args['default'] ?? null;
    }

    /**
     * Lấy giá trị hiện tại của tùy chọn từ database.
     * Nếu không có giá trị, sẽ trả về giá trị mặc định.
     * @since 1.0.0
     * @return mixed
     */
    public function get_value_attribute() {
        $options = get_option($this->section->tab->settings->option_name);
        $dot = new Dot($options);
        return $dot->get($this->get_option_key_path(), $this->get_default_value());
    }
    
    /**
     * [MỚI] Tạo thuộc tính data-condition nếu được định nghĩa.
     * @return string - The HTML data attribute.
     */
    public function get_condition_attribute() {
        $condition = $this->get_arg('condition');
        if (empty($condition)) {
            return '';
        }
        return 'data-condition="' . esc_attr(json_encode($condition)) . '"';
    }
}