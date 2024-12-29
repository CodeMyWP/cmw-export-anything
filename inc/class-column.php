<?php
namespace CodeMyWP\Plugins\ExportAnything;

class Column {

    public static $table_name_without_prefix = 'cmw_ea_columns';

    public function __construct() {
        add_action('wp_ajax_cmw_ea_add_column', [$this, 'ajax_add_column']);
        add_action('wp_ajax_cmw_ea_delete_column', [$this, 'ajax_delete_column']);
    }

    public static function add($post_type_id, $name, $key, $type) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table_name_without_prefix;

        $result = $wpdb->insert(
            $table_name,
            [
                'post_type_id' => $post_type_id,
                'name' => $name,
                'key' => $key,
                'type' => $type
            ]
        );

        if ($result) {
            return $wpdb->insert_id;
        } else {
            return false;
        }
    }

    public static function update($id, $args) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table_name_without_prefix;

        return $wpdb->update(
            $table_name,
            $args,
            ['id' => $id]
        );
    }

    public static function get($args = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name_without_prefix;
        $sql = "SELECT ";
        if(isset($args['columns'])) {
            $columns = array_map(function($column) {
                return "`$column`";
            }, $args['columns']);
            $sql .= implode(",", $columns);
        } else {
            $sql .= "*";
        }
        $sql .= " FROM $table_name WHERE 1=1";
        if(isset($args['conditions'])) {
            $conditions = $args['conditions'];
            foreach($conditions as $key => $condition) {
                if(!is_array($condition)) {
                    $sql .= " AND {$key}={$condition}";
                } else {
                    $sql .= " AND {$condition['key']}{$condition['operator']}{$condition['value']}";
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

    public static function remove($id) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table_name_without_prefix;

        return $wpdb->delete(
            $table_name,
            ['id' => $id]
        );
    }

    public static function ajax_add_column() {
        check_ajax_referer('cmw_ea_add_column_nonce', 'security');

        if (!isset($_POST['post_type_id'], $_POST['name'], $_POST['key'], $_POST['type']) || empty($_POST['post_type_id']) || empty($_POST['name']) || empty($_POST['key']) || empty($_POST['type'])) {
            wp_send_json_error(['message' => 'Missing required parameters']);
        }

        $post_type_id = sanitize_text_field($_POST['post_type_id']);
        $name = sanitize_text_field($_POST['name']);
        $key = sanitize_text_field($_POST['key']);
        $type = sanitize_text_field($_POST['type']);

        if(isset($_POST['id']) && !empty($_POST['id'])) {
            $id = intval($_POST['id']);
            $result = self::update($id, [
                'name' => $name,
                'key' => $key,
                'type' => $type
            ]);
            if($result) {
                wp_send_json_success(['message' => 'Column updated successfully']);
            } else {
                wp_send_json_error(['message' => 'Failed to update column']);
            }
        } else {
            $result = self::add($post_type_id, $name, $key, $type);
            if ($result) {
                wp_send_json_success(['message' => 'Column added successfully', 'id' => $result]);
            } else {
                wp_send_json_error(['message' => 'Failed to add column']);
            }
        }
    }

    public function ajax_delete_column() {
        check_ajax_referer('delete_column_nonce', 'security');

        if (!isset($_POST['id']) || empty($_POST['id'])) {
            wp_send_json_error(['message' => 'Missing required parameter: id']);
        }

        $id = intval($_POST['id']);

        $result = self::remove($id);

        if ($result) {
            wp_send_json_success(['message' => 'Column deleted successfully']);
        } else {
            wp_send_json_error(['message' => 'Failed to delete column']);
        }
    }

}

return new Column();