<ul id="<?php echo esc_attr($option->get_id_attribute()); ?>">
<?php foreach ($option->get_arg('options', []) as $key => $label) : ?>
    <li class="components-checkbox-control">
        <span class="components-checkbox-control__input-container">
            <input type="checkbox" name="<?php echo esc_attr($option->get_name_attribute()); ?>" value="<?php echo esc_attr($key); ?>" <?php echo in_array($key, $option->get_value_attribute() ?? []) ? 'checked' : ''; ?>  class="components-checkbox-control__input <?php echo esc_attr($option->get_input_class_attribute()); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" role="presentation" class="components-checkbox-control__checked" aria-hidden="true" focusable="false"><path d="M16.7 7.1l-6.3 8.5-3.3-2.5-.9 1.2 4.5 3.4L17.9 8z"></path></svg>
        </span>
        <label>
            <?php echo ($label); ?>
        </label>
    </li>
<?php endforeach; ?>
</ul>

<?php if($option->get_arg('select') === true) : ?>
    <p><a href="javascript:void(0);" class="select-all components-button is-compact is-tertiary"><?php _e('Select all'); ?></a> | <a href="javascript:void(0);" class="deselect components-button is-compact is-tertiary"><?php _e('Deselect'); ?></a></p>
<?php endif; ?>