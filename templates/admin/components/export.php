<?php
namespace CodeMyWP\Plugins\ExportAnything;

// Security check to prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>

<div class="d-flex justify-content-between p-3 bg-light rounded border mb-3" id="export-<?php echo esc_attr($export->id) ?>">
    <div class="export-info">
        <h5 class="mb-1">Export #<?php echo esc_html($export->id) ?></h5>
        <p class="mb-0"><span>Date: </span><span class="fw-semibold"><?php echo esc_html(gmdate("d M Y h:i:s A", strtotime($export->created_at))) ?></span></p>
    </div>
    <div class="export-actions">
        <?php 
        switch($export->status) {
            case 'pending':
                if($export->page > 1) {
                    ?>
                    <a href="#" class="btn btn-primary btn-sm resume-export" data-export-id="<?php echo esc_attr($export->id) ?>">Resume</a>
                    <?php
                } else {
                    ?>
                    <a href="#" class="btn btn-primary btn-sm start-export" data-export-id="<?php echo esc_attr($export->id) ?>">Start</a>
                    <?php
                }
                break;
            case 'processing':
                ?>
                <a href="#" class="btn btn-secondary btn-sm disabled">Processing</a>
                <?php
                break;
            case 'completed':
                ?>
                <a href="<?= esc_url(admin_url('admin-post.php?action=cmw_ea_download_export&export_id=' . $export->id . '&cmw_ea_nonce=' . wp_create_nonce('cmw_ea_download_export'))) ?>" class="btn btn-success btn-sm">Download</a>
                <?php      
                break;
            case 'failed':
                ?>
                <a href="#" class="btn btn-secondary btn-sm disabled">Failed</a>
                <?php
                break;
        }
        ?>
        <a href="#" class="btn btn-outline-danger btn-sm delete-export" data-export-id="<?php echo esc_attr($export->id) ?>">Delete</a>
    </div>
</div>