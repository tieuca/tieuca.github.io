<?php
/**
 * Lớp khởi tạo chính cho ứng dụng AZ Settings.
 *
 * Lớp này chịu trách nhiệm đăng ký các hook cơ bản của WordPress,
 * xử lý việc kích hoạt plugin, thêm các liên kết vào trang danh sách plugin,
 * và quan trọng nhất là tải các tài nguyên (CSS/JS) cần thiết cho ứng dụng.
 *
 * @package     AZ_Settings
 * @subpackage  App
 * @since       1.0.0
 * @author      TieuCA
 */
namespace OXT\App;

use TieuCA\WPSettings\Helpers;
use TieuCA\WPSettings\WPSettings;

class Init {
    use CommonProperties;

    /**
     * Đường dẫn đến tệp tin chính của plugin.
     * Được sử dụng để đăng ký các hook kích hoạt và tạo link.
     *
     * @since 1.0.0
     * @var string
     */
    private $main_file;

    /**
     * Hàm khởi tạo cho lớp Init.
     *
     * Đây là nơi tất cả các hook và hành động ban đầu được đăng ký.
     *
     * @since 1.0.0
     * @param string $main_file Đường dẫn tuyệt đối đến tệp tin plugin chính.
     */
    public function __construct( $main_file ) {
        $this->main_file = $main_file;
        $this->initialize_common_properties();

        // Đăng ký hook sẽ được gọi khi plugin được kích hoạt lần đầu.
        register_activation_hook($this->main_file, array($this, 'plugin_activate'));

        // Đăng ký hook để thực hiện chuyển hướng đến trang cài đặt sau khi kích hoạt.
        add_action('admin_init', array($this, 'activation_redirect'));
        
        // Thêm các link hành động như "Settings" vào danh sách plugin.
		add_filter('plugin_action_links_' . plugin_basename($this->main_file), [$this, 'add_plugin_action_links']);
		
        // Thêm các link meta như "Documentation", "Support" vào danh sách plugin.
        add_filter('plugin_row_meta', [$this, 'add_plugin_meta_links'], 10, 2 );
		
		// Xoá các thông báo cập nhật của WordPress khỏi trang cài đặt của plugin.
		add_action('admin_notices', [$this, 'remove_core_update_notice'], 1);
		
		// Đăng ký hook để tải các tệp dịch thuật.
		add_action('plugins_loaded', [$this, 'load_textdomain']);
		
        // Đăng ký hook để tải các tài nguyên CSS/JS của App.
        // Đặt độ ưu tiên là 20 để đảm bảo nó chạy SAU hook của Framework (mặc định là 10).
        add_action('admin_enqueue_scripts', [$this, 'enqueue_app_assets'], 20);
        
        // Chỉ khởi tạo lớp xử lý AJAX khi có yêu cầu AJAX, để tối ưu hiệu suất.
        if (wp_doing_ajax()) {
            new Ajax();
        }
    }

