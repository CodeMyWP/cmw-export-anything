jQuery(function($) {
    $(".actions .export").on("click", function(e) {
        e.preventDefault();

        var postTypeId = $(this).data('post_type_id');

        if (!postTypeId) {
            console.error('Post Type ID is missing.');
            return;
        }

        $.post(
            exportAnythingExport.ajax_url,
            {
                action: 'cmw_ea_regsiter_export',
                nonce: exportAnythingExport.register_nonce,
                post_type_id: postTypeId
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    console.error('Export registration failed:', response.data);
                }
            }
        ).fail(function(response) {
            console.error('Ajax request failed:', response);
        });
    });

    $(".export-actions").on("click", ".start-export,.resume-export", function(e) {
        e.preventDefault();

        $("#exportProgressModal").modal('show');

        var exportId = $(this).data('export-id');
        var exportRunning = true;

        if (!exportId) {
            console.error('Export ID is missing.');
            return;
        }

        $(".cancel-export").on("click", function() {
            exportRunning = false;
        });

        function runExport() {
            if (!exportRunning) {
                $("#exportProgressModal").modal('hide');
                $("#export-" + exportId + " .export-actions .start-export").remove();
                $("#export-" + exportId + " .export-actions").prepend('<a href="#" class="btn btn-primary btn-sm resume-export" data-export-id="'+exportId+'">Resume</a>');
                return;
            }

            $.post(
                exportAnythingExport.ajax_url,
                {
                    action: 'cmw_ea_start_export',
                    nonce: exportAnythingExport.start_nonce,
                    export_id: exportId
                }, function(response) {
                    if (response.success) {
                        var status = response.data.export.status;
                        var totalItems = response.data.total_items;
                        var currentPage = response.data.export.page;
                        var itemsPerPage = response.data.export.per_page;
                        var exportedItems = currentPage * itemsPerPage;
                        var progress = Math.min((exportedItems / totalItems) * 100, 100);

                        $(".progress-bar").css("width", progress + "%").attr("aria-valuenow", progress);
                        $(".export-progress").text(progress.toFixed(2));
                        if(status == 'pending') {
                            runExport();
                        } else if(status == 'completed') {
                            $("#exportProgressModal .message").text(response.data.message);
                            $("#exportProgressModal .cancel-export").hide();
                            $("#exportProgressModal .download-export").removeClass('d-none').attr('href', response.data.download_url);
                            $("#export-" + exportId + " .export-actions .start-export").remove();
                            $("#export-" + exportId + " .export-actions .resume-export").remove();
                            $("#export-" + exportId + " .export-actions").prepend('<a href="'+response.data.download_url+'" class="btn btn-success btn-sm">Download</a>');
                        }
                    } else {
                        console.error('Export failed:', response.data);
                    }
                }
            ).fail(function(response) {
                console.error('Ajax request failed:', response);
            });
        }

        runExport();
    });

    $(".delete-export").on("click", function(e) {
        e.preventDefault();

        var exportId = $(this).data('export-id');

        if (!exportId) {
            console.error('Export ID is missing.');
            return;
        }

        $.post(
            exportAnythingExport.ajax_url,
            {
                action: 'cmw_ea_deregister_export',
                nonce: exportAnythingExport.deregister_nonce,
                export_id: exportId
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    console.error('Export deregistration failed:', response.data);
                }
            }
        ).fail(function(response) {
            console.error('Ajax request failed:', response);
        });
    });
});