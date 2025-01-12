<?php

// Ensure the file is being accessed within the WordPress context
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Post Types Template
 *
 * This template handles the display of post types in the admin area.
 *
 * @package CodeMyWP\Plugins\ExportAnything
 */

/**
 * Hook: Before Post Types
 * 
 * @hooked CodeMyWP\Plugins\ExportAnything\Settings::start_post_types
 * 
 */
do_action('export_anything_before_post_types');

/**
 * Hook: Post Types
 * 
 * @hooked CodeMyWP\Plugins\ExportAnything\Settings::post_types - 10
 * 
 */
do_action('export_anything_post_types', $args);

/**
 * Hook: After Post Types
 * 
 * @hooked CodeMyWP\Plugins\ExportAnything\Settings::end_post_types
 * 
 */
do_action('export_anything_after_post_types');