<input type="hidden" name="<?php echo esc_attr($option->get_name_attribute()); ?>" value="" />
<select data-width="100%" id="<?php echo esc_attr($option->get_id_attribute()); ?>" name="<?php echo esc_attr($option->get_name_attribute()); ?>" class="select2 <?php echo esc_attr($option->get_input_class_attribute()); ?>" <?php echo ($option->get_arg('multiple') === true) ? 'multiple' : ''; ?>>
    <?php foreach ($option->get_arg('options', []) as $key => $label) : ?>
        <option value="<?php echo esc_attr($key); ?>" <?php echo in_array($key, $option->get_value_attribute() ?? []) ? 'selected' : null; ?>><?php echo esc_html($label); ?></option>
    <?php endforeach; ?>
</select>