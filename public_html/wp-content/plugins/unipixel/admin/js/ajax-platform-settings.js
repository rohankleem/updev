(function ($) {
    var UniPixelPlatformSettings = {
        init: function () {
            this.bindEvents();
        },
        bindEvents: function () {
            // Bind form submission for updating platform settings
            $('#platform-settings-form').on('submit', this.handlePlatformSettingsSubmit.bind(this));
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
