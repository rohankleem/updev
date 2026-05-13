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

    $(document).on('click', 'a[href="#tiktok-setup-wizard"]', function(e) {
        e.preventDefault();
        var modalEl = document.getElementById('tiktok-setup-wizard-modal');
        if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    });

    $(document).ready(function() {
        var $modal = $('#tiktok-setup-wizard-modal');
        if ($modal.length === 0) return;

        var $steps         = $modal.find('.tiktok-wizard-step');
        var $stepIndicator = $modal.find('.tiktok-wizard-current-step');
        var $nextBtn       = $modal.find('.tiktok-wizard-next');
        var $backBtn       = $modal.find('.tiktok-wizard-back');

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
            $modal.find('#wizard-tiktok-pixel-id').val($('#pixel_id').val());
            $modal.find('#wizard-tiktok-access-token').val($('#access_token').val());
            $modal.find('#wizard-tiktok-save-result').empty().hide().removeClass('alert alert-success alert-danger alert-warning');
            $modal.find('#wizard-tiktok-test-connection-result').empty().hide().removeClass('alert alert-success alert-danger alert-warning');
        });

        $nextBtn.on('click', function() { showStep(currentStep + 1); });
        $backBtn.on('click', function() { showStep(currentStep - 1); });

        $modal.find('#wizard-tiktok-save-btn').on('click', function() {
            var pid     = $.trim($modal.find('#wizard-tiktok-pixel-id').val());
            var token   = $.trim($modal.find('#wizard-tiktok-access-token').val());
            var $result = $modal.find('#wizard-tiktok-save-result');

            if (!pid || !token) {
                setResult($result, 'pending', 'Please paste both your Pixel ID and Access Token before saving.');
                return;
            }

            $('#pixel_id').val(pid);
            $('#access_token').val(token);

            var $saveBtn = $(this);
            $saveBtn.prop('disabled', true);
            setResult($result, 'pending', 'Saving...');

            var platformId   = $('#platform_id').val();
            var pixelSetting = $('input[name="pixel_setting"]:checked').val() || 'include';

            if (!$('#platform_enabled').is(':checked')) $('#platform_enabled').prop('checked', true);
            if (!$('#serverside_global_enabled').is(':checked')) $('#serverside_global_enabled').prop('checked', true);

            $.ajax({
                type: 'POST',
                url: unipixel_ajax_obj.ajaxurl,
                data: {
                    action: 'unipixel_update_platform',
                    nonce: unipixel_ajax_obj.nonce,
                    platform_id: platformId,
                    pixel_id: pid,
                    access_token: token,
                    platform_enabled: 1,
                    serverside_global_enabled: 1,
                    pageview_send_serverside: 1,
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

        $modal.find('#wizard-tiktok-test-connection-btn').on('click', function() {
            var pid    = $('#pixel_id').val();
            var token  = $('#access_token').val();
            var $result = $modal.find('#wizard-tiktok-test-connection-result');
            var $b      = $(this);

            $b.prop('disabled', true);
            setResult($result, 'pending', 'Testing connection...');

            $.ajax({
                type: 'POST',
                url: unipixel_ajax_obj.ajaxurl,
                data: {
                    action: 'unipixel_tiktok_test_connection',
                    nonce: unipixel_ajax_obj.nonce,
                    pixel_id: pid,
                    access_token: token
                },
                success: function(response) {
                    if (response && response.success && response.data) {
                        setResult($result, 'success', response.data.message);
                        setTimeout(function() { showStep(currentStep + 1); }, 1500);
                    } else {
                        var msg = (response && response.data && response.data.message) ? response.data.message : 'Test failed.';
                        setResult($result, 'danger', msg);
                    }
                },
                error: function(xhr, textStatus) {
                    setResult($result, 'danger', 'Request failed: ' + (textStatus || 'unknown error'));
                },
                complete: function() {
                    $b.prop('disabled', false);
                }
            });
        });

        $modal.find('#wizard-tiktok-done-btn').on('click', function() {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var instance = bootstrap.Modal.getOrCreateInstance($modal[0]);
                $modal.one('hidden.bs.modal', function() { window.location.reload(); });
                instance.hide();
            } else {
                window.location.reload();
            }
        });

        $modal.on('click', '.tiktok-wizard-looks-different', function(e) {
            e.preventDefault();
            var step  = currentStep;
            var title = WIZARD_STEP_TITLES[step] || '';

            if (typeof bootstrap === 'undefined' || !bootstrap.Modal) return;

            var wizardInstance = bootstrap.Modal.getOrCreateInstance($modal[0]);

            $modal.one('hidden.bs.modal', function() {
                $('#unipixelFeedbackType').val('Issue').trigger('change');
                $('#unipixelFeedback').val('TikTok server-side wizard, step ' + step + ' (' + title + '): ').trigger('focus');
                var feedbackEl = document.getElementById('unipixelFeedbackModal');
                if (feedbackEl) {
                    bootstrap.Modal.getOrCreateInstance(feedbackEl).show();
                }
            });

            wizardInstance.hide();
        });
    });
})(jQuery);
