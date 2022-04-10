$( document ).ready(function() {
    const msgSuccess = 'success';
    const emailPattern = /^[A-Z0-9._%+-]+@([A-Z0-9-]+\.)+[A-Z]{2,4}$/i;

    function getCurrentLink() {
        return location.protocol+'//'+location.host+location.pathname;
    }

    // converts form data to indexed array for sending as JSON via AJAX
    function getFormData($form){
        let unindexed_array = $form.serializeArray(),
            indexed_array = {};

        $.map(unindexed_array, function(n, i){
            indexed_array[n['name']] = n['value'];
        });

        return indexed_array;
    }

    // Common AJAX form processing flow
    function ajaxSendForm(submission, onSuccessCall) {
        submission.preventDefault();
        let form = $(submission.currentTarget),
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
    function closeModal() {
        $('#li-modal .data').html('');
        $('#li-modal').removeClass('active');
    }

    // Load more lists
    function loadMore() {
        console.log($('#loader').attr('data-url'));
        $.ajax({
            url: $('#loader').attr('data-url'),
            method: 'GET'
        }).done(function( msg ) {
            $('#tls').append(msg);
        });
    }

    // Adds form to repeatable fields
    function addFormToCollection($collectionHolderClass, $deletable = false) {
        let $collectionHolder = $('.' + $collectionHolderClass),
            prototype = $collectionHolder.data('prototype'),
            index = $collectionHolder.attr('data-index'),
            newForm = prototype;

        newForm = newForm.replace(/__name__/g, index);
        $collectionHolder.attr('data-index', parseInt(index) + 1);

        let $newFormLi = $('<li></li>').append(newForm);
        $collectionHolder.append($newFormLi);
        if ($deletable) addTagFormDeleteLink($newFormLi);

        return index;
    }

    // Add remove button to repeatable fields
    function addTagFormDeleteLink($tagFormLi) {
        let $removeFormButton = $(
            '<button class="delete"><span class="iconly-brokenDelete icon-large"></span></button>'
        );
        $tagFormLi.append($removeFormButton);

        $removeFormButton.on('click', function(e) {
            $tagFormLi.remove();
        });
    }

    // Adding task items on "Task Edit" page
    function addTaskItems(e, nameField, formName) {
        if ($(nameField).val() !== '') {
            let collectionHolderClass = $(e.currentTarget).data('collectionHolderClass');
            let index = addFormToCollection(collectionHolderClass, true);
            $(['name', 'qty']).each(function (i, current) {
                let input =  formName + '[taskItems][' + index + '][' + current + ']',
                    source = '.add-task-item-' + current;
                $('input[name="' + input + '"]').val($(source).val());
                $(source).val('');
            });
        } else {
            $(nameField).focus();
        }
    }

    function addTaskListUsers(e, selector) {
        let emailField = '.add-user-email';
        if (emailPattern.test($(emailField).val())) {
            let $collectionHolderClass = $(e.currentTarget).data('collectionHolderClass');
            let index = addFormToCollection($collectionHolderClass);
            $(['email']).each(function (i, current) {
                let input =  selector + '[users][' + index + '][' + current + ']';
                $('input[name="' + input + '"]').val($(emailField).val());
                $(emailField).val('');
            });
        } else {
            $(emailField).focus();
        }
    }

    // Add 'data-index' tad for multi-select forms
    function addDataIndex($selector, $count = 1) {
        $($selector).attr('data-index', $($selector).find('input').length / $count);
    }

    // Get notifications updates for loaded page
    var getNotificationsUpdate = function getNotificationsUpdate() {
        let url = $('#app-data').attr('data-update-notofications');
        $.ajax({
            url: url,
            method: 'GET'
        }).done(function( msg ) {
            console.log(msg.status);
            if (msg.status === msgSuccess) {
                $(".have-updates").addClass('active');
            }
        });
    };
    var notificationsUpdateInterval = 1000 * 60 * 2; //2 minutes
    setInterval(getNotificationsUpdate, notificationsUpdateInterval);

    // Process success result of share_list_email call
    window.shareListByEmailSuccess = function(msg, form) {
        $('#shared-users').append(msg.data);
        form.find("input[type=text], textarea").val('');
    };

    // Process success result of notification_read call
    window.readNotification = function(msg, form) {
        $('#unread-notifications').text(
            parseInt($('#unread-notifications').text()) - 1
        );
        form.closest('li').remove();
    };

    // Process success result of notification_read call
    window.editItem = function(msg, form) {
        let id = form.find('input[name="task_item_edit[id]"]').val();
        $('#item-id-' + id).replaceWith(msg.data);
        closeModal();
    };

    // Share list by email call handler
    $('form[name="share_list_email"]').on('submit', function(e) {
        ajaxSendForm(e, 'shareListByEmailSuccess');
    });

    // Share list by email call handler
    $('form[name="notification_read"]').on('submit', function(e) {
        ajaxSendForm(e, 'readNotification');
    });

    // Edit task item call handler
    $(document).on("submit", 'form[name="task_item_edit"]', function(e) {
        ajaxSendForm(e, 'editItem');
    });

    // Task item completion call handler
    $(document).on("click", '#list-items > .ti:not(.ti-counter)', function(e) {
        e.preventDefault();

        const classLoading = 'loading';
        if ($(this).hasClass(classLoading)) {
            return false;
        }

        let description = $(this),
            form = $(this).find("form"),
            data = getFormData(form);

        description.addClass(classLoading);

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: JSON.stringify(data)
        }).done(function( msg ) {
            description.removeClass(classLoading);

            if (msg.status === msgSuccess) {
                $("#list-items").html(msg.data);
            } else {
                alert( msg.data );
            }
        });
    });

    // Task item increment call handler
    $(document).on("click", '#list-items > .ti.ti-counter', function(e) {
        e.stopPropagation();

        const classLoading = 'loading';

        let description = $(this).parent().parent(),
            form = $(this).find("form"),
            id = $(form).find('input[name="task_item_increment[id]"]').val(),
            data = getFormData(form),
            selector = '#item-id-' + id;

        if ($(this).hasClass(classLoading) || ($(selector).hasClass('completed'))) {
            return false;
        }

        description.addClass(classLoading);

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: JSON.stringify(data)
        }).done(function( msg ) {
            description.removeClass(classLoading);

            if (msg.status === msgSuccess) {
                let response = $.parseJSON(msg.data);

                if (response.id === parseInt(id)) {
                    $(selector).find('.tiq').html(response.qty);
                } else {
                    alert( 'Error' );
                }
            } else {
                alert( msg.data );
            }
        });
    });

    $(document).on("click", '#list-items > .ti a', function(e) {
        e.stopPropagation();
    });

    // Task item edit call handler
    $(document).on("click", '.ti > .ie', function(e) {
        $.get($(this).attr('data-url')).then(function(data) {
            $('#li-modal').addClass('active');
            $('#li-modal .data').html(data);
        });
    });

    // Close modal window
    $(document).on("click", '#li-modal .close', function(e) {
        closeModal();
    });

    // Close admin notification popup
    $(document).on("click", '.a-n .close', function(e) {
        $.get($(this).attr('data-url')).then(function(msg) {
            if (msg.status != msgSuccess) {
                alert( msg.data );
            }
        });
    });

    // Load more lists on scrolling down
    $(window).on("scroll", function() {
        if ($('#loader').length) {
            let scrollHeight = $(document).height();
            let scrollPos = $(window).height() + $(window).scrollTop();
            let footerHeight = $('footer').height();
            if (scrollHeight - scrollPos < (footerHeight + 50)) {
                loadMore();
                $('#loader').remove();
            }
        }
    });

    // Close modal window
    $(document).on("click", '#loader button', function(e) {
        loadMore();
        $('#loader').remove();
    });

    // Add new task item inputs
    $(document).on('click', '#task_list .add-task-item', function(e) {
        e.preventDefault();
        addTaskItems(e, '.add-task-item-name', 'task_list');
    });

    // Add new counter task item inputs
    $(document).on('click', '#task_list_counter .add-task-item', function(e) {
        e.preventDefault();
        addTaskItems(e, '.add-task-item-name', 'task_list_counter');
    });

    // Add new task list users inputs
    $(document).on('click', '#task_list .add-t-l-user', function(e) {
        e.preventDefault();
        addTaskListUsers(e, 'task_list');
    });

    // Add new counter list users inputs
    $(document).on('click', '#task_list_counter .add-t-l-user', function(e) {
        e.preventDefault();
        addTaskListUsers(e, 'task_list_counter');
    });

    // Check favourite users input
    $(document).on('click', '.f-u-add .u-link, .f-u-add .u-link > a', function(e) {
        e.preventDefault();

        $(this).toggleClass('active');
        let active = $(this).hasClass('active'),
            email = $(this).find('.email').html(),
            select = $('#task_list_favouriteUsers');

        select.children().filter(function() {
            return this.text == email;
        }).prop('selected', active);
    });

    // Toggle task list user active state
    $(document).on('focus', '.task-list-users .t-l-user', function(e) {
        e.preventDefault();

        $(this).toggleClass('active');
        $(this).parent().siblings("input[name$='[active]']").val(
            $(this).hasClass('active') ? 1 : 0
        );
        $(this).blur();
    });

    // Hide completed items
    $(document).on("click", '.btn.hide-completed', function(e) {
        var link = getCurrentLink() + '/hide-completed';
        $.get( link, function( data ) {
            if (data.status === msgSuccess) {
                let response = $.parseJSON(data.data);
                $(".btn.hide-completed").text(response.button);
                $("#task-list-view").toggleClass('hidden-completed');
            } else {
                alert(data.data);
            }
        });
    });

    // Add index for creating Task Items form
    addDataIndex('ul.list-items', 2);
    addDataIndex('ul.task-list-users', 2);
    $('ul.list-items').find('li').each(function() {
        addTagFormDeleteLink($(this));
    });
});
