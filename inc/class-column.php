<?php
namespace CodeMyWP\Plugins\ExportAnything;

class Column {

    public static $table_name_without_prefix = 'cmw_ea_columns';

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
            $sql .= implode(",", $args['columns']);
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
}