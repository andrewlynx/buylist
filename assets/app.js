/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

import './styles/app.scss';

import './common';

$( document ).ready(function() {
    const msgSuccess = 'success';

    // converts form data to indexed array for sending as JSON via AJAX
    function getFormData($form){
        var unindexed_array = $form.serializeArray(),
            indexed_array = {};

        $.map(unindexed_array, function(n, i){
            indexed_array[n['name']] = n['value'];
        });

        return indexed_array;
    };

    // Common AJAX form processing flow
    function ajaxSendForm(submission, onSuccessCall){
        submission.preventDefault();
        var form = $(submission.currentTarget),
            data = getFormData(form);

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: JSON.stringify(data)
        }).done(function( msg ) {
            if (msg.status === msgSuccess) {
                window[onSuccessCall](msg, form);
            } else {
                alert( msg.data );
            }
        });
    }

    // Process success result of task_item_create call
    window.taskItemCreateSuccess = function(msg, form){
        $('#list-items').append(msg.data);
        form.find("input[type=text], textarea").val('');
    };

    // Process success result of share_list_email call
    window.shareListByEmailSuccess = function(msg, form){
        $('#shared-users').append(msg.data);
        form.find("input[type=text], textarea").val('');
    };

    // Process success result of notification_read call
    window.readNotification = function(msg, form){
        $('#unread-notifications').text(
            parseInt($('#unread-notifications').text()) - 1
        );
        form.closest('li').remove();
    };

    // Create task item call handler
    $('form[name="task_item_create"]').on('submit', function(e){
        ajaxSendForm(e, 'taskItemCreateSuccess');
    });

    // Share list by email call handler
    $('form[name="share_list_email"]').on('submit', function(e){
        ajaxSendForm(e, 'shareListByEmailSuccess');
    });

    // Share list by email call handler
    $('form[name="notification_read"]').on('submit', function(e){
        ajaxSendForm(e, 'readNotification');
    });

    // Task item completion call handler
    $(document).on("click", '.task-item > .task-item-description', function(e){
        e.preventDefault();
        const classLoading = 'loading';
        if ($(this).hasClass(classLoading)) {
            return false;
        }

        var description = $(this),
            form = $(this).find("form"),
            id = $(form).find('input[name="task_item_complete[id]"]').val(),
            data = getFormData(form);

        description.addClass(classLoading);

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: JSON.stringify(data)
        }).done(function( msg ) {
            description.removeClass(classLoading);

            if (msg.status === msgSuccess) {
                var selector = '#item-id-' + id;
                var response = $.parseJSON(msg.data);

                if (response.id === parseInt(id)) {
                    $(form).find('input[name="task_item_complete[completed]"]').val(response.completed ? 1 : 0);
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
