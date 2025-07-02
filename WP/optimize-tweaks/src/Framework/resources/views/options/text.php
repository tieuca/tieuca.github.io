<?php
/**
 * Template for the Text option type.
 * This file ONLY contains the unique HTML for the input field itself.
 * The <tr>, <th>, label, description, etc., are handled by the field-wrapper.php template.
 *
 * @var \TieuCA\WPSettings\Options\OptionAbstract $option
 */
?>
<input
    name="<?php echo esc_attr($option->get_name_attribute()); ?>"
    id="<?php echo esc_attr($option->get_id_attribute()); ?>"
    type="<?php echo esc_attr($option->get_arg('type', 'text')); ?>"
    value="<?php echo esc_attr($option->get_value_attribute()); ?>"
    class="regular-text <?php echo esc_attr($option->get_input_class_attribute()); ?>"
    <?php if ($min = $option->get_arg('min')) echo ' min="' . esc_attr($min) . '"'; ?>
    <?php if ($max = $option->get_arg('max')) echo ' max="' . esc_attr($max) . '"'; ?>
    <?php if ($step = $option->get_arg('step')) echo ' step="' . esc_attr($step) . '"'; ?>
    <?php if ($option->get_arg('required')) echo ' required="required"'; ?>
>
