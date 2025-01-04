<?php
namespace CodeMyWP\Plugins\ExportAnything;

class Export {

    public static $table_name_without_prefix = 'cmw_ea_exports';

    public function __construct($ajax = true) {
        if($ajax) {
            add_action('wp_ajax_cmw_ea_regsiter_export', array($this, 'register'));
        }
    }

    public static function add($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name_without_prefix;
        $wpdb->insert($table_name, $data);
        return $wpdb->insert_id;
    }

    public static function get($args) {
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

    public static function update($id, $data) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name_without_prefix;
        return $wpdb->update($table_name, $data, array('id' => $id), array('%d'));
    }

    public static function delete($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name_without_prefix;
        return $wpdb->delete($table_name, array('id' => $id), array('%d'));
    }

    public function register() {
        check_ajax_referer('cmw-ea-register-export', 'nonce');

        if(!isset($_POST['post_type_id'])) {
            wp_send_json_error(
                array(
                    'message' => 'Post type ID is required.'
                )
            );
        }

        if(!current_user_can('export')) {
            wp_send_json_error(array(
                'message' => 'You do not have permission to export.'
            ));
        }

        $post_type_id = $_POST['post_type_id'];

        // Create a CSV File in Uploads DIR
        $upload_dir = wp_upload_dir();
        $exports_dir = $upload_dir['basedir'] . '/cmw-ea-exports/';
        if(!file_exists($exports_dir)) {
            mkdir($exports_dir);
        }
        $file_path = $exports_dir . uniqid() . '.csv';

        $export_id = self::add(array(
            'post_type_id' => $post_type_id,
            'page' => 1,
            'per_page' => 100,
            'status' => 'pending',
            'file_path' => $file_path,
            'created_at' => current_time('mysql')
        ));

        wp_send_json_success(array(
            'message' => 'Export has been registered.',
            'export_id' => $export_id
        ));
    }

    public function process() {
        $exports = self::get(array(
            'status' => 'pending'
        ));

        foreach ($exports as $export) {
            $this->export($export);
        }
    }

    public function export($export) {
        $export_id = $export->id;

        $this->update($export_id, array('status' => 'processing'));

        $post_type_id = $export->post_type_id;
        $file_path = $export->file_path;
        $page = $export->page;
        $per_page = $export->per_page;
        
        $post_type = PostType::get(array(
            'per_page' => 1,
            'columns' => array('post_type'), 
            'conditions' => array(
                'id' => $post_type_id
            ),
        ));

        $columns = Column::get(array(
            'columns' => array('id', 'name', 'key', 'type'), 
            'conditions' => array(
                'post_type_id' => $post_type_id
            )
        ));

        if(sizeof($columns) == 0) {
            wp_send_json_error(array(
                'message' => 'No columns found for this post type.'
            ));
        }

        $result = $this->query($post_type, $columns, $page, $per_page);

        $this->update_file($file_path, $result);

        if(sizeof($result) < $per_page) {
            $this->update($export_id, array('status' => 'completed'));
        } else {
            $this->update($export_id, array('page' => $page + 1, 'status' => 'pending'));
        }
    }

    public function query($post_type, $columns, $page = 1, $per_page = 100) {

        $offset = ($page - 1) * $per_page;

        global $wpdb;

        $select_columns = [];
        $join_clauses = [];
        $where_clauses = [];

        foreach ($columns as $column) {
            if ($column->type == 'postmeta') {
                $meta_key = $column->key;
                $meta_alias = 'meta_' . $meta_key;
                $select_columns[] = "$meta_alias.meta_value AS '{$column->name}'";
                $join_clauses[] = "LEFT JOIN {$wpdb->postmeta} AS $meta_alias ON {$wpdb->posts}.ID = $meta_alias.post_id AND $meta_alias.meta_key = '$meta_key'";
            } else {
                $select_columns[] = "{$wpdb->posts}.{$column->key} AS '{$column->name}'";
            }
        }

        $select_clause = implode(', ', $select_columns);
        $join_clause = implode(' ', $join_clauses);
        $where_clause = implode(' AND ', $where_clauses);

        $query = "
            SELECT {$wpdb->posts}.ID, $select_clause
            FROM {$wpdb->posts}
            $join_clause
            WHERE {$wpdb->posts}.post_type = %s
            LIMIT %d, %d
        ";

        $sql = $wpdb->prepare($query, $post_type, $offset, $per_page);

        $results = $wpdb->get_results($sql);

        return $results;
    }

    public function update_file($file_path, $data) {
        if (!file_exists($file_path)) {
            $file = fopen($file_path, 'w');
            if ($file === false) {
                error_log("Failed to open file for writing: $file_path");
                return;
            }
            fputcsv($file, array_keys((array)$data[0]));
            fclose($file);
        }

        $file = fopen($file_path, 'a');
        if ($file === false) {
            error_log("Failed to open file for appending: $file_path");
            return;
        }

        foreach ($data as $row) {
            fputcsv($file, (array)$row);
        }

        fclose($file);
    }
}

return new Export();