<?php
namespace CodeMyWP\Plugins\ExportAnything;

class Column {

    /**
     * @var string
     */
    public static $table_name_without_prefix = 'cmw_ea_columns';

    /**
     * Column constructor.
     */
    public function __construct() {
        add_action('wp_ajax_cmw_ea_add_column', [$this, 'ajax_add_column']);
        add_action('wp_ajax_cmw_ea_delete_column', [$this, 'ajax_delete_column']);
    }

    /**
     * Add a column to the database
     * 
     * @param string $post_type_id
     * @param string $name
     * @param string $key
     * @param string $type
     * 
     * @return bool|int
     */
    public static function add($post_type_id, $name, $key, $type) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table_name_without_prefix;

        $result = $wpdb->insert(
            $table_name,
            [
                'post_type_id' => sanitize_text_field($post_type_id),
                'name' => sanitize_text_field($name),
                'column_key' => sanitize_text_field($key),
                'type' => sanitize_text_field($type)
            ]
        );

        if ($result) {
            return $wpdb->insert_id;
        } else {
            return false;
        }
    }

    /**
     * Get a column by ID
     * 
     * @param int $id
     * 
     * @return object|null
     */
    public static function update($id, $args) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table_name_without_prefix;

        return $wpdb->update(
            $table_name,
            $args,
            ['id' => intval($id)]
        );
    }

    /**
     * Get columns by Post Type ID
     * 
     * @param int $id
     * 
     * @return object|null
     */
    public static function get_by_post_type_id($post_type_id) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table_name_without_prefix;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM %i WHERE post_type_id = %d",
                $table_name,
                $post_type_id
            )
        );
    }

    /**
     * Remove a column from the database
     * 
     * @param int $id
     * 
     * @return bool
     */
    public static function remove($id) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table_name_without_prefix;

        return $wpdb->delete(
            $table_name,
            ['id' => intval($id)]
        );
    }

    /**
     * AJAX: Add a column
     * 
     * @return void
     */
    public static function ajax_add_column() {
        check_ajax_referer('cmw_ea_add_column_nonce', 'security');

        if (!isset($_POST['post_type_id'], $_POST['name'], $_POST['key'], $_POST['type']) || empty($_POST['post_type_id']) || empty($_POST['name']) || empty($_POST['key']) || empty($_POST['type'])) {
            wp_send_json_error(['message' => __('Missing required parameters', 'cmw-export-anything')]);
        }

        $post_type_id = sanitize_text_field(wp_unslash($_POST['post_type_id']));
        $name = sanitize_text_field(wp_unslash($_POST['name']));
        $key = sanitize_text_field(wp_unslash($_POST['key']));
        $type = sanitize_text_field(wp_unslash($_POST['type']));

        if(isset($_POST['id']) && !empty($_POST['id'])) {
            $id = intval($_POST['id']);
            $result = self::update($id, [
                'name' => $name,
                'column_key' => $key,
                'type' => $type
            ]);
            if($result) {
                wp_send_json_success(['message' => __('Column updated successfully', 'cmw-export-anything')]);
            } else {
                wp_send_json_error(['message' => __('Failed to update column', 'cmw-export-anything')]);
            }
        } else {
            $result = self::add($post_type_id, $name, $key, $type);
            if ($result) {
                wp_send_json_success(['message' => __('Column added successfully', 'cmw-export-anything'), 'id' => $result]);
            } else {
                wp_send_json_error(['message' => __('Failed to add column', 'cmw-export-anything')]);
            }
        }
    }

    /**
     * AJAX: Delete a column
     * 
     * @return void
     */
    public function ajax_delete_column() {
        check_ajax_referer('cmw_ea_delete_column_nonce', 'security');

        if (!isset($_POST['id']) || empty($_POST['id'])) {
            wp_send_json_error(['message' => __('Missing required parameter: id', 'cmw-export-anything')]);
        }

        $id = intval($_POST['id']);

        $result = self::remove($id);

        if ($result) {
            wp_send_json_success(['message' => __('Column deleted successfully', 'cmw-export-anything')]);
        } else {
            wp_send_json_error(['message' => __('Failed to delete column', 'cmw-export-anything')]);
        }
    }

}

return new Column();