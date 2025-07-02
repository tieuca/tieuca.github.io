<?php
/** @var \TieuCA\WPSettings\Options\OptionAbstract $option */
?>
<input
    name="<?php echo esc_attr($option->get_name_attribute()); ?>"
    id="<?php echo esc_attr($option->get_id_attribute()); ?>"
    type="button"
    value="<?php echo esc_attr($option->get_arg('type', 'Button')); ?>"
    class="components-button is-primary is-compact <?php echo esc_attr($option->get_input_class_attribute()); ?>">
<span class="spinner"></span>
<button id="stop_<?php echo esc_attr($option->get_id_attribute()); ?>" type="button" class="button components-button is-compact is-tertiary <?php echo esc_attr($option->get_input_class_attribute()); ?>"><span style="line-height: 30px;"  class="dashicons dashicons-controls-pause"></span><?php echo esc_html__('Pause'); ?></button>
<div id="result_<?php echo esc_attr($option->get_id_attribute()); ?>"></div>
