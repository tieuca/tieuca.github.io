<div class="site-icon-section" data-media-library="<?php echo esc_attr(json_encode($option->get_media_library_config())); ?>">
    <?php if($preview = $option->get_preview_url()) : ?>
        <div class="wps-media-preview" style="display: flex;">
            <img src="<?php echo esc_url($preview); ?>" />
        </div>
    <?php else : ?>
        <div class="wps-media-preview"></div>
    <?php endif; ?>
    
    <input name="<?php echo esc_attr($option->get_name_attribute()); ?>" id="<?php echo esc_attr($option->get_id_attribute()); ?>" type="hidden" value="<?php echo esc_attr($option->get_value_attribute()); ?>" class="wps-media-target <?php echo esc_attr($option->get_input_class_attribute()); ?>">

    <button type="button" class="wps-media-open components-button is-secondary is-compact"><?php echo esc_html($option->get_arg('button_open_text', __('Select', 'az-settings'))); ?></button>
    
    <button type="button" class="wps-media-clear components-button is-compact is-tertiary has-icon" aria-label="<?php esc_attr_e('Clear', 'az-settings'); ?>" style="<?php echo empty($option->get_value_attribute()) ? 'display: none;' : ''; ?>"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M12 13.06l3.712 3.713 1.061-1.06L13.061 12l3.712-3.712-1.06-1.06L12 10.938 8.288 7.227l-1.061 1.06L10.939 12l-3.712 3.712 1.06 1.061L12 13.061z"></path></svg></button>
</div>