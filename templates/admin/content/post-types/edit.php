<form method="post" action="<?= admin_url('admin.php'); ?>">
    <input type="hidden" name="page" value="export-anything">
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="post_type_id" id="post_type_id" value="<?= $_REQUEST['id'] ?>">
    <div class="columns">
        
    </div>
    <div class="">
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-outline-secondary add-column">Add Column</button>
    </div>
</form>