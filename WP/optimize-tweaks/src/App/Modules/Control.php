<?php
namespace OXT\App\Modules;

use OXT\App\Settings;
use OXT\App\Base;

class Control extends Base {
    private $tab_name;
    private $settings_page_url;
    
    public function __construct() {
		parent::__construct();

        $this->tab_name = 'control'; // id tab control
        $this->settings_page_url = admin_url('admin.php?page=' . $this->slug .'&tab='. $this->tab_name);
    }
    
	protected $features = [
		'no_backend',
		'plugin_blocked_users',
		'themeplugin_update',
		'themeplugin_edits',
		'core_updates',
		'debug_mode',
		'misc_client_nags',
	];
	
    // Hiển thị thông báo lỗi tiêu chuẩn WordPress
    public function show_access_denied_notice() {
        $message = sprintf(
            '<div class="notice notice-error is-dismissible"><p><strong>%s</strong> %s </p></div>',
            __('Error:'),
            __('You do not have permission to access this page.', 'optimize-tweaks')
            );
        echo $message;
    }

    // 1. Restricted backend access for non-admins.
    public function no_backend() {
        add_action( 'admin_init', [$this, 'redirect_non_admin_user']);
    }

	public function redirect_non_admin_user() {
        if ( defined( 'DOING_AJAX' ) || current_user_can( 'administrator' ) ) {
            return;
        }

        $user = wp_get_current_user();
        $allowed_roles = (array) Settings::get_option('no_backend');

        if ( array_intersect( $allowed_roles, $user->roles ) ) {
            wp_redirect( site_url() );
            exit;
        }
    }
    
    
    // 2. Gán hook kiểm tra quyền truy cập trang plugin
    public function plugin_blocked_users() {
        add_action('load-toplevel_page_' . $this->slug, [$this, 'handle_restricted_access']);
    }

    // Kiểm tra xem người dùng có được phép truy cập plugin hay không
    public static function can_user_access_plugin() {
        $current_user_id = get_current_user_id();
        if (!$current_user_id) return false;
    
        // Lấy danh sách user ID bị chặn (được chọn trong adminmenu_blocked_users)
        $blocked_users = Settings::get_option('plugin_blocked_users', []);
    
        // Nếu user hiện tại nằm trong danh sách bị chặn → trả về FALSE (không cho truy cập)
        if (in_array($current_user_id, (array)$blocked_users)) {
            return false;
        }
    
        // Ngược lại → cho phép truy cập
        return true;
    }

