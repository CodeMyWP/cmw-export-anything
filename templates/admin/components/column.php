<?php
namespace CodeMyWP\Plugins\ExportAnything;

// Security check to prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>
<div class="d-flex justify-content-between p-3 bg-light rounded border mb-3" data-id="<?= esc_attr($id) ?>" id="column-<?= esc_attr($column->id) ?>">
    <div class="column-info">
        <h5 class="mb-1"><?= esc_html($column->name) ?></h5>
        <p class="mb-0"><span>Type: </span><span class="fw-semibold"><?= esc_html(ucfirst($column->type)) ?></span></p>
        <p class="mb-0"><span>Key: </span><span class="fw-semibold"><?= esc_html($column->key) ?></span></p>
    </div>
    <div class="column-actions">
        <a href="#" class="btn btn-outline-primary btn-sm edit-column" data-column-id="<?= esc_attr($column->id) ?>" data-column-name="<?= esc_attr($column->name) ?>" data-column-key="<?= esc_attr($column->key) ?>" data-column-type="<?= esc_attr($column->type) ?>">Edit</a>
        <a href="#" class="btn btn-outline-danger btn-sm delete-column" data-column-id="<?= esc_attr($column->id) ?>">Delete</a>
    </div>
</div>