
$(document).ready(function() {

    $("#cf-submit").on('click', function(e) {
        e.preventDefault();
        $("#cf-result").html('');
        $(':input', '#contact-form').removeClass('cf-error');
        var formdata = $("#contact-form").serialize();
        $.ajax({
            type: 'POST',
            url: '/ajax/contactform.php',
            data: formdata,
            dataType: 'json'
        })
        .done(function(data) {
            if (data.success == 1) {
                $("#cf-result").html('<p class="cf-success">Your message has been sent to Guy! Thanks for getting in touch!</p>');
                $(':input', '#contact-form').val('');
                $("#cf-submit").prop('disabled', true);
                $("#cf-submit").css('cursor','not-allowed');

            } else {
                var focusIdx = -1;
                $.each(data.errors, function(index, errorObj) {
                    $("#cf-result").append('<p class="cf-error">'+errorObj.message+'</p>');
                    if (errorObj.id != '') {
                        $("#"+errorObj.id).addClass('cf-error');
                        if (focusIdx < 0) {
                            $("#"+errorObj.id).focus();
                            focusIdx = index;
                        }
                    }
                });
            }
        })
        .fail(function() {
            $("#cf-result").append('<p class="cf-error">There was a problem processing your message. Please try again.</p>');
        });

        return false;
    });
});
