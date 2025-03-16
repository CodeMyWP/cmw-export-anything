<?php
namespace CodeMyWP\Plugins\ExportAnything;


// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class Filter
 * Handles the filters functionality for the plugin.
 */
class Filter {

    /**
     * @var string The table name without the WordPress prefix.
     */
    public static $table_name_without_prefix = 'cmw_ea_filters';

    /**
     * Filter constructor.
     * @param bool $cron Whether the constructor is called for a cron job.
     */
    public function __construct() {
        
    }

}
return new Filter();