<?php

namespace TieuCA\WPSettings;

use TieuCA\WPSettings\Options\OptionAbstract;

class ExportDatabase extends OptionAbstract
{
    /**
     * @var array Theo dõi các script đã được render để tránh lặp lại.
     */
    protected static $script_rendered = [];
    
    /**
     * @var int Số lượng dòng dữ liệu lấy trong mỗi lần truy vấn.
     */
    const CHUNK_SIZE = 1000;

    /**
     * Khởi tạo và đăng ký hook để xử lý việc export.
     */
    public function __construct($section, $args = [])
    {
        $option_name = $section->tab->settings->option_name;
        add_action('admin_post_handle_wpsettings_export_db_' . $option_name, [$this, 'handle_export']);
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

        $export_action = 'handle_wpsettings_export_db_' . $option_name;
        $nonce = wp_create_nonce($export_action);
        
        $url = add_query_arg([
            'action'   => $export_action,
            '_wpnonce' => $nonce
        ], admin_url('admin-post.php'));

        $button_id = 'wpsettings-export-db-button-' . esc_attr($option_name);
        ?>
        <tr valign="top">
            <th scope="row">
                <label><?php echo esc_html($label); ?></label>
            </th>
            <td>
                <button type="button" id="<?php echo esc_attr($button_id); ?>" class="<?php echo esc_attr($class); ?>" data-url="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></button>
                
                <?php if ($description) : ?>
                    <p class="description"><?php echo esc_html($description); ?></p>
                <?php endif; ?>
            </td>
        </tr>
        <?php

        if (!isset(self::$script_rendered[$option_name])) {
            self::$script_rendered[$option_name] = true;
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

                            if (typeof showNotify === 'function') {
                                showNotify(
                                    '<?php echo esc_js(__("Exporting the database can take a while for large sites. Please do not close this window.", "az-settings")); ?>',
                                    'confirm', {}, 'sweetalert2',
                                    '<?php echo esc_js(__("Confirm Database Export", "az-settings")); ?>'
                                ).then(function(confirmed) {
                                    if(confirmed) {
                                        button.addClass('is-busy').text('<?php echo esc_js(__('Exporting...', 'az-settings')); ?>').prop('disabled', true);
                                        window.location.href = url;
                                        setTimeout(function() {
                                            button.removeClass('is-busy').text(originalLabel).prop('disabled', false);
                                        }, 8000);
                                    }
                                });
                            } else {
                                if(confirm('<?php echo esc_js(__("Exporting the database can take a while. Continue?", "az-settings")); ?>')) {
                                    window.location.href = url;
                                }
                            }
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
        $export_action = 'handle_wpsettings_export_db_' . $option_name;

        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], $export_action)) {
            wp_die(__('Security check failed.', 'az-settings'));
        }
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'az-settings'));
        }

        global $wpdb, $wp_version;

        $site = preg_replace('/^www\./', '', parse_url(get_site_url(), PHP_URL_HOST));
        $file_name = $site . '-database-' . date('Ymd-His') . '.sql';

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output_handle = fopen('php://output', 'w');

        // Thêm thông tin metadata
        fwrite($output_handle, "-- WordPress Database Backup\n");
        fwrite($output_handle, "--\n");
        fwrite($output_handle, "-- Host: " . DB_HOST . "\n");
        fwrite($output_handle, "-- Database: " . DB_NAME . "\n");
        fwrite($output_handle, "-- Generation Time: " . date('Y-m-d H:i:s') . "\n");
        fwrite($output_handle, "-- WordPress Version: " . $wp_version . "\n");
        fwrite($output_handle, "-- PHP Version: " . phpversion() . "\n");
        fwrite($output_handle, "--\n\n");

        $tables = $wpdb->get_results('SHOW TABLES', ARRAY_N);
        $tables = array_map(fn($table) => $table[0], $tables);

        foreach ($tables as $table) {
            fwrite($output_handle, "\n\n--\n-- Table structure for table `$table`\n--\n\n");
            fwrite($output_handle, "DROP TABLE IF EXISTS `$table`;\n");
            $create_table = $wpdb->get_row("SHOW CREATE TABLE `$table`", ARRAY_N);
            fwrite($output_handle, $create_table[1] . ";\n\n");

            fwrite($output_handle, "--\n-- Dumping data for table `$table`\n--\n\n");
            
            // Xử lý theo từng "gói" để tiết kiệm bộ nhớ
            $offset = 0;
            while (true) {
                $query = $wpdb->prepare("SELECT * FROM `$table` LIMIT %d OFFSET %d", self::CHUNK_SIZE, $offset);
                $rows = $wpdb->get_results($query, ARRAY_A);

                if (empty($rows)) {
                    break; // Không còn dữ liệu để lấy
                }

                foreach ($rows as $row) {
                    $values = [];
                    foreach ($row as $value) {
                        if (is_null($value)) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = "'" . $wpdb->_real_escape($value) . "'";
                        }
                    }
                    fwrite($output_handle, "INSERT INTO `$table` VALUES(" . implode(', ', $values) . ");\n");
                }
                
                $offset += self::CHUNK_SIZE;
            }
        }

        fclose($output_handle);
        exit;
    }

    /**
     * Lấy nhãn cho nút bấm.
     */
    public function get_label()
    {
        return $this->get_arg('label') ?: __('Export Database', 'az-settings');
    }
}
