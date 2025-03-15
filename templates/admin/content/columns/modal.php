<?php
namespace CodeMyWP\Plugins\ExportAnything;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if(!isset($_REQUEST['id']) || empty($_REQUEST['id'])) {
    return;
}
?>
<!-- Add Column Modal -->
<div class="modal fade" id="addColumnModal" tabindex="-1" aria-labelledby="addColumnModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?php echo esc_url( admin_url('admin-ajax.php') ) ?>" class="add-column-form" method="post">
                <input type="hidden" name="action" value="cmw_ea_add_column">
                <input type="hidden" name="security" value="<?php echo esc_attr( wp_create_nonce('cmw_ea_add_column_nonce') ) ?>">
                <input type="hidden" name="post_type_id" id="post_type_id" value="<?php echo esc_attr( sanitize_text_field(wp_unslash($_REQUEST['id']))) ?>">
                <input type="hidden" name="id" id="id" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="addColumnModalLabel"><?php esc_html_e( 'Add Field', 'cmw-export-anything' ); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning" style="display:none" id="add-column-alert" role="alert"></div>
                    <div class="mb-3">
                        <label for="modal_column_type" class="form-label"><?php esc_html_e( 'Field Type', 'cmw-export-anything' ); ?></label>
                        <select class="form-control" name="type" id="modal_column_type">
                            <option value=""><?php esc_html_e( 'Select Field Type', 'cmw-export-anything' ); ?></option>
                            <?php 
                            switch($post_type) {
                                case 'user':
                                    ?>
                                    <option value="usermeta"><?php esc_html_e( 'User Meta', 'cmw-export-anything' ); ?></option>
                                    <option value="users"><?php esc_html_e( 'Users', 'cmw-export-anything' ); ?></option>
                                    <?php
                                break;
                                default:
                                ?>
                                <option value="postmeta"><?php esc_html_e( 'Post Meta', 'cmw-export-anything' ); ?></option>
                                <option value="posts"><?php esc_html_e( 'Posts', 'cmw-export-anything' ); ?></option>
                                <?php
                            } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="modal_column_key" class="form-label"><?php esc_html_e( 'Field Key', 'cmw-export-anything' ); ?></label>
                        <select class="form-control select2" name="key" id="modal_column_key">
                            <option value=""><?php esc_html_e( 'Select Field Key', 'cmw-export-anything' ); ?></option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="modal_column_name" class="form-label"><?php esc_html_e( 'Field Name', 'cmw-export-anything' ); ?></label>
                        <input type="text" class="form-control" name="name" id="modal_column_name" placeholder="<?php esc_attr_e( 'Field Name', 'cmw-export-anything' ); ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php esc_html_e( 'Close', 'cmw-export-anything' ); ?></button>
                    <button type="submit" class="btn btn-primary" id="saveColumn"><?php esc_html_e( 'Save', 'cmw-export-anything' ); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
