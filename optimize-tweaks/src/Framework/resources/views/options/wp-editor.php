<?php
\wp_editor(
    html_entity_decode($option->get_value_attribute() ?? '', ENT_QUOTES, 'UTF-8'), 
    esc_attr($option->get_id_attribute()), 
    [
        'textarea_name' => esc_attr($option->get_name_attribute()),
        'wpautop' => $option->get_arg('wpautop', true),
        'teeny' => $option->get_arg('teeny', false),
        'media_buttons' => $option->get_arg('media_buttons', true),
        'default_editor' => $option->get_arg('default_editor'),
        'drag_drop_upload' => $option->get_arg('drag_drop_upload', false),
        'textarea_rows' => $option->get_arg('textarea_rows', 10),
        'tabindex' => $option->get_arg('tabindex'),
        'tabfocus_elements' => $option->get_arg('tabfocus_elements'),
        'editor_css' => $option->get_arg('editor_css'),
        'editor_class' => $option->get_arg('editor_class'),
        'tinymce' => $option->get_arg('tinymce', true),
        'quicktags' => $option->get_arg('quicktags', true)
    ]
);
?>