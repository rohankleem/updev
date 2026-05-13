(function($) {
    'use strict';

    $(document).ready(function() {
        var $row    = $('#tiktok-test-connection-row');
        var $btn    = $('#tiktok-test-connection-btn');
        var $result = $('#tiktok-test-connection-result');
        var $pid    = $('#pixel_id');
        var $token  = $('#access_token');

        if ($btn.length === 0 || $pid.length === 0 || $token.length === 0) {
            return;
        }

        function clearResult() {
            $result.removeClass('alert alert-success alert-danger alert-warning').empty().hide();
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
            var hasPid   = $.trim($pid.val()) !== '';
            var hasToken = $.trim($token.val()) !== '';

            if (hasPid && hasToken) {
                $row.show();
            } else {
                $row.hide();
                clearResult();
            }
        }

        $pid.on('input', updateVisibility);
        $token.on('input', updateVisibility);
        updateVisibility();

        $btn.on('click', function(e) {
            e.preventDefault();
            if ($btn.prop('disabled')) return;

            $btn.prop('disabled', true);
            showResult('pending', 'Testing connection...');

            $.ajax({
                type: 'POST',
                url: unipixel_ajax_obj.ajaxurl,
                data: {
                    action: 'unipixel_tiktok_test_connection',
                    nonce: unipixel_ajax_obj.nonce,
                    pixel_id: $pid.val(),
                    access_token: $token.val()
                },
                success: function(response) {
                    if (response && response.success && response.data) {
                        showResult('success', response.data.message);
                    } else {
                        var msg = (response && response.data && response.data.message)
                            ? response.data.message
                            : 'Unknown error from TikTok.';
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
