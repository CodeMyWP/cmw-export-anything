<!-- Add Column Modal -->
<div class="modal fade" id="addColumnModal" tabindex="-1" aria-labelledby="addColumnModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addColumnModalLabel">Add Column</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="modal_column_name" class="form-label">Field Name</label>
                    <input type="text" class="form-control" id="modal_column_name" placeholder="Field Name">
                </div>
                <div class="mb-3">
                    <label for="modal_column_type" class="form-label">Field Type</label>
                    <select class="form-control" id="modal_column_type">
                        <option value="postmeta">Post Meta</option>
                        <option value="posts">Posts</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="modal_column_key" class="form-label">Field Key</label>
                    <select class="form-control select2" id="modal_column_key">
                        <option value="">Select Field Key</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveColumn">Save</button>
            </div>
        </div>
    </div>
</div>
