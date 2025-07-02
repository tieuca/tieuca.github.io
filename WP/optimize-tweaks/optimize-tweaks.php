<?php
/**
 * Plugin Name:         Optimize X Tweaks
 * Plugin URI:          #
 * Description:         An ecommerce toolkit that helps you sell anything. Beautifully.
 * Version:             1.0.1
 * Author:              TieuCA
 * Author URI:          #
 * Text Domain:         optimize-tweaks
 * Domain Path:         /languages/
 * Requires at least:   6.6
 * Requires PHP:        7.4
 * License:             GPL-2.0+
 * License URI:         http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Định nghĩa các hằng số môi trường cơ bản của plugin.
 *
 * @since 1.0.0
 */
define('OXT_FILE', __FILE__);
define('OXT_PATH', plugin_dir_path(__FILE__));
define('OXT_URL', plugins_url('/', __FILE__));
define('OXT_VERSION', '1.0.1');

/**
 * Tải tệp autoloader để quản lý các lớp.
 *
 * @since 1.0.0
 */
require_once OXT_PATH . 'src/autoload.php';

/**
 * Khởi tạo các thành phần cốt lõi của ứng dụng (App).
 *
 * @since 1.0.0
 */
if (is_admin()) {
    new OXT\App\Init(__FILE__);
}
new OXT\App\Settings();
new OXT\App\REDX();

/*
 * --- CHANGELOG ---
 *
 * 1.1.0 (2025-06-16):
 * - Removed hard-coded `OXT_PRO` constant definition.
 * - License status is now handled dynamically by the License class.
 * - Updated version number.
 *
 * 1.0.9 (Previous Version):
 * - Refactored to remove dependency on `OXT_PRO` constant.
 * - Initial implementation of dynamic PRO status.
 */
