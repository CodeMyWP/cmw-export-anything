<form action="<?= admin_url('admin.php'); ?>" method="post" class="form-horizontal">
    <input type="hidden" name="page" value="export-anything">
    <input type="hidden" name="action" value="save">
    <div class="row mb-3">
        <label for="name" class="col-sm-2 control-label">Name</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="name" name="name" placeholder="Name" required>
        </div>
    </div>
    <div class="row mb-3">
        <label for="post_type" class="col-sm-2 control-label">Post Type</label>
        <div class="col-sm-10">
            <select class="form-control" id="post_type" name="post_type" required>
                <?php foreach ($wp_post_types as $post_type): ?>
                    <option value="<?php echo esc_attr($post_type->name); ?>"><?php echo esc_html($post_type->label); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="offset-sm-2 col-sm-10">
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </div>
</form>