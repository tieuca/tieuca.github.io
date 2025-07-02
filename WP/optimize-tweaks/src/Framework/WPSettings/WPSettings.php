<?php
/**
 * Text Domain: az-settings
 */
 
/**
 * Lớp WPSettings - Cốt lõi của Framework Cài đặt.
 *
 * Lớp này chịu trách nhiệm khởi tạo trang cài đặt, quản lý các tab, section,
 * tùy chọn, và tải các tài nguyên CSS/JS cần thiết cho giao diện.
 * Nó được thiết kế để hoạt động độc lập với logic của ứng dụng (App).
 *
 * @package     AZ_Settings
 * @subpackage  Framework/WPSettings
 * @since       1.0.0
 * @author      TieuCA
 */
namespace TieuCA\WPSettings;

use Adbar\Dot;

class WPSettings {
    
    /**
     * Định nghĩa các handle tĩnh và công khai cho tài nguyên của Framework.
     * Bất kỳ App nào cũng có thể sử dụng các hằng số này để khai báo phụ thuộc.
     * @since 1.0.0
     */
    const FRAMEWORK_NAME = 'WPSettings';
    
    public const FRAMEWORK_DOMAIN = 'az-settings';
    
    public $title;

    public $menu_title;

    public $slug;

    public $parent_slug;

    public $capability = 'manage_options';

    public $menu_icon;

    public $menu_position;

    public $option_name;

    public $tabs = [];

    public $errors;

    public $flash;

    public $version;

    public $sidebar;

    public $plugin_data;
    
    public $styling_loaded = false;
    
    /**
     * Mảng cấu hình được "tiêm" vào từ App.
     * @since 1.0.0
     * @var array
     */
    public $config = [];

    /**
     * Hàm khởi tạo của Framework.
     * Nhận các cấu hình từ App thông qua Dependency Injection.
     *
     * @since 1.0.0
     * @param string $title  Tiêu đề chính của trang cài đặt.
     * @param string|null $slug   Slug của trang cài đặt.
     * @param array $config Mảng cấu hình được truyền từ App.
     */
    public function __construct($title, $slug = null, $config = []) {
        $this->title = $title;
        $this->slug = $slug ?? sanitize_title($title);
        
        // Thiết lập các giá trị mặc định cho framework
        $this->config = wp_parse_args($config, [
            'resources_url'   => plugin_dir_url(__FILE__) . '../resources/',
            'version'         => null,
            'is_pro'          => false,
        ]);
    }
    
    public function is_on_plugin_page() {
        return isset($_GET['page']) && $_GET['page'] === $this->slug;
    }

    public function set_menu_parent_slug($slug)
    {
        $this->parent_slug = $slug;

        return $this;
    }

    public function set_menu_title($title)
    {
        $this->menu_title = $title;

        return $this;
    }

    public function get_menu_title()
    {
        return $this->menu_title ?? $this->title;
    }

    public function set_capability($capability)
    {
        $this->capability = $capability;

        return $this;
    }

    public function set_option_name($name)
    {
        $this->option_name = $name;

        return $this;
    }

    public function set_menu_icon($icon)
    {
        $this->menu_icon = $icon;

        return $this;
    }

    public function set_menu_position($position)
    {
        $this->menu_position = $position;

        return $this;
    }

    public function set_version($version = null)
    {
        $this->version = $version;

        return $this;
    }
    
    public function set_icon($icon = null)
    {
        $this->title_icon = $icon;

        return $this;
    }
    
    public function set_sidebar($items = []) {
        $this->sidebar = [];

        foreach ($items as $title => $message) {
            $this->sidebar[] = [
                'title' => $title,
                'message' => $message
            ];
        }

        return $this;
    }

    public function get_sidebar() {
        return $this->sidebar;
    }

    public function set_plugin_data($plugin_data = null)
    {
        $this->plugin_data = $plugin_data;

        return $this;
    }

    public function add_to_menu()
    {
        if ($this->parent_slug) {
            \add_submenu_page($this->parent_slug, $this->title, $this->get_menu_title(), $this->capability, $this->slug, [$this, 'render'], $this->menu_position);
        } else {
            \add_menu_page($this->title, $this->get_menu_title(), $this->capability, $this->slug, [$this, 'render'], $this->menu_icon, $this->menu_position);
        }
    }

    public function make() {
        $this->errors = new Error($this);
        $this->flash = new Flash($this);
        add_action('admin_init', [$this, 'save'], 20);
        add_action('admin_menu', [$this, 'add_to_menu'], 20);
        add_action('admin_head', [$this, 'styling'], 20);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_styling']);
        add_filter('admin_footer_text', [$this, 'admin_rate_us']);
        add_action('admin_notices', [$this, 'admin_notice']);
        add_action('wp_ajax_' . $this->slug . '_save_settings', array($this, 'ajax_save'));
        add_action('wp_ajax_' . $this->slug . '_restore_settings', [$this, 'ajax_restore']);
    }
    
