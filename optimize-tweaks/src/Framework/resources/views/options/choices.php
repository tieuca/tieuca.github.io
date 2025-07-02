<ul>
<?php foreach($option->get_arg('options', []) as $key => $label) : ?>
    <li class="components-radio-control__option">
        <label for="<?php echo esc_attr($option->get_id_attribute() . '_' . $key); ?>">
            <input name="<?php echo esc_attr($option->get_name_attribute()); ?>" id="<?php echo esc_attr($option->get_id_attribute() . '_' . $key); ?>" type="radio" value="<?php echo esc_attr($key); ?>" <?php checked($key, $option->get_value_attribute()); ?> class="components-radio-control__input <?php echo esc_attr($option->get_input_class_attribute()); ?>">
            <?php echo esc_html($label); ?>
        </label>
    </li>
<?php endforeach; ?>
</ul>