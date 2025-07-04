/**
 * Notifications and Utilities Script - Refactored Version
 *
 * @description Provides utility functions like notifications and a framework for future AJAX actions.
 * Organized using the Module Pattern for better structure and maintainability.
 *
 * @version 3.0.0
 */
const AppUtils = (function(window, $) {
    'use strict';

    const module = {

        /**
         * Hiển thị thông báo sử dụng thư viện iziToast hoặc SweetAlert2.
         * @param {string} message - Nội dung thông báo.
         * @param {string} type - Loại thông báo ('info', 'success', 'error', 'warning', 'confirm').
         * @param {object} options - Các tùy chọn bổ sung cho thư viện.
         * @param {string} library - Tên thư viện ('izitoast' hoặc 'sweetalert2').
         * @param {string} title - Tiêu đề thông báo.
         * @returns {Promise|void} - Trả về một Promise nếu là thông báo xác nhận (confirm).
         */
        showNotify: function(message, type = 'info', options = {}, library = null, title = null) {
            library = library || 'izitoast';
            if (type === 'confirm') library = 'sweetalert2';

            if (library === 'izitoast') {
                this._showIziToast(message, type, options, title);
            } else if (library === 'sweetalert2') {
                return this._showSweetAlert(message, type, options, title);
            } else {
                console.error('Invalid notification library:', library);
            }
        },

        _showIziToast: function(message, type, options, title) {
            const validTypes = ['success', 'error', 'warning', 'info'];
            if (typeof iziToast === 'undefined' || !validTypes.includes(type)) {
                console.error('iziToast library not loaded or invalid type:', type);
                return;
            }

            const iziToastOptions = {
                title: title || '',
                message: message,
                position: "topRight",
                timeout: 5000,
                ...options,
            };
            iziToast[type](iziToastOptions);
        },

        _showSweetAlert: function(message, type, options, title) {
            if (typeof Swal === 'undefined') {
                console.error('SweetAlert2 library is not loaded.');
                return;
            }
            
            const defaultOptions = {
                title: title || type.charAt(0).toUpperCase() + type.slice(1),
                text: message,
                icon: type === 'confirm' ? 'question' : type,
                position: type === 'confirm' ? 'center' : this._convertPositionToSweetAlert(options.position),
                timer: type === 'confirm' ? null : 5000,
                timerProgressBar: type !== 'confirm',
                toast: type !== 'confirm',
                showConfirmButton: type === 'confirm',
                allowOutsideClick: false,
                showCloseButton: true,
                ...options,
            };

            if (type === 'confirm') {
                defaultOptions.showCancelButton = true;
                defaultOptions.confirmButtonText = azs_i18n.yes || 'Yes';
                defaultOptions.cancelButtonText = azs_i18n.no || 'No';
                return Swal.fire(defaultOptions).then(result => result.isConfirmed);
            }

            Swal.fire(defaultOptions);
        },

        _convertPositionToSweetAlert: function(position) {
            const positionMap = {
                'topRight': 'top-end', 'topLeft': 'top-start', 'topCenter': 'top',
                'bottomRight': 'bottom-end', 'bottomLeft': 'bottom-start', 'bottomCenter': 'bottom',
                'center': 'center',
            };
            return positionMap[position] || 'top-end';
        },

        /**
         * Hàm thực hiện AJAX action chung.
         * @param {string} action - Tên action của WordPress.
         * @param {object} options - Các tùy chọn cho thông báo.
         * @param {string} library - Thư viện thông báo sẽ sử dụng.
         * @param {object} inputData - Dữ liệu bổ sung cần gửi.
         */
        performAjaxAction: function(action, options = {}, library = null, inputData = {}) {
            $.ajax({
                url: wpSettingsAjax.ajax_url,
                type: 'POST',
                data: {
                    action: action,
                    nonce: wpSettingsAjax.nonce,
                    ...inputData,
                },
                success: (response) => {
                    if (response.success) {
                        this.showNotify(response.data.message, response.data.type, {
                            title: response.data.title || options.title || '',
                            position: response.data.position || options.position || 'topRight',
                            timer: response.data.timer || options.timer || 5000,
                        }, library);
                    } else {
                        const errorMessage = response.data || azs_i18n.ajaxFailed || 'An error occurred.';
                        this.showNotify(errorMessage, 'error');
                    }
                },
                error: () => {
                    this.showNotify(azs_i18n.ajaxFailed || 'AJAX request failed.', 'error');
                },
            });
        },

        /**
         * Khởi tạo các sự kiện cho các nút có thuộc tính data-action.
         * Các thuộc tính data được hỗ trợ:
         * - data-action (bắt buộc): Tên của WordPress AJAX action.
         * - data-message (bắt buộc): Nội dung thông báo.
         * - data-type ('confirm' | ...): Loại hành động, 'confirm' sẽ hiển thị hộp thoại xác nhận.
         * - data-library ('izitoast' | 'sweetalert2'): Thư viện thông báo sẽ sử dụng.
         * - data-title (tùy chọn): Tiêu đề của thông báo.
         * - data-input-target (tùy chọn): Selector của một trường input để lấy dữ liệu.
         * - data-input-key (tùy chọn): Tên key cho dữ liệu lấy từ input-target.
         */
        initActionButtons: function() {
            $(document).on('click', 'button[data-action]', (e) => {
                e.preventDefault();
                const button = $(e.currentTarget);
                const action = button.data('action');
                const library = button.data('library') || 'izitoast';
                const title = button.data('title') || null;
                const message = button.data('message');
                const type = button.data('type');
                const position = button.data('position');
                const timer = button.data('timer') || 5000;
        
                const inputData = {};
                const inputTarget = button.data('input-target');
                const inputKey = button.data('input-key');
        
                if (inputTarget && inputKey) {
                    const value = $(inputTarget).val();
                    if (value !== undefined) {
                        inputData[inputKey] = value;
                    }
                }

                if (type === 'confirm') {
                    this.showNotify(message, 'confirm', {
                        position: position,
                        timer: timer,
                    }, 'sweetalert2', title).then((confirmed) => {
                        if (confirmed) {
                            this.performAjaxAction(action, { title, position, timer }, library, inputData);
                        }
                    });
                } else {
                    this.performAjaxAction(action, { title, position, timer }, library, inputData);
                }
            });
        },
        
        /**
         * Đây là điểm khởi đầu duy nhất cho module này, được gọi bởi main.js
         */
        init: function() {
            // Gán các hàm cần thiết ra phạm vi toàn cục (window)
            window.showNotify = this.showNotify.bind(this);
            window.performAjaxAction = this.performAjaxAction.bind(this);
            
            // Khởi tạo các listener cho nút action
            this.initActionButtons();
            
            console.log("Module Tiện ích (AppUtils) đã được khởi tạo thành công.");
        }
    };
    
    // Trả về module để các file khác có thể tham chiếu (nếu cần)
    return module;

})(window, jQuery);