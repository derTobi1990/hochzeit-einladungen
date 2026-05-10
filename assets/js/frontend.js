(function ($) {
    'use strict';

    $(document).ready(function () {

        var $form    = $('#he-rsvp-form');
        var $wrap    = $('#he-rsvp-wrap');
        var $success = $('#he-success');
        var $error   = $('#he-error');
        var $submit  = $('#he-submit');
        var $personenField = $('#he-personen-field');

        // Hide personen field when "kommt nicht" is selected
        $form.on('change', 'input[name="status"]', function () {
            if ($(this).val() === 'kommt_nicht') {
                $personenField.slideUp(200);
            } else {
                $personenField.slideDown(200);
            }
        });

        $form.on('submit', function (e) {
            e.preventDefault();

            var name     = $.trim($('#he-name').val());
            var status   = $('input[name="status"]:checked').val();
            var personen = parseInt($('#he-personen').val(), 10) || 1;
            var anmerkung = $.trim($('#he-anmerkung').val());

            // Basic validation
            if (!name) {
                showError('Bitte gib deinen Namen ein.');
                return;
            }
            if (status === 'kommt' && (personen < 1 || personen > 20)) {
                showError('Bitte gib eine gültige Personenanzahl ein.');
                return;
            }

            setLoading(true);
            hideError();

            $.ajax({
                url:    HE.ajaxurl,
                method: 'POST',
                data: {
                    action:    'he_rueckmeldung',
                    nonce:     HE.nonce,
                    name:      name,
                    status:    status,
                    personen:  status === 'kommt_nicht' ? 0 : personen,
                    anmerkung: anmerkung,
                },
                success: function (res) {
                    if (res.success) {
                        $form.fadeOut(300, function () {
                            $('#he-success-msg').text(res.data.message);
                            $success.fadeIn(400);
                        });
                    } else {
                        showError(res.data && res.data.message ? res.data.message : 'Ein Fehler ist aufgetreten.');
                    }
                },
                error: function () {
                    showError('Verbindungsfehler. Bitte versuche es erneut.');
                },
                complete: function () {
                    setLoading(false);
                }
            });
        });

        function setLoading(on) {
            $submit.prop('disabled', on);
            $submit.find('.he-submit-text').toggle(!on);
            $submit.find('.he-submit-loading').toggle(on);
        }

        function showError(msg) {
            $error.text(msg).fadeIn(200);
        }

        function hideError() {
            $error.hide();
        }
    });

}(jQuery));
