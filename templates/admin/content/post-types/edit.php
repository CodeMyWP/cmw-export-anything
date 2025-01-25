<?php
namespace CodeMyWP\Plugins\ExportAnything;

if(!defined('ABSPATH')) {
    exit;
}
?>
<div>
    <div class="columns">
        <?php
        if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
            if(sizeof($columns) > 0) {
                foreach($columns as $column) {
                    Utilities::load_template('components/column', true, ['id' => esc_attr(sanitize_text_field(wp_unslash($_REQUEST['id']))), 'column' => $column]);
                }
            } else {
                Utilities::load_template('components/no-columns', true);
            }
        } else {
            Utilities::load_template('components/something-went-wrong', true);
        }
        ?>
    </div>
    <?php if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])): ?>
        <div class="mt-3">
            <a href="#" class="btn btn-primary ms-1" id="add-column">Add Field</a>
        </div>
    <?php endif; ?>
</div>