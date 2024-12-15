<div class="d-flex flex-column flex-md-row align-items-md-center mb-3 border-bottom pb-3" data-id="<?= $id ?>" id="column-<?= $id ?>">
    <div class="col-md-3 mb-3 mb-md-0 me-md-3">
        <input type="text" class="form-control" name="name[]" id="name-<?= $id ?>" placeholder="Field Name">
    </div>
    <div class="col-md-3 mb-3 mb-md-0 me-md-3">
        <input type="text" class="form-control" name="key[]" id="key-<?= $id ?>" placeholder="Field Key">
    </div>
    <div class="col-md-3 mb-3 mb-md-0 me-md-3">
        <select class="form-control" name="type[]" id="type-<?= $id ?>">
            <option value="postmeta">Post Meta</option>
            <option value="posts">Posts</option>
        </select>
    </div>
    <div class="col-md-3">
        <button type="button" class="btn btn-outline-success save-column btn-sm">Save</button>
        <button type="button" class="btn btn-outline-danger remove-column btn-sm">Remove</button>
    </div>
</div>