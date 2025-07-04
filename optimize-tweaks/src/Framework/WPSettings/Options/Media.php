<?php

namespace TieuCA\WPSettings\Options;

use TieuCA\WPSettings\Enqueuer;

class Media extends OptionAbstract
{
    public $view = 'media';

    public function __construct($section, $args = [])
    {
        add_action('az_settings_before_render_settings_page', [$this, 'enqueue']);

        parent::__construct($section, $args);
    }

    /**
     * Lấy URL xem trước cho một tệp đính kèm (media).
     * Đã được cải tiến để hỗ trợ các định dạng SVG và WebP.
     *
     * @return string URL xem trước hoặc URL của biểu tượng thay thế.
     */
    public function get_preview_url() {
        $value = $this->get_value_attribute();
    
        if (empty($value)) {
            return '';
        }
    
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
    
        $attachment_id = absint($value);
        if (!$attachment_id) {
            return '';
        }
    
        $attachment_url = wp_get_attachment_url($attachment_id);
        $mime_type = get_post_mime_type($attachment_id);
    
        if (!$attachment_url) {
            return '/wp-includes/images/media/document.png';
        }

        if (in_array($mime_type, ['image/svg+xml', 'image/webp'])) {
            return $attachment_url;
        }
        
        $image_src = wp_get_attachment_image_src($attachment_id, 'thumbnail');
        if ($image_src) {
            return $image_src[0];
        }
    
        if (strpos($mime_type, 'video') !== false) {
            return '/wp-includes/images/media/video.png';
        }
    
        if (strpos($mime_type, 'audio') !== false) {
            return '/wp-includes/images/media/audio.png';
        }
    
        return '/wp-includes/images/media/document.png';
    }

    public function get_media_library_config()
    {
        return wp_parse_args($this->get_arg('media_library', []), [
            'title' => __('Add Media'),
            'button' => [
                'text' => __('Add Media'),
            ],
            'multiple' => false,
        ]);
    }

    public function enqueue()
    {
        Enqueuer::add('wps-media', function () {
            wp_enqueue_media();
            ?>
            <style>
                .wps-media-wrapper > div {
                    display: flex;
                    gap: 15px;
                    align-items: start;
                }

                .wps-media-wrapper .wps-media-preview {
                    width: 80px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 10px 0;
                    display: none;
                }

                .wps-media-wrapper .wps-media-preview img {
                    max-width: 100%;
                }
            </style>
            <script>
                document.addEventListener('DOMContentLoaded', function () {

                    document.querySelectorAll('.wps-media-wrapper').forEach((el) => {
                        let trigger = el.querySelector('.wps-media-open');
                        let clear = el.querySelector('.wps-media-clear');
                        let target = el.querySelector('.wps-media-target');
                        let preview = el.querySelector('.wps-media-preview');
                        let media_library_config = JSON.parse(el.getAttribute('data-media-library'));

                        let media_library = wp.media(media_library_config);

                        clear.addEventListener('click', function (e) {
                            e.preventDefault();

                            target.value = '';
                            preview.innerHTML = '';
                            clear.style.display = 'none';
                            preview.style.display = 'none';
                        });

                        trigger.addEventListener('click', function (e) {
                            e.preventDefault();

                            media_library.open();
                        });

                        media_library.on('open', function() {
                            if(target.value === '') {
                                return;
                            }

                            let selection = media_library.state().get('selection');
                            let attachment = wp.media.attachment(target.value);

                            selection.add(attachment ? [attachment] : []);
                        });

                        media_library.on('select', function () {
                            let attachment = media_library.state().get('selection').first().toJSON();

                            target.value = attachment.id;

                            if (attachment.type === 'image') {
                                preview.innerHTML = '<img src="' + attachment.url + '">'; //attachment.sizes.thumbnail.url
                            } else {
                                preview.innerHTML = '<img src="' + attachment.icon + '">';
                            }

                            preview.style.display = 'flex';
                            clear.style.display = 'inline-block';
                        });
                    });
                });
            </script>
            <?php
        });
    }
}
