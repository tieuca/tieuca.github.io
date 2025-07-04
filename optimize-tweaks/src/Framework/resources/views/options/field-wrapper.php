<?php
/**
 * Wrapper template for all setting options.
 * This template handles the rendering of the table row (<tr>),
 * the label cell (<th>), and the common description/error messages.
 *
 * @package TieuCA\WPSettings
 * @var \TieuCA\WPSettings\Options\OptionAbstract $option        The option object.
 * @var string                                 $field_content The HTML content of the specific field.
 */
?>
<tr valign="top" class="<?php echo $option->get_row_classes(); ?>" <?php echo $option->get_condition_attribute(); ?>>
    <th scope="row">
        <label for="<?php echo esc_attr($option->get_id_attribute()); ?>" class="<?php echo esc_attr($option->get_label_class_attribute()); ?>">
            <?php echo $option->get_label(); ?>
        </label>
        <?php if ($link = $option->get_arg('link')) : ?>
            <a target="_blank" class="tooltip" href="<?php echo esc_url($link); ?>" title="<?php esc_attr_e('Help', 'az-settings'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M12 4.75a7.25 7.25 0 100 14.5 7.25 7.25 0 000-14.5zM3.25 12a8.75 8.75 0 1117.5 0 8.75 8.75 0 01-17.5 0zM12 8.75a1.5 1.5 0 01.167 2.99c-.465.052-.917.44-.917 1.01V14h1.5v-.845A3 3 0 109 10.25h1.5a1.5 1.5 0 011.5-1.5zM11.25 15v1.5h1.5V15h-1.5z"></path></svg>
            </a>
        <?php endif; ?>
    </th>
    <td>
        <div class="field-type-wrapper <?php echo esc_attr($option->get_type_class()); ?>">
            <?php echo $field_content; ?>
            <?php $option->render_description(); ?>
            <?php $option->render_error(); ?>
        </div>
    </td>
</tr>
