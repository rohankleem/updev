(function($) {
    'use strict';

    $(document).ready(function() {
        var $row    = $('#google-test-connection-row');
        var $btn    = $('#google-test-connection-btn');
        var $result = $('#google-test-connection-result');
        var $mid    = $('#pixel_id');      // Measurement ID input
        var $secret = $('#access_token');  // API Secret input

        if ($btn.length === 0 || $mid.length === 0 || $secret.length === 0) {
            return;
        }

        function clearResult() {
            $result
                .removeClass('alert alert-success alert-danger alert-warning')
                .empty()
                .hide();
        }

        function showResult(level, message) {
            var cls = 'alert-danger';
            if (level === 'success') cls = 'alert-success';
            else if (level === 'pending') cls = 'alert-warning';

            $result
                .removeClass('alert-success alert-danger alert-warning')
                .addClass('alert')
                .addClass(cls)
                .text(message)
                .show();
        }

        function updateVisibility() {
            var hasMid    = $.trim($mid.val()) !== '';
            var hasSecret = $.trim($secret.val()) !== '';

            if (hasMid && hasSecret) {
                $row.show();
            } else {
                $row.hide();
                clearResult();
            }
        }

        $mid.on('input', updateVisibility);
        $secret.on('input', updateVisibility);
        updateVisibility();

        $btn.on('click', function(e) {
            e.preventDefault();

            if ($btn.prop('disabled')) {
                return;
            }

            $btn.prop('disabled', true);
            showResult('pending', 'Testing connection...');

            $.ajax({
                type: 'POST',
                url: unipixel_ajax_obj.ajaxurl,
                data: {
                    action: 'unipixel_google_test_connection',
                    nonce: unipixel_ajax_obj.nonce,
                    pixel_id: $mid.val(),
                    access_token: $secret.val()
                },
                success: function(response) {
                    if (response && response.success && response.data) {
                        showResult('success', response.data.message);
                    } else {
                        var msg = (response && response.data && response.data.message)
                            ? response.data.message
                            : 'Unknown error from Google.';
                        showResult('danger', msg);
                    }
                },
                error: function(xhr, textStatus) {
                    showResult('danger', 'Request failed: ' + (textStatus || 'unknown error'));
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });
    });
})(jQuery);