    // Chặn truy cập plugin khi không đủ quyền
    public function handle_restricted_access() {
        // Kiểm tra xem feature 'adminmenu_extra' có đang được bật không
        if (!Settings::is_feature_active('plugin_blocked_users')) {
            return;
        }
        
        // Chỉ hiển thị trên trang plugin
        if (!isset($_GET['page']) || $_GET['page'] !== $this->slug) {
            return;
        }
        
        // Nếu người dùng không có quyền
        if (!self::can_user_access_plugin()) {
             // Hiển thị thông báo lỗi
            add_action('admin_notices', [$this, 'show_access_denied_notice']);
    
            // → Xóa menu plugin khỏi Admin Menu
            remove_menu_page($this->slug);
    
            // → Ẩn nội dung trang bằng CSS (fallback)
            add_action('admin_enqueue_scripts', function () {
                wp_add_inline_style('common', '
                    #poststuff,
                    #post-body,
                    .wrap > form,
                    .nav-tab-content {
                        display: none !important;
                    }
                ');
            });
    
            // → Dừng script sớm nhất có thể
            add_action('all_admin_notices', function () {
                exit;
            });
        }
    }
	
    
    // Theme & Plugin Auto-Update
	public function themeplugin_update(){
        //add_filter('auto_update_core', '__return_true');           // WordPress core
        add_filter('auto_update_plugin', '__return_true');         // Plugin
        add_filter('auto_update_theme', '__return_true');          // Theme
        //add_filter('auto_update_translation', '__return_true');    // Bản dịch
    }
    
    // Theme & Plugin Editors
	public function themeplugin_edits(){
        if(defined('DISALLOW_FILE_EDIT') || defined('DISALLOW_FILE_MODS')) {
            add_action('admin_notices', [$this, 'notice_disallow_file']);
        } else {
            define( 'DISALLOW_FILE_EDIT', true );
            define( 'DISALLOW_FILE_MODS', true );
        }
    }

	public function notice_disallow_file() {
		$message = sprintf(
			'<div class="notice notice-error"><p><strong>%s</strong> <code>%s</code> %s <a href="%s">%s</a> %s</p></div>',
			__('Warning:'),
			__('DISALLOW_FILE_EDIT / DISALLOW_FILE_MODS', 'optimize-tweaks'),
            __('is already enabled or defined somewhere else on your site.', 'optimize-tweaks'),
            esc_url($this->settings_page_url),
            __('Click here', 'optimize-tweaks'),
            __(' to disable it via plugin settings. We suggest managing it from one place only.', 'optimize-tweaks')
		);
		echo $message;
	}
    
    
    // All Core Updates
	public function core_updates(){
        if(defined('WP_AUTO_UPDATE_CORE')) {
            add_action('admin_notices', [$this, 'notice_auto_update_core']);
        } else {
            define( 'WP_AUTO_UPDATE_CORE', false );
        }
    }

	public function notice_auto_update_core() {
		$message = sprintf(
			'<div class="notice notice-error"><p><strong>%s</strong> <code>%s</code> %s <a href="%s">%s</a> %s</p></div>',
			__('Warning:'),
			__('WP_AUTO_UPDATE_CORE', 'optimize-tweaks'),
            __('is already enabled or defined somewhere else on your site.', 'optimize-tweaks'),
            esc_url($this->settings_page_url),
            __('Click here', 'optimize-tweaks'),
            __(' to disable it via plugin settings. We suggest managing it from one place only.', 'optimize-tweaks')
		);
		echo $message;
	}
	
	
    // Enable WP_DEBUG mode
    public function debug_mode() {
        if (defined('WP_DEBUG')) {
            add_action('admin_notices', [$this, 'notice_debug_already_defined']);
        } else {
            define('WP_DEBUG', true); // Có thể thay bằng true nếu bạn muốn bật debug
        }
    }
    
    // Hàm hiển thị thông báo nếu WP_DEBUG đã được định nghĩa ở nơi khác
    public function notice_debug_already_defined() {
        $message = sprintf(
            '<div class="notice notice-error"><p><strong>%s</strong> <code>%s</code> %s <a href="%s">%s</a> %s</p></div>',
            __('Warning:'),
            __('WP_DEBUG', 'optimize-tweaks'),
            __('is already enabled or defined somewhere else on your site.', 'optimize-tweaks'),
            esc_url($this->settings_page_url),
            __('Click here', 'optimize-tweaks'),
            __(' to disable it via plugin settings. We suggest managing it from one place only.', 'optimize-tweaks')
        );
        echo $message;
    }
	
	
    // Nags & Notices
    public function misc_client_nags() {
        add_action('admin_notices', [$this, 'remove_core_update_notice'], 1);
    }

    public function remove_core_update_notice() {
        // Fallback - Dùng CSS để ẩn thông báo nếu PHP không ăn
        echo '<style>
            .update-nag,
            .notice.update-message,
            .notice[data-dismissible="update-core-php-nag-notice"] {
                display: none !important;
            }
        </style>';
    }


//add_filter('admin_footer_text', [$this, 'remove_footer_admin']);
function remove_footer_admin () {
    global $pagenow;
    //echo 'Thanks for Choosing <a href="https://redxmedia.net" target=_blank">OXT.</a>' . $pagenow;
    echo __('You do not have permission to access this page.', 'optimize-tweaks');
}
}