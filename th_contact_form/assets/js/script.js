jQuery(document).ready(function($) {
    $('#custom-contact-form').on('submit', function(e) {
        e.preventDefault();

        var formData = $(this).serialize();
        formData += '&action=submit_contact_form&nonce=' + ajax_object.nonce;

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#form-response').removeClass('error').addClass('success').html(response.data);
                    $('#custom-contact-form')[0].reset();
                } else {
                    $('#form-response').removeClass('success').addClass('error').html(response.data);
                }
            }
        });
    });
});