<?php
namespace CodeMyWP\Plugins\ExportAnything;

// Ensure the file is being accessed within the WordPress context
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Hook: Before Settings page
 */
do_action('cmw_ea_before_settings_content');

/**
 * Hook: Settings page
 * 
 * @hooked CodeMyWP\Plugins\ExportAnything\Settings::content
 * 
 */
do_action('cmw_ea_settings_content');

/**
 * Hook: After Settings page
 */
do_action('cmw_ea_after_settings_content');

?>