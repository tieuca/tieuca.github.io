/**
 * Xử lý các hành động AJAX cho trang kích hoạt License.
 *
 * This script depends on `jQuery`, `iziToast`, and the `appLicense` object
 * localized from PHP.
 *
 * @since 1.8.0
 */
jQuery(function($) {
    // Chỉ thực thi code nếu các element cần thiết tồn tại trên trang
    const activateBtn = $('#activate-license-btn');
    if (!activateBtn.length) {
        return;
    }

    const deactivateBtn = $('#deactivate-license-btn');
    const keyInput = $('#license-key-input');
    const statusRow = $('#license-status-row');

    /**
     * Hiển thị trạng thái đang tải trên nút.
     * @param {jQuery} btn - Đối tượng jQuery của nút.
     */
    const showLoading = (btn) => {
        btn.addClass('is-busy').prop('disabled', true);
    };

    /**
     * Tắt trạng thái đang tải trên nút.
     * @param {jQuery} btn - Đối tượng jQuery của nút.
     */
    const hideLoading = (btn) => {
        btn.removeClass('is-busy').prop('disabled', false);
    };
    
    /**
     * Cập nhật giao diện hiển thị trạng thái (Active/Inactive).
     * @param {boolean} isActive - Trạng thái license có được kích hoạt hay không.
     */
    const updateStatus = (isActive) => {
        // Lấy chuỗi dịch từ PHP, nếu không có thì dùng giá trị mặc định
        const activeText = appLicense.text.active || 'Active';
        const inactiveText = 'Inactive'; // Inactive không cần dịch vì nó là trạng thái mặc định
        
        const statusText = isActive ? activeText : inactiveText;
        const statusClass = isActive ? 'status-active' : 'status-inactive';
        
        statusRow.find('.license-status').text(statusText).removeClass('status-active status-inactive').addClass(statusClass);
    };

    // Gắn sự kiện cho nút "Activate"
    activateBtn.on('click', function() {
        const btn = $(this);
        const key = keyInput.val().trim();

        if (!key) {
            iziToast.warning({ title: 'Warning', message: 'Please enter a license key.' });
            return;
        }

        $.ajax({
            url: appLicense.ajax_url,
            type: 'POST',
            data: {
                action: 'OXT_activate_license',
                nonce: appLicense.nonce,
                key: key
            },
            beforeSend: () => showLoading(btn),
            success: function(response) {
                if (response.success) {
                    iziToast.success({ title: 'Success', message: response.data.message });
                    keyInput.prop('disabled', true).val(response.data.masked_key);
                    activateBtn.hide();
                    deactivateBtn.show();
                    updateStatus(true);
                } else {
                    iziToast.error({ title: 'Error', message: response.data.message });
                }
            },
            error: function(jqXHR) {
                let msg = jqXHR.responseJSON && jqXHR.responseJSON.data ? jqXHR.responseJSON.data.message : 'An unknown error occurred.';
                iziToast.error({ title: 'Error', message: msg });
            },
            complete: function() {
                hideLoading(btn);
            }
        });
    });

    // Gắn sự kiện cho nút "Deactivate"
    deactivateBtn.on('click', function() {
        const btn = $(this);
        $.ajax({
            url: appLicense.ajax_url,
            type: 'POST',
            data: {
                action: 'OXT_deactivate_license',
                nonce: appLicense.nonce
            },
            beforeSend: () => showLoading(btn),
            success: function(response) {
                if (response.success) {
                    iziToast.info({ title: 'Info', message: response.data.message });
                    keyInput.prop('disabled', false).val('').attr('placeholder', 'Nhập license key của bạn...');
                    deactivateBtn.hide();
                    activateBtn.show();
                    updateStatus(false);
                }
            },
            error: function() {
                iziToast.error({ title: 'Error', message: 'An unknown error occurred.' });
            },
            complete: function() {
                hideLoading(btn);
            }
        });
    });
});
