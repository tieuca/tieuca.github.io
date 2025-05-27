/**
 * File: ats-admin-metaboxes.js
 *
 * JavaScript tùy chỉnh cho các metaboxes của plugin Advanced Thunder Sales.
 *
 * @package     AdvancedThunderSales
 * @subpackage  Assets/JS
 * @since       1.0.0.0
 * @version     1.0.3.7
 * @lastupdate  26/05/2025 10:42 PM 
 */

jQuery(document).ready(function($) {
    'use strict';

    var ats_params = window.ats_admin_params || { 
        text: {
            // ... (các text khác giữ nguyên) ...
            adding_product_loading: 'Đang thêm sản phẩm...', // Chuỗi mới
        }, 
        flatpickr_locale: 'default', 
        wc_product_search_nonce: '', 
        product_details_nonce: '', 
        ajax_url: '' 
    }; 
    ats_params.text = ats_params.text || {}; // Đảm bảo text object tồn tại

    function atsShowNotification(message, type = 'info', options = {}, title = null) {
        // ... (Hàm atsShowNotification giữ nguyên như v1.0.3.5) ...
        const textParams = ats_params.text;
        if (typeof Swal === 'undefined') {
            console.error('ATS: SweetAlert2 library is not loaded.');
            if (type === 'confirm') { return Promise.resolve(confirm(message)); }
            alert((title ? title + ': ' : '') + message); return;
        }
        let swalOptions = { text: message };
        if (type === 'confirm') {
            const confirmTitle = title || (textParams.confirm_delete_product_title && String(textParams.confirm_delete_product_title).trim() !== '' ? textParams.confirm_delete_product_title : 'Are you sure?');
            const confirmBtnText = (textParams.confirm_button_text && String(textParams.confirm_button_text).trim() !== '' ? textParams.confirm_button_text : 'Yes, do it!');
            const cancelBtnText = (textParams.cancel_button_text && String(textParams.cancel_button_text).trim() !== '' ? textParams.cancel_button_text : 'Cancel');
            swalOptions = {title: confirmTitle, text: message, icon: 'warning', showCancelButton: true, confirmButtonColor: '#3085d6', cancelButtonColor: '#d33', confirmButtonText: confirmBtnText, cancelButtonText: cancelBtnText, ...options };
            return Swal.fire(swalOptions).then((result) => result.isConfirmed);
        } else {
            const defaultTitles = { info: (textParams.info_title && String(textParams.info_title).trim() !== '' ? textParams.info_title : 'Info'), success: (textParams.success_title && String(textParams.success_title).trim() !== '' ? textParams.success_title : 'Success!'), warning: (textParams.warning_title && String(textParams.warning_title).trim() !== '' ? textParams.warning_title : 'Warning!'), error: (textParams.error_title && String(textParams.error_title).trim() !== '' ? textParams.error_title : 'Error!')};
            swalOptions = {title: title || defaultTitles[type] || type.charAt(0).toUpperCase() + type.slice(1), text: message, icon: type, toast: true, position: 'top-end', showConfirmButton: false, timer: options.timer || 3000, timerProgressBar: true, didOpen: (toast) => { toast.addEventListener('mouseenter', Swal.stopTimer); toast.addEventListener('mouseleave', Swal.resumeTimer);}, ...options };
            Swal.fire(swalOptions);
        }
    }

    // --- Campaign Details Metabox ---
    // ... (Flatpickr init code giữ nguyên) ...
    if (typeof flatpickr !== "undefined" && $('.ats-datetimepicker').length > 0) {
        var flatpickrConfig = { enableTime: true, dateFormat: "d/m/Y H:i", time_24hr: true, allowInput: true, defaultHour: 0, defaultMinute: 0, };
        if (ats_params.flatpickr_locale && flatpickr.l10ns && flatpickr.l10ns[ats_params.flatpickr_locale]) { flatpickrConfig.locale = flatpickr.l10ns[ats_params.flatpickr_locale]; } else if (ats_params.flatpickr_locale === 'vn' && typeof flatpickr.l10ns !== 'undefined' && typeof flatpickr.l10ns.vn !== 'undefined' ) { flatpickrConfig.locale = 'vn';}
        $('.ats-datetimepicker').each(function() { flatpickr(this, flatpickrConfig); });
    } else if ($('.ats-datetimepicker').length > 0) { console.error('ATS: Flatpickr library IS NOT loaded.');}


    // --- Campaign Products Metabox ---
    if ($('#ats_campaign_products_wrapper').length > 0) {
        var $productSearchSelect = $('#ats_product_search');
        var $productsTableBody = $('#ats_campaign_products_list'); 
        var $removeSelectedButton = $('.ats-remove-selected-products');
        var $selectAllCheckbox = $('#ats_campaign_products_wrapper thead input[type="checkbox"]'); 

        if (typeof $().select2 === 'function' && $productSearchSelect.length) {
            // ... (Select2 init code giữ nguyên) ...
            $productSearchSelect.select2({
                ajax: { url: ats_params.ajax_url, dataType: 'json', delay: 250, data: function (p) { return { term: p.term, action:'woocommerce_json_search_products_and_variations', security: ats_params.wc_product_search_nonce, exclude_type:'grouped,external,bundle', display_stock:true }; }, processResults: function (d) { var t=[]; if(d){$.each(d,function(i,txt){t.push({id:i,text:txt});});} return {results:t};}, cache:true },
                minimumInputLength: 2, language: { errorLoading:function(){return ats_params.text.select2_error_loading;}, inputTooShort:function(a){var r=a.minimum-a.input.length;return ats_params.text.select2_input_too_short.replace('%s',r);}, loadingMore:function(){return ats_params.text.select2_loading_more;}, noResults:function(){return ats_params.text.select2_no_results;}, searching:function(){return ats_params.text.select2_searching;} },
                escapeMarkup: function (m) { return m; }, placeholder: $productSearchSelect.data('placeholder') || ats_params.text.select2_placeholder
            });
            $productSearchSelect.on('select2:select', function (e) {
                var d = e.params.data; if (d && d.id) { fetchAndAddProducts(d.id); }
            });
        } else if ($productSearchSelect.length){ console.error('ATS: Select2 library is not loaded or #ats_product_search not found.');}

        function fetchAndAddProducts(productIdOrParentId) {
            if (!productIdOrParentId) return;
            var $tempLoadingRow = $productsTableBody.find('tr.loading-product');
            if ($tempLoadingRow.length === 0) { 
                // Sử dụng chuỗi mới cho thông báo loading
                $tempLoadingRow = $('<tr class="loading-product"><td colspan="10">' + (ats_params.text.adding_product_loading || 'Đang thêm sản phẩm...') + '</td></tr>');
                $productsTableBody.find('.no-items').remove(); $productsTableBody.append($tempLoadingRow);
            }
            $.ajax({
                url: ats_params.ajax_url, type: 'POST', data: { action: 'ats_get_product_details_for_metabox', product_id: productIdOrParentId, nonce: ats_params.product_details_nonce },
                success: function(response) {
                    $tempLoadingRow.remove();
                    if (response.success && response.data && Array.isArray(response.data)) {
                        let allRowsHtml = ''; 
                        let newProductIdsForVirtualSoldInit = [];
                        response.data.forEach(function(productData) {
                            if ($productsTableBody.find('tr[data-product_id="' + productData.product_id + '"]').length > 0) return;
                            if (typeof wp !== 'undefined' && typeof wp.template === 'function') {
                                var template = wp.template('ats-campaign-product-row');
                                allRowsHtml += template(productData); 
                                newProductIdsForVirtualSoldInit.push(productData.product_id);
                            } else { console.error('ATS: wp.template is not available.'); atsShowNotification(ats_params.text.template_error, 'error', {}, 'Lỗi Giao Diện'); return false; }
                        });
                        if (allRowsHtml) {
                            $productsTableBody.find('.no-items').remove(); 
                            $productsTableBody.append(allRowsHtml); 
                            newProductIdsForVirtualSoldInit.forEach(function(pid) { $productsTableBody.find('tr[data-product_id="' + pid + '"] .ats-virtual-sold-checkbox').trigger('change'); });
                        }
                        $productSearchSelect.val(null).trigger('change'); 
                        updateRemoveSelectedButtonState(); updateSelectAllCheckboxState(); 
                    } else { atsShowNotification((response.data && response.data.message) || 'No data or invalid format.', 'error', {}, ats_params.text.error_fetching_product_details); }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $tempLoadingRow.remove(); console.error("ATS AJAX Error (Fetch):",textStatus,errorThrown,jqXHR.responseText);
                    atsShowNotification(jqXHR.responseText, 'error', {timer: 7000}, ats_params.text.ajax_error);
                }
            });
        }
        
        // ... (Các hàm updateRemoveSelectedButtonState, updateSelectAllCheckboxState, và các event handlers khác giữ nguyên như v1.0.3.5) ...
        function updateRemoveSelectedButtonState() {
            var checkedCount = $productsTableBody.find('input.ats-product-item-checkbox:checked').length;
            $removeSelectedButton.prop('disabled', checkedCount === 0);
        }
        function updateSelectAllCheckboxState() {
            var total = $productsTableBody.find('input.ats-product-item-checkbox').length;
            var checked = $productsTableBody.find('input.ats-product-item-checkbox:checked').length;
            $selectAllCheckbox.prop('checked', total > 0 && total === checked);
        }
        $productsTableBody.on('change', '.ats-virtual-sold-checkbox', function() {
            var $cb = $(this), $row = $cb.closest('tr'), $soldInput = $row.find('.ats-input-sold-count');
            $soldInput.prop('readonly', !$cb.is(':checked')).css('background-color', $cb.is(':checked') ? '' : '#f0f0f0');
        });
        $productsTableBody.on('click', '.ats-remove-product-row', function(e) {
            e.preventDefault(); var $r = $(this).closest('tr');
            var isParent = $r.hasClass('ats-parent-row');
            var pId = isParent ? $r.data('product_id') : $r.data('parent_id');
            var confirmText = isParent ? ats_params.text.confirm_delete_parent_product_text : ats_params.text.confirm_delete_product_text;
            atsShowNotification(confirmText, 'confirm', { title: ats_params.text.confirm_delete_product_title })
            .then((c) => {
                if (c) {
                    if (isParent && pId){ $productsTableBody.find('tr.ats-child-of-'+pId).remove(); } $r.remove();
                    if (!isParent && pId) { if($productsTableBody.find('tr.ats-child-of-'+pId).length===0){$productsTableBody.find('tr.ats-parent-row[data-product_id="'+pId+'"]').remove();}}
                    if ($productsTableBody.find('tr:not(.no-items)').length===0){$productsTableBody.append('<tr class="no-items"><td colspan="10">'+ats_params.text.no_products_in_campaign+'</td></tr>');}
                    updateSelectAllCheckboxState(); updateRemoveSelectedButtonState();
                }
            });
        });
        $removeSelectedButton.on('click', function() {
            var $cR = $productsTableBody.find('input.ats-product-item-checkbox:checked').closest('tr'); if ($cR.length===0) return;
            atsShowNotification(ats_params.text.confirm_delete_selected_text,'confirm',{title:ats_params.text.confirm_delete_selected_title})
            .then((c) => {
                if (c) {
                    var pIds=new Set(); $cR.each(function(){var $r=$(this); if($r.hasClass('ats-variation-row')&&$r.data('parent_id')){pIds.add($r.data('parent_id'));} $r.remove();});
                    pIds.forEach(function(pId){if($productsTableBody.find('tr.ats-child-of-'+pId).length===0){$productsTableBody.find('tr.ats-parent-row[data-product_id="'+pId+'"]').remove();}});
                    if($productsTableBody.find('tr:not(.no-items)').length===0){$productsTableBody.find('.no-items').remove();$productsTableBody.append('<tr class="no-items"><td colspan="10">'+ats_params.text.no_products_in_campaign+'</td></tr>');}
                    updateSelectAllCheckboxState();updateRemoveSelectedButtonState(); 
                }
            });
        });
        $productsTableBody.on('change', 'input.ats-product-item-checkbox', function(){updateRemoveSelectedButtonState();updateSelectAllCheckboxState();});
        $selectAllCheckbox.on('change', function(){var i=$(this).is(':checked');$productsTableBody.find('input.ats-product-item-checkbox').prop('checked',i);updateRemoveSelectedButtonState();});
        updateRemoveSelectedButtonState();updateSelectAllCheckboxState();
        $productsTableBody.find('.ats-virtual-sold-checkbox').each(function(){$(this).trigger('change');});
    } 
});
/*
-----------------------------------------------------------------------------------
 Ghi chú phiên bản và cập nhật:
-----------------------------------------------------------------------------------
 *
 * Phiên bản 1.0.3.7 (26/05/2025 10:42 PM)
 * - Thay đổi văn bản của dòng loading tạm thời khi thêm sản phẩm từ "Đang tìm..."
 * thành "Đang thêm sản phẩm..." (sử dụng ats_params.text.adding_product_loading).
 * - Cập nhật ghi chú phiên bản với giờ phút.
 *
 * Phiên bản 1.0.3.6 (26/05/2025 09:55 PM)
 * - Tối ưu hóa hàm fetchAndAddProducts: Thay vì gọi addSingleProductToUI cho mỗi sản phẩm/biến thể,
 * tạo một chuỗi HTML chứa tất cả các dòng mới rồi append vào DOM một lần duy nhất.
 * - Loại bỏ hàm addSingleProductToUI vì logic của nó đã được tích hợp vào fetchAndAddProducts.
 *
 * (Các phiên bản cũ hơn được lược bỏ)
 */
