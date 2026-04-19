// File: public_html\wp-content\plugins\unipixel\admin\js\ajax-consent-settings.js

(function ($) {
    var UnipixelConsentSettings = {
        init: function () {
            this.bindEvents();
        },
        bindEvents: function () {
            // Bind form submission for updating consent settings
            $('#consentSettingsForm').on('submit', this.handleConsentSettingsSubmit.bind(this));
        },
        handleConsentSettingsSubmit: function (e) {
            e.preventDefault();

            var consentHonour = $('#enableConsentHonour').is(':checked') ? 1 : 0;
            var consentUI = $('#consentUI').val() || 'unipixel';
            var consentUIStyle = $('#consentUIStyle').val() || 1;

            var formData = {
                action: 'unipixel_update_consent_settings',
                nonce: unipixel_ajax_obj.nonce,
                consent_honour: consentHonour,
                consent_ui: consentUI,
                consent_ui_style: consentUIStyle
            };

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
            var $loader = $('#general-settings-form-loader');
            if (show) {
                $formContainer.addClass('loading-mask');
                $loader.removeClass('d-none');
            } else {
                $formContainer.removeClass('loading-mask');
                $loader.addClass('d-none');
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
        UnipixelConsentSettings.init();
    });
})(jQuery);
