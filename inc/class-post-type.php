<?php

namespace CodeMyWP\Plugins\ExportAnything;

class PostType {

    public static $table_name_without_prefix = 'cmw_ea_post_types';

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

    public static function add($args) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name_without_prefix;
        $result = $wpdb->insert(
            $table_name,
            $args
        );
        return $result;
    }

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