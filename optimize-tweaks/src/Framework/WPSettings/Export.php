<?php

namespace TieuCA\WPSettings;

use TieuCA\WPSettings\Options\OptionAbstract;

class Export extends OptionAbstract
{
    /**
     * @var array Theo dõi các script đã được render để tránh lặp lại.
     */
    protected static $script_rendered = [];

    /**
     * Khởi tạo và đăng ký hook để xử lý việc export.
     */
    public function __construct($section, $args = [])
    {
        // Sử dụng hook admin_post_{action} hiệu suất cao hơn.
        $option_name = $section->tab->settings->option_name;
        add_action('admin_post_handle_wpsettings_export_' . $option_name, [$this, 'handle_export']);
        parent::__construct($section, $args);
    }

    /**
     * Hiển thị giao diện (nút bấm) cho người dùng.
     */
    public function render()
    {
        $option_name  = $this->section->tab->settings->option_name;
        $label        = $this->get_label();
        $description  = $this->get_arg('description');
        $class        = $this->get_arg('class', 'button button-secondary');

        // Tạo action và nonce duy nhất
        $export_action = 'handle_wpsettings_export_' . $option_name;
        $nonce = wp_create_nonce($export_action);
        
        // Tạo URL để tải về
        $url = add_query_arg([
            'action'   => $export_action,
            '_wpnonce' => $nonce
        ], admin_url('admin-post.php'));

        $button_id = 'wpsettings-export-button-' . esc_attr($option_name);
        ?>
        <tr valign="top">
            <th scope="row">
                <label><?php echo esc_html($label); ?></label>
            </th>
            <td>
                <p style="margin-top: 0;">
                    <small><?php esc_html_e('Source Option Name:', 'az-settings'); ?> <code><?php echo esc_html($option_name); ?></code></small>
                </p>
                <!-- Nút bấm sẽ được xử lý bằng JavaScript -->
                <button type="button" id="<?php echo esc_attr($button_id); ?>" class="<?php echo esc_attr($class); ?>" data-url="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></button>
                
                <?php if ($description) : ?>
                    <p class="description"><?php echo esc_html($description); ?></p>
                <?php endif; ?>
            </td>
        </tr>
        <?php

        // Thêm JavaScript để xử lý hiệu ứng cho nút bấm
        if (!isset(self::$script_rendered[$option_name])) {
            self::$script_rendered[$option_name] = true;
            // Truyền biến $label vào trong hàm callback
            add_action('admin_footer', function() use ($button_id, $label) {
                ?>
                <script type="text/javascript">
                    jQuery(document).ready(function($) {
                        const exportButton = $('#<?php echo esc_js($button_id); ?>');
                        const originalLabel = '<?php echo esc_js($label); ?>';

                        exportButton.on('click', function(e) {
                            e.preventDefault();
                            const button = $(this);
                            const url = button.data('url');

                            if (button.hasClass('is-busy')) {
                                return;
                            }

                            // Thêm hiệu ứng is-busy, đổi text và vô hiệu hóa nút
                            button.addClass('is-busy').text('<?php echo esc_js(__('Exporting...', 'az-settings')); ?>').prop('disabled', true);
                            
                            if (typeof showNotify === 'function') {
                                showNotify('<?php echo esc_js(__('Export started. Your file will be downloaded shortly.', 'az-settings')); ?>', 'success', { timeout: 3000 }, 'izitoast');
                            }

                            // Điều hướng trực tiếp để tải file, không dùng iframe
                            window.location.href = url;

                            // Xóa hiệu ứng, trả lại text cũ và kích hoạt lại nút sau một khoảng thời gian
                            setTimeout(function() {
                                button.removeClass('is-busy').text(originalLabel).prop('disabled', false);
                            }, 4000);
                        });
                    });
                </script>
                <?php
            });
        }
    }

    /**
     * Xử lý logic khi người dùng nhấn nút export.
     */
    public function handle_export()
    {
        $option_name = $this->section->tab->settings->option_name;
        $export_action = 'handle_wpsettings_export_' . $option_name;

        // 1. Kiểm tra bảo mật
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], $export_action)) {
            wp_die(__('Security check failed.', 'az-settings'), 'Error', ['response' => 403]);
        }
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'az-settings'), 'Error', ['response' => 403]);
        }

        // 2. Lấy dữ liệu cài đặt
        $settings = get_option($option_name);

        // 3. Chuẩn bị thông tin cho file JSON
        $plugin_name    = $this->section->tab->settings->slug ?? 'plugin';
        $plugin_version = $this->section->tab->settings->config['version'] ?? '1.0.0';
        $site = preg_replace('/^www\./', '', parse_url(get_site_url(), PHP_URL_HOST));
        $file_name = $site . '-' . $plugin_name . '-settings-' . date('Ymd-His') . '.json';

        $data = [
            'plugin'  => $plugin_name,
            'version' => $plugin_version,
            'option'  => $option_name,
            'data'    => $settings,
        ];

        // 4. Gửi file về cho người dùng
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Lấy nhãn cho nút bấm.
     */
    public function get_label()
    {
        return $this->get_arg('label') ?: __('Export Settings', 'az-settings');
    }
}
