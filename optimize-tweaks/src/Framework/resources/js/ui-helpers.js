/**
 * UI Helper Script - Refactored Version 3.0.0
 *
 * @description This script handles specific UI interactions using the Revealing Module Pattern (IIFE).
 * It manages clipboard, checkbox styling, and tab navigation.
 *
 * @version 2.0.0
 */
const UIHelpersModule = (function($) {

    // =================================================================
    // 1. THÀNH PHẦN PRIVATE (RIÊNG TƯ)
    // Các hàm này xử lý các chức năng giao diện cụ thể.
    // =================================================================

    /**
     * Xử lý chức năng sao chép vào clipboard.
     */
    function handleClipboard() {
        if (typeof ClipboardJS === 'undefined') {
            return;
        }

        const clipboard = new ClipboardJS('.clipboard');
        let successTimeout;

        clipboard.on('success', function(event) {
            const triggerElement = $(event.trigger);
            const successElement = $('.success', triggerElement.closest('.copy-to-clipboard-container'));

            event.clearSelection();
            clearTimeout(successTimeout);
            successElement.removeClass('hidden').addClass('visible');

            successTimeout = setTimeout(function() {
                successElement.removeClass('visible').addClass('hidden');
            }, 3000);

            // Thông báo cho các công nghệ hỗ trợ (ví dụ: trình đọc màn hình)
            if (typeof wp?.a11y?.speak === 'function') {
                wp.a11y.speak(wp.i18n.__( 'The content has been copied to your clipboard', 'az-settings' ));
            }
        });
    }

    /**
     * Xử lý hiệu ứng giao diện cho các checkbox kiểu toggle.
     */
    function handleCheckboxStyling() {
        $('input[type="checkbox"]').on('change', function() {
            const $span = $(this).closest('.components-form-toggle');
            // Thêm hoặc xóa class 'is-checked' để CSS có thể thay đổi giao diện
            $span.toggleClass('is-checked', $(this).is(':checked'));
        }).trigger('change'); // Kích hoạt sự kiện change ngay khi tải trang để đảm bảo giao diện đúng
    }

    /**
     * Xử lý logic chuyển đổi giữa các tab cài đặt.
     */
    function handleTabs() {
        const tabLinks = $('.nav-section a');
        const tabContents = $('.nav-tab-content .tab-content');

        if (!tabLinks.length) {
            return;
        }

        // Kích hoạt tab đầu tiên và hiển thị nội dung tương ứng
        tabContents.not(':first').hide();
        tabLinks.first().addClass('nav-tab-active');

        // Gán sự kiện click cho các tab
        tabLinks.on('click', function(e) {
            e.preventDefault();
            const $this = $(this);

            // Không làm gì nếu click vào tab đang active
            if ($this.hasClass('nav-tab-active')) {
                return;
            }

            // Bỏ active ở tất cả các tab và kích hoạt tab được click
            tabLinks.removeClass('nav-tab-active');
            $this.addClass('nav-tab-active');

            // Ẩn tất cả nội dung và chỉ hiển thị nội dung của tab được click
            tabContents.hide();
            $('#' + $this.data('section')).show();
        });
    }

    // =================================================================
    // 2. API CÔNG KHAI (PUBLIC)
    // Trả về một đối tượng chỉ chứa phương thức `init` công khai.
    // =================================================================
    return {
        /**
         * Phương thức khởi tạo công khai cho module giao diện.
         */
        init: function() {
            handleClipboard();
            handleCheckboxStyling();
            handleTabs();
            console.log("Module Giao diện (UI) đã được khởi tạo thành công.");
        }
    };

})(jQuery);