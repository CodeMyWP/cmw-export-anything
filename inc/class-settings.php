<?php

namespace CodeMyWP\Plugins\ExportAnything;

use ParagonIE\Sodium\Core\Util;

class Settings {

    public function __construct() {

        // Add Menu Page
        add_action('admin_menu', [$this, 'add_admin_menu']);

        // Enqueue Admin Styles
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);

        // Action Handler
        add_action('admin_init', [$this, 'action_handler']);

        // Add content
        add_action('export_anything_settings_content', [$this, 'content']);

        // Add Start of Post Types
        add_action('export_anything_before_post_types', [$this, 'start_post_types'], 20);

        // Add Post Types
        add_action('export_anything_post_types', [$this, 'post_types'], 10, 1);

        // Add End of Post Types
        add_action('export_anything_after_post_types', [$this, 'end_post_types']);

        // AJAX actions
        add_action('wp_ajax_cmw_ea_create_column', [$this, 'create_column']);
        add_action('wp_ajax_cmw_ea_save_column', [$this, 'save_column']);
        add_action('wp_ajax_cmw_ea_get_field_keys', [$this, 'get_field_keys']);
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Export Anything', // Page title
            'Export Anything', // Menu title
            'manage_options',    // Capability
            EXPORT_ANYTHING_SLUG, // Menu slug
            [$this, 'menu_page'], // Callback function
            'dashicons-download', // Icon URL
            6 // Position
        );
    }

    /**
     * Enqueue admin styles
     */
    public function enqueue_admin_styles($hook) {
        if ($hook !== 'toplevel_page_' . EXPORT_ANYTHING_SLUG) {
            return;
        }
        wp_enqueue_style(EXPORT_ANYTHING_SLUG . '-admin', EXPORT_ANYTHING_URL . 'assets/css/admin/admin.css', [], EXPORT_ANYTHING_VERSION);
        wp_enqueue_script('bootstrap', EXPORT_ANYTHING_URL . 'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.1.3', true);
        wp_enqueue_script(EXPORT_ANYTHING_SLUG, EXPORT_ANYTHING_URL . 'assets/js/admin/script.js', array('jquery'), EXPORT_ANYTHING_VERSION);
        wp_localize_script(EXPORT_ANYTHING_SLUG, 'exportAnything', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'create_column_nonce' => wp_create_nonce('create_column_nonce'),
            'delete_column_nonce' => wp_create_nonce('delete_column_nonce'),
            'save_column_nonce' => wp_create_nonce('save_column_nonce'),
            'get_field_keys_nonce' => wp_create_nonce('get_field_keys_nonce'),
        ));
        wp_enqueue_script(EXPORT_ANYTHING_SLUG . '_export', EXPORT_ANYTHING_URL . 'assets/js/admin/export.js', array('jquery'), EXPORT_ANYTHING_VERSION, true);
        wp_localize_script(EXPORT_ANYTHING_SLUG . '_export', 'exportAnythingExport', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cmw-ea-export'),
        ));
        wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0-rc.0', true);
        wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0-rc.0');
    }

    /**
     * Callback function for the menu page
     */
    public function menu_page() {
        $this->header();
        $this->settings();
        $this->footer();
    }

    public function action_handler() {
        if (!isset($_REQUEST['page']) || $_REQUEST['page'] !== EXPORT_ANYTHING_SLUG) {
            return;
        }
        if(isset($_REQUEST['action'])) {
            switch($_REQUEST['action']) {
                case 'save':
                    
                    if(!isset($_REQUEST['post_type']) || !isset($_REQUEST['name'])) {
                        wp_redirect(admin_url('admin.php?page=' . EXPORT_ANYTHING_SLUG));
                        exit();
                    }

                    $args = array();
                    $args['name'] = $_REQUEST['name'];
                    $args['post_type'] = $_REQUEST['post_type'];

                    PostType::add($args);

                    wp_redirect(admin_url('admin.php?page=' . EXPORT_ANYTHING_SLUG . '&action=edit'));
                    exit();

                break;
                case 'update':
                    wp_redirect(admin_url('admin.php?page=' . EXPORT_ANYTHING_SLUG));
                    exit();
                break;
                case 'delete':
                    if(!isset($_REQUEST['id'])) {
                        wp_redirect(admin_url('admin.php?page=' . EXPORT_ANYTHING_SLUG));
                        exit();
                    }

                    PostType::delete($_REQUEST['id']);

                    wp_redirect(admin_url('admin.php?page=' . EXPORT_ANYTHING_SLUG));
                    exit();
                break;
                case 'export':
                    wp_redirect(admin_url('admin.php?page=' . EXPORT_ANYTHING_SLUG));
                    exit();
                break;
                case 'cancel':
                    wp_redirect(admin_url('admin.php?page=' . EXPORT_ANYTHING_SLUG));
                    exit();
                break;
            }
        }
    }

    public function settings() {
        Utilities::load_template('content/settings', true);
    }

    public function header() {
        Utilities::load_template('layout/partials/header', true);
    }

    public function footer() {
        Utilities::load_template('layout/partials/footer', true);
    }

    public function actions() {
        Utilities::load_template('components/actions', true);
    }

    public function content() {
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
        $this->action_content($action);
        if(!empty($action) && $action === 'edit') {
            $this->add_column_modal();
        }
    } 

    public function action_content($action) {
        $args = array();
        switch($action) {
            case 'add':
                $heading = 'Add Post Type';
                $actions = array(
                    array(
                        'key' => 'cancel', 
                        'label' => 'Cancel', 
                        'type' => 'outline-danger'
                    )
                );
                $args['wp_post_types'] = Utilities::get_wp_post_types();
            break;
            case 'edit':
                $args['post_id'] = $_REQUEST['id'];
                $post_type_name = PostType::get(array(
                    "columns" => array(
                        "name"
                    ),
                    "conditions" => array(
                        "id" => $_REQUEST['id']
                    ),
                    "per_page" => 1
                ));
                $columns = Column::get(array(
                    "columns" => array(
                        "id",
                        "name",
                        "key",
                        "type"
                    ),
                    "conditions" => array(
                        "post_type_id" => $_REQUEST['id']
                    )
                ));
                $args['columns'] = $columns;
                $heading = 'Edit ' . $post_type_name;
                $actions = array(
                    array(
                        'key' => 'cancel', 
                        'label' => 'Cancel', 
                        'type' => 'outline-danger'
                    ),
                    array(
                        'key' => 'delete', 
                        'label' => 'Delete', 
                        'type' => 'danger',
                        'args' => array(
                            'id' => $_REQUEST['id']
                        )
                    )
                );
            break;
            case 'view':
                $args['post_id'] = $_REQUEST['id'];
                $exports = Export::get($_REQUEST['id']);
                $args['exports'] = $exports;
                $post_type_name = PostType::get(array(
                    "columns" => array(
                        "name"
                    ),
                    "conditions" => array(
                        "id" => $_REQUEST['id']
                    ),
                    "per_page" => 1
                ));
                $heading = $post_type_name . ' Exports';
                $actions = array(
                    array(
                        'key' => 'export', 
                        'label' => 'Export', 
                        'type' => 'primary',
                        'args' => array(
                            'id' => $_REQUEST['id']
                        ),
                        'data' => array(
                            'post_type_id' => $_REQUEST['id']
                        )
                    ),
                    array(
                        'key' => 'cancel', 
                        'label' => 'Cancel', 
                        'type' => 'outline-danger'
                    )
                );
            break;
            default:
                $args['post_types'] = PostType::get();
                $heading = 'Post Types';
                $actions = array(
                    array(
                        'key' => 'add', 
                        'label' => 'Add Post Type', 
                        'type' => 'primary'
                    )
                );
        }

        Utilities::load_template('components/heading', true, array(
            'heading' => $heading, 
            'actions' => $actions
        ));
        if(!empty($action)) {
            Utilities::load_template('content/post-types/' . $action, true, $args);
        } else {
            Utilities::load_template('content/post-types', true, $args);
        }
    }

    public function start_post_types() {
        Utilities::load_template('layout/post-types/start', true);
    }

    public function end_post_types() {
        Utilities::load_template('layout/post-types/end', true);
    }

    public function post_types($args) {
        $post_types = $args['post_types'];
        if(sizeof($post_types) > 0) {
            foreach($post_types as $post_type) {
                Utilities::load_template('components/post-type', true, array('post_type' => $post_type));
            }
        } else {
            Utilities::load_template('components/no-post-types', true);
        }
    }

    public function after_content() {
        
    }

    public function create_column() {
        check_ajax_referer('create_column_nonce', 'nonce');

        if (!isset($_POST['post_type_id']) || !isset($_POST['name']) || !isset($_POST['key']) || !isset($_POST['type'])) {
            wp_send_json_error(array(
                'message' => 'Missing required parameters'
            ));
        }

        $post_type_id = sanitize_text_field($_POST['post_type_id']);
        $name = sanitize_text_field($_POST['name']);
        $key = sanitize_text_field($_POST['key']);
        $type = sanitize_text_field($_POST['type']);

        $column_id = Column::add($post_type_id, $name, $key, $type);

        if ($column_id) {
            ob_start();
            Utilities::load_template('components/column', true, array('id' => $column_id));
            $column = ob_get_clean();
            wp_send_json_success(array(
                'column' => $column,
                'id' => $column_id,
            ));
        } else {
            wp_send_json_error(array(
                'message' => 'Something went wrong.'
            ));
        }
    }

    public function save_column() {
        check_ajax_referer('save_column_nonce', 'nonce');

        if (!isset($_POST['id']) || !isset($_POST['post_type_id']) || !isset($_POST['name']) || !isset($_POST['key']) || !isset($_POST['type'])) {
            wp_send_json_error(array(
                'message' => 'Missing required parameters'
            ));
        }

        $id = intval($_POST['id']);

        $args = [
            'post_type_id' => sanitize_text_field($_POST['post_type_id']),
            'name' => sanitize_text_field($_POST['name']),
            'key' => sanitize_text_field($_POST['key']),
            'type' => sanitize_text_field($_POST['type'])
        ];

        $result = Column::update($id, $args);

        if ($result !== false) {
            wp_send_json_success(array(
                'message' => 'Column updated'
            ));
        } else {
            wp_send_json_error(array(
                'message' => 'Failed to update column'
            ));
        }
    }

    public function get_field_keys() {
        check_ajax_referer('get_field_keys_nonce', 'nonce');

        if (!isset($_POST['type']) || !isset($_POST['post_type_id'])) {
            wp_send_json_error(array(
                'message' => 'Missing required parameters'
            ));
        }

        $type = sanitize_text_field($_POST['type']);
        $post_type_id = sanitize_text_field($_POST['post_type_id']);

        if ($type === 'posts') {
            $keys = Utilities::get_wp_posts_columns();
        } else if ($type === 'postmeta') {
            $keys = Utilities::get_post_meta_keys($post_type_id);
        } else {
            wp_send_json_error(array(
                'message' => 'Invalid type'
            ));
        }

        wp_send_json_success($keys);
    }

    public function add_column_modal() {
        Utilities::load_template('content/columns/modal', true);
    }

}

return new Settings();