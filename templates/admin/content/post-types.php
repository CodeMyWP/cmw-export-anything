<?php

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