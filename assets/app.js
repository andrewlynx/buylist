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

    // Close modal window
    function closeModal(){
        $('#li-modal .data').html('');
        $('#li-modal').removeClass('active');
    }

    // Load more lists
    function loadMore(){
        console.log($('#loader').attr('data-url'));
        $.ajax({
            url: $('#loader').attr('data-url'),
            method: 'GET'
        }).done(function( msg ) {
            $('#tls').append(msg);
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

    // Process success result of notification_read call
    window.editItem = function(msg, form){
        var id = form.find('input[name="task_item_edit[id]"]').val();
        $('#item-id-' + id).replaceWith(msg.data);
        closeModal();
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

    // Edit task item call handler
    $(document).on("submit", 'form[name="task_item_edit"]', function(e){
        ajaxSendForm(e, 'editItem');
    });

    // Task item completion call handler
    $(document).on("click", '.ti > .tid', function(e){
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

    // Task item edit call handler
    $(document).on("click", '.ti > .ie', function(e){
        $.get($(this).attr('data-url')).then(function(data){
            $('#li-modal').addClass('active');
            $('#li-modal .data').html(data);
        });
    });

    // Close modal window
    $(document).on("click", '#li-modal .close', function(e){
        closeModal();
    });

    $(document).on("click", '.a-n .close', function(e){
        $.get($(this).attr('data-url')).then(function(msg){
            if (msg.status != msgSuccess) {
                alert( msg.data );
            }
        });
    });

    // Load more lists on scrolling down
    $(window).on("scroll", function() {
        if ($('#loader').length) {
            var scrollHeight = $(document).height();
            var scrollPos = $(window).height() + $(window).scrollTop();
            var footerHeight = $('footer').height();
            if (scrollHeight - scrollPos < (footerHeight + 50)) {
                loadMore();
                $('#loader').remove();
                console.log("bottom!");
            }
        }
    });

    // Close modal window
    $(document).on("click", '#loader button', function(e){
        loadMore();
        $('#loader').remove();
    });
});
