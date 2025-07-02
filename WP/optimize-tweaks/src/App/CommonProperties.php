<?php
namespace OXT\App;

//use OXT\App\Config;

trait CommonProperties {
    protected $name;
    protected $id;
    protected $slug;
    protected $version;
    protected $option_name;
    protected $plugin_url;

    /**
     * Khởi tạo các thuộc tính chung từ Config
     */
    protected function initialize_common_properties() {
        $this->name         = Config::get('name');
        $this->id           = Config::get('id');
        $this->slug         = Config::get('slug');
        $this->version      = Config::get('version');
        $this->option_name  = Config::get('option_name');
        $this->plugin_url   = Config::get('plugin_url');
    }

    // Getter methods (tuỳ chọn)
    public function get_name() {
        return $this->name;
    }

    public function get_id() {
        return $this->id;
    }

    public function get_slug() {
        return $this->slug;
    }

    public function get_version() {
        return $this->version;
    }

    public function get_option_name() {
        return $this->option_name;
    }

    public function get_plugin_url() {
        return $this->plugin_url;
    }
}