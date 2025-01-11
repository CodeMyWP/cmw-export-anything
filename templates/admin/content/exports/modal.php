<!-- Export Progress Modal -->
<div class="modal fade" id="exportProgressModal" tabindex="-1" aria-labelledby="exportProgressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportProgressModalLabel">Exporting...</h5>
            </div>
            <div class="modal-body">
                <p class="message">Please wait the export is in progress.</p>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <p class="mt-2 text-muted text-end">Exported: <span class="export-progress">0</span>%</p>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-success download-export d-none">Download</a>
                <a href="#" class="btn btn-secondary cancel-export" data-bs-dismiss="modal">Pause Export</a>
            </div>
        </div>
    </div>
</div>
