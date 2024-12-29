jQuery(function($) {

    $('#add-column').click(function(e) {
        e.preventDefault();
        $('#addColumnModal').modal('show');
    });

    $('.add-column-form').on('submit', function(e) {
        e.preventDefault();

        $("#add-column-alert").hide();

        var formData = $(this).serializeArray();
        var data = {};
        $.each(formData, function(index, field) {
            data[field.name] = field.value;
        });

        $.post(
            exportAnything.ajax_url, 
            data,
            function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    $("#add-column-alert").html(response.data.message).show();
                }
            }
        )
    });

    $('.edit-column').click(function(e) {
        e.preventDefault();
        var id = $(this).data('column-id');
        var name = $(this).data('column-name');
        var type = $(this).data('column-type');
        var key = $(this).data('column-key');

        $('#addColumnModal').find('input[name="id"]').val(id);
        $('#addColumnModal').find('input[name="name"]').val(name);
        $('#addColumnModal').find('select[name="type"]').val(type).trigger('change');

        // Trigger change event to load keys via AJAX
        $('#modal_column_type').val(type).trigger('change');

        // Wait for AJAX call to complete and then set the key value
        $(document).ajaxComplete(function(event, xhr, settings) {
            if (settings.data.includes('action=cmw_ea_get_field_keys')) {
                $('#addColumnModal').find('select[name="key"]').val(key).trigger('change.select2');
            }
        });

        $('#addColumnModal').modal('show');
    });

    $('.edit-column-form').on('submit', function(e) {
        e.preventDefault();

        $("#edit-column-alert").hide();

        var formData = $(this).serializeArray();
        var data = {};
        $.each(formData, function(index, field) {
            data[field.name] = field.value;
        });

        $.post(
            exportAnything.ajax_url, 
            data,
            function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    $("#edit-column-alert").html(response.data.message).show();
                }
            }
        )
    });

    $('.delete-column').click(function(e) {
        e.preventDefault();
        var id = $(this).data('column-id');
        if (confirm('Are you sure you want to delete this column?')) {
            $.post(
                exportAnything.ajax_url,
                {
                    action: 'cmw_ea_delete_column',
                    id: id,
                    security: exportAnything.delete_column_nonce
                },
                function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data.message);
                    }
                }
            );
        }
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