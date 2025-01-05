jQuery(function($) {
    $(".actions .export").on("click", function(e) {
        e.preventDefault();

        var postTypeId = $(this).data('post_type_id');

        $.post(
            exportAnythingExport.ajax_url,
            {
                action: 'cmw_ea_regsiter_export',
                nonce: exportAnythingExport.nonce,
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

        var postTypeId = $(this).data('post_type_id');
        $("#exportProgressModal").modal('show');
    });
});