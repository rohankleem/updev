(function ($) {
    var UniPixelGeneralSettings = {
        init: function () {
            this.bindEvents();
        },
        bindEvents: function () {
            // Bind form submission for updating general (logging) settings
            $('#general-settings-form').on('submit', this.handleGeneralSettingsSubmit.bind(this));
        },
        handleGeneralSettingsSubmit: function (e) {
            e.preventDefault();

            const $form = document.getElementById('general-settings-form');
            const formData = new FormData($form);

            formData.append('action', 'unipixel_update_general_settings');
            formData.append('nonce', unipixel_ajax_obj.nonce);

            this.toggleLoading(true);

            $.ajax({
                type: 'POST',
                url: unipixel_ajax_obj.ajaxurl,
                data: formData,
                contentType: false,
                processData: false,
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
            var $loader = $('#general-settings-form-loader');
            if (show) {
                $formContainer.addClass('loading-mask');
                $loader.removeClass('d-none');  // Ensure the loader is shown
            } else {
                $formContainer.removeClass('loading-mask');
                $loader.addClass('d-none');       // Ensure the loader is hidden
            }
        },
        showFeedbackMessage: function (message, type) {
            var $messageContainer = $('#general-settings-feedback-message');
            $messageContainer
                .removeClass('alert-success alert-danger')
                .addClass('alert-' + type)
                .text(message)
                .show();
        }
    };

    $(document).ready(function () {
        UniPixelGeneralSettings.init();
    });
})(jQuery);
