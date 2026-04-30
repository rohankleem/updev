// File: public_html\wp-content\plugins\unipixel\admin\js\ajax-consent-i18n.js

(function ($) {
    'use strict';

    var UnipixelConsentI18n = {

        init: function () {
            this.$feedback   = $('#consent-i18n-feedback');
            this.$accordion  = $('#consentI18nAccordion');
            this.bindEvents();
        },

        bindEvents: function () {
            $('#consentI18nAddBtn').on('click', this.handleAddLocale.bind(this));
            this.$accordion.on('submit', '.consent-i18n-form', this.handleSaveLocale.bind(this));
            this.$accordion.on('click', '.consent-i18n-delete', this.handleDeleteLocale.bind(this));
            this.$accordion.on('click', '.consent-i18n-reset', this.handleResetField.bind(this));
        },

        showFeedback: function (message, type) {
            this.$feedback
                .removeClass('alert-success alert-danger alert-warning')
                .addClass('alert-' + type)
                .text(message)
                .show();
            // Auto-hide after 4s
            clearTimeout(this._fbTimer);
            this._fbTimer = setTimeout(function () {
                $('#consent-i18n-feedback').fadeOut(300);
            }, 4000);
        },

        handleAddLocale: function (e) {
            e.preventDefault();
            var $select = $('#consentI18nAddLocale');
            var locale  = $select.val();
            if (!locale) {
                this.showFeedback('Pick a language from the dropdown first.', 'warning');
                return;
            }

            var self = this;

            $.ajax({
                type: 'POST',
                url: unipixel_ajax_obj.ajaxurl,
                data: {
                    action: 'unipixel_consent_i18n_add_locale',
                    nonce:  unipixel_ajax_obj.nonce,
                    locale: locale
                },
                success: function (response) {
                    if (response && response.success) {
                        self.showFeedback('Language added: ' + response.data.label + '. Expand it to customise the text.', 'success');
                        self.insertNewLocaleItem(response.data.locale, response.data.label);
                        // Remove the just-added option from the Add dropdown
                        $select.find('option[value="' + locale + '"]').remove();
                    } else {
                        var msg = (response && response.data && response.data.message) ? response.data.message : 'Could not add language.';
                        self.showFeedback(msg, 'danger');
                    }
                },
                error: function (xhr, textStatus) {
                    self.showFeedback('Request failed: ' + textStatus, 'danger');
                }
            });
        },

        insertNewLocaleItem: function (locale, label) {
            // Hide the "no languages yet" empty-state message if present
            $('#consentI18nEmptyMsg').remove();

            var itemTpl  = document.getElementById('consentI18nItemTemplate');
            var fieldTpl = document.getElementById('consentI18nFieldTemplate');
            if (!itemTpl || !fieldTpl) return;

            // Locale slug safe for DOM id
            var slug = locale.replace(/[^a-zA-Z0-9]/g, '-');

            var itemHtml = itemTpl.innerHTML
                .replace(/__LOCALE__/g, locale)
                .replace(/__LOCALE_SLUG__/g, slug)
                .replace(/__LABEL__/g, label)
                .replace('__FIELDS__', fieldTpl.innerHTML);

            this.$accordion.append(itemHtml);
        },

        handleSaveLocale: function (e) {
            e.preventDefault();
            var $form  = $(e.currentTarget);
            var locale = $form.data('locale');

            // Collect all field inputs as { key: value }
            var strings = {};
            $form.find('.consent-i18n-input').each(function () {
                var $input = $(this);
                strings[$input.attr('name')] = $input.val();
            });

            var self = this;

            $.ajax({
                type: 'POST',
                url: unipixel_ajax_obj.ajaxurl,
                data: {
                    action: 'unipixel_consent_i18n_save_locale',
                    nonce:  unipixel_ajax_obj.nonce,
                    locale: locale,
                    strings: strings
                },
                success: function (response) {
                    if (response && response.success) {
                        self.showFeedback('Saved ' + locale + '. ' + Object.keys(response.data.stored || {}).length + ' overrides stored.', 'success');
                        // Update the "customised" badge on the accordion header
                        var count = Object.keys(response.data.stored || {}).length;
                        var $header = $form.closest('.consent-i18n-locale-item').find('.accordion-header .badge');
                        if (count > 0) {
                            $header.removeClass('bg-secondary').addClass('bg-success').text(count + ' customised');
                        } else {
                            $header.removeClass('bg-success').addClass('bg-secondary').text('Using defaults');
                        }
                        // After save, replace input values with what the server stored (so the user sees sanitised version)
                        var stored = response.data.stored || {};
                        $form.find('.consent-i18n-input').each(function () {
                            var $input = $(this);
                            var key = $input.attr('name');
                            $input.val(stored[key] || '');
                        });
                    } else {
                        var msg = (response && response.data && response.data.message) ? response.data.message : 'Save failed.';
                        self.showFeedback(msg, 'danger');
                    }
                },
                error: function (xhr, textStatus) {
                    self.showFeedback('Request failed: ' + textStatus, 'danger');
                }
            });
        },

        handleDeleteLocale: function (e) {
            e.preventDefault();
            var $btn    = $(e.currentTarget);
            var $item   = $btn.closest('.consent-i18n-locale-item');
            var locale  = $item.data('locale');

            if (!confirm('Remove ' + locale + '? All custom text you have entered for this language will be deleted. Visitors in this language will see the default English strings (or a WordPress community translation if one exists).')) {
                return;
            }

            var self = this;

            $.ajax({
                type: 'POST',
                url: unipixel_ajax_obj.ajaxurl,
                data: {
                    action: 'unipixel_consent_i18n_delete_locale',
                    nonce:  unipixel_ajax_obj.nonce,
                    locale: locale
                },
                success: function (response) {
                    if (response && response.success) {
                        self.showFeedback('Removed ' + locale + '. Refresh the page if you want to re-add it.', 'success');
                        $item.remove();
                    } else {
                        var msg = (response && response.data && response.data.message) ? response.data.message : 'Remove failed.';
                        self.showFeedback(msg, 'danger');
                    }
                },
                error: function (xhr, textStatus) {
                    self.showFeedback('Request failed: ' + textStatus, 'danger');
                }
            });
        },

        handleResetField: function (e) {
            e.preventDefault();
            var $btn = $(e.currentTarget);
            var $field = $btn.closest('.consent-i18n-field');
            $field.find('.consent-i18n-input').val('');
        }

    };

    $(document).ready(function () {
        if ($('#consentI18nAccordion').length) {
            UnipixelConsentI18n.init();
        }
    });

})(jQuery);
