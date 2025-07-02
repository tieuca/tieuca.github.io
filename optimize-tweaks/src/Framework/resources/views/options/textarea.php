<textarea
    name="<?php echo esc_attr($option->get_name_attribute()); ?>"
    id="<?php echo esc_attr($option->get_id_attribute()); ?>"
    class="<?php echo esc_attr($option->get_input_class_attribute()); ?>"
    rows="<?php echo esc_attr($option->get_arg('rows', 5)); ?>"
    cols="<?php echo esc_attr($option->get_arg('cols', 50)); ?>"><?php echo esc_textarea($option->get_value_attribute()); ?></textarea>