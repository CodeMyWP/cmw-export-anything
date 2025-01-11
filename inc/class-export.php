<?php
namespace CodeMyWP\Plugins\ExportAnything;

use Error;

class Export {

    public static $table_name_without_prefix = 'cmw_ea_exports';

    public function __construct($cron = false) {
        if(!$cron) {

            // Register a Export
            add_action('wp_ajax_cmw_ea_regsiter_export', array($this, 'register'));

            // Process Export
            add_action('wp_ajax_cmw_ea_start_export', array($this, 'start'));

            // Download Export
            add_action('admin_post_cmw_ea_download_export', array($this, 'download'));
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

        $sql .= " ORDER BY id DESC";

        if(isset($args['per_page'])) {
            $sql .= " LIMIT {$args['per_page']}";
        }
        
        if(isset($args['columns']) && isset($args['per_page'])) {
            if(sizeof($args['columns']) == 1 && $args['per_page'] == 1) {
                return $wpdb->get_var($sql);
            }
        }

        if(isset($args['per_page']) && $args['per_page'] == 1) {
            return $wpdb->get_row($sql);
        }

        return $wpdb->get_results($sql);
    }

    public static function update($id, $data) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name_without_prefix;
        return $wpdb->update($table_name, $data, array('id' => $id));
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

    public function start() {
        check_ajax_referer('cmw-ea-start-export', 'nonce');

        if(!isset($_POST['export_id'])) {
            wp_send_json_error(array(
                'message' => 'Export ID is required.'
            ));
        }

        if(!current_user_can('export')) {
            wp_send_json_error(array(
                'message' => 'You do not have permission to process exports.'
            ));
        }

        $export_id = $_POST['export_id'];
        $export = self::get(array(
            'per_page' => 1,
            'conditions' => array(
                'id' => $export_id
            )
        ));

        if($export->status != 'pending') {
            wp_send_json_error(array(
                'message' => 'Export is not pending.'
            ));
        }

        $total_items = $this->total($export->post_type_id);
        
        $this->export($export);

        $response = array(
            'export' => $export,
            'total_items' => intval($total_items)
        );

        if($export->status == 'completed') {
            $response['message'] = 'Congratulations! Your export is ready for download.';
            $response['download_url'] = admin_url('admin-post.php?action=cmw_ea_download_export&export_id=' . $export_id);
        } else {
            $response['message'] = 'Please wait the export is in progress.';
        }

        wp_send_json_success($response);
    }

    public function export_all() {
        $exports = self::get(array(
            'conditions' => array(
                'status' => "'pending'"
            )
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
            $export->status = 'completed';
        } else {
            $this->update($export_id, array('page' => $page + 1, 'status' => 'pending'));
            $export->page = $page + 1;
        }

        return $export;
    }

    public function total($post_type_id) {
        $post_type = PostType::get(array(
            'per_page' => 1,
            'columns' => array('post_type'), 
            'conditions' => array(
                'id' => $post_type_id
            ),
        ));
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s", $post_type));
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

    public function download() {
        if (!isset($_REQUEST['export_id'])) {
            wp_die('Export ID is required.');
        }

        if (!current_user_can('export')) {
            wp_die('You do not have permission to download exports.');
        }

        $export_id = $_REQUEST['export_id'];

        $export = self::get(array(
            'columns' => array('file_path'),
            'conditions' => array(
                'id' => $export_id
            )
        ));

        if (sizeof($export) == 0) {
            wp_die('Export not found.');
        }

        $file_path = $export[0]->file_path;

        if (!file_exists($file_path)) {
            wp_die('File not found.');
        }

        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        header('Content-Length: ' . filesize($file_path));

        readfile($file_path);
        exit;
    }
}

return new Export();