    public function is_on_settings_page()
    {
        $screen = get_current_screen();

        if ($screen && $screen->base === 'settings_page_'.$this->slug) {
            return true;
        }

        return false;
    }

    public function is_on_toplevel_page()
    {
        $screen = get_current_screen();

        if ($screen && $screen->base === 'toplevel_page_'.$this->slug) {
            return true;
        }

        return false;
    }
    
    public function is_on_parent_page()
    {
        $screen = get_current_screen();

        if ($screen && strpos($screen->base, $this->slug) !== false) {
            return true;
        }
        
        return false;
    }


    public function styling()
    {
        if (! $this->is_on_settings_page()) {
            return;
        }
        ?>
        <style>.wps-error-feedback {color: #d63638;margin: 5px 0;}</style>
        <?php
    }
    
    public function enqueue_styling()
    {
        if ($this->styling_loaded) {
            return;
        }
        
        $resources_url = $this->config['resources_url'] ?? '';
        $version       = $this->config['version'] ?? null;
        
        if ($this->is_on_plugin_page() && !empty($resources_url)) {
            
            $main_handle = Helpers::normalizeString(self::FRAMEWORK_NAME);
            
            wp_enqueue_script('clipboard');
            wp_enqueue_style('wp-components');
            wp_enqueue_style('izitoast', $resources_url . 'vendor/iziToast/iziToast.min.css');
            wp_enqueue_script('izitoast', $resources_url . 'vendor/iziToast/iziToast.min.js', array(), $this->version, true);
            wp_enqueue_script('sweetalert2', $resources_url . 'vendor/sweetalert2/sweetalert2.all.min.js', [], $version, true);
            wp_enqueue_script('jSticky', $resources_url . 'vendor/jSticky/jquery.jsticky.min.js', [], $this->version, true);
            wp_enqueue_style($main_handle, $resources_url . 'css/settings.css');
            wp_enqueue_script($main_handle, $resources_url . 'js/settings.js', [], $this->version, true);
            wp_enqueue_script($main_handle .'-admin', $resources_url . 'js/admin.js', array('jquery', 'wp-color-picker', 'izitoast'), $this->version, true);
            wp_enqueue_script($main_handle .'-ajax', $resources_url . 'js/ajax.js', array('jquery', 'wp-color-picker', 'izitoast'), $this->version, true);
            wp_enqueue_script($main_handle .'-conditional', $resources_url . 'js/conditional-logic.js', array('jquery'), $this->version, true);

            $localized_strings = [
            'noChanges'           => __('No changes to save.', 'az-settings'),
            'areYouSure'          => __('Are you sure?', 'az-settings'),
            'restoreConfirm'      => __('All settings in this tab will be reset. This action cannot be undone.', 'az-settings'),
            'ajaxFailed'          => __('AJAX request failed. Please try again.', 'az-settings'),
            'ajaxError'          => __('AJAX Error.', 'az-settings'),
            'error'               => __('Error', 'az-settings'),
            'saveSuccess'         => __('Changes saved successfully!', 'az-settings'),
            'saveError'           => __('Error saving settings:', 'az-settings'),
            'validationRequired'  => __('Please fill in the "%s" field.', 'az-settings'),
            'yes'                 => __('Yes', 'az-settings'),
            'no'                  => __('No', 'az-settings'),
            'validationRequired'  => __('Please fill in the "%s" field.', 'az-settings'),
            'validationMinMax'    => __('Value for "%s" must be between %d and %d.', 'az-settings'),
            'validationMin'       => __('Value for "%s" must be greater than or equal to %d.', 'az-settings'),
            'validationMax'       => __('Value for "%s" must be less than or equal to %d.', 'az-settings'),
            ];
            
            wp_localize_script($main_handle, 'azs_i18n', $localized_strings);
    
            // 2. "Tiêm" đối tượng chứa thông tin AJAX vào script chính
            $ajax_params = [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce($this->slug . '_ajax_nonce'),
                'prefix'   => $this->slug,
            ];
            wp_localize_script($main_handle, 'saveAjax', $ajax_params);
        }

        $this->styling_loaded = true;
    }
    
    public function admin_rate_us( $footer_text ) {
        if ( isset($_GET['page']) && $_GET['page'] === $this->slug && $this->plugin_data ) {
            if( ! function_exists('get_plugin_data') ){
                require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            }
            $plugin_data = get_plugin_data( $this->plugin_data );
            $thank_text = sprintf(
                /* translators: 1. link to plugin uri; 2. link to plugin name; 3. link to author name */
                __( 'Thank you for using <a href="%1$s" target="_blank">%2$s</a>. Made with ♥ by <strong>%3$s</strong>' ),
                $plugin_data['PluginURI'],
                $plugin_data['Name'],
                $plugin_data['AuthorName']
            );
            return str_replace( '</span>', '', $footer_text ) . ' | ' . $thank_text . '</span>';
        } else {
            return $footer_text;
        }
    }
    
    private function license_expired($exp_date) {
        $today = date('Y-m-d H:i:s');
        return $exp_date < $today;
    }

    public function admin_notice() {
        $lic = get_option($this->option_name);
        if (isset($_GET['page']) && $_GET['page'] === $this->slug) {
            if (isset($lic['license_expires']) && $this->license_expired($lic['license_expires'])) {
                echo '<div class="notice notice-error is-dismissible">';
                echo '<p>'.$this->option_name . esc_html__('Your license key has expired.', $this->slug) . '</p>';
                echo '</div>';
            } elseif (isset($lic['license_status']) && $lic['license_status'] !== 'valid') {
                $url = esc_url(admin_url('admin.php?page=' . $this->slug . '&tab=license'));
                echo '<div class="notice notice-warning is-dismissible">';
                echo '<p>' . sprintf(
                    /* translators: 1. link to plugin site; 2. link to plugin name */
                    __('Activate <a href="%1$s">your license</a> to enable access to updates, support & PRO features for <strong>%2$s</strong>.', $this->slug),
                    esc_url($url),
                    esc_html($this->title)
                ) . '</p>';
                echo '</div>';
            }
        }
    }

    public function get_tab_by_slug($slug)
    {
        foreach ($this->tabs as $tab) {
            if ($tab->slug === $slug) {
                return $tab;
            }
        }

        return false;
    }

    public function get_active_tab()
    {
        $default = $this->tabs[0] ?? false;

        if (isset($_GET['tab'])) {
            return in_array($_GET['tab'], array_map(function ($tab) {
                return $tab->slug;
            }, $this->tabs)) ? $this->get_tab_by_slug($_GET['tab']) : $default;
        }

        return $default;
    }

    public function add_tab($title, $slug = null)
    {
        $tab = new Tab($this, $title, $slug);

        $this->tabs[] = $tab;

        return $tab;
    }

    public function add_section($title, $args = [])
    {
        if (empty($this->tabs)) {
            $tab = $this->add_tab('Unnamed tab');
        } else {
            $tab = end($this->tabs);
        }

        return $tab->add_section($title, $args);
    }

    public function add_option($type, $args = [])
    {
        $tab = end($this->tabs);

        if (! $tab instanceof Tab) {
            return false;
        }

        $section = end($tab->sections);

        if (! $section instanceof Section) {
            return false;
        }

        return $section->add_option($type, $args);
    }

    public function should_make_tabs() {
        return count($this->tabs) > 1;
    }

    public function get_url()
    {
        if ($this->parent_slug && strpos($this->parent_slug, '.php') !== false) {
            return \add_query_arg('page', $this->slug, \admin_url($this->parent_slug));
        }

        return \admin_url("admin.php?page=$this->slug");
    }
    
    public function get_full_url() {
        $params = [];

        if ($active_tab = $this->get_active_tab()) {
            $params['tab'] = $active_tab->slug;

            if ($active_section = $active_tab->get_active_section()) {
                $params['section'] = $active_section->slug;
            }
        }
        
        return add_query_arg($params, $this->get_url());
    }

    public function render_tab_menu()
    {
        if (! $this->should_make_tabs()) {
            return;
        }

        Helpers::view('tab-menu', ['settings' => $this]);
    }

    public function render_active_sections()
    {
        Helpers::view('sections', ['settings' => $this]);
    }

    public function render()
    {
        Enqueuer::setEnqueueManager(new EnqueueManager);

        do_action('az_settings_before_render_settings_page');

        Enqueuer::enqueue();

        Helpers::view('settings-page', ['settings' => $this]);

        do_action('wp_settings_after_render_settings_page');
    }

    public function get_options()
    {
        return get_option($this->option_name, []);
    }

    public function find_option($search_option)
    {
        foreach ($this->tabs as $tab) {
            foreach ($tab->sections as $section) {
                foreach ($section->options as $option) {
                    if ($option->args['name'] == $search_option) {
                        return $option;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Xử lý việc lưu các cài đặt vào database.
     *
     * @since 1.0.0
     */
    public function save() {
        if (! isset($_POST['_wpnonce']) || ! wp_verify_nonce($_POST['_wpnonce'], 'wp_settings_save_'.$this->option_name)) {
            return;
        }

        if (! current_user_can($this->capability)) {
            wp_die(__('You need a higher level of permission.'));
        }
        
        if (isset($_POST['reset'])) {
            return $this->reset();
        }

        $current_options = $this->get_options();
        $submitted_options = $_POST[$this->option_name] ?? [];
        $options_to_save = $this->get_options();
        
        // Logic lưu các option (không phải license key)
        foreach ($this->get_active_tab()->get_active_sections() as $section) {
            foreach ($section->options as $option) {
                if (!method_exists($option->implementation, 'get_option_key_path')) continue;
                $key_path = $option->implementation->get_option_key_path();
                // Bỏ qua license key để không lưu vào mảng option chính
                if ($option->get_arg('name') === 'license_key') continue;
                $value = (new Dot($submitted_options))->get($key_path);
                (new Dot($options_to_save))->set($key_path, $option->implementation->sanitize($value));
            }
        }
        
        update_option($this->option_name, $options_to_save);
        
        // Kích hoạt hook và truyền dữ liệu đã submit.
        do_action('wps_settings_saved', $this->option_name, $submitted_options);
    }
    
    public function ajax_save() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], $this->slug . '_ajax_nonce')) {
            wp_send_json_error(__('Invalid nonce.'), 403);
        }
        if (!current_user_can($this->capability)) {
            wp_send_json_error(__('You need a higher level of permission.'), 403);
        }
        
        parse_str($_POST['settings'] ?? '', $decoded_data);
        $submitted_options = $decoded_data[$this->option_name] ?? [];

        // Lưu các cài đặt thông thường
        $options_to_save = get_option($this->option_name, []);
        $options_to_save = array_merge($options_to_save, $submitted_options);
        unset($options_to_save['license_key']);
        update_option($this->option_name, $options_to_save);

        do_action('wps_settings_saved', $this->option_name, $submitted_options);

        // Logic quyết định khi nào cần tải lại trang
        $reload_page = false;

        $referer_url = $decoded_data['_wp_http_referer'] ?? '';
        parse_str(parse_url($referer_url, PHP_URL_QUERY) ?? '', $referer_query);
        $active_tab_slug = $referer_query['tab'] ?? null;

        // Nếu không có tham số 'tab', giả định đó là tab đầu tiên.
        if ($active_tab_slug === null && !empty($this->tabs)) {
            $active_tab_slug = $this->tabs[0]->slug;
        }

        // Lấy slug của tab Module (thường là tab đầu tiên, không có slug riêng)
        $module_tab_slug = !empty($this->tabs) ? $this->tabs[0]->slug : 'modules';
        if (strpos($this->tabs[0]->title, 'Modules') !== false) {
             $module_tab_slug = $this->tabs[0]->slug;
        }

        // Nếu người dùng vừa submit key HOẶC đang ở tab "Modules", thì cần reload
        if (isset($submitted_options['license_key']) || $active_tab_slug === $module_tab_slug) {
            $reload_page = true;
        }

        wp_send_json_success([
            'message' => __('Changes saved successfully!', 'az-settings'),
            'type'    => 'success',
            'reload'  => $reload_page,
        ]);
    }

    /**
     * Xử lý yêu cầu khôi phục cài đặt qua AJAX.
     */
    public function ajax_restore(){
        // 1. Kiểm tra bảo mật (nonce và quyền của người dùng)
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'azs_restore_nonce')) {
            wp_send_json_error(['message' => __('Security check failed.', 'az-settings')], 403);
            return;
        }

        if (!current_user_can($this->capability)) {
            wp_send_json_error(['message' => __('You do not have permission.', 'az-settings')], 403);
            return;
        }

        // 2. Gọi hàm reset hiện có để thực hiện việc khôi phục
        // Hàm này sẽ tự động reset các cài đặt của tab đang hoạt động.
        $this->reset();

        // 3. Trả về thông báo thành công và URL để chuyển hướng
        wp_send_json_success([
            'message' => __('Settings have been restored to default successfully!', 'az-settings'),
            // URL của trang Module chính (trang gốc của plugin)
            'redirect_url' => admin_url('admin.php?page=' . $this->slug)
        ]);
    }
    
    public function reset() {
        $default_options = $this->get_default_options();
        update_option($this->option_name, $default_options);
    }
    
    public function get_default_options()
    {
        $default_options = [];
        foreach ($this->get_active_tab()->get_active_sections() as $section) {
            foreach ($section->options as $option) {
                $default_options[$option->implementation->get_option_key_path()] = $option->implementation->get_default_value();
            }
        }
        return $default_options;
    }

    /**
     * @deprecated
     */
    public function maybe_unset_options($current_options, $new_options)
    {
        if (! isset($_REQUEST['wp_settings_submitted'])) {
            return $current_options;
        }

        foreach ($_REQUEST['wp_settings_submitted'] as $submitted) {
            if (empty($new_options[$submitted])) {
                unset($current_options[$submitted]);
            }
        }

        return $current_options;
    }

}
