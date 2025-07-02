<?php
/**
 * Consolidated Helper utility class for the Framework.
 * Contains reusable static methods for the framework.
 *
 * @package     AZ_Settings
 * @subpackage  Framework/WPSettings
 * @since       1.8.0
 */
namespace TieuCA\WPSettings;

class Helpers {

    /**
     * Renders a view file. This replaces the old procedural view() function.
     *
     * @param string $file The view file name (without .php extension).
     * @param array $variables The variables to extract for the view.
     * @return void
     */
    public static function view($file, $variables = [])
    {
        extract($variables, EXTR_SKIP);

        $full_path = __DIR__."/../resources/views/{$file}.php";

        if (! file_exists($full_path)) {
            return;
        }

        ob_start();
        include $full_path;
        echo apply_filters('az_settings_render_view', ob_get_clean(), $file, $variables);
    }

    /**
     * Normalizes a string into a slug-like format.
     * It converts to lowercase, removes accents, and replaces spaces with hyphens.
     *
     * @param string $string The input string.
     * @return string The normalized string.
     */
    public static function normalizeString($string) {
        // Use WordPress's reliable remove_accents function if available.
        if (function_exists('remove_accents')) {
            $string = remove_accents($string);
        }

        // Convert to lowercase.
        $string = strtolower($string);
        
        // Remove all characters that are not letters, numbers, spaces, or hyphens.
        $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
        
        // Replace sequences of spaces and/or hyphens with a single hyphen.
        $string = preg_replace('/[\s-]+/', '-', $string);

        // Trim hyphens from the beginning and end of the string.
        $string = trim($string, '-');
        
        return $string;
    }
}
