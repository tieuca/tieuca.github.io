<?php

namespace TieuCA\WPSettings;
use TieuCA\WPSettings\Module;
use TieuCA\WPSettings\Import;
use TieuCA\WPSettings\Export;
use TieuCA\WPSettings\ExportDatabase;
use TieuCA\WPSettings\Restore;
use TieuCA\WPSettings\Options\Checkbox;
use TieuCA\WPSettings\Options\CheckboxMultiple;
use TieuCA\WPSettings\Options\Choices;
use TieuCA\WPSettings\Options\CodeEditor;
use TieuCA\WPSettings\Options\Color;
use TieuCA\WPSettings\Options\Image;
use TieuCA\WPSettings\Options\Media;
use TieuCA\WPSettings\Options\Select;
use TieuCA\WPSettings\Options\Select2;
use TieuCA\WPSettings\Options\Text;
use TieuCA\WPSettings\Options\Button;
use TieuCA\WPSettings\Options\Password;
use TieuCA\WPSettings\Options\Textarea;
use TieuCA\WPSettings\Options\Video;
use TieuCA\WPSettings\Options\WPEditor;
use TieuCA\WPSettings\Options\HTML;

class Option
{
    public $section;

    public $type;

    public $args = [];

    public $implementation;

    public function __construct($section, $type, $args = [])
    {
        $this->section = $section;
        $this->type = $type;
        $this->args = $args;

        $type_map = apply_filters('az_settings_option_type_map', [
            'text'      => Text::class,
            'button'    => Button::class,
            'password'  => Password::class,
            'checkbox'  => Checkbox::class,
            'choices'   => Choices::class,
            'textarea'  => Textarea::class,
            'wp-editor' => WPEditor::class,
            'select'    => Select::class,
            'select2'   => Select2::class,
            'color'     => Color::class,
            'media'     => Media::class,
            'image'     => Image::class,
            'video'     => Video::class,
            'module'    => Module::class,
            'import'    => Import::class,
            'export'    => Export::class,
            'restore'   => Restore::class,
            'html'      => Options\HTML::class,
            'code-editor'   => CodeEditor::class,
            'checkbox-multiple'     => CheckboxMultiple::class,
            'export_database'       => ExportDatabase::class,
        ]);

        $this->implementation = new $type_map[$this->type]($section, $args);
    }

    public function get_arg($key, $fallback = null)
    {
        return $this->args[$key] ?? $fallback;
    }

    public function sanitize($value)
    {
        if (\is_callable($this->get_arg('sanitize'))) {
            return $this->get_arg('sanitize')($value);
        }

        return $this->implementation->sanitize($value);
    }

    public function validate($value)
    {
        if (is_array($this->get_arg('validate'))) {
            foreach ($this->get_arg('validate') as $validate) {
                if (! \is_callable($validate['callback'])) {
                    continue;
                }

                $valid = $validate['callback']($value);

                if (! $valid) {
                    $this->section->tab->settings->errors->add($this->get_arg('name'), $validate['feedback']);

                    return false;
                }
            }

            return true;
        }

        if (\is_callable($this->get_arg('validate'))) {
            return $this->get_arg('validate')($value);
        }

        return $this->implementation->validate($value);
    }

    public function render()
    {
        if (\is_callable($this->get_arg('visible')) && $this->get_arg('visible')() === false) {
            return;
        }

        if (\is_callable($this->get_arg('render'))) {
            echo $this->get_arg('render')($this->implementation);

            return;
        }

        echo $this->implementation->render();
    }
}
