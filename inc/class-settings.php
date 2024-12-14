<?php

namespace CodeMyWP\Plugins\ExportAnything;

use ParagonIE\Sodium\Core\Util;

class Settings {

    public function __construct() {

        // Add Menu Page
        add_action('admin_menu', [$this, 'add_admin_menu']);

        // Enqueue Admin Styles
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);

        // Add content
        add_action('export_anything_settings_content', [$this, 'content']);

        // Add actions before post types
        add_action('export_anything_before_post_types', [$this, 'actions'], 10);

        // Add Start of Post Types
        add_action('export_anything_before_post_types', [$this, 'start_post_types'], 20);

        // Add Post Types
        add_action('export_anything_post_types', [$this, 'post_types'], 10, 1);

        // Add End of Post Types
        add_action('export_anything_after_post_types', [$this, 'end_post_types']);
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Export Anything', // Page title
            'Export Anything', // Menu title
            'manage_options',    // Capability
            'export-anything', // Menu slug
            [$this, 'menu_page'], // Callback function
            'dashicons-download', // Icon URL
            6 // Position
        );
    }

    /**
     * Enqueue admin styles
     */
    public function enqueue_admin_styles($hook) {
        if ($hook !== 'toplevel_page_export-anything') {
            return;
        }
        wp_enqueue_style('export-anything-admin', EXPORT_ANYTHING_URL . 'assets/css/admin/admin.css', [], EXPORT_ANYTHING_VERSION);
    }

    /**
     * Callback function for the menu page
     */
    public function menu_page() {
        $this->header();
        $this->settings();
        $this->footer();
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
        if(!isset($_GET['action'])) {
            $post_types = PostType::get_post_types();
            Utilities::load_template('content/post-types', true, array('post_types' => $post_types));
        } else {
            switch($_GET['action']) {
                case 'add':
                    Utilities::load_template('components/post-types/add', true);
                    break;
                case 'edit':
                    Utilities::load_template('components/post-types/edit', true);
                    break;
                case 'delete':
                    Utilities::load_template('components/post-types/delete', true);
                    break;
            }
        }
    } 

    public function start_post_types() {
        Utilities::load_template('layout/post-types/start', true);
    }

    public function end_post_types() {
        Utilities::load_template('layout/post-types/end', true);
    }

    public function post_types($post_types) {
        if(sizeof($post_types) > 0) {
            foreach($post_types as $post_type) {
                Utilities::load_template('components/post-types/post-type', true, array('post_type' => $post_type));
            }   
        } else {
            Utilities::load_template('content/no-post-types', true);
        }
    }

    public function after_content() {
        
    }
}

return new Settings();