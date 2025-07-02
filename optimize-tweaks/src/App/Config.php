<?php
namespace OXT\App;

class Config {
    // Các hằng số cấu hình logic
    const PLUGIN_NAME     = 'X Tweaks';
    const PLUGIN_SLUG     = 'optimize-tweaks';

    private static $config = [];

    // Khởi tạo một lần để nạp cấu hình
    public static function init() {
        self::$config = [
            'name'              => self::PLUGIN_NAME,
            'slug'              => self::PLUGIN_SLUG,
            'option_name'       => self::PLUGIN_SLUG . '_option',
            'version'           => defined('OXT_VERSION') ? OXT_VERSION : '1.0.0',
            'plugin_url'        => defined('OXT_URL') ? OXT_URL : '',
        ];
    }

    // Phương thức tĩnh để lấy giá trị cấu hình
    public static function get($key, $default = null) {
        if (empty(self::$config)) {
            self::init();
        }
        return self::$config[$key] ?? $default;
    }
}