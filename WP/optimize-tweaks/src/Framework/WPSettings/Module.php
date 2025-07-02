<?php

namespace TieuCA\WPSettings;

use TieuCA\WPSettings\Options\OptionAbstract;
use TieuCA\WPSettings\Enqueuer;

class Module extends OptionAbstract
{
    public $view = 'module';

    public function __construct($section, $args = [])
    {
        add_action('az_settings_before_render_settings_page', [$this, 'enqueue']);

        parent::__construct($section, $args);
    }
    
    public function get_name_attribute()
    {
        $name = parent::get_name_attribute();

        return "{$name}[]";
    }

    public function sanitize($value)
    {
        return (array) $value;
    }
    
    public function enqueue()
    {
        Enqueuer::add('wps-module', function () {
            ?>
            <style>
                .wps-module {
                    padding: 10px 0 0 20px !important;
                }
                .wps-module li {
                    border-radius: 4px; padding-top:10px;
                }
                .wps-module label svg {
                    float: right;
                }
                .wps-module label span {
                    margin-right: 5px;
                }
            </style>
            <?php
        });
    }
    
    public function render() { ?>
        <tr valign="top" class="<?php echo $this->get_row_classes(); ?>">
            <td colspan="2" class="wps-module">
                <div class="module-container">
                    <?php
                    // Lấy danh sách các module từ tham số 'options'
                    $modules = $this->get_arg('options', []);
                    foreach ($modules as $key => $module) {
                        // Kiểm tra xem module có phải là mảng hay không
                        if (is_array($module)) {
                            $label = $module['label'] ?? '';
                            $description = $module['description'] ?? '';
                            $icon = $module['icon'] ?? '';
                            ?>
                            <div class="card">
                                <div class="card__header">
                                    <span class="card__title">
                                    <?php if (!empty($icon)) {
                                        if ( strpos($icon, '<svg') === 0 || strpos($icon, '<span') === 0 ) {
                                            echo html_entity_decode(esc_html($icon));
                                        } else {
                                            echo '<img src="'. esc_url($icon) .'" alt="Icon" />';
                                        }
                                    } ?>
                                        <?php echo esc_html($label); ?>
                                    </span>
                                    <span class="switch">
                                        <input type="checkbox" id="<?php echo $this->get_id_attribute(); ?>_<?php echo $key; ?>" name="<?php echo esc_attr($this->get_name_attribute()); ?>" value="<?php echo $key; ?>" <?php echo in_array($key, $this->get_value_attribute() ?? []) ? 'checked' : ''; ?>  class="toggle <?php echo $this->get_input_class_attribute(); ?>">
                                        <span class="slider"></span>
                                    </span>
                                </div>
                                <p class="card__content"><?php echo esc_html($description); ?></p>
                                <div class="card__arrow"></div>
                            </div>
                        <?php }
                    } ?>
                </div>
                <?php if ($description = $this->get_arg('description')) { ?>
                    <p class="description"><?php echo esc_html($description); ?></p>
                <?php } ?>
    
                <?php if ($error = $this->has_error()) { ?>
                    <div class="wps-error-feedback"><?php echo esc_html($error); ?></div>
                <?php } ?>
            </td>
        </tr>
        <?php
    }
}
