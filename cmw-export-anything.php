<?php
/**
 * Plugin name: Export Anything
 * Description: This plugin allows you to export anything from your WordPress site.
 * Author: CodeMyWP
 * Author uri: https://www.codemywp.com
 * Version: 1.0.0
 */

namespace CodeMyWP\Plugins\ExportAnything;

define('EXPORT_ANYTHING_VERSION', '1.0.0');
define('EXPORT_ANYTHING_FILE', __FILE__);
define('EXPORT_ANYTHING_DIR', plugin_dir_path(__FILE__));
define('EXPORT_ANYTHING_URL', plugin_dir_url(__FILE__));

// Initialize Post Type
require_once 'inc/class-post-type.php';

// Initialize Column
require_once 'inc/class-column.php';

// Initialize Utilities
require_once 'inc/class-utilities.php';

// Initialize Settings
require_once 'inc/class-settings.php';

// Initialize Plugin
require_once 'inc/class-initialize.php';