jQuery(document).ready(($) => {

    const form = $("#" + saveAjax.prefix + "-form");
    const saveButtons = $("#submit, #" + saveAjax.prefix + "-fixed-submit");
    let initialFormData;
    
    setTimeout(function() {
        initialFormData = getFormData();
    }, 500);
    
	// Make page header sticky on scroll. Using https://github.com/AndrewHenderson/jSticky
	$('#main-header').sticky({
		topSpacing: 0,
		zIndex: 100,
		stopper: '',
		stickyClass: 'sticky'
	})
		
	// Xử lý hiển thị nút lưu cố định
	$(window).on("load scroll", handleFixedSaveButton)

	// Gọi hàm khi kích thước cửa sổ thay đổi (để xử lý trường hợp xoay màn hình trên thiết bị di động)
	$(window).on("resize", handleFixedSaveButton)

	// Theo dõi thay đổi trong TinyMCE editor
    if (typeof tinymce !== "undefined") {
        tinymce.on("AddEditor", (e) => {
            e.editor.on("change", () => {
                //console.log("Nội dung TinyMCE editor đã thay đổi");
                // Kích hoạt sự kiện change trên form để cập nhật trạng thái
                form.trigger("change");
            });
        });
    }

	function showNotification(message, type) {
		//console.log("Hiển thị thông báo:", message, type)
		iziToast[type]({
			//title: type.charAt(0).toUpperCase() + type.slice(1),
			message: message,
			position: "topRight",
		})
	}
	
	// Xử lý hiển thị nút lưu cố định
	function handleFixedSaveButton() {
		var fixedSaveButton = $(".fixed-save-button")
		var formBottom = form.offset().top + form.height()
		var windowBottom = $(window).scrollTop() + $(window).height()

		if (formBottom > windowBottom) {
			fixedSaveButton.addClass("visible")
		} else {
			fixedSaveButton.removeClass("visible")
		}
	}

    // Xử lý checkbox: Đảm bảo rằng tất cả checkbox đều có giá trị
    function processCheckboxes(formData) {
        // Lấy danh sách tất cả các checkbox trong form
        const checkboxes = {};
        $('input[type="checkbox"]', form).each(function () {
            const name = $(this).attr('name');
            const value = $(this).is(':checked') ? $(this).val() : '0'; // Gán giá trị nếu được chọn, ngược lại gán 0
    
            // Nhóm các checkbox có cùng tên vào một mảng
            if (!checkboxes[name]) {
                checkboxes[name] = [];
            }
            checkboxes[name].push(value);
        });
    
        // Thêm các giá trị checkbox vào formData
        for (const [name, values] of Object.entries(checkboxes)) {
            // Loại bỏ các giá trị trùng lặp (nếu có)
            const uniqueValues = [...new Set(values)];
    
            // Xóa các giá trị cũ của checkbox trong formData
            formData = formData.filter(item => item.name !== name);
    
            // Thêm các giá trị mới vào formData
            uniqueValues.forEach(value => {
                formData.push({ name: name, value: value });
            });
        }
    
        return formData;
    }
    
    // Hàm để lấy dữ liệu form bao gồm cả nội dung editor và xử lý checkbox
    function getFormData() {
        //console.log("Đang lấy dữ liệu form");
        var formData = form.serializeArray();
    
        // Xử lý checkbox
        formData = processCheckboxes(formData);
    
        // Cập nhật nội dung từ TinyMCE editor
        if (typeof tinymce !== "undefined") {
            $(".wp-editor-area").each(function () {
                var editorId = $(this).attr("id");
                var editor = tinymce.get(editorId);
                if (editor) {
                    // Đồng bộ nội dung từ TinyMCE vào textarea
                    editor.save();
    
                    // Tìm và cập nhật giá trị của trường editor trong formData
                    var found = false;
                    for (var i = 0; i < formData.length; i++) {
                        if (formData[i].name === saveAjax.prefix + "_option[" + editorId + "]") {
                            formData[i].value = editor.getContent();
                            found = true;
                            break;
                        }
                    }
    
                    // Nếu không tìm thấy, thêm mới vào formData
                    if (!found) {
                        formData.push({
                            name: saveAjax.prefix + "_option[" + editorId + "]",
                            value: editor.getContent(),
                        });
                    }
                    //console.log("Đã cập nhật nội dung editor:", editorId, editor.getContent());
                }
            });
        }
    
        //console.log("Dữ liệu form:", formData);
        return $.param(formData); // Chuyển formData thành chuỗi query string
    }

	function saveSettings() {
		const formData = getFormData();

		$.ajax({
			url: saveAjax.ajax_url,
			type: "POST",
			data: {
				action: saveAjax.prefix + "_save_settings",
				nonce: saveAjax.nonce,
				settings: formData,
			},
            // Không cần beforeSend nữa vì đã xử lý ở click handler
			success: (response) => {
				if (response.success) {
                    const responseData = response.data || {};
					showNotification(azs_i18n.saveSuccess, "success");
                    initialFormData = getFormData(); // Cập nhật trạng thái sau khi lưu thành công
					
                    if (responseData.reload === true) {
                        setTimeout(() => { window.location.reload(); }, 2000);
                        // Không return để complete vẫn chạy và gỡ bỏ is-busy trước khi reload
                    }
				} else {
				    showNotification(azs_i18n.saveError + (response.data.message || response.data), "error");
				}
			},
			error: (xhr, status, error) => {
				showNotification(azs_i18n.ajaxError + error, "error");
			},
			complete: () => {
				saveButtons.removeClass('is-busy').prop('disabled', false).attr('aria-disabled', 'false');
			},
		});
	}

    // Kiểm tra required
    function validateForm() {
        let isValid = true;
        let errorMessages = [];
    
        // Tìm tất cả các trường có thuộc tính required
        $('input[required], textarea[required], select[required]', form).each(function () {
            const field = $(this);
            const fieldValue = field.val()?.trim();
            field.removeClass('error').next('.tooltip.error').remove();
    
            // Kiểm tra nếu trường rỗng
            if (!fieldValue) {
                isValid = false;
                const label = $(`label[for="${field.attr('id')}"]`).text().trim() || field.attr('name');
                const errorMessage = azs_i18n.validationRequired.replace('%s', `<strong>${label}</strong>`);
                
                field.addClass('error');
                errorMessages.push(errorMessage);
                field.after(`<div class="tooltip error">${errorMessage}</div>`);

            }
        });
    
        return { isValid, errorMessages };
    }
    
    // Kiểm tra các ràng buộc min, max, step
    function validateConstraints() {
        let isValid = true;
        let errorMessages = []; // Mảng để lưu trữ các thông báo lỗi
    
        // Tìm tất cả các trường input có thuộc tính min, max hoặc step
        $('input[type="number"][min], input[type="number"][max]', form).each(function () {
            const field = $(this);
            const fieldValue = parseFloat(field.val()) || 0;
            const label = $(`label[for="${field.attr('id')}"]`).text().trim() || field.attr('name');
            const min = parseFloat(field.attr('min'));
            const max = parseFloat(field.attr('max'));
            let errorMessage = '';
            
            field.removeClass('error').next('.tooltip.error').remove();
    
            if ((!isNaN(min) && !isNaN(max)) && (fieldValue < min || fieldValue > max)) {
                isValid = false;
                errorMessage = azs_i18n.validationMinMax.replace('%s', `<strong>${label}</strong>`).replace('%d', min).replace('%d', max);
            } else if (!isNaN(min) && fieldValue < min) {
                isValid = false;
                errorMessage = azs_i18n.validationMin.replace('%s', `<strong>${label}</strong>`).replace('%d', min);
            } else if (!isNaN(max) && fieldValue > max) {
                isValid = false;
                errorMessage = azs_i18n.validationMax.replace('%s', `<strong>${label}</strong>`).replace('%d', max);
            }
            
            if (!isValid) {
                errorMessages.push(errorMessage);
                field.addClass('error');
                field.after(`<div class="tooltip error">${errorMessage}</div>`);
            }
        });
    
        return { isValid, errorMessages };
    }

    // Xóa lớp error khi người dùng tương tác với bất kỳ trường input nào
    $('input, select, textarea', form).on('input focus', function () {
        $(this).removeClass('error');
    });
    
    // Xử lý khi bấm nút submit
    saveButtons.on("click", (e) => {
        e.preventDefault();
        console.log("Đã nhấp vào nút submit");
        // Thêm trạng thái busy ngay lập tức để người dùng thấy phản hồi
        saveButtons.addClass('is-busy').prop('disabled', true).attr('aria-disabled', 'true');
    
        // Đồng bộ nội dung từ TinyMCE
        if (typeof tinymce !== "undefined") {
            tinymce.triggerSave();
        }
        
        const currentFormData = getFormData();
        if (initialFormData === currentFormData) {
            showNotification(azs_i18n.noChanges, "info");
            saveButtons.removeClass('is-busy').prop('disabled', false).attr('aria-disabled', 'false');
            return;
        }
        
        // Kiểm tra các trường bắt buộc
        const formValidation = validateForm();
        const constraintsValidation = validateConstraints();
    
        // Tổng hợp kết quả
        const allErrors = [...formValidation.errorMessages, ...constraintsValidation.errorMessages];
        const isFormValid = formValidation.isValid && constraintsValidation.isValid;
    
        // Nếu có lỗi, hiển thị thông báo tổng hợp
        if (!isFormValid) {
            showNotification(allErrors.join('<br>'), "error");
            saveButtons.removeClass('is-busy').prop('disabled', false).attr('aria-disabled', 'false');
            return;
        }
    
        saveSettings();
    });

	console.log("Khởi tạo JavaScript hoàn tất")
})