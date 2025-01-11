jQuery(function($) {
    $(".actions .export").on("click", function(e) {
        e.preventDefault();

        var postTypeId = $(this).data('post_type_id');

        $.post(
            exportAnythingExport.ajax_url,
            {
                action: 'cmw_ea_regsiter_export',
                nonce: exportAnythingExport.register_nonce,
                post_type_id: postTypeId
            }, function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        ).fail(function(response) {
            console.log(response);
        });
    });

    $(".start-export").on("click", function(e) {
        e.preventDefault();

        $("#exportProgressModal").modal('show');

        var exportId = $(this).data('export-id');
        var exportRunning = true;

        $(".cancel-export").on("click", function() {
            exportRunning = false;
        });

        function runExport() {
            if (!exportRunning) return;

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
                        }
                    }
                }
            ).fail(function(response) {
                console.log(response);
            });
        }

        runExport();
    });
});