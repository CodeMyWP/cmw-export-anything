<?php
namespace CodeMyWP\Plugins\ExportAnything;


// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class Export
 * Handles the export functionality for the plugin.
 */
class Export {

    /**
     * @var string The table name without the WordPress prefix.
     */
    public static $table_name_without_prefix = 'cmw_ea_exports';

    /**
     * Export constructor.
     * @param bool $cron Whether the constructor is called for a cron job.
     */
    public function __construct($cron = false) {
        if(!$cron) {

            // Register a Export
            add_action('wp_ajax_cmw_ea_regsiter_export', array($this, 'register'));

            // Process Export
            add_action('wp_ajax_cmw_ea_start_export', array($this, 'start'));

            // Process Resume
            add_action('wp_ajax_cmw_ea_resume_export', array($this, 'resume'));

            // Download Export
            add_action('admin_post_cmw_ea_download_export', array($this, 'download'));

            // Deregister Export
            add_action('wp_ajax_cmw_ea_deregister_export', array($this, 'deregister'));
        }
    }

    /**
     * Add a new export record to the database.
     *
     * @param array $data The data to insert.
     * @return int The ID of the inserted record.
     */
    public static function add($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name_without_prefix;
        $wpdb->insert($table_name, $data);
        return $wpdb->insert_id;
    }

    /**
     * Get export record from the database by ID.
     *
     * @param int $id ID of Export record.
     * @return object The export record.
     */
    public static function get_by_id($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name_without_prefix;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM %i WHERE id=%d", $table_name, $id));
    }

