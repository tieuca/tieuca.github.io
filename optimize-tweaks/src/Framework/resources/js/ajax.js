function showNotify(message, type = 'info', options = {}, library = null, title = null) {
    library = library || 'izitoast';
    //console.log("Hiển thị thông báo:", message, type, library);
    
    if (type === 'confirm') library = 'sweetalert2';

    // Danh sách các type hợp lệ cho iziToast
    const validIziToastTypes = ['success', 'error', 'warning', 'info'];

    if (library === 'izitoast') {
        const defaultTitle = title ?? '';
        if (typeof iziToast === 'undefined') {
            console.error('iziToast library is not loaded.');
            return;
        }

        // Kiểm tra xem type có hợp lệ không
        if (!validIziToastTypes.includes(type)) {
            console.error(`Invalid type for iziToast: ${type}`);
            return;
        }

        // Cấu hình mặc định cho iziToast
        const iziToastOptions = {
            title: defaultTitle,
            message: message,
            position: "topRight",
            timeout: options.timer ? options.timer : 5000,
            ...options, // Ghi đè tùy chọn mặc định bằng tùy chọn người dùng cung cấp
        };

        iziToast[type](iziToastOptions);
    } else if (library === 'sweetalert2') {
        const defaultTitle = title || type.charAt(0).toUpperCase() + type.slice(1);
        options.position = type === 'confirm' ? 'center' : convertPositionToSweetAlert(options.position);
        options.timer = type === 'confirm' ? null : 5000;
        // Cấu hình mặc định cho SweetAlert2
        const defaultOptions = {
            title: defaultTitle,
            text: message,
            icon: type === 'confirm' ? 'question' : type,
            position: type === 'confirm' ? 'center' : 'bottom-end',
            timer: type === 'confirm' ? null : 5000,
            timerProgressBar: type !== 'confirm',
            toast: type !== 'confirm',
            showConfirmButton: type === 'confirm',
            allowOutsideClick: false,
            showCloseButton: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            },
            ...options,
        };
        
        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 library is not loaded.');
            return;
        }

        if (type === 'confirm') {
            defaultOptions.showCancelButton = true;
            defaultOptions.confirmButtonText = azs_i18n.yes;
            defaultOptions.cancelButtonText = azs_i18n.no;
            //swalOptions.reverseButtons = true;

            return Swal.fire(defaultOptions).then((result) => result.isConfirmed);
        }

        Swal.fire(defaultOptions);
    } else {
        console.error('Invalid notification library:', library);
    }
}
function convertPositionToSweetAlert(position) {
    const positionMap = {
        'topRight': 'top-end',
        'topLeft': 'top-start',
        'topCenter': 'top',
        'bottomRight': 'bottom-end',
        'bottomLeft': 'bottom-start',
        'bottomCenter': 'bottom',
        'center': 'center',
    };

    return positionMap[position] || 'top-end'; // Mặc định là 'top-end'
}
/*
jQuery(document).ready(function ($) {
    $(document).on('click', 'button[data-action]', function (e) {
        e.preventDefault();

        // Lấy thông tin từ nút
        var action      = $(this).data('action');
        var library     = $(this).data('library') || 'izitoast';
        var title       = $(this).data('title') || null;
        var message     = $(this).data('message');
        var type        = $(this).data('type');
        var position    = $(this).data('position');
        var timer       = $(this).data('timer') || 5000;
        var userInput   = $('#new_site_title').val();
        
        var inputData = {
            user_input: userInput,
        };

        //console.log('Button clicked with action:', library);

        // Hiển thị hộp thoại xác nhận nếu cần
        if (action === 'change_site_title_2') {
            performAjaxAction(action, {}, '', inputData); 
        } else if (action === 'change_site_title') {
            showNotify(message, type, {
                position: position,
                timer: timer,
            }, 'sweetalert2', title).then((confirmed) => {
                if (confirmed) {
                    performAjaxAction(action);
                } else {
                    showNotify('Hí ní, nề cà chế ba la cà!!!', 'warning', {
                        position: 'center',
                        timer: timer,
                        toast: false,
                    }, 'sweetalert2', 'Ế tề ^^')
                }
            });
        } else {
            // Đối với các hành động khác, thực hiện ngay lập tức
            performAjaxAction(action, { title: title, position: position, timer: timer }, library);
        }
    });

    function performAjaxAction(action, options = {}, library = null, inputData = {}) {
        $.ajax({
            url: wpSettingsAjax.ajax_url,
            type: 'POST',
            data: {
                action: action,
                nonce: wpSettingsAjax.nonce,
                ...inputData, // Truyền tất cả các giá trị đầu vào từ object inputData
            },
            success: function (response) {
                if (response.success) {
                    showNotify(response.data.message, response.data.type, {
                        title: response.data.title || options.title || '',
                        position: response.data.position || options.position || 'topRight',
                        timer: response.data.timer || options.timer || 5000,
                    }, library);
                } else {
                    showNotify(response.data || 'An error occurred.', 'error');
                }
            },
            error: function () {
                showNotify('AJAX request failed.', 'error');
            },
        });
    }
}); */