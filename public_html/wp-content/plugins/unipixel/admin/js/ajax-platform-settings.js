(function ($) {
    var UniPixelPlatformSettings = {
        init: function () {
            this.bindEvents();
            // Make sure the pill reflects current form state on initial load
            // (in case the server-rendered value drifts from form defaults).
            this.updateClientSidePill();
        },
        bindEvents: function () {
            // Bind form submission for updating platform settings
            $('#platform-settings-form').on('submit', this.handlePlatformSettingsSubmit.bind(this));

            // Live-update the Client-side tracking pill (Active / Off) whenever
            // the master toggle flips or the Pixel ID input changes. Reflects
            // intent immediately so users don't have to reload after Update.
            $('#platform_enabled').on('change', this.updateClientSidePill.bind(this));
            $('#pixel_id').on('input change', this.updateClientSidePill.bind(this));

            // Disarm the readonly attribute on credential password inputs the
            // moment the user actually interacts with them. The readonly is
            // applied server-side to stop Chrome's browser autofill from
            // injecting the user's saved WP login password (or any other
            // saved password) into Access Token / API Secret / Conversion
            // Access Token fields. autocomplete="new-password" and hidden
            // honeypot fields aren't enough on Chrome for password-typed
            // inputs; the readonly attribute is the only reliable signal
            // that the field should be left alone at autofill scan time.
            $(document).on('focus mousedown touchstart',
                'input[type="password"][readonly]',
                function () { this.removeAttribute('readonly'); }
            );
        },
        updateClientSidePill: function () {
            var $pill = $('.unipixel-client-side-pill');
            if (!$pill.length) return;
            var pixelId = $.trim($('#pixel_id').val() || '');
            var platformEnabled = $('#platform_enabled').is(':checked');
            var active = (pixelId !== '' && platformEnabled);
            var labelActive = $pill.data('label-active') || 'Active';
            var labelOff = $pill.data('label-off') || 'Off';
            if (active) {
                $pill.removeClass('bg-secondary').addClass('bg-success').text(labelActive);
            } else {
                $pill.removeClass('bg-success').addClass('bg-secondary').text(labelOff);
            }
        },
        handlePlatformSettingsSubmit: function (e) {
            e.preventDefault();

            var platform_enabled = $('#platform_enabled').is(':checked') ? 1 : 0;

            var formData = {
                'action': 'unipixel_update_platform',
                'nonce': unipixel_ajax_obj.nonce,
                'platform_id': $('#platform_id').val(),
                'pixel_id': $('#pixel_id').val(),
                'access_token': $('#access_token').length ? $('#access_token').val() : '',
                'platform_enabled': platform_enabled,
                'additional_id': $('#additional_id').val() || '',
                'serverside_global_enabled': $('#serverside_global_enabled').is(':checked') ? 1 : 0
            };

            // Add pixel_setting directly
            formData['pixel_setting'] = $('input[name="pixel_setting"]:checked').val() || '';

            this.toggleLoading(true);

            $.ajax({
                type: 'POST',
                url: unipixel_ajax_obj.ajaxurl,
                data: formData,
                success: function (response) {
                    console.log('Success:', response);
                    if (response.success) {
                        this.showFeedbackMessage(response.data.message, 'success');
                        // Canonical pill update from persisted state, after save success.
                        this.updateClientSidePill();
                    } else {
                        this.showFeedbackMessage(response.data.message, 'danger');
                    }
                }.bind(this),
                error: function (xhr, textStatus, errorThrown) {
                    console.log('AJAX Error:', textStatus, errorThrown);
                    this.showFeedbackMessage('Ajax request failed: ' + textStatus + ' ' + errorThrown, 'danger');
                }.bind(this),
                complete: function () {
                    this.toggleLoading(false);
                }.bind(this)
            });
        },
        toggleLoading: function (show) {
            var $formContainer = $('.UniPixelShell');
            var $loader = $('#platform-settings-form-loader');
            if (show) {
                $formContainer.addClass('loading-mask');
                $loader.removeClass('d-none');  // Ensure the loader is shown
            } else {
                $formContainer.removeClass('loading-mask');
                $loader.addClass('d-none');  // Ensure the loader is hidden
            }
        },
        showFeedbackMessage: function (message, type) {
            var $messageContainer = $('#platform-settings-feedback-message');
            $messageContainer
                .removeClass('alert-success alert-danger')
                .addClass('alert-' + type)
                .text(message)
                .show();
        }
    };

    $(document).ready(function () {
        UniPixelPlatformSettings.init();

        $('#btnUniPixelUpdatePageViewSettings').on('click', function (e) {
            e.preventDefault();
            $('#btnUniPixelUpdatePlatformSettings').trigger('click');
        });

    });
})(jQuery);
