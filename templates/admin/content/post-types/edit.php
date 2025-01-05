<?php
namespace CodeMyWP\Plugins\ExportAnything;

?>
<div>
    <div class="columns">
        <?php
        if(sizeof($columns) > 0) {
            foreach($columns as $column) {
                Utilities::load_template('components/column', true, ['id' => $_REQUEST['id'], 'column' => $column]);
            }
        } else {
            Utilities::load_template('components/no-columns', true);
        }
        ?>
    </div>
    <div class="mt-3">
        <a href="#" class="btn btn-primary ms-1" id="add-column">Add Field</a>
    </div>
</div>