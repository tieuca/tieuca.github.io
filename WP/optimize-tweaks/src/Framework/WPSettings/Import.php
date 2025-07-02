<?php

namespace TieuCA\WPSettings;

use TieuCA\WPSettings\Options\OptionAbstract;

class Import extends OptionAbstract
{
    /**
     * @var array Theo dõi các script đã được render để tránh lặp lại.
     */
    protected static $script_rendered = [];

    /**
     * Khởi tạo và đăng ký hook AJAX để xử lý việc import.
     */
    public function __construct($section, $args = []) {
        $option_name = $section->tab->settings->option_name;
        add_action('wp_ajax_handle_wpsettings_import_' . $option_name, [$this, 'handle_ajax_import']);
        parent::__construct($section, $args);
    }

    /**
     * Hiển thị giao diện (nút bấm và ô chọn file) cho người dùng.
     */
    public function render() {
        $option_name  = $this->section->tab->settings->option_name;
        $label        = $this->get_label();
        $description  = $this->get_arg('description', __('Import settings from a .json file. This will overwrite all current settings.', 'az-settings'));
        
        // Tạo các ID và action duy nhất để tránh xung đột
        $ajax_action = 'handle_wpsettings_import_' . $option_name;
        $nonce_action = 'wpsettings_import_nonce_' . $option_name;
        $button_id = 'wpsettings-import-button-' . esc_attr($option_name);
        $file_input_id = 'wpsettings-import-file-' . esc_attr($option_name);
        ?>
        <tr valign="top">
            <th scope="row">
                <label><?php echo esc_html($label); ?></label>
            </th>
            <td>
                <p style="margin-top: 0;">
                    <small><?php esc_html_e('Target Option Name:', 'az-settings'); ?> <code><?php echo esc_html($option_name); ?></code></small>
                </p>
                <button type="button" id="<?php echo esc_attr($button_id); ?>" class="button button-secondary"><?php esc_html_e('Import Settings', 'az-settings'); ?></button>
                <input type="file" id="<?php echo esc_attr($file_input_id); ?>" accept="application/json" style="display: none;" />
                
                <?php if ($description) : ?>
                    <p class="description"><?php echo esc_html($description); ?></p>
                <?php endif; ?>
            </td>
        </tr>
        <?php
        
        if (!isset(self::$script_rendered[$option_name])) {
            self::$script_rendered[$option_name] = true;
            add_action('admin_footer', function() use ($button_id, $file_input_id, $ajax_action, $nonce_action) {
                $nonce = wp_create_nonce($nonce_action);
                ?>
                <script type="text/javascript">
                    jQuery(document).ready(function($) {
                        const importButton = $('#<?php echo esc_js($button_id); ?>');
                        const fileInput = $('#<?php echo esc_js($file_input_id); ?>');

                        // 1. Khi người dùng bấm nút "Import Settings", kích hoạt ô chọn file ẩn
                        importButton.on('click', function() {
                            fileInput.trigger('click');
                        });

                        // 2. Khi người dùng đã chọn một file
                        fileInput.on('change', function() {
                            if (this.files.length === 0) {
                                return; // Không làm gì nếu không chọn file
                            }

                            const file = this.files[0];

                            // 3. Hiển thị popup xác nhận bằng showNotify
                            showNotify(
                                '<?php echo esc_js(__("This will overwrite all current settings. Are you sure?", "az-settings")); ?>',
                                'confirm', {}, 'sweetalert2',
                                '<?php echo esc_js(__("Confirm Import", "az-settings")); ?>'
                            ).then(function(confirmed) {
                                if (confirmed) {
                                    // Nếu người dùng đồng ý, tiến hành AJAX
                                    const button = importButton;
                                    // Thêm class is-busy
                                    button.prop('disabled', true).text('<?php echo esc_js(__('Importing...', 'az-settings')); ?>').addClass('is-busy');

                                    const formData = new FormData();
                                    formData.append('action', '<?php echo esc_js($ajax_action); ?>');
                                    formData.append('_ajax_nonce', '<?php echo esc_js($nonce); ?>');
                                    formData.append('import_file', file);

                                    $.ajax({
                                        url: ajaxurl,
                                        type: 'POST',
                                        data: formData,
                                        processData: false,
                                        contentType: false,
                                        dataType: 'json',
                                        success: function(response) {
                                            if (response.success) {
                                                // 4. Thông báo thành công bằng iziToast
                                                showNotify(response.data.message, 'success', { timeout: 2500 }, 'izitoast');
                                                setTimeout(function() {
                                                    window.location.reload();
                                                }, 2500);
                                            } else {
                                                showNotify('<?php echo esc_js(__("Error:", "az-settings")); ?> ' + response.data.message, 'error', {}, 'izitoast');
                                            }
                                        },
                                        error: function(xhr) {
                                            showNotify('<?php echo esc_js(__("An unexpected error occurred.", "az-settings")); ?>', 'error', {}, 'izitoast');
                                            console.error('Import Error:', xhr.responseText);
                                        },
                                        complete: function() {
                                            // Xóa class is-busy
                                            button.prop('disabled', false).text('<?php echo esc_js(__('Import Settings', "az-settings")); ?>').removeClass('is-busy');
                                            fileInput.val(''); // Reset để có thể chọn lại file cũ
                                        }
                                    });
                                } else {
                                    // Nếu không đồng ý, reset ô chọn file
                                    fileInput.val('');
                                }
                            });
                        });
                    });
                </script>
                <?php
            });
        }
    }

    /**
     * Xử lý logic import được gọi qua AJAX.
     */
    public function handle_ajax_import()
    {
        $option_name = $this->section->tab->settings->option_name;
        $nonce_action = 'wpsettings_import_nonce_' . $option_name;

        check_ajax_referer($nonce_action);

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have permission to perform this action.', 'az-settings')]);
        }

        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(['message' => __('File upload failed. Please try again.', 'az-settings')]);
        }

        $file_path = $_FILES['import_file']['tmp_name'];
        $file_content = file_get_contents($file_path);
        $import_data = json_decode($file_content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error(['message' => __('Invalid JSON file. The file could not be read.', 'az-settings')]);
        }

        if (!is_array($import_data) || !isset($import_data['option']) || !isset($import_data['data'])) {
            wp_send_json_error(['message' => __('Invalid file format. The import file is not compatible.', 'az-settings')]);
        }

        if ($import_data['option'] !== $option_name) {
            $error_message = sprintf(
                __('Settings file mismatch. This file is for "%s", but the current settings are for "%s".', 'az-settings'),
                $import_data['option'],
                $option_name
            );
            wp_send_json_error(['message' => $error_message]);
        }

        update_option($option_name, $import_data['data']);

        wp_send_json_success(['message' => __('Settings imported successfully!', 'az-settings')]);
    }

    /**
     * Lấy nhãn cho nút bấm.
     */
    public function get_label()
    {
        return $this->get_arg('label') ?: __('Import', 'az-settings');
    }
}
