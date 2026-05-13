(function($) {
    'use strict';

    $(document).ready(function() {
        var $row    = $('#pinterest-test-connection-row');
        var $btn    = $('#pinterest-test-connection-btn');
        var $result = $('#pinterest-test-connection-result');
        var $tag    = $('#pixel_id');       // Pinterest Tag ID
        var $acct   = $('#additional_id');  // Pinterest Ad Account ID
        var $token  = $('#access_token');   // Pinterest Conversion Access Token

        if ($btn.length === 0 || $tag.length === 0 || $acct.length === 0 || $token.length === 0) {
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
            var hasTag   = $.trim($tag.val()) !== '';
            var hasAcct  = $.trim($acct.val()) !== '';
            var hasToken = $.trim($token.val()) !== '';

            if (hasTag && hasAcct && hasToken) {
                $row.show();
            } else {
                $row.hide();
                clearResult();
            }
        }

        $tag.on('input', updateVisibility);
        $acct.on('input', updateVisibility);
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
                    action: 'unipixel_pinterest_test_connection',
                    nonce: unipixel_ajax_obj.nonce,
                    pixel_id: $tag.val(),
                    additional_id: $acct.val(),
                    access_token: $token.val()
                },
                success: function(response) {
                    if (response && response.success && response.data) {
                        showResult('success', response.data.message);
                    } else {
                        var msg = (response && response.data && response.data.message)
                            ? response.data.message
                            : 'Unknown error from Pinterest.';
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
