$( document ).ready(function() {
    const msgSuccess = 'success';

    $('form[name="task_item_create"]').on('submit', function(e){
        e.preventDefault();
        var form = $(e.currentTarget),
            data = getFormData(form);

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: JSON.stringify(data)
        }).done(function( msg ) {
            if (msg.status === msgSuccess) {
                $('#list-items').append(msg.data);
                form.find("input[type=text], textarea").val('');
            } else {
                alert( msg.data );
            }
        });
    });

    function getFormData($form){
        var unindexed_array = $form.serializeArray(),
            indexed_array = {};

        $.map(unindexed_array, function(n, i){
            indexed_array[n['name']] = n['value'];
        });

        return indexed_array;
    };

    $('input[name="task_item_complete[completed]"]').on('change', function(e){
        e.preventDefault();

        var checkbox = $(e.currentTarget),
            form = checkbox.closest("form"),
            id = $(form).find('input[name="task_item_complete[id]"]').val(),
            data = getFormData(form);

        checkbox.attr('disabled', true);

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: JSON.stringify(data)
        }).done(function( msg ) {
            checkbox.attr('disabled', false);

            if (msg.status === msgSuccess) {
                var selector = '#item-id-' + id;
                response = $.parseJSON(msg.data);

                if (response.id === parseInt(id)) {
                    if (response.completed === true) {
                        $(selector).addClass('completed');
                    } else {
                        $(selector).removeClass('completed');
                    }
                } else {
                    alert( 'Error' );
                }
            } else {
                alert( msg.data );
            }
        });
    });
});