    /**
     * Tải các tài nguyên (CSS/JS) dành riêng cho App.
     *
     * Hàm này được gọi bởi hook 'admin_enqueue_scripts'. Nó chịu trách nhiệm:
     * - Thêm các CSS tùy chỉnh (ví dụ: cho bản PRO).
     * - Tải file JavaScript xử lý trang License.
     * - Truyền dữ liệu từ PHP sang JavaScript (localize) để xử lý AJAX.
     * - Tải file CSS chính của App và khai báo phụ thuộc của nó.
     *
     * @since 1.1.0
     * @param string $hook_suffix Trang admin hiện tại. Dùng để đảm bảo script chỉ được tải trên trang cài đặt của plugin.
     */
    public function enqueue_app_assets($hook_suffix) {
        // Chỉ tải tài nguyên khi đang ở đúng trang cài đặt của plugin
        if (isset($_GET['page']) && $_GET['page'] === (string) $this->slug) {
            
            // Đường dẫn đến thư mục assets của App
            $app_assets_url = OXT_URL . 'src/App/assets/';
            $framework_css_handle = Helpers::normalizeString(WPSettings::FRAMEWORK_NAME);
            
            //wp_enqueue_style( $this->slug .'-app-core', $app_assets_url . 'css/core.css', [$framework_css_handle], $this->version );
            
            if (Settings::isPro()) {
                $custom_css = '.pro * { pointer-events: unset !important; opacity: 1 }';
                wp_add_inline_style($framework_css_handle, $custom_css);
            }
            
            wp_enqueue_script( 'license-handler', $app_assets_url . 'js/license-handler.js', ['jquery', $framework_css_handle], $this->version, true );
            wp_localize_script('license-handler', 'appLicense', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('license-nonce'),
                'text'     => [
                    'activating'   => __('Activating...', 'optimize-tweaks'),
                    'deactivating' => __('Deactivating...', 'optimize-tweaks'),
                    'activate'     => __('Activate', 'optimize-tweaks'),
                    'deactivate'   => __('Deactivate', 'optimize-tweaks'),
                    'active'       => __('Active', 'optimize-tweaks'),
                ]
            ]);

        }
    }

    /**
     * Ghi một option vào database để đánh dấu việc cần chuyển hướng sau khi kích hoạt.
     * @since 1.0.0
     */
    public function plugin_activate() {
        add_option($this->slug . '_do_activation_redirect', true);
    }
    
    /**
     * Thực hiện chuyển hướng đến trang cài đặt của plugin nếu option được đặt.
     * @since 1.0.0
     */
    public function activation_redirect() {
        if (get_option($this->slug . '_do_activation_redirect', false)) {
            delete_option($this->slug . '_do_activation_redirect');
            if(!isset($_GET['activate-multi'])) {
                wp_redirect(admin_url('admin.php?page=' . $this->slug));
                exit;
            }
        }
    }

	/**
     * Thêm link "Settings" vào danh sách các hành động của plugin.
     * @since 1.0.0
     * @param array $links Mảng các link hành động hiện có.
     * @return array Mảng các link đã được thêm vào.
     */
	public function add_plugin_action_links( $links ) {
		$settings_link  = '<a href="' . esc_url( admin_url('admin.php?page=' . $this->slug) ) . '">' . __( 'Settings' ) . '</a>';
		$links[]        = '<a href="#" style="color: #39b54a; font-weight: bold" target="_blank">' . __( 'Upgrade', 'optimize-tweaks' ) . '</a>';
		array_unshift($links, $settings_link);
		return $links;
	}

	/**
     * Thêm các link meta như "Documentation", "Support" vào phần mô tả của plugin.
     * @since 1.0.0
     * @param array $meta Mảng các link meta hiện có.
     * @param string $file Tên file plugin.
     * @return array Mảng các link meta đã được thêm vào.
     */
	public function add_plugin_meta_links( $meta, $file ) {
		if ( $file !== plugin_basename( $this->main_file ) ) {
			return $meta;
		}
        $upgradeable = false;
        $meta[] = '<a href="#" target="_blank">' . __( 'Documentation' ) . '</a>';
		if ( !$upgradeable ) {
            $meta[] = '<a href="#" target="_blank">' . __( 'Support' ) . '</a>';
        } else {
            $meta[] = '<a href="#" target="_blank">' . __( 'Donate', 'optimize-tweaks' ) . '</a>';
            $meta[] = '<a href="#" target="_blank" title="' . esc_html__( 'Rate WP EXtra on WordPress.org', 'optimize-tweaks' ) . '" style="color: #ffb900">'
                . str_repeat( '<span class="dashicons dashicons-star-filled" style="font-size:13px;width:13px;height:13px;line-height:1.8;"></span>', 5 )
                . '</a>';
        }
		return $meta;
	}
	
    /**
     * Xóa các thông báo cập nhật của WordPress khỏi trang cài đặt của plugin.
     * @since 1.0.0
     */
    public function remove_core_update_notice() {
        if (isset($_GET['page']) && $_GET['page'] === $this->slug) {
            echo '<style>
                .update-nag,
                .notice.update-message,
                .notice[data-dismissible="update-core-php-nag-notice"] {
                    display: none !important;
                }
            </style>';
        }
    }
    
    /**
     * Tải các tệp dịch thuật của plugin.
     * @since 1.0.0
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'optimize-tweaks', false, dirname( plugin_basename( OXT_FILE ) ) . '/languages/' );
    }
}

/*
 * --- CHANGELOG ---
 *
 * 1.0.0 (Initial Version):
 * - Initial creation of the Init class.
 */
