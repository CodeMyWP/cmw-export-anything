<div class="d-flex justify-content-between p-3 bg-light rounded border mb-3" data-id="<?= $id ?>" id="column-<?= $column->id ?>">
    <div class="column-info">
        <h5 class="mb-1"><?= $column->name ?></h5>
        <p class="mb-0"><span>Type: </span><span class="fw-semibold"><?= ucfirst($column->type) ?></span></p>
        <p class="mb-0"><span>Key: </span><span class="fw-semibold"><?= $column->key ?></span></p>
    </div>
    <div class="column-actions">
        <a href="#" class="btn btn-outline-primary btn-sm edit-column" data-column-id="<?= $column->id ?>" data-column-name="<?= $column->name ?>" data-column-key="<?= $column->key ?>" data-column-type="<?= $column->type ?>">Edit</a>
        <a href="#" class="btn btn-outline-danger btn-sm delete-column" data-column-id="<?= $column->id ?>">Delete</a>
    </div>
</div>