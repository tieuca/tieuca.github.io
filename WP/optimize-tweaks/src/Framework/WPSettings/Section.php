<?php

namespace TieuCA\WPSettings;

use TieuCA\WPSettings\Traits\HasOptionLevel;

class Section {
    use HasOptionLevel;

    public $tab;

    public $as_link;

    public $title;

    public $slug;

    public $description;

    public $options = [];

    public $args = [];

    public function __construct($tab, $title, $args = [])
    {
        $this->tab = $tab;
        $this->title = $title;
        $this->args = $args;
        $this->slug = $this->args['slug'] ?? sanitize_title($title);
        $this->description = $this->args['description'] ?? null;
        $this->as_link = $this->args['as_link'] ?? false;
    }
    
    /**
     * Tạo thuộc tính data-condition.
     * @return string
     */
    public function get_condition_attribute() {
        $condition = $this->args['condition'] ?? null;
        if (empty($condition)) {
            return '';
        }
        return 'data-condition="' . esc_attr(json_encode($condition)) . '"';
    }
    
    public function add_option($type, $args = []){
        $option = new Option($this, $type, $args);

        $this->options[] = $option;

        return $option;
    }
}
