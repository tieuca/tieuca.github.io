/**
 * Advanced Conditional Logic System - Refactored Version
 *
 * @description Encapsulated into a self-contained module.
 * It initializes the conditional logic and signals when it's ready.
 *
 * @version 2.0.0
 */
const ConditionalLogicModule = (function($) {

    // --- PRIVATE MEMBERS ---
    let conditionalElements = null;

    /**
     * Lấy giá trị của một trường trong form, hỗ trợ nhiều loại input.
     */
    function getFieldValue($field) {
        if (!$field.length) return null;
        if ($field.is('ul')) {
            const checkedValues = [];
            $field.find('input[type="checkbox"]:checked').each(function() {
                checkedValues.push($(this).val());
            });
            return checkedValues;
        }
        const type = $field.attr('type');
        if (type === 'checkbox') return $field.is(':checked') ? ($field.val() || true) : false;
        if (type === 'radio') {
            const groupName = $field.attr('name');
            return $(`input[name="${groupName}"]:checked`).val() || null;
        }
        return $field.val();
    }

    /**
     * Đánh giá một quy tắc điều kiện đơn lẻ.
     */
    function evaluateSingleRule(rule) {
        const $targetField = $(`#${rule.id}`);
        if (!$targetField.length) return false;
        const actualValue = getFieldValue($targetField);
        const operator = rule.operator;
        const expectedValue = rule.value;
        switch (operator) {
            case '==': case 'eq': return actualValue == expectedValue;
            case '!=': case 'neq': return actualValue != expectedValue;
            case '>': case 'gt': return parseFloat(actualValue) > parseFloat(expectedValue);
            case '<': case 'lt': return parseFloat(actualValue) < parseFloat(expectedValue);
            case '>=': case 'gte': return parseFloat(actualValue) >= parseFloat(expectedValue);
            case '<=': case 'lte': return parseFloat(actualValue) <= parseFloat(expectedValue);
            case 'contains': return Array.isArray(actualValue) && actualValue.includes(expectedValue);
            case 'not contains': return Array.isArray(actualValue) && !actualValue.includes(expectedValue);
            case 'is_checked': return $targetField.is(':checked');
            case 'is_not_checked': return !$targetField.is(':checked');
            case 'in': return Array.isArray(expectedValue) && expectedValue.includes(String(actualValue));
            case 'not in': return Array.isArray(expectedValue) && !expectedValue.includes(String(actualValue));
            case 'is_empty': return actualValue === '' || actualValue === null || (Array.isArray(actualValue) && actualValue.length === 0);
            case 'is_not_empty': return actualValue !== '' && actualValue !== null && (!Array.isArray(actualValue) || actualValue.length > 0);
            default: return false;
        }
    }

    /**
     * Đánh giá một nhóm các quy tắc điều kiện (có thể lồng nhau).
     */
    function evaluateRuleGroup(ruleGroup) {
        if (!Array.isArray(ruleGroup)) return false;
        const relation = (ruleGroup.relation === 'OR') ? 'OR' : 'AND';
        const rules = Object.values(ruleGroup).filter(item => typeof item !== 'string');
        for (const ruleOrSubgroup of rules) {
            let result = ruleOrSubgroup.id ? evaluateSingleRule(ruleOrSubgroup) : evaluateRuleGroup(ruleOrSubgroup);
            if (relation === 'OR' && result) return true;
            if (relation === 'AND' && !result) return false;
        }
        return relation === 'AND';
    }

    /**
     * Áp dụng tất cả các điều kiện lên các phần tử trên trang.
     */
    function applyAllConditions(useAnimation = true) {
        const animationSpeed = useAnimation ? 250 : 0;

        conditionalElements.each(function() {
            const $dependentElement = $(this);
            const conditions = $dependentElement.data('condition');
            if (!conditions) return;

            for (const [behavior, ruleGroup] of Object.entries(conditions)) {
                let $target = $dependentElement;
                let finalRules = ruleGroup;

                // Xử lý cấu trúc có 'target' và 'rules' cho các hành vi như disabled/readonly
                if (typeof ruleGroup.target !== 'undefined' && typeof ruleGroup.rules !== 'undefined') {
                    $target = $('#' + ruleGroup.target).closest('tr');
                    finalRules = ruleGroup.rules;
                }
                
                if (!$target.length) continue;

                const conditionMet = evaluateRuleGroup(finalRules);
                const $field = $target.find('input, select, textarea').first();
                
                switch (behavior) {
                    case 'visible':
                        if ($target.is('a.nav-tab')) {
                            const $contentSection = $('#' + $target.data('section'));
                            if (conditionMet) $target.fadeIn(animationSpeed);
                            else { $target.fadeOut(animationSpeed); $contentSection.hide(); }
                        } else if ($target.is('tr')) {
                            const $wrappers = $target.find('.cell-content-wrapper');
                            if (conditionMet) {
                                if (!$target.is(':visible')) $target.css('display', 'table-row');
                                $wrappers.slideDown(animationSpeed);
                            } else {
                                $wrappers.slideUp(animationSpeed).promise().done(function() {
                                    if (!$target.find('.cell-content-wrapper').is(':visible')) {
                                        $target.css('display', 'none');
                                    }
                                });
                            }
                        }
                        break;
                    
                    case 'disabled':
                        $field.prop('disabled', conditionMet);
                        $target.toggleClass('is-conditionally-disabled', conditionMet);
                        break;
                    case 'readonly':
                        $field.prop('readonly', conditionMet);
                        $target.toggleClass('is-conditionally-readonly', conditionMet);
                        break;
                    case 'required':
                        $field.prop('required', conditionMet);
                        $target.children('th').find('label').first().toggleClass('is-conditionally-required', conditionMet);
                        break;
                }
            }
        });

        setTimeout(function() {
            const $activeTab = $('.nav-section > a.nav-tab-active');
            if ($activeTab.length && $activeTab.is(':hidden')) {
                $('.nav-section > a.nav-tab:visible').first().trigger('click');
            }
        }, animationSpeed + 50);
    }

    /**
     * Bắn ra tín hiệu báo cho các module khác rằng framework đã sẵn sàng.
     */
    const fireReadySignal = () => {
        console.log('Framework is ready. Firing az-settings:ready signal.');
        window.azSettingsFrameworkReady = true; // Dựng cờ toàn cục
        jQuery(document).trigger('az-settings:ready');
    };

    // --- PUBLIC API ---
    return {
        /**
         * Phương thức khởi tạo công khai cho module.
         * Đây là điểm bắt đầu duy nhất.
         */
        init: function() {
            console.log('Conditional Logic module starting...');
            conditionalElements = $('tr[data-condition], .nav-section > a.nav-tab[data-condition]');
            
            // Nếu không có phần tử điều kiện nào, bắn tín hiệu ngay và thoát.
            if (!conditionalElements.length) {
                fireReadySignal();
                return;
            }

            // Bọc nội dung của các hàng <tr> có điều kiện để có hiệu ứng mượt mà
            $('tr[data-condition]').children('th, td').each(function() {
                if ($(this).html().trim().length > 0) {
                    $(this).wrapInner('<div class="cell-content-wrapper" style="display: none;"></div>');
                }
            });

            // Tìm các trường trigger
            const triggerFields = new Set();
            conditionalElements.each(function() {
                const conditions = $(this).data('condition');
                if (!conditions) return;
                function extractIds(ruleGroup) {
                    if (!ruleGroup) return;
                    const rules = ruleGroup.rules || ruleGroup;
                    Object.values(rules).forEach(ruleOrSubgroup => {
                        if (typeof ruleOrSubgroup === 'string') return;
                        if (ruleOrSubgroup.id) triggerFields.add(`#${ruleOrSubgroup.id}`);
                        else if (Array.isArray(ruleOrSubgroup)) extractIds(ruleOrSubgroup);
                    });
                }
                Object.values(conditions).forEach(extractIds);
            });
            
            // Gán sự kiện cho các trường trigger
            const triggerSelector = Array.from(triggerFields).join(', ');
            if (triggerSelector) {
                $('#post-body').on('change input click', triggerSelector, () => applyAllConditions(true));
            }

            // Áp dụng điều kiện lần đầu và bắn tín hiệu
            applyAllConditions(false);
            fireReadySignal();
        }
    };

})(jQuery);