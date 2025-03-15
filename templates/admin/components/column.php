<?php
namespace CodeMyWP\Plugins\ExportAnything;

// Security check to prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>
<div class="d-flex justify-content-between p-3 bg-light rounded border mb-3" data-id="<?php echo esc_attr($id) ?>" id="column-<?php echo esc_attr($column->id) ?>">
    <div class="column-info">
        <h5 class="mb-1"><?php echo esc_html($column->name) ?></h5>
        <p class="mb-0"><span>Type: </span><span class="fw-semibold"><?php echo esc_html(ucfirst($column->type)) ?></span></p>
        <p class="mb-0"><span>Key: </span><span class="fw-semibold"><?php echo esc_html($column->column_key) ?></span></p>
    </div>
    <div class="column-actions">
        <a href="#" class="btn btn-outline-primary btn-sm edit-column" data-column-id="<?php echo esc_attr($column->id) ?>" data-column-name="<?php echo esc_attr($column->name) ?>" data-column-key="<?php echo esc_attr($column->column_key) ?>" data-column-type="<?php echo esc_attr($column->type) ?>">Edit</a>
        <a href="#" class="btn btn-outline-danger btn-sm delete-column" data-column-id="<?php echo esc_attr($column->id) ?>">Delete</a>
    </div>
</div>