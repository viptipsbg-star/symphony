function showmessage(message, level) {
    var $message = $('<div class="alert global-alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button> </div>');
    $message.append(message);
    if (level == "success") {
        $message.addClass('alert-success');
    }
    else if (level == "danger") {
        $message.addClass('alert-danger');
    }
    $message.hide().appendTo('body');

    $message.alert();
    $message.fadeTo(2000, 500).slideUp(500, function(){
        $message.alert('close');
    });
}
