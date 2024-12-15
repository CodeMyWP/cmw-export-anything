jQuery(function($) {

    $('.add-column').click(function(e) {
        e.preventDefault();
        $('#addColumnModal').modal('show');
    });

    $('#saveColumn').click(function() {
        var post_type_id = $('#post_type_id').val();
        var name = $('#modal_column_name').val();
        var key = $('#modal_column_key').val();
        var type = $('#modal_column_type').val();

        $.post(exportAnything.ajax_url, {
            action: 'cmw_ea_create_column',
            nonce: exportAnything.create_column_nonce,
            post_type_id: post_type_id,
            name: name,
            key: key,
            type: type
        }, function(response) {
            if (response.success) {
                var columnId = response.data.id;
                var columnHtml = $(response.data.column);
                columnHtml.find('input[name="name[]"]').attr('id', 'name-' + columnId);
                columnHtml.find('input[name="key[]"]').attr('id', 'key-' + columnId);
                columnHtml.find('select[name="type[]"]').attr('id', 'type-' + columnId);
                $('.columns-container').append(columnHtml);
                $('#modal_column_name').val('');
                $('#modal_column_key').val('');
                $('#modal_column_type').val('');
                $('#addColumnModal').modal('hide');
            } else {
                alert(response.data.message);
            }
        });
    });

    $(document).on('click', '.remove-column', function() {
        $(this).closest('.d-flex').remove();
    });

    $('.select2').select2(); // Initialize Select2
    $('#modal_column_type').change(function() {
        var type = $(this).val();
        var postTypeId = $('#post_type_id').val(); // Assuming you have a hidden input with post type ID
        $.ajax({
            url: exportAnything.ajax_url,
            method: 'POST',
            data: {
                action: 'cmw_ea_get_field_keys',
                type: type,
                post_type_id: postTypeId,
                nonce: exportAnything.get_field_keys_nonce
            },
            success: function(response) {
                if (response.success) {
                    var options = '<option value="">Select Field Key</option>';
                    $.each(response.data, function(index, value) {
                        options += '<option value="' + value + '">' + value + '</option>';
                    });
                    $('#modal_column_key').html(options).trigger('change.select2'); // Update Select2 options
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
});