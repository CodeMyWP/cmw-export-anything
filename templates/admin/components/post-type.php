<?php
namespace CodeMyWP\Plugins\ExportAnything;

// Security check to prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

use CodeMyWP\Plugins\ExportAnything\Utilities as Utilities;
?>
<div class="post-type-item d-flex justify-content-between p-3 bg-light rounded border mb-3">
    <div class="post-type-info">
        <h5 class="post-type-name"><?php echo esc_html( $post_type->name ) ?></h5>
        <p class="post-type-type mb-0"><span class="text-muted">Post Type: </span><span class="fw-bold"><?php echo esc_html( Utilities::get_wp_post_type_name( $post_type->post_type ) ) ?></span></p>
    </div>
    <div class="post-type-actions">
        <div class="d-flex">
            <a class="btn btn-outline-secondary btn-sm me-2" href="<?php echo esc_url( admin_url( 'admin.php?page=' . EXPORT_ANYTHING_SLUG . '&action=edit&id=' . $post_type->id ) ) ?>">Edit</a>
            <a class="btn btn-primary btn-sm" href="<?php echo esc_url( admin_url( 'admin.php?page=' . EXPORT_ANYTHING_SLUG . '&action=view&id=' . $post_type->id ) ) ?>">Exports</a>
        </div>
    </div>
</div>