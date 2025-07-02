<span class="wp-pwd">
    <input name="<?php echo esc_attr($option->get_name_attribute()); ?>" id="<?php echo esc_attr($option->get_id_attribute()); ?>" type="password" value="<?php echo esc_attr(base64_decode($option->get_value_attribute() ?? '')); ?>" class="regular-text <?php echo esc_attr($option->get_input_class_attribute()); ?>">
    <button type="button" class="button wp-hide-pw hide-if-no-js tooltip" data-toggle="0" title="<?php esc_attr_e('Show password', 'az-settings'); ?>">
        <span class="dashicons dashicons-visibility" aria-hidden="true"></span>
    </button>
</span>