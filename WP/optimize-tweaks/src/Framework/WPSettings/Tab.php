<?php
/**
 * Lớp Tab cho Framework Cài đặt AZ.
 *
 * Lớp này đại diện cho một tab riêng lẻ trong giao diện cài đặt của plugin.
 * Mỗi tab có thể chứa nhiều section khác nhau.
 *
 * @package     AZ_Settings
 * @subpackage  Framework/WPSettings
 * @since       1.0.0
 * @author      TieuCA
 */

namespace TieuCA\WPSettings;

use TieuCA\WPSettings\Traits\HasOptionLevel;

class Tab {
    use HasOptionLevel;

    /**
     * Tham chiếu đến đối tượng WPSettings chính.
     *
     * @var WPSettings
     */
    public $settings;

    /**
     * Tiêu đề của tab sẽ hiển thị trên giao diện.
     *
     * @var string
     */
    public $title;

    /**
     * Chuỗi định danh (slug) của tab, dùng trong URL.
     *
     * @var string
     */
    public $slug;

    /**
     * Mảng chứa tất cả các đối tượng Section thuộc về tab này.
     *
     * @var Section[]
     */
    public $sections = [];

    /**
     * Thuộc tính để kiểm soát việc hiển thị các nút submit.
     * Mặc định là true, tức là luôn hiển thị.
     *
     * @since 1.2.0
     * @var bool
     */
    public $show_submit_buttons = true;

    /**
     * Hàm khởi tạo cho lớp Tab.
     *
     * @since 1.0.0
     * @param WPSettings $settings Đối tượng WPSettings cha.
     * @param string     $title    Tiêu đề của tab.
     * @param string|null $slug    Chuỗi định danh (slug). Sẽ tự động tạo từ tiêu đề nếu để trống.
     */

    public function __construct($settings, $title, $slug = null){
        $this->settings = $settings;
        $this->title = $title;
        $this->slug = $slug;

        if ($this->slug === null) {
            $this->slug = sanitize_title($title);
        }
    }
    /**
     * Phương thức để khai báo việc ẩn/hiện các nút submit.
     * Cho phép sử dụng theo dạng chuỗi (method chaining).
     *
     * @since 1.2.0
     * @param bool $show Đặt là false để ẩn các nút.
     * @return $this Trả về chính đối tượng Tab để có thể chuỗi các phương thức.
     */
    public function show_submit_buttons($show = true){
        $this->show_submit_buttons = (bool) $show;
        return $this;
    }
    
    /**
     * Thêm một section mới vào tab.
     *
     * @since 1.0.0
     * @param string $title Tiêu đề của section.
     * @param array  $args  Các tham số tùy chọn cho section.
     * @return Section Đối tượng Section vừa được tạo.
     */
    public function add_section($title, $args = [])
    {
        $section = new Section($this, $title, $args);
        $this->sections[] = $section;
        return $section;
    }

    /**
     * Lấy danh sách các section được cấu hình để hiển thị dưới dạng link.
     *
     * @since 1.0.0
     * @return Section[] Mảng các đối tượng Section.
     */
    public function get_section_links()
    {
        return array_filter($this->sections, function ($section) {
            return $section->as_link;
        });
    }

    /**
     * Kiểm tra xem tab này có phải chỉ chứa các section dạng link hay không.
     *
     * @since 1.0.0
     * @return bool
     */
    public function contains_only_section_links()
    {
        return count($this->get_section_links()) === count($this->sections);
    }

    /**
     * Tìm một section trong tab dựa vào slug của nó.
     *
     * @since 1.0.0
     * @param string $name Slug của section cần tìm.
     * @return Section|false Đối tượng Section nếu tìm thấy, ngược lại là false.
     */
    public function get_section_by_name($name)
    {
        foreach ($this->sections as $section) {
            if ($section->slug == $name) {
                return $section;
            }
        }
        return false;
    }

    /**
     * Lấy section đang được kích hoạt (active) trong tab.
     * Chỉ áp dụng cho các tab có section dạng link.
     *
     * @since 1.0.0
     * @return Section|void
     */
    public function get_active_section()
    {
        if (empty($this->get_section_links())) {
            return;
        }

        if (isset($_REQUEST['section'])) {
            return $this->get_section_by_name($_REQUEST['section']);
        }

        if ($this->contains_only_section_links()) {
            return $this->sections[0];
        }
    }

    /**
     * Lấy danh sách các section sẽ được hiển thị trên trang.
     *
     * @since 1.0.0
     * @return Section[]
     */
    public function get_active_sections()
    {
        if (! isset($_REQUEST['section']) && $this->contains_only_section_links()) {
            return [$this->sections[0]];
        }

        return array_filter($this->sections, function ($section) {
            if (isset($_REQUEST['section'])) {
                return $section->as_link && $_REQUEST['section'] == $section->slug;
            }

            return ! $section->as_link;
        });
    }
}

/*
 * --- CHANGELOG ---
 *
 * 1.2.0 (2025-06-17):
 * - Added `show_submit_buttons` property and method to control the visibility of save/restore buttons per tab.
 * - Added comprehensive PHPDoc comments for all properties and methods.
 * - Added file header and changelog.
 *
 * 1.0.0 (Initial Version):
 * - Initial creation of the Tab class.
 */