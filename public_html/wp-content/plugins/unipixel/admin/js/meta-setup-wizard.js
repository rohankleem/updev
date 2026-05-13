(function($) {
    'use strict';

    var WIZARD_STEP_TITLES = [
        '',
        "What you'll achieve",
        'Prerequisites',
        'What to ignore',
        'Get your credentials',
        'Paste and save',
        'Test connection',
        'Done'
    ];
    var TOTAL_STEPS = 7;

    // Document-level delegation: a help-icon popover can contain a link with
    // href="#meta-setup-wizard". We can't reliably use data-bs-toggle="modal" inside
    // popovers because Bootstrap's popover sanitizer strips unknown attributes from
    // links. The href-anchor selector survives sanitization, so we hook it here.
    $(document).on('click', 'a[href="#meta-setup-wizard"]', function(e) {
        e.preventDefault();
        var modalEl = document.getElementById('meta-setup-wizard-modal');
        if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    });

    $(document).ready(function() {
        var $modal = $('#meta-setup-wizard-modal');
        if ($modal.length === 0) return;

        var $steps         = $modal.find('.meta-wizard-step');
        var $stepIndicator = $modal.find('.meta-wizard-current-step');
        var $nextBtn       = $modal.find('.meta-wizard-next');
        var $backBtn       = $modal.find('.meta-wizard-back');

        var currentStep = 1;

        function showStep(n) {
            n = Math.max(1, Math.min(TOTAL_STEPS, n));
            currentStep = n;
            $steps.addClass('d-none');
            $steps.filter('[data-step="' + n + '"]').removeClass('d-none');
            $stepIndicator.text(n);
            $backBtn.prop('disabled', n === 1);
            if (n >= 5) {
                $nextBtn.addClass('d-none');
            } else {
                $nextBtn.removeClass('d-none');
            }
        }

        function setResult($el, level, message) {
            var cls = 'alert-danger';
            if (level === 'success') cls = 'alert-success';
            else if (level === 'pending') cls = 'alert-warning';

            $el.removeClass('alert-success alert-danger alert-warning')
                .addClass('alert')
                .addClass(cls)
                .text(message)
                .show();
        }

        $modal.on('show.bs.modal', function() {
            showStep(1);
            $modal.find('#wizard-pixel-id').val($('#pixel_id').val());
            $modal.find('#wizard-access-token').val($('#access_token').val());
            $modal.find('#wizard-save-result').empty().hide().removeClass('alert alert-success alert-danger alert-warning');
            $modal.find('#wizard-test-connection-result').empty().hide().removeClass('alert alert-success alert-danger alert-warning');
        });

        $nextBtn.on('click', function() { showStep(currentStep + 1); });
        $backBtn.on('click', function() { showStep(currentStep - 1); });

        // Step 5: Save -> mirror to page form + fire existing update_platform AJAX -> advance
        $modal.find('#wizard-save-btn').on('click', function() {
            var pixelId = $.trim($modal.find('#wizard-pixel-id').val());
            var token   = $.trim($modal.find('#wizard-access-token').val());
            var $result = $modal.find('#wizard-save-result');

            if (!pixelId || !token) {
                setResult($result, 'pending', 'Please paste both your Pixel ID and Access Token before saving.');
                return;
            }

            // Mirror to page form so the underlying values stay consistent
            $('#pixel_id').val(pixelId);
            $('#access_token').val(token);

            var $saveBtn = $(this);
            $saveBtn.prop('disabled', true);
            setResult($result, 'pending', 'Saving...');

            var platformId        = $('#platform_id').val();
            var platformEnabled   = $('#platform_enabled').is(':checked') ? 1 : 1; // ensure on through wizard
            var serversideEnabled = $('#serverside_global_enabled').is(':checked') ? 1 : 1; // ensure on through wizard
            var pixelSetting      = $('input[name="pixel_setting"]:checked').val() || 'include';
            var pageviewSendServerside = $('#pageview_send_serverside').is(':checked') ? 1 : 1;

            // Reflect those defaults back into the page form so the UI doesn't lie
            if (!$('#platform_enabled').is(':checked')) $('#platform_enabled').prop('checked', true);
            if (!$('#serverside_global_enabled').is(':checked')) $('#serverside_global_enabled').prop('checked', true);

            $.ajax({
                type: 'POST',
                url: unipixel_ajax_obj.ajaxurl,
                data: {
                    action: 'unipixel_update_platform',
                    nonce: unipixel_ajax_obj.nonce,
                    platform_id: platformId,
                    pixel_id: pixelId,
                    access_token: token,
                    platform_enabled: platformEnabled,
                    serverside_global_enabled: serversideEnabled,
                    pageview_send_serverside: pageviewSendServerside,
                    pixel_setting: pixelSetting
                },
                success: function(response) {
                    if (response && response.success) {
                        setResult($result, 'success', 'Saved. Moving to verification.');
                        setTimeout(function() { showStep(currentStep + 1); }, 700);
                    } else {
                        var msg = (response && response.data && response.data.message) ? response.data.message : 'Could not save.';
                        setResult($result, 'danger', msg);
                    }
                },
                error: function(xhr, textStatus) {
                    setResult($result, 'danger', 'Request failed: ' + (textStatus || 'unknown error'));
                },
                complete: function() {
                    $saveBtn.prop('disabled', false);
                }
            });
        });

        // Step 6: Test Connection -> existing meta_test_connection AJAX -> advance on success
        $modal.find('#wizard-test-connection-btn').on('click', function() {
            var pixelId = $('#pixel_id').val();
            var token   = $('#access_token').val();
            var $result = $modal.find('#wizard-test-connection-result');
            var $btn    = $(this);

            $btn.prop('disabled', true);
            setResult($result, 'pending', 'Testing connection...');

            $.ajax({
                type: 'POST',
                url: unipixel_ajax_obj.ajaxurl,
                data: {
                    action: 'unipixel_meta_test_connection',
                    nonce: unipixel_ajax_obj.nonce,
                    pixel_id: pixelId,
                    access_token: token
                },
                success: function(response) {
                    if (response && response.success && response.data) {
                        setResult($result, 'success', response.data.message);
                        setTimeout(function() { showStep(currentStep + 1); }, 1000);
                    } else {
                        var msg = (response && response.data && response.data.message) ? response.data.message : 'Test failed.';
                        setResult($result, 'danger', msg);
                    }
                },
                error: function(xhr, textStatus) {
                    setResult($result, 'danger', 'Request failed: ' + (textStatus || 'unknown error'));
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });

        // Step 7: Done -> close modal, reload page so the strip flips green
        $modal.find('#wizard-done-btn').on('click', function() {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var instance = bootstrap.Modal.getOrCreateInstance($modal[0]);
                $modal.one('hidden.bs.modal', function() { window.location.reload(); });
                instance.hide();
            } else {
                window.location.reload();
            }
        });

        // "Looks different?" links: close wizard, open existing feedback modal pre-filled
        $modal.on('click', '.meta-wizard-looks-different', function(e) {
            e.preventDefault();
            var step  = currentStep;
            var title = WIZARD_STEP_TITLES[step] || '';

            if (typeof bootstrap === 'undefined' || !bootstrap.Modal) return;

            var wizardInstance = bootstrap.Modal.getOrCreateInstance($modal[0]);

            $modal.one('hidden.bs.modal', function() {
                $('#unipixelFeedbackType').val('Issue').trigger('change');
                $('#unipixelFeedback').val('Meta server-side wizard, step ' + step + ' (' + title + '): ').trigger('focus');
                var feedbackEl = document.getElementById('unipixelFeedbackModal');
                if (feedbackEl) {
                    bootstrap.Modal.getOrCreateInstance(feedbackEl).show();
                }
            });

            wizardInstance.hide();
        });
    });
})(jQuery);
