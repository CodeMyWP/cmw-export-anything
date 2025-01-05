<?php
namespace CodeMyWP\Plugins\ExportAnything;
?>
<div>
    <div class="exports">
        <?php
        if(sizeof($exports) > 0) {
            foreach($exports as $export) {
                Utilities::load_template('components/export', true, array('export' => $export));
            }
        } else {
            Utilities::load_template('components/no-exports', true);
        }
        ?>
    </div>
</div>