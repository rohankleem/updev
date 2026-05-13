(function($) {
    'use strict';

    $(document).ready(function() {
        var $row    = $('#microsoft-test-connection-row');
        var $btn    = $('#microsoft-test-connection-btn');
        var $result = $('#microsoft-test-connection-result');
        var $tag    = $('#pixel_id');      // UET Tag ID
        var $token  = $('#access_token');  // CAPI Access Token

        if ($btn.length === 0 || $tag.length === 0 || $token.length === 0) return;

        function clearResult() {
            $result.removeClass('alert alert-success alert-danger alert-warning').empty().hide();
        }
        function showResult(level, message) {
            var cls = 'alert-danger';
            if (level === 'success') cls = 'alert-success';
            else if (level === 'pending') cls = 'alert-warning';
            $result.removeClass('alert-success alert-danger alert-warning').addClass('alert').addClass(cls).text(message).show();
        }
        function updateVisibility() {
            var hasTag   = $.trim($tag.val()) !== '';
            var hasToken = $.trim($token.val()) !== '';
            if (hasTag && hasToken) { $row.show(); }
            else { $row.hide(); clearResult(); }
        }

        $tag.on('input', updateVisibility);
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
                    action: 'unipixel_microsoft_test_connection',
                    nonce: unipixel_ajax_obj.nonce,
                    pixel_id: $tag.val(),
                    access_token: $token.val()
                },
                success: function(response) {
                    if (response && response.success && response.data) {
                        showResult('success', response.data.message);
                    } else {
                        var msg = (response && response.data && response.data.message) ? response.data.message : 'Unknown error from Microsoft.';
                        showResult('danger', msg);
                    }
                },
                error: function(xhr, textStatus) {
                    showResult('danger', 'Request failed: ' + (textStatus || 'unknown error'));
                },
                complete: function() { $btn.prop('disabled', false); }
            });
        });
    });
})(jQuery);
