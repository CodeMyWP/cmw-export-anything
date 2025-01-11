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
     * Retrieve post types from the database.
     *
     * @param array $args {
     *     Optional. Arguments to filter the query.
     *
     *     @type array $columns    Columns to select.
     *     @type array $conditions Conditions for the WHERE clause.
     *     @type int   $per_page   Number of results per page.
     * }
     * @return array|object|null Database query results.
     */
    public static function get($args = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name_without_prefix;
        $sql = "SELECT ";
        if(isset($args['columns'])) {
            $sql .= implode(",", $args['columns']);
        } else {
            $sql .= "*";
        }
        $sql .= " FROM $table_name WHERE 1=1";
        if(isset($args['conditions'])) {
            $conditions = $args['conditions'];
            foreach($conditions as $key => $condition) {
                if(!is_array($condition)) {
                    $sql .= $wpdb->prepare(" AND {$key}=%s", $condition);
                } else {
                    $sql .= $wpdb->prepare(" AND {$condition['key']}{$condition['operator']}%s", $condition['value']);
                }
            }
        }
        
        if(isset($args['columns']) && isset($args['per_page'])) {
            if(sizeof($args['columns']) == 1 && $args['per_page'] == 1) {
                return $wpdb->get_var($sql);
            }
        }
        return $wpdb->get_results($sql);
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