<?php
/**
 * Plugin name: Export Anything
 * Description: The Export Anything plugin allows you to export data from your WordPress site, including WooCommerce products, orders, and more. This plugin is highly customizable and easy to use.
 * Author: CodeMyWP
 * Author uri: https://www.codemywp.com
 * Version: 1.0.5
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Tested up to: 6.7
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cmw-export-anything
 */

namespace CodeMyWP\Plugins\ExportAnything;

define('EXPORT_ANYTHING_VERSION', '1.0.5');
define('EXPORT_ANYTHING_FILE', __FILE__);
define('EXPORT_ANYTHING_DIR', plugin_dir_path(__FILE__));
define('EXPORT_ANYTHING_URL', plugin_dir_url(__FILE__));
define('EXPORT_ANYTHING_SLUG', 'cmw-export-anything');

// Initialize Post Type
require_once 'inc/class-post-type.php';

// Initialize Column
require_once 'inc/class-column.php';

// Initialize Export
require_once 'inc/class-export.php';

// Initialize Utilities
require_once 'inc/class-utilities.php';

// Initialize Settings
require_once 'inc/class-settings.php';

// Initialize Plugin
require_once 'inc/class-initialize.php';