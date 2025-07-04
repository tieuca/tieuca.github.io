/**
 * Admin Settings Script - Refactored Version 3.0.0
 *
 * @description This script handles the admin settings page using the Revealing Module Pattern (IIFE)
 * for robust encapsulation and a clear public API. It relies on the global `showNotify` function.
 *
 * @version 2.0.0
 */
const settingsPageModule = (function($) {

    // =================================================================
    // 1. THÀNH PHẦN PRIVATE (RIÊNG TƯ)
    // Các biến và hàm này chỉ có thể được truy cập từ bên trong module.
    // Chúng được che giấu hoàn toàn với bên ngoài.
    // =================================================================

    let form = null;
    let saveButtons = null;
    let initialFormData = null;

    /**
     * Gán tất cả các sự kiện cần thiết.
     * Đây là một hàm private.
     */
    function bindEvents() {
        // Sự kiện cho các nút lưu
        saveButtons.on("click", (e) => handleSubmit(e));

        // Sự kiện cho thanh header và nút lưu cố định
        $('#main-header').sticky({ topSpacing: 0, zIndex: 100, stickyClass: 'sticky' });
        $(window).on("load scroll resize", handleFixedSaveButton);

        // Sự kiện khi nội dung trong TinyMCE editor thay đổi
        if (typeof tinymce !== "undefined") {
            tinymce.on("AddEditor", (e) => {
                e.editor.on("change", () => form.trigger("change"));
            });
        }

        // Xóa lớp 'error' và tooltip khi người dùng bắt đầu nhập liệu
        form.on('input focus', 'input.error, select.error, textarea.error', function() {
            $(this).removeClass('error').next('.tooltip.error').remove();
        });
    }

    /**
     * Xử lý sự kiện khi người dùng nhấn nút lưu.
     * Đây là phương thức điều phối chính, một hàm private.
     */
    function handleSubmit(e) {
        e.preventDefault();
        saveButtons.addClass('is-busy').prop('disabled', true).attr('aria-disabled', 'true');

        // Đồng bộ nội dung từ TinyMCE trước khi lấy dữ liệu
        if (typeof tinymce !== "undefined") {
            tinymce.triggerSave();
        }

        // Kiểm tra xem có thay đổi nào không
        const currentFormData = getFormData();
        if (initialFormData === currentFormData) {
            showNotify(azs_i18n.noChanges, "info");
            saveButtons.removeClass('is-busy').prop('disabled', false).attr('aria-disabled', 'false');
            return;
        }

        // Validate form
        const { isValid, errorMessages } = validateAll();
        if (!isValid) {
            showNotify(errorMessages.join('<br>'), "error");
            saveButtons.removeClass('is-busy').prop('disabled', false).attr('aria-disabled', 'false');

            const firstErrorField = form.find('.error').first();
            if (firstErrorField.length) {
                $('html, body').animate({
                    scrollTop: firstErrorField.offset().top - 150
                }, 500);
            }
            return;
        }

        // Nếu mọi thứ hợp lệ, tiến hành lưu cài đặt
        saveSettings();
    }

    /**
     * Gửi dữ liệu đến server bằng AJAX.
     * Sử dụng async/await để code dễ đọc và quản lý lỗi tốt hơn.
     * Đây là một hàm private.
     */
    async function saveSettings() {
        try {
            const response = await $.ajax({
                url: saveAjax.ajax_url,
                type: "POST",
                data: {
                    action: saveAjax.prefix + "_save_settings",
                    nonce: saveAjax.nonce,
                    settings: getFormData(),
                },
            });

            if (response.success) {
                const responseData = response.data || {};
                showNotify(azs_i18n.saveSuccess, "success");
                initialFormData = getFormData(); // Cập nhật trạng thái mới nhất

                if (responseData.reload === true) {
                    setTimeout(() => { window.location.reload(); }, 2000);
                }
            } else {
                showNotify(azs_i18n.saveError + (response.data.message || response.data), "error");
            }
        } catch (error) {
            showNotify(azs_i18n.ajaxError + (error.statusText || 'Unknown Error'), "error");
        } finally {
            saveButtons.removeClass('is-busy').prop('disabled', false).attr('aria-disabled', 'false');
        }
    }

    /**
     * Tổng hợp tất cả các hàm kiểm tra lỗi vào một nơi.
     * Đây là một hàm private.
     * @returns {object} - { isValid: boolean, errorMessages: array }
     */
    function validateAll() {
        $('.tooltip.error', form).remove();
        $('input.error, select.error, textarea.error', form).removeClass('error');

        const requiredValidation = validateRequiredFields();
        const constraintsValidation = validateConstraintFields();

        const allErrors = [...requiredValidation.errorMessages, ...constraintsValidation.errorMessages];
        const isFormValid = requiredValidation.isValid && constraintsValidation.isValid;

        return { isValid: isFormValid, errorMessages: allErrors };
    }

    /**
     * Kiểm tra các trường bắt buộc (required).
     * Đây là một hàm private.
     */
    function validateRequiredFields() {
        let isValid = true;
        let errorMessages = [];
        $('input[required], textarea[required], select[required]', form).each(function() {
            const field = $(this);
            // Chỉ validate các trường đang hiển thị
            if (field.is(':visible') && !field.val()?.trim()) {
                isValid = false;
                const label = $(`label[for="${field.attr('id')}"]`).text().trim() || field.attr('name');
                const errorMessage = azs_i18n.validationRequired.replace('%s', `<strong>${label}</strong>`);
                errorMessages.push(errorMessage);
                field.addClass('error').after(`<div class="tooltip error">${errorMessage}</div>`);
            }
        });
        return { isValid, errorMessages };
    }

    /**
     * Kiểm tra các ràng buộc min, max.
     * Đây là một hàm private.
     */
    function validateConstraintFields() {
        let isValid = true;
        let errorMessages = [];
        $('input[type="number"][min], input[type="number"][max]', form).each(function() {
            const field = $(this);
            if (!field.is(':visible') || !field.val()) return;

            const fieldValue = parseFloat(field.val());
            const min = parseFloat(field.attr('min'));
            const max = parseFloat(field.attr('max'));

            if ((!isNaN(min) && fieldValue < min) || (!isNaN(max) && fieldValue > max)) {
                isValid = false;
                const label = $(`label[for="${field.attr('id')}"]`).text().trim() || field.attr('name');
                let errorMessage = '';
                if (!isNaN(min) && !isNaN(max)) {
                    errorMessage = azs_i18n.validationMinMax.replace('%s', `<strong>${label}</strong>`).replace('%d', min).replace('%d', max);
                } else if (!isNaN(min)) {
                    errorMessage = azs_i18n.validationMin.replace('%s', `<strong>${label}</strong>`).replace('%d', min);
                } else {
                    errorMessage = azs_i18n.validationMax.replace('%s', `<strong>${label}</strong>`).replace('%d', max);
                }
                errorMessages.push(errorMessage);
                field.addClass('error').after(`<div class="tooltip error">${errorMessage}</div>`);
            }
        });
        return { isValid, errorMessages };
    }

    /**
     * Lấy toàn bộ dữ liệu form.
     * Đây là một hàm private.
     * @returns {string} - Dữ liệu form dưới dạng query string.
     */
    function getFormData() {
        let formData = form.serializeArray();
        $('input[type="checkbox"]', form).each(function() {
            const checkbox = $(this);
            let found = false;
            for (let i = 0; i < formData.length; i++) {
                if (formData[i].name === checkbox.attr('name')) {
                    found = true;
                    break;
                }
            }
            if (!found) {
                formData.push({ name: checkbox.attr('name'), value: '0' });
            }
        });
        return $.param(formData);
    }

    /**
     * Hiển thị hoặc ẩn nút lưu cố định ở cuối trang.
     * Đây là một hàm private.
     */
    function handleFixedSaveButton() {
        const fixedSaveButton = $(".fixed-save-button");
        if (form.length === 0) return;

        const formBottom = form.offset().top + form.height();
        const windowBottom = $(window).scrollTop() + $(window).height();

        if (formBottom > windowBottom) {
            fixedSaveButton.addClass("visible");
        } else {
            fixedSaveButton.removeClass("visible");
        }
    }


    // =================================================================
    // 2. API CÔNG KHAI (PUBLIC)
    // Đây là đối tượng được trả về, chỉ chứa những phương thức mà bên ngoài
    // được phép gọi. Trong trường hợp này, chỉ có `init`.
    // =================================================================
    return {
        /**
         * Phương thức khởi tạo công khai.
         * Đây là điểm bắt đầu duy nhất để tương tác với module.
         */
        init: function() {
            console.log('admin.js init is starting...');
            form = $("#" + saveAjax.prefix + "-form");
            saveButtons = $("#submit, #" + saveAjax.prefix + "-fixed-submit");
        
            if (form.length === 0) {
                console.warn("Settings form not found. Admin script will not run.");
                return;
            }
        
            const captureInitialData = () => {
                initialFormData = getFormData();
                console.log("Framework is ready. Initial form data captured.");
            };
            
            // Kiểm tra xem cờ đã được dựng chưa (tức là conditional-logic đã chạy xong chưa)
            if (window.azSettingsFrameworkReady) {
                // Nếu rồi, chạy ngay lập tức để không bỏ lỡ tín hiệu
                captureInitialData();
            } else {
                // Nếu chưa, hãy lắng nghe tín hiệu
                jQuery(document).on('az-settings:ready', captureInitialData);
            }
            
            bindEvents();
        
            console.log("Module Cài đặt (IIFE) đã được khởi tạo thành công.");
        }
    };

})(jQuery);