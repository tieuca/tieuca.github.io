<span class="components-form-toggle <?php echo $option->is_checked() ? 'is-checked' : null; ?>">
    <input name="<?php echo esc_attr($option->get_name_attribute()); ?>" id="<?php echo esc_attr($option->get_id_attribute()); ?>" type="checkbox" value="<?php echo esc_attr($option->get_value_attribute()); ?>" <?php echo $option->is_checked() ? 'checked' : null; ?> class="components-form-toggle__input <?php echo esc_attr($option->get_input_class_attribute()); ?>">
    <span class="components-form-toggle__track"></span>
    <span class="components-form-toggle__thumb"></span>
</span>
<input type="hidden" name="wp_settings_submitted[]" value="<?php echo esc_attr($option->get_name()); ?>">