    /**
     * Get export records from the database.
     * 
     * @param string $status The status of the export.
     * @return object The export records.
     */
    public static function get_by_status($status) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name_without_prefix;
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM %i WHERE status=%s", $table_name, $status));
    }

    /**
     * Get an export record from the database.
     * 
     * @param array $args The arguments to query the database.
     * @return object|array The export record, or an array of records.
     */
    public static function get_by_post_type_id($post_type_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name_without_prefix;
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM %i WHERE post_type_id=%d ORDER BY id DESC", $table_name, $post_type_id));
    }

    /**
     * Update an existing export record in the database.
     *
     * @param int $id The ID of the record to update.
     * @param array $data The data to update.
     * @return int|false The number of rows updated, or false on error.
     */
    public static function update($id, $data) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name_without_prefix;
        return $wpdb->update($table_name, $data, array('id' => $id));
    }

    /**
     * Delete an export record from the database.
     *
     * @param int $id The ID of the record to delete.
     * @return int|false The number of rows deleted, or false on error.
     */
    public static function delete($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name_without_prefix;
        return $wpdb->delete($table_name, array('id' => $id), array('%d'));
    }

    /**
     * Register a new export via AJAX.
     */
    public function register() {
        check_ajax_referer('cmw_ea_register_export', 'nonce');

        if(!isset($_POST['post_type_id'])) {
            wp_send_json_error(
                array(
                    'message' => esc_html__('Post type ID is required.', 'cmw-export-anything')
                )
            );
        }

        if(!current_user_can('export')) {
            wp_send_json_error(array(
                'message' => esc_html__('You do not have permission to export.', 'cmw-export-anything')
            ));
        }

        $post_type_id = sanitize_text_field(wp_unslash($_POST['post_type_id']));

        // Create a CSV File in Uploads DIR
        $exports_dir = Settings::get_setting('path');
        if(!file_exists($exports_dir)) {
            wp_mkdir_p($exports_dir);
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
            'message' => esc_html__('Export has been registered.', 'cmw-export-anything'),
            'export_id' => $export_id
        ));
    }

    /**
     * Deregister an export via AJAX.
     */
    public function deregister() {
        check_ajax_referer('cmw_ea_deregister_export', 'nonce');

        if(!isset($_POST['export_id'])) {
            wp_send_json_error(array(
                'message' => esc_html__('Export ID is required.', 'cmw-export-anything')
            ));
        }

        if(!current_user_can('export')) {
            wp_send_json_error(array(
                'message' => esc_html__('You do not have permission to deregister exports.', 'cmw-export-anything')
            ));
        }

        $export_id = sanitize_text_field(wp_unslash($_POST['export_id']));

        $export = self::get_by_id($export_id);

        if(!is_object($export)) {
            wp_send_json_error(array(
                'message' => esc_html__('Export not found.', 'cmw-export-anything')
            ));
        }

        if (file_exists($export->file_path)) {
            wp_delete_file($export->file_path);
        }

        $this->delete($export_id);

        wp_send_json_success(array(
            'message' => esc_html__('Export has been deregistered.', 'cmw-export-anything')
        ));
    }

    /**
     * Start processing an export via AJAX.
     */
    public function start() {
        check_ajax_referer('cmw_ea_start_export', 'nonce');

        if(!isset($_POST['export_id'])) {
            wp_send_json_error(array(
                'message' => esc_html__('Export ID is required.', 'cmw-export-anything')
            ));
        }

        if(!current_user_can('export')) {
            wp_send_json_error(array(
                'message' => esc_html__('You do not have permission to process exports.', 'cmw-export-anything')
            ));
        }

        $export_id = sanitize_text_field(wp_unslash($_POST['export_id']));
        $export = self::get_by_id($export_id);

        if($export->status != 'pending') {
            wp_send_json_error(array(
                'message' => esc_html__('Export is not pending.', 'cmw-export-anything')
            ));
        }

        $total_items = $this->total($export->post_type_id);
        
        $this->export($export);

        $response = array(
            'export' => $export,
            'total_items' => intval($total_items)
        );

        if($export->status == 'completed') {
            $response['message'] = esc_html__('Congratulations! Your export is ready for download.', 'cmw-export-anything');
            $response['download_url'] = esc_url_raw(admin_url('admin-post.php?action=cmw_ea_download_export&export_id=' . $export_id . '&cmw_ea_nonce=' . wp_create_nonce('cmw_ea_download_export')));
        } else {
            $response['message'] = esc_html__('Please wait the export is in progress.', 'cmw-export-anything');
        }

        wp_send_json_success($response);
    }

    /**
     * Export all pending exports.
     */
    public function export_all() {
        $exports = self::get_by_status('pending');

        foreach ($exports as $export) {
            $this->export($export);
        }
    }

    /**
     * Process a single export.
     *
     * @param object $export The export object.
     * @return object The updated export object.
     */
    public function export($export) {
        $export_id = $export->id;

        $this->update($export_id, array('status' => 'processing'));

        $post_type_id = $export->post_type_id;
        $file_path = $export->file_path;
        $page = $export->page;
        $per_page = $export->per_page;
        
        $post_type = PostType::get($post_type_id)->post_type;

        $columns = Column::get_by_post_type_id($post_type_id);

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

    /**
     * Get the total number of items for a post type.
     *
     * @param int $post_type_id The post type ID.
     * @return int The total number of items.
     */
    public function total($post_type_id) {
        $post_type = PostType::get($post_type_id)->post_type;
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s", $post_type));
    }

    /**
     * Query the database for export data.
     *
     * @param string $post_type The post type.
     * @param array $columns The columns to select.
     * @param int $page The page number.
     * @param int $per_page The number of items per page.
     * @return array The query results.
     */
    public function query($post_type, $columns, $page = 1, $per_page = 100) {

        $offset = ($page - 1) * $per_page;

        global $wpdb;

        $select_columns = [];
        $join_clauses = [];

        foreach ($columns as $column) {
            if ($column->type == 'postmeta') {
                $meta_key = $column->column_key;
                $meta_alias = 'meta_' . $meta_key;
                $select_columns[] = $wpdb->prepare("%i.meta_value AS %s", $meta_alias, $column->name);
                $join_clauses[] = $wpdb->prepare("LEFT JOIN %i AS %i ON %i.ID = %i.post_id AND %i.meta_key = %s", $wpdb->postmeta, $meta_alias, $wpdb->posts, $meta_alias, $meta_alias, $meta_key);
            } else {
                $select_columns[] = $wpdb->prepare("%i.%i AS %s", $wpdb->posts, $column->column_key, $column->name);
            }
        }

        $select_clause = implode(', ', $select_columns);
        $join_clause = implode(' ', $join_clauses);

        $sql = $wpdb->prepare("
            SELECT %i.ID, " . $select_clause . "
            FROM %i
            " . $join_clause . "
            WHERE %i.post_type = %s
            LIMIT %d, %d", $wpdb->posts, $wpdb->posts, $wpdb->posts, $post_type, $offset, $per_page);

        $results = $wpdb->get_results($sql);

        return $results;
    }

    /**
     * Update the export file with new data.
     *
     * @param string $file_path The file path.
     * @param array $data The data to write.
     */
    public function update_file($file_path, $data) {
        global $wp_filesystem;
    
        // Initialize the WP_Filesystem
        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        
        WP_Filesystem();
    
        if (!is_array($data) || empty($data)) {
            return; // Return if the data is invalid
        }
    
        // Check if the file exists, if not create it with headers
        if (!$wp_filesystem->exists($file_path)) {
            $headers = array_keys((array)$data[0]);
            $content = implode(',', $headers) . PHP_EOL;
    
            // Create the file and write headers
            if (!$wp_filesystem->put_contents($file_path, $content, FS_CHMOD_FILE)) {
                return; // Return if the file couldn't be created
            }
        }
    
        // Append the data to the file
        $content = '';
        foreach ($data as $row) {
            $content .= implode(',', (array)$row) . PHP_EOL;
        }
    
        if (!$wp_filesystem->put_contents($file_path, $wp_filesystem->get_contents($file_path) . $content, FS_CHMOD_FILE)) {
            return; // Return if the data couldn't be written
        }
    }    

    /**
     * Download the export file.
     */
    public function download() {

        check_admin_referer('cmw_ea_download_export', 'cmw_ea_nonce');

        if (!isset($_REQUEST['export_id'])) {
            wp_die(esc_html__('Export ID is required.', 'cmw-export-anything'));
        }

        if (!current_user_can('export')) {
            wp_die(esc_html__('You do not have permission to download exports.', 'cmw-export-anything'));
        }

        $export_id = sanitize_text_field(wp_unslash($_REQUEST['export_id']));

        $export = self::get_by_id($export_id);

        if (!is_object($export)) {
            wp_die(esc_html__('Export not found.', 'cmw-export-anything'));
        }

        $file_path = $export->file_path;

        global $wp_filesystem;

        // Initialize the WP_Filesystem
        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        WP_Filesystem();

        if (!$wp_filesystem->exists($file_path)) {
            wp_die(esc_html__('File not found.', 'cmw-export-anything'));
        }

        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        header('Content-Length: ' . filesize($file_path));

        $content = $wp_filesystem->get_contents($file_path);
        if ($content === false) {
            wp_die(esc_html__('Unable to read file.', 'cmw-export-anything'));
        }

        echo esc_html($content);
        
        exit;
    }
}

return new Export();