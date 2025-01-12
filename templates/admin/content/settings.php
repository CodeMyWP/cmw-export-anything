<?php

// Ensure the file is being accessed within the WordPress context
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Hook: Before Settings page
 * 
 * @hooked CodeMyWP\Plugins\ExportAnything\Settings::actions - 10
 * 
 */
do_action('export_anything_before_settings_content');

/**
 * Hook: Settings page
 * 
 * @hooked CodeMyWP\Plugins\ExportAnything\Settings::content
 * 
 */
do_action('export_anything_settings_content');

/**
 * Hook: After Settings page
 */
do_action('export_anything_after_settings_content');

?>