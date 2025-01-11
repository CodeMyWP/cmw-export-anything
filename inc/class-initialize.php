<?php

namespace CodeMyWP\Plugins\ExportAnything;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Class Initialize
 *
 * Handles the initialization, activation, deactivation, and uninstallation of the plugin.
 */
class Initialize {
    
    /**
     * Initialize constructor.
     *
     * Registers activation, deactivation, and uninstall hooks, and adds action for plugin upgrade.
     */
    public function __construct() {
        // Register activation and deactivation hooks
        register_activation_hook(EXPORT_ANYTHING_FILE, [$this, 'activate']);
        register_deactivation_hook(EXPORT_ANYTHING_FILE, [$this, 'deactivate']);
        register_uninstall_hook(EXPORT_ANYTHING_FILE, [__CLASS__, 'uninstall']);

        // Add action for plugin upgrade
        add_action('upgrader_process_complete', [$this, 'upgrade'], 10, 2);
    }

    /**
     * Activate the plugin and initialize tables.
     *
     * @return void
     */
    public function activate() {
        $this->initialize_tables();
    }

    /**
     * Deactivate the plugin.
     *
     * @return void
     */
    public function deactivate() {
        // Deactivation code here
    }

    /**
     * Uninstall the plugin - Remove all tables
     *
     * @return void
     */
    public static function uninstall() {
        global $wpdb;

        $tables = [
            $wpdb->prefix . PostType::$table_name_without_prefix,
            $wpdb->prefix . Column::$table_name_without_prefix,
            $wpdb->prefix . Export::$table_name_without_prefix,
        ];

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }

    /**
     * Initialize the necessary tables.
     *
     * @return void
     */
    public function initialize_tables() {
        $this->create_post_types_table();
        $this->create_columns_table();
        $this->create_exports_table();
    }

    /**
     * Create the post types table in the database.
     *
     * @return void
     */
    public function create_post_types_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . PostType::$table_name_without_prefix;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            `name` text NOT NULL,
            post_type varchar(20) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create the columns table in the database.
     *
     * @return void
     */
    public function create_columns_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . Column::$table_name_without_prefix;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_type_id mediumint(9) NOT NULL,
            name text NOT NULL,
            `key` varchar(50) NOT NULL,
            type ENUM('posts', 'postmeta') NOT NULL,
            PRIMARY KEY  (id),
            INDEX post_type_id (post_type_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create the exports table in the database.
     *
     * @return void
     */
    public function create_exports_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . Export::$table_name_without_prefix;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_type_id mediumint(9) NOT NULL,
            status ENUM('pending','processing','completed','failed') NOT NULL,
            page int NOT NULL,
            per_page int NOT NULL,
            file_path text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            INDEX post_type_id (post_type_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Handle the plugin upgrade process to initialize tables.
     *
     * @param object $upgrader_object The upgrader object.
     * @param array $options The options for the upgrade process.
     * @return void
     */
    public function upgrade($upgrader_object, $options) {
        if ($options['action'] == 'update' && $options['type'] == 'plugin') {
            $this->initialize_tables();
        }
    }
}

return new Initialize();