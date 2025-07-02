<?php

namespace TieuCA\WPSettings\Options;

use TieuCA\WPSettings\Enqueuer;

class Password extends OptionAbstract
{
    public $view = 'password';
    
    public function __construct($section, $args = [])
    {
        add_action('az_settings_before_render_settings_page', [$this, 'enqueue']);

        parent::__construct($section, $args);
    }

    public function sanitize($value)
    {
        return base64_encode($value);
    }

    public function enqueue()
    {
        Enqueuer::add('wps-password', function () {
            wp_enqueue_script('user-profile');
        });
    }

}
