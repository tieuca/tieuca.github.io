<select id="<?php echo esc_attr($option->get_id_attribute()); ?>" name="<?php echo esc_attr($option->get_name_attribute()); ?>" class="<?php echo esc_attr($option->get_input_class_attribute()); ?>">
    <?php foreach ($option->get_arg('options', []) as $key => $label) : ?>
        <option value="<?php echo esc_attr($key); ?>" <?php selected($option->get_value_attribute(), $key); ?>><?php echo esc_html($label); ?></option>
    <?php endforeach; ?>
</select>