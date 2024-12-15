<?php

use CodeMyWP\Plugins\ExportAnything\Utilities;
?>
<div class="post-type-item d-flex justify-content-between p-3 bg-light rounded border mb-3">
    <div class="post-type-info">
        <h5 class="post-type-name"><?= $post_type->name ?></h5>
        <p class="post-type-type mb-0"><span class="text-muted">Post Type: </span><span class="fw-bold"><?= Utilities::get_wp_post_type_name($post_type->post_type) ?></span></p>
    </div>
    <div class="post-type-actions">
        <div class="d-flex">
            <a class="btn btn-outline-primary btn-sm" href="<?= admin_url('admin.php?page=export-anything&action=edit&id=' . $post_type->id) ?>">Edit</a>
            <a class="btn btn-danger btn-sm ms-2" href="<?= admin_url('admin.php?page=export-anything&action=delete&id=' . $post_type->id) ?>">Delete</a>
        </div>
    </div>
</div>