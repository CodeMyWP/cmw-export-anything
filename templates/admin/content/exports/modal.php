<?php
namespace CodeMyWP\Plugins\ExportAnything;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>
<!-- Export Progress Modal -->
<div class="modal fade" id="exportProgressModal" tabindex="-1" aria-labelledby="exportProgressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportProgressModalLabel"><?php esc_html_e( 'Exporting...', 'cmw-export-anything' ); ?></h5>
            </div>
            <div class="modal-body">
                <p class="message"><?php esc_html_e( 'Please wait the export is in progress.', 'cmw-export-anything' ); ?></p>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <p class="mt-2 text-muted text-end"><?php esc_html_e( 'Exported:', 'cmw-export-anything' ); ?> <span class="export-progress">0</span>%</p>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-success download-export d-none"><?php esc_html_e( 'Download', 'cmw-export-anything' ); ?></a>
                <a href="#" class="btn btn-secondary cancel-export" data-bs-dismiss="modal"><?php esc_html_e( 'Pause Export', 'cmw-export-anything' ); ?></a>
            </div>
        </div>
    </div>
</div>
