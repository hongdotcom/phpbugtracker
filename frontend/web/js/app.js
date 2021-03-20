function toggleActivityModal(tag, idrecord) {
    $(tag).modal();

    $.ajax({
        url: '/crm-record/view?id=' + idrecord,
        cache: false,
        type: 'POST',
        error: function (xhr, ajaxOptions, thrownError) {
            alert("There was an error occurred");
        },
        success: function (response) {
            $(tag + " .modal-body").html(response);
        }
    });
}

function submitWorklog(tag) {
    var form = $(tag)[0];
    var formdata = new FormData(form);

    $.ajax({
        url: '/crm-activity/save',
        cache: false,
        dataType: 'json',
        type: 'POST',
        processData: false,
        contentType: false,
        data: formdata,
        error: function (xhr, ajaxOptions, thrownError) {
            alert("error");
        },
        success: function (response) {
            if (response.success) {
                $("#worklog-form")[0].reset();
                location.reload();
            } else {
                alert(response.msg);
            }
        }
    });
}

function notify(message, type, delay, dismissable, title, icon) {

    if (typeof title === 'undefined') {
        title = null;
    }
    if (typeof icon === 'undefined') {
        icon = null;
    }
    if (typeof delay === 'undefined') {
        delay = 2500;
    }
    if (typeof dismissable === 'undefined') {
        dismissable = true;
    }

    $.notify({
        // options
        message: message,
        title: title,
        icon: icon,
    }, {
        // settings
        type: type,
        showProgressbar: true,
        delay: delay,
        icon_type: 'class',
        mouse_over: 'pause',
        placement: {
            from: "top",
            align: "right"
        },
    });
}

function checkForm(formId, $section) {
    var $form = $("#" + formId), data = $form.data("yiiActiveForm");

    if ($section == null) {
        $.each(data.attributes, function () {
            this.status = 3;
        });
        //$form.yiiActiveForm("validate");
    } else {
        $.each(data.attributes, function () {
            var $item = $("#" + this.id, $section);

            if ($item.length > 0) {
                this.status = 3;
                $form.yiiActiveForm('validateAttribute', this.id);
            }
        });
    }

    $form.yiiActiveForm("validate");

    if ($section == null) {
        return $form.find('.has-error').length == 0;
    } else {
        return $section.find('.has-error').length == 0;
    }
}