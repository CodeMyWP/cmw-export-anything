<!-- Add Column Modal -->
<div class="modal fade" id="addColumnModal" tabindex="-1" aria-labelledby="addColumnModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= admin_url('admin-ajax.php') ?>" class="add-column-form" method="post">
                <input type="hidden" name="action" value="cmw_ea_add_column">
                <input type="hidden" name="security" value="<?= wp_create_nonce('cmw_ea_add_column_nonce') ?>">
                <input type="hidden" name="post_type_id" id="post_type_id" value="<?= $_REQUEST['id'] ?>">
                <input type="hidden" name="id" id="id" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="addColumnModalLabel">Add Column</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning" style="display:none" id="add-column-alert" role="alert"></div>
                    <div class="mb-3">
                        <label for="modal_column_name" class="form-label">Field Name</label>
                        <input type="text" class="form-control" name="name" id="modal_column_name" placeholder="Field Name">
                    </div>
                    <div class="mb-3">
                        <label for="modal_column_type" class="form-label">Field Type</label>
                        <select class="form-control" name="type" id="modal_column_type">
                            <option value="">Select Field Type</option>
                            <option value="postmeta">Post Meta</option>
                            <option value="posts">Posts</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="modal_column_key" class="form-label">Field Key</label>
                        <select class="form-control select2" name="key" id="modal_column_key">
                            <option value="">Select Field Key</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saveColumn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
