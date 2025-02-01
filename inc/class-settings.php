<?php

namespace CodeMyWP\Plugins\ExportAnything;

class Settings {

    /**
     * Constructor to initialize hooks
     */
    public function __construct() {
        // Add Menu Page
        add_action('admin_menu', [$this, 'add_admin_menu']);

        // Enqueue Admin Styles
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);

        // Action Handler
        add_action('admin_init', [$this, 'action_handler']);

        // Add content
        add_action('cmw_ea_settings_content', [$this, 'content']);

        // Add Start of Post Types
        add_action('cmw_ea_before_post_types', [$this, 'start_post_types'], 20);

        // Add Post Types
        add_action('cmw_ea_post_types', [$this, 'post_types'], 10, 1);

        // Add End of Post Types
        add_action('cmw_ea_after_post_types', [$this, 'end_post_types']);

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
            __('Export Anything', 'cmw-export-anything'), // Page title
            __('Export Anything', 'cmw-export-anything'), // Menu title
            'manage_options',    // Capability
            EXPORT_ANYTHING_SLUG, // Menu slug
            [$this, 'menu_page'], // Callback function
            'dashicons-download', // Icon URL
            6 // Position
        );
    }

    /**
     * Enqueue admin styles
     *
     * @param string $hook The current admin page.
     */
    public function enqueue_admin_styles($hook) {
        if ($hook !== 'toplevel_page_' . EXPORT_ANYTHING_SLUG) {
            return;
        }
        wp_enqueue_style(EXPORT_ANYTHING_SLUG . '-admin', EXPORT_ANYTHING_URL . 'assets/css/admin/admin.css', [], EXPORT_ANYTHING_VERSION);
        wp_enqueue_script('bootstrap', EXPORT_ANYTHING_URL . 'assets/js/admin/bootstrap.bundle.min.js', array('jquery'), '5.1.3', array('in_footer' => true));
        wp_enqueue_script(EXPORT_ANYTHING_SLUG, EXPORT_ANYTHING_URL . 'assets/js/admin/script.js', array('jquery'), EXPORT_ANYTHING_VERSION, array('in_footer' => true));
        wp_localize_script(EXPORT_ANYTHING_SLUG, 'exportAnything', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'create_column_nonce' => wp_create_nonce('cmw_ea_create_column_nonce'),
            'delete_column_nonce' => wp_create_nonce('cmw_ea_delete_column_nonce'),
            'save_column_nonce' => wp_create_nonce('cmw_ea_save_column_nonce'),
            'get_field_keys_nonce' => wp_create_nonce('cmw_ea_get_field_keys_nonce'),
        ));
        wp_enqueue_script(EXPORT_ANYTHING_SLUG . '_export', EXPORT_ANYTHING_URL . 'assets/js/admin/export.js', array('jquery'), EXPORT_ANYTHING_VERSION, array('in_footer' => true));
        wp_localize_script(EXPORT_ANYTHING_SLUG . '_export', 'exportAnythingExport', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'register_nonce' => wp_create_nonce('cmw_ea_register_export'),
            'deregister_nonce' => wp_create_nonce('cmw_ea_deregister_export'),
            'start_nonce' => wp_create_nonce('cmw_ea_start_export')
        ));
        wp_enqueue_script('select2', EXPORT_ANYTHING_URL . 'assets/js/admin/select2.min.js', array('jquery'), EXPORT_ANYTHING_VERSION, array('in_footer' => true));
        wp_enqueue_style('select2-css', EXPORT_ANYTHING_URL . 'assets/css/admin/select2.min.css', array(), EXPORT_ANYTHING_VERSION);
    }

    /**
     * Callback function for the menu page
     */
    public function menu_page() {
        $this->header();
        $this->settings();
        $this->footer();
    }

    /**
     * Action handler
     */
    public function action_handler() {
        if (!isset($_REQUEST['page']) || $_REQUEST['page'] !== EXPORT_ANYTHING_SLUG) {
            return;
        }
        if(isset($_REQUEST['action'])) {
            switch($_REQUEST['action']) {
                case 'save':
                    // Verify nonce
                    if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])), 'cmw_ea_save_post_type')) {
                        wp_die(esc_html__('Nonce verification failed', 'cmw-export-anything'));
                    }
                    
                    // Check required parameters
                    if(!isset($_REQUEST['post_type']) || !isset($_REQUEST['name'])) {
                        wp_redirect(admin_url('admin.php?page=' . EXPORT_ANYTHING_SLUG));
                        exit();
                    }

                    $args = array();
                    $args['name'] = sanitize_text_field(wp_unslash($_REQUEST['name']));
                    $args['post_type'] = sanitize_text_field(wp_unslash($_REQUEST['post_type']));

                    // Add post type
                    $id = PostType::add($args);

                    wp_redirect(admin_url('admin.php?page=' . EXPORT_ANYTHING_SLUG . '&action=edit&id=' . $id));
                    exit();

                break;
                case 'delete':
                    // Verify nonce
                    if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])), 'cmw_ea_delete_post_type')) {
                        wp_die(esc_html__('Nonce verification failed', 'cmw-export-anything'));
                    }

                    // Check required parameters
                    if(!isset($_REQUEST['id'])) {
                        wp_redirect(admin_url('admin.php?page=' . EXPORT_ANYTHING_SLUG));
                        exit();
                    }

                    // Delete post type
                    PostType::delete(intval($_REQUEST['id']));

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

    /**
     * Load settings template
     */
    public function settings() {
        Utilities::load_template('content/settings', true);
    }

    /**
     * Load header template
     */
    public function header() {
        Utilities::load_template('layout/partials/header', true);
    }

    /**
     * Load footer template
     */
    public function footer() {
        Utilities::load_template('layout/partials/footer', true);
    }

    /**
     * Load actions template
     */
    public function actions() {
        Utilities::load_template('components/actions', true);
    }

    /**
     * Load content based on action
     */
    public function content() {
        $action = isset($_REQUEST['action']) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : '';
        $this->action_content($action);
        if(!empty($action)) {
            switch($action) {
                case 'add':
                break;
                case 'edit':
                    $this->add_column_modal();
                break;
                case 'view':
                    $this->export_progress_modal();
                break;
            }
        }
    }

    /**
     * Load action-specific content
     *
     * @param string $action The action to load content for.
     */
    public function action_content($action) {
        $args = array();
        switch($action) {
            case 'add':
                $heading = __('Add Post Type', 'cmw-export-anything');
                $actions = array(
                    array(
                        'key' => 'cancel', 
                        'label' => __('Cancel', 'cmw-export-anything'), 
                        'type' => 'outline-danger'
                    )
                );
                $args['wp_post_types'] = Utilities::get_wp_post_types();
            break;
            case 'edit':
                $actions = array(
                    array(
                        'key' => 'cancel', 
                        'label' => __('Go Back', 'cmw-export-anything'), 
                        'type' => 'secondary'
                    )
                );
                if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
                    $args['post_id'] = intval($_REQUEST['id']);
                    $post_type_name = PostType::get(intval($_REQUEST['id']))->name;
                    $heading = __('Edit ', 'cmw-export-anything') . $post_type_name;

                    $columns = Column::get_by_post_type_id(intval($_REQUEST['id']));
                    $args['columns'] = $columns;

                    array_push($actions, array(
                        'key' => 'delete', 
                        'label' => __('Delete', 'cmw-export-anything'), 
                        'type' => 'danger',
                        'args' => array(
                            'id' => intval($_REQUEST['id']),
                            '_wpnonce' => wp_create_nonce('cmw_ea_delete_post_type')
                        )
                    ));
                } else {
                    $heading = __('Not Found', 'cmw-export-anything');
                }
            break;
            case 'view':
                $actions = array(
                    array(
                        'key' => 'cancel', 
                        'label' => __('Go Back', 'cmw-export-anything'), 
                        'type' => 'secondary'
                    )
                );
                if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
                    $args['post_id'] = intval($_REQUEST['id']);

                    $exports = Export::get_by_post_type_id(intval($_REQUEST['id']));
                    $args['exports'] = $exports;

                    $post_type_name = PostType::get(intval($_REQUEST['id']))->name;
                    $heading = $post_type_name . __(' Exports', 'cmw-export-anything');

                    array_push($actions, array(
                        'key' => 'export', 
                        'label' => __('Add Export Job', 'cmw-export-anything'), 
                        'type' => 'primary',
                        'args' => array(
                            'id' => intval($_REQUEST['id'])
                        ),
                        'data' => array(
                            'post_type_id' => intval($_REQUEST['id'])
                        )
                    ));
                } else {
                    $heading = __('Not Found', 'cmw-export-anything');
                }
            break;
            default:
                $args['post_types'] = PostType::get_all();
                $heading = __('Post Types', 'cmw-export-anything');
                $actions = array(
                    array(
                        'key' => 'add', 
                        'label' => __('Add Post Type', 'cmw-export-anything'), 
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

    /**
     * Load start post types template
     */
    public function start_post_types() {
        Utilities::load_template('layout/post-types/start', true);
    }

    /**
     * Load end post types template
     */
    public function end_post_types() {
        Utilities::load_template('layout/post-types/end', true);
    }

    /**
     * Load post types template
     *
     * @param array $args Arguments for loading post types.
     */
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

    /**
     * Placeholder for after content actions
     */
    public function after_content() {
        // Placeholder for after content actions
    }

    /**
     * Handle AJAX request to create a column
     */
    public function create_column() {
        check_ajax_referer('cmw_ea_create_column_nonce', 'nonce');

        if (!isset($_POST['post_type_id']) || !isset($_POST['name']) || !isset($_POST['key']) || !isset($_POST['type'])) {
            wp_send_json_error(array(
                'message' => __('Missing required parameters', 'cmw-export-anything')
            ));
        }

        $post_type_id = sanitize_text_field(wp_unslash($_POST['post_type_id']));
        $name = sanitize_text_field(wp_unslash($_POST['name']));
        $key = sanitize_text_field(wp_unslash($_POST['key']));
        $type = sanitize_text_field(wp_unslash($_POST['type']));

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
                'message' => __('Something went wrong.', 'cmw-export-anything')
            ));
        }
    }

    /**
     * Handle AJAX request to save a column
     */
    public function save_column() {
        check_ajax_referer('cmw_ea_save_column_nonce', 'nonce');

        if (!isset($_POST['id']) || !isset($_POST['post_type_id']) || !isset($_POST['name']) || !isset($_POST['key']) || !isset($_POST['type'])) {
            wp_send_json_error(array(
                'message' => __('Missing required parameters', 'cmw-export-anything')
            ));
        }

        $id = intval($_POST['id']);

        $args = [
            'post_type_id' => sanitize_text_field(wp_unslash($_POST['post_type_id'])),
            'name' => sanitize_text_field(wp_unslash($_POST['name'])),
            'key' => sanitize_text_field(wp_unslash($_POST['key'])),
            'type' => sanitize_text_field(wp_unslash($_POST['type']))
        ];

        $result = Column::update($id, $args);

        if ($result !== false) {
            wp_send_json_success(array(
                'message' => __('Column updated', 'cmw-export-anything')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Failed to update column', 'cmw-export-anything')
            ));
        }
    }

    /**
     * Handle AJAX request to get field keys
     */
    public function get_field_keys() {
        check_ajax_referer('cmw_ea_get_field_keys_nonce', 'nonce');

        if (!isset($_POST['type']) || !isset($_POST['post_type_id'])) {
            wp_send_json_error(array(
                'message' => __('Missing required parameters', 'cmw-export-anything')
            ));
        }

        $type = sanitize_text_field(wp_unslash($_POST['type']));
        $post_type_id = sanitize_text_field(wp_unslash($_POST['post_type_id']));

        if ($type === 'posts') {
            $keys = Utilities::get_wp_posts_columns();
        } else if ($type === 'postmeta') {
            $keys = Utilities::get_post_meta_keys($post_type_id);
        } else {
            wp_send_json_error(array(
                'message' => __('Invalid type', 'cmw-export-anything')
            ));
        }

        wp_send_json_success($keys);
    }

    /**
     * Load add column modal template
     */
    public function add_column_modal() {
        Utilities::load_template('content/columns/modal', true);
    }

    /**
     * Load export progress modal template
     */
    public function export_progress_modal() {
        Utilities::load_template('content/exports/modal', true);
    }

}

return new Settings();