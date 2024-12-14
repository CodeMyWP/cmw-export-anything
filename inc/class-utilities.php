<?php

namespace CodeMyWP\Plugins\ExportAnything;

class Utilities {
    /**
     * Load a template file
     *
     * @param string $template_name
     * @param bool $is_admin
     * @param array $args
     */
    public static function load_template($template_name, $is_admin = false, $args = array()) {
        $directory = $is_admin ? 'templates/admin/' : '/templates/';
        $file_path = EXPORT_ANYTHING_DIR . $directory . $template_name . '.php';

        if (file_exists($file_path)) {
            if (!empty($args) && is_array($args)) {
                extract($args);
            }
            include $file_path;
        } else {
            error_log("Template file not found: " . $file_path);
        }
    }
}