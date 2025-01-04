<?php
namespace CodeMyWP\Plugins\ExportAnything;

class CLI {
    
    public function __construct() {
        add_action('init', array($this, 'register'));
    }

    public function register() {
        if(defined('WP_CLI') && WP_CLI) {
            \WP_CLI::add_command('cmw-export', array($this, 'export'));
        }
    }

    public function export($args, $assoc_args) {
        $export = new Export(false);
        $export->process();

        \WP_CLI::success('Export Processed');
    }
}

return new CLI();