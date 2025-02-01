<?php

namespace CodeMyWP\Plugins\ExportAnything;

// Prevent direct access.
if(!defined('ABSPATH')) {
    exit;
}

/**
 * Class PostType
 *
 * Handles operations related to custom post types.
 */
class PostType {

    /**
     * @var string The table name without the WordPress table prefix.
     */
    public static $table_name_without_prefix = 'cmw_ea_post_types';

    /**
     * Get a post type from the database.
     * 
     * @param int $id The ID of the post type to get.
     * @return object|false The post type object, or false if not found.
     */
    public static function get($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name_without_prefix;
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM %i WHERE id = %d",
            $table_name,
            $id
        ));
        return $result;
    }

    /**
     * Get all post types from the database.
     * 
     * @return array An array of post types.
     */
    public static function get_all() {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name_without_prefix;
        $results = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM %i", $table_name)
        );
        return $results;
    }

    /**
     * Add a new post type to the database.
     *
     * @param array $args Data to insert into the database.
     * @return int|false The number of rows inserted, or false on error.
     */
    public static function add($args) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name_without_prefix;
        $result = $wpdb->insert(
            $table_name,
            $args
        );
        if ($result !== false) {
            return $wpdb->insert_id;
        }
        return false;
    }

    /**
     * Delete a post type from the database.
     *
     * @param int $post_type_id The ID of the post type to delete.
     * @return int|false The number of rows deleted, or false on error.
     */
    public static function delete($post_type_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name_without_prefix;
        $result = $wpdb->delete(
            $table_name,
            array('id' => $post_type_id),
            array('%d')
        );
        return $result;
    }
}

return new PostType();