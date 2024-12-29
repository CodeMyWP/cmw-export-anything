<?php
namespace CodeMyWP\Plugins\ExportAnything;

class Export {

    public static $table_name_without_prefix = 'cmw_ea_exports';

    public function __construct() {
        
    }

    public static function add($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name_without_prefix;
        $wpdb->insert($table_name, $data);
        return $wpdb->insert_id;
    }

    public static function get($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name_without_prefix;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
    }

    public static function delete($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name_without_prefix;
        return $wpdb->delete($table_name, array('id' => $id), array('%d'));
    }

    public static function process($id) {

        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['basedir'] . '/exports/export_' . $id . '_' . date('YmdHis') . '.csv';

        $export_id = self::add(array(
            'post_type_id' => $id,
            'status' => 'processing',
            'file_path' => $file_path,
            'created_at' => current_time('mysql')
        ));
        
    }
}