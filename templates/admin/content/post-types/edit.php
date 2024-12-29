<?php
namespace CodeMyWP\Plugins\ExportAnything;

?>
<div>
    <div class="columns">
        <?php
        foreach($columns as $column) {
            Utilities::load_template('components/column', true, ['id' => $_REQUEST['id'], 'column' => $column]);
        }
        ?>
    </div>
    <div class="">
        <a href="<?= admin_url('?page=' . EXPORT_ANYTHING_SLUG) ?>" class="btn btn-outline-danger">Cancel</a>
        <a href="#" class="btn btn-primary" id="add-column">Add Column</a>
    </div>
</div>