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
            echo "Template file not found: " . esc_url($file_path);
        }
    }

    public static function get_wp_post_types() {
        $post_types = get_transient('cmw_ea_wp_post_types');
        if(!$post_types) {
            $post_types = get_post_types(array('public' => true), 'objects');
            set_transient('cmw_ea_wp_post_types', $post_types, 60 * 60 * 24 * 365);
        }
        return $post_types;
    }

    public static function get_wp_post_type_name($slug) {
        $post_types = self::get_wp_post_types();
        return isset($post_types[sanitize_key($slug)]) ? $post_types[sanitize_key($slug)]->labels->singular_name : null;
    }

    public static function get_wp_posts_columns() {
        global $wpdb;
        $columns = $wpdb->get_col("DESC {$wpdb->posts}", 0);
        return array_map('sanitize_text_field', $columns);
    }

    public static function get_post_meta_keys($post_type_id) {
        $post_type_id = absint($post_type_id);
        $post_type_type = PostType::get($post_type_id)->post_type;
        
        global $wpdb;
        $meta_keys = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT meta_key FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE p.post_type = %s",
            $post_type_type
        ));
        return array_map('sanitize_text_field', $meta_keys);
    }
}