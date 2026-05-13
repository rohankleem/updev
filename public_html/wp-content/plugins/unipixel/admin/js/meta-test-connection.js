(function($) {
    'use strict';

    $(document).ready(function() {
        var $row       = $('#meta-test-connection-row');
        var $btn       = $('#meta-test-connection-btn');
        var $result    = $('#meta-test-connection-result');
        var $token     = $('#access_token');
        var $pixelId   = $('#pixel_id');

        if ($btn.length === 0 || $token.length === 0 || $pixelId.length === 0) {
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
            var hasToken   = $.trim($token.val()) !== '';
            var hasPixelId = $.trim($pixelId.val()) !== '';

            if (hasToken && hasPixelId) {
                $row.show();
            } else {
                $row.hide();
                clearResult();
            }
        }

        $token.on('input', updateVisibility);
        $pixelId.on('input', updateVisibility);
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
                    action: 'unipixel_meta_test_connection',
                    nonce: unipixel_ajax_obj.nonce,
                    access_token: $token.val(),
                    pixel_id: $pixelId.val()
                },
                success: function(response) {
                    if (response && response.success && response.data) {
                        showResult('success', response.data.message);
                    } else {
                        var msg = (response && response.data && response.data.message)
                            ? response.data.message
                            : 'Unknown error from Meta.';
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
