(function ($) {
    if (!$('.unipixel-conversions-list, .unipixel-conversion-builder').length) return;

    var STANDARD_EVENTS_BY_PLATFORM = {
        1: ['AddPaymentInfo','AddToCart','AddToWishlist','CompleteRegistration','Contact','CustomizeProduct','Donate','FindLocation','InitiateCheckout','Lead','Purchase','Schedule','Search','StartTrial','SubmitApplication','Subscribe','ViewContent'],
        2: ['AddToCart','Checkout','Lead','PageVisit','Search','Signup','ViewCategory','WatchVideo'],
        3: ['AddPaymentInfo','AddToCart','AddToWishlist','ClickButton','CompletePayment','CompleteRegistration','Contact','Download','InitiateCheckout','PlaceAnOrder','Search','SubmitForm','Subscribe','ViewContent'],
        4: ['generate_lead','sign_up','login','search','select_content','share','begin_checkout','add_to_cart','view_item','purchase','view_promotion','select_promotion'],
        5: ['add_to_cart','begin_checkout','purchase','subscribe','sign_up','lead','contact','search']
    };

    // Conceptual → per-platform default name. Mirrors PHP unipixel_conceptual_event_map().
    var CONCEPTUAL_EVENT_MAP = {
        Lead:                 {1:'Lead',                2:'Lead',     3:'Contact',              4:'generate_lead', 5:'lead'},
        ContactFormSubmitted: {1:'Contact',             2:'Lead',     3:'Contact',              4:'generate_lead', 5:'contact'},
        NewsletterSignup:     {1:'Subscribe',           2:'Signup',   3:'Subscribe',            4:'sign_up',       5:'subscribe'},
        Registration:         {1:'CompleteRegistration',2:'Signup',   3:'CompleteRegistration', 4:'sign_up',       5:'sign_up'},
        Search:               {1:'Search',              2:'Search',   3:'Search',               4:'search',        5:'search'},
        ViewContent:          {1:'ViewContent',         2:'PageVisit',3:'ViewContent',          4:'view_item',     5:'view_item'}
    };

    var TRIGGER_LABELS = {click: 'On Element Clicked', shown: 'On Element Shown', url: 'On Page URL Match'};

    var state = {
        platforms: [],
        pages: [],
        groupId: 0,
        editing: null
    };

    function ajaxObj() {
        return window.unipixel_ajax_obj || {};
    }

    function postAjax(action, data) {
        return $.post(ajaxObj().ajaxurl, $.extend({action: action, nonce: ajaxObj().nonce}, data || {}));
    }

    function escapeHtml(s) {
        return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function showFeedback(msg, type) {
        var $box = $('#builder-feedback');
        $box.html('<div class="notice notice-' + (type === 'error' ? 'error' : 'success') + '"><p>' + escapeHtml(msg) + '</p></div>');
    }

    // ============================== LIST VIEW ==============================

    function initList() {
        postAjax('unipixel_conversions_list')
            .done(function (resp) {
                if (!resp || !resp.success) {
                    $('#conversions-list-container').html('<div class="notice notice-error"><p>' + escapeHtml((resp && resp.data && resp.data.message) || 'Failed to load.') + '</p></div>');
                    return;
                }
                state.platforms = resp.data.platforms || [];
                renderList(resp.data.groups || [], resp.data.enabled_platform_count || 0);
            })
            .fail(function () {
                $('#conversions-list-container').html('<div class="notice notice-error"><p>Network error loading conversions.</p></div>');
            });
    }

    // Build the "you need to enable at least one platform" warning, with quick-links to each
    // platform's setup page. Returns HTML string. Used by both list and builder views.
    function noPlatformsWarningHtml(platforms) {
        var links = (platforms || []).map(function (p) {
            return '<a href="' + escapeHtml(p.admin_url) + '" class="btn btn-sm btn-outline-primary me-1 mb-1">' + escapeHtml(p.platform_name) + ' Setup</a>';
        }).join(' ');
        return '<div class="card border-warning">'
            + '<div class="card-body">'
            +   '<h5 class="mb-2"><i class="fa-solid fa-triangle-exclamation text-warning"></i> No tracking platforms are enabled yet</h5>'
            +   '<p class="mb-3">UniPixel can\'t send events anywhere until you set up at least one tracking platform. Pick a platform to get started:</p>'
            +   '<div>' + links + '</div>'
            + '</div>'
            + '</div>';
    }

    function renderList(groups, enabledCount) {
        var $container = $('#conversions-list-container');
        $container.empty();

        if (enabledCount === 0) {
            $('#conversions-count').text('No platforms enabled.');
            $container.html(noPlatformsWarningHtml(state.platforms));
            // Disable the Create button until they enable a platform
            var $createBtn = $('a[href$="action=builder"]');
            $createBtn.addClass('disabled').attr('aria-disabled', 'true').css('pointer-events', 'none').css('opacity', '0.5');
            return;
        }

        $('#conversions-count').text(groups.length === 0 ? 'No conversions yet.' : (groups.length + ' conversion' + (groups.length === 1 ? '' : 's')));
        if (groups.length === 0) {
            $container.html('<div class="card"><div class="card-body text-center text-muted py-5"><i class="fa-solid fa-bullseye fa-2x mb-3"></i><p>No conversions configured yet. Click <strong>Create new conversion</strong> to get started.</p></div></div>');
            return;
        }
        var html = '<div class="table-responsive"><table class="table table-hover wp-list-table widefat striped">';
        html += '<thead><tr><th>Conversion</th><th>Trigger</th><th>Target</th><th>Coverage</th><th></th></tr></thead><tbody>';
        groups.forEach(function (g) {
            var editUrl = 'admin.php?page=unipixel_conversions&action=builder&group_id=' + g.id;
            var coverage = (g.platform_count || 0) + ' of ' + (enabledCount || 0) + ' enabled platforms';
            html += '<tr>'
                + '<td><strong>' + escapeHtml(g.conceptual_event) + '</strong>' + (g.description ? '<br><small class="text-muted">' + escapeHtml(g.description) + '</small>' : '') + '</td>'
                + '<td>' + escapeHtml(TRIGGER_LABELS[g.event_trigger] || g.event_trigger) + '</td>'
                + '<td><code>' + escapeHtml(g.trigger_target) + '</code></td>'
                + '<td><span class="badge bg-secondary">' + escapeHtml(coverage) + '</span></td>'
                + '<td><a class="btn btn-sm btn-outline-primary" href="' + editUrl + '">Edit</a></td>'
                + '</tr>';
        });
        html += '</tbody></table></div>';
        $container.html(html);
    }

    // ============================== BUILDER VIEW ==============================

    function initBuilder() {
        var $root = $('.unipixel-conversion-builder');
        state.groupId = parseInt($root.attr('data-group-id'), 10) || 0;

        // Fetch platforms + pages in parallel, then optionally fetch group
        $.when(
            postAjax('unipixel_conversions_platforms'),
            postAjax('unipixel_conversions_pages')
        ).done(function (platRes, pageRes) {
            var platBody = platRes[0]; var pageBody = pageRes[0];
            if (!platBody || !platBody.success || !pageBody || !pageBody.success) {
                showFeedback('Failed to load platforms/pages.', 'error');
                return;
            }
            state.platforms = platBody.data.platforms || [];
            state.pages = pageBody.data.pages || [];

            // Hard gate: no platforms enabled at all → show the warning, hide the form.
            var enabledCount = state.platforms.filter(function (p) { return p.enabled; }).length;
            if (enabledCount === 0) {
                $('#builder-loading').hide();
                $('#unipixel-conversion-builder-form').hide();
                var $root = $('.unipixel-conversion-builder');
                $root.append(noPlatformsWarningHtml(state.platforms));
                return;
            }

            populatePagePicker();
            populateDisabledLinks();

            if (state.groupId > 0) {
                postAjax('unipixel_conversions_get', {group_id: state.groupId})
                    .done(function (gRes) {
                        if (gRes && gRes.success) {
                            state.editing = gRes.data.group;
                            populateBuilderFromGroup(state.editing);
                        } else {
                            showFeedback('Could not load this conversion.', 'error');
                        }
                        $('#builder-loading').hide();
                        $('#unipixel-conversion-builder-form').show();
                    });
            } else {
                $('#builder-loading').hide();
                $('#unipixel-conversion-builder-form').show();
            }

            wireBuilderEvents();
        }).fail(function () {
            showFeedback('Network error loading builder.', 'error');
        });
    }

    function populatePagePicker() {
        var $sel = $('#builder-page-picker');
        state.pages.forEach(function (p) {
            $sel.append('<option value="' + escapeHtml(p.path) + '">' + escapeHtml(p.title) + ' — ' + escapeHtml(p.path) + '</option>');
        });
    }

    function populateDisabledLinks() {
        var disabled = state.platforms.filter(function (p) { return !p.enabled; });
        if (disabled.length === 0) {
            $('#builder-platforms-disabled-hint').hide();
            return;
        }
        var html = disabled.map(function (p) {
            return '<a href="' + escapeHtml(p.admin_url) + '">' + escapeHtml(p.platform_name) + ' Setup</a>';
        }).join(' · ');
        $('#builder-disabled-links').html(html);
        $('#builder-platforms-disabled-hint').show();
    }

    function populateBuilderFromGroup(group) {
        $('#builder-event-trigger').val(group.event_trigger).trigger('change');
        $('#builder-trigger-target').val(group.trigger_target);
        if (group.event_trigger === 'url') {
            var mode = pickUrlModeForValue(group.trigger_target);
            if (mode === 'page') {
                $('#builder-page-picker').val(group.trigger_target);
            }
            $('#url-mode-' + mode).prop('checked', true);
            applyUrlMode();
        }
        // Try to set conceptual event; if it's not in the dropdown, fall back to Custom
        var $conceptual = $('#builder-conceptual-event');
        if ($conceptual.find('option[value="' + group.conceptual_event + '"]').length) {
            $conceptual.val(group.conceptual_event).trigger('change');
        } else {
            $conceptual.val('__CUSTOM__').trigger('change');
            $('#builder-custom-name').val(group.conceptual_event);
        }
        $('#builder-description').val(group.description || '');
        // Now apply per-platform overrides from linked_rows
        if (group.linked_rows && group.linked_rows.length) {
            group.linked_rows.forEach(function (row) {
                var $platRow = $('.builder-platform-row[data-platform-id="' + row.platform_id + '"]');
                if (!$platRow.length) return;
                $platRow.find('.platform-include').prop('checked', true);
                setPlatformEventName($platRow, row.event_name);
                $platRow.find('.platform-send-client').prop('checked', !!parseInt(row.send_client, 10));
                $platRow.find('.platform-send-server').prop('checked', !!parseInt(row.send_server, 10));
                $platRow.find('.platform-log-response').prop('checked', !!parseInt(row.send_server_log_response, 10));
                refreshPlatformRowDisabled($platRow);
            });
        }
    }

    function wireBuilderEvents() {
        $('#builder-event-trigger').on('change', onTriggerTypeChange);
        $('.url-mode-radio').on('change', applyUrlMode);
        $('#builder-page-picker').on('change', function () {
            var v = $(this).val();
            if (v && $('input[name="builder-url-mode"]:checked').val() === 'page') {
                $('#builder-trigger-target').val(v);
            }
        });
        $('#builder-conceptual-event').on('change', onConceptualEventChange);
        $(document).on('change', '.platform-include', function () {
            refreshPlatformRowDisabled($(this).closest('.builder-platform-row'));
        });
        // Google G-001 mutex (client/server radio pair) for non-Purchase events.
        $(document).on('change', '.builder-platform-row[data-platform-id="4"] .platform-send-client, .builder-platform-row[data-platform-id="4"] .platform-send-server', function () {
            if (!isPurchaseConceptual()) {
                var $row = $(this).closest('.builder-platform-row');
                if (this.checked) {
                    if ($(this).hasClass('platform-send-client')) {
                        $row.find('.platform-send-server').prop('checked', false);
                    } else {
                        $row.find('.platform-send-client').prop('checked', false);
                    }
                }
            }
        });
        $('#unipixel-conversion-builder-form').on('submit', onBuilderSubmit);
        $('#builder-delete-btn').on('click', onBuilderDelete);
    }

    function onTriggerTypeChange() {
        var $select = $('#builder-event-trigger');
        var v = $select.val();
        var prev = $select.data('previousTrigger') || '';
        var $wrap = $('#builder-trigger-target-wrap');
        var $label = $('#builder-trigger-target-label');
        var $help = $('#builder-trigger-target-help');
        var $modes = $('#builder-url-modes');
        var $target = $('#builder-trigger-target');

        // Clear the target value when crossing between trigger families (URL ↔ CSS selector)
        // since the value semantics differ entirely. Click ↔ shown can share a selector.
        var crossingFamily = (prev === 'url') !== (v === 'url');
        if (crossingFamily && prev !== '') {
            $target.val('');
            $('#builder-page-picker').val('');
            $('#url-mode-page').prop('checked', true); // reset to default mode
        }
        $select.data('previousTrigger', v);

        if (!v) {
            $wrap.hide();
            return;
        }
        $wrap.show();
        if (v === 'url') {
            $label.text('URL pattern');
            $modes.show();
            $target.attr('placeholder', '/thank-you* or *');
            applyUrlMode();
        } else {
            $label.text('CSS selector');
            $modes.hide();
            $target.attr('placeholder', '#contact-form or .cta-button').prop('disabled', false);
            $help.text('CSS selector for the element to track.');
        }
    }

    // Apply the currently-selected URL mode (page / any / custom).
    // - page: page picker active; text input disabled, value = picked path
    // - any: text input disabled, value = '*', picker cleared
    // - custom: text input editable; picker cleared
    function applyUrlMode() {
        var mode = $('input[name="builder-url-mode"]:checked').val() || 'page';
        var $target = $('#builder-trigger-target');
        var $picker = $('#builder-page-picker');
        var $help = $('#builder-trigger-target-help');

        if (mode === 'page') {
            $picker.prop('disabled', false);
            $target.prop('disabled', true);
            var picked = $picker.val();
            if (picked) {
                $target.val(picked);
            }
            $help.text('Picked from your site list. Switch to "custom" if you need to add a wildcard.');
        } else if (mode === 'any') {
            $picker.prop('disabled', true).val('');
            $target.val('*').prop('disabled', true);
            $help.text('Fires on any URL on your site.');
        } else {
            $picker.prop('disabled', true).val('');
            $target.prop('disabled', false);
            $help.text('Use * as wildcard. Examples: /thank-you/, /thank-you*, *thank*, *');
        }
    }

    // Detect which URL mode best matches an existing trigger_target value.
    // Used when populating the builder for editing an existing group.
    function pickUrlModeForValue(value) {
        if (value === '*') return 'any';
        // If exactly matches a page path → 'page'
        for (var i = 0; i < state.pages.length; i++) {
            if (state.pages[i].path === value) return 'page';
        }
        return 'custom';
    }

    function onConceptualEventChange() {
        var v = $('#builder-conceptual-event').val();
        if (v === '__CUSTOM__') {
            $('#builder-custom-name-wrap').show();
        } else {
            $('#builder-custom-name-wrap').hide();
        }
        renderPlatformRows();
    }

    function isPurchaseConceptual() {
        // Reserved for future "Purchase" conceptual. None today.
        return false;
    }

    function getEventNameForPlatform(platformId, conceptual, customName) {
        if (conceptual === '__CUSTOM__') return customName || '';
        var map = CONCEPTUAL_EVENT_MAP[conceptual];
        if (map && map[platformId]) return map[platformId];
        return '';
    }

    function renderPlatformRows() {
        var $container = $('#builder-platforms-rows');
        var conceptual = $('#builder-conceptual-event').val();
        var customName = $('#builder-custom-name').val();
        if (!conceptual) {
            $container.html('<div class="text-muted">Pick a conversion type above to populate platform rows.</div>');
            return;
        }
        var enabled = state.platforms.filter(function (p) { return p.enabled; });
        if (enabled.length === 0) {
            $container.html('<div class="notice notice-warning"><p>No platforms enabled. Enable at least one platform first.</p></div>');
            return;
        }
        var html = '<div class="table-responsive"><table class="table align-middle">';
        html += '<thead><tr><th>Include</th><th>Platform</th><th>Event name</th><th>Client</th><th>Server</th><th>Log response</th></tr></thead><tbody>';
        enabled.forEach(function (p) {
            var defaultName = getEventNameForPlatform(p.id, conceptual, customName);
            var standardEvents = STANDARD_EVENTS_BY_PLATFORM[p.id] || [];
            var isStandard = standardEvents.indexOf(defaultName) !== -1;
            var defaultServer = (parseInt(p.id, 10) === 4) ? 0 : 1; // G-001: Google defaults to server-only for non-Purchase
            html += '<tr class="builder-platform-row" data-platform-id="' + p.id + '">'
                + '<td><div class="form-check form-switch"><input type="checkbox" class="form-check-input platform-include" checked></div></td>'
                + '<td>' + escapeHtml(p.platform_name) + '</td>'
                + '<td>' + buildPlatformEventNameCell(p.id, defaultName, isStandard) + '</td>'
                + '<td><div class="form-check form-switch"><input type="checkbox" class="form-check-input platform-send-client"' + (parseInt(p.id, 10) !== 4 ? ' checked' : '') + '></div></td>'
                + '<td><div class="form-check form-switch"><input type="checkbox" class="form-check-input platform-send-server"' + (defaultServer ? ' checked' : '') + (parseInt(p.id, 10) === 4 ? ' checked' : '') + '></div></td>'
                + '<td><div class="form-check form-switch"><input type="checkbox" class="form-check-input platform-log-response" checked></div></td>'
                + '</tr>';
        });
        html += '</tbody></table></div>';
        if (enabled.some(function(p){ return parseInt(p.id, 10) === 4; }) && !isPurchaseConceptual()) {
            html += '<div class="text-muted small mt-1"><em>Note:</em> Google allows client OR server tracking for this event type, not both. Toggling one off enables the other.</div>';
        }
        $container.html(html);
    }

    function buildPlatformEventNameCell(platformId, value, isStandard) {
        var standardEvents = STANDARD_EVENTS_BY_PLATFORM[platformId] || [];
        var inCustomMode = value !== '' && !isStandard;
        var selectValue = inCustomMode ? '__CUSTOM__' : value;
        var optionsHtml = standardEvents.map(function (n) {
            return '<option value="' + escapeHtml(n) + '"' + (n === selectValue ? ' selected' : '') + '>' + escapeHtml(n) + '</option>';
        }).join('');
        return ''
            + '<select class="form-control form-control-sm platform-event-name-select">'
            +   '<option value="" disabled' + (selectValue === '' ? ' selected' : '') + '>Choose…</option>'
            +   optionsHtml
            +   '<option value="__CUSTOM__"' + (selectValue === '__CUSTOM__' ? ' selected' : '') + '>Custom…</option>'
            + '</select>'
            + '<input type="text" class="form-control form-control-sm mt-1 platform-event-name-custom" placeholder="Custom name" value="' + escapeHtml(inCustomMode ? value : '') + '"' + (inCustomMode ? '' : ' style="display:none"') + '>'
            + '<input type="hidden" class="platform-event-name-value" value="' + escapeHtml(value) + '">';
    }

    function setPlatformEventName($row, value) {
        var platformId = parseInt($row.attr('data-platform-id'), 10);
        var standardEvents = STANDARD_EVENTS_BY_PLATFORM[platformId] || [];
        var isStandard = standardEvents.indexOf(value) !== -1;
        var $select = $row.find('.platform-event-name-select');
        var $custom = $row.find('.platform-event-name-custom');
        var $hidden = $row.find('.platform-event-name-value');
        if (isStandard) {
            $select.val(value);
            $custom.hide().val('');
        } else {
            $select.val('__CUSTOM__');
            $custom.show().val(value);
        }
        $hidden.val(value);
    }

    function refreshPlatformRowDisabled($row) {
        var include = $row.find('.platform-include').is(':checked');
        $row.find('select, input[type=text], .platform-send-client, .platform-send-server, .platform-log-response').prop('disabled', !include);
        $row.toggleClass('text-muted', !include);
    }

    $(document).on('change', '.platform-event-name-select', function () {
        var $sel = $(this);
        var $row = $sel.closest('.builder-platform-row');
        var $custom = $row.find('.platform-event-name-custom');
        var $hidden = $row.find('.platform-event-name-value');
        var v = $sel.val();
        if (v === '__CUSTOM__') {
            $custom.show().focus();
            $hidden.val($custom.val());
        } else {
            $custom.hide();
            $hidden.val(v || '');
        }
    });
    $(document).on('input', '.platform-event-name-custom', function () {
        var $input = $(this);
        $input.closest('.builder-platform-row').find('.platform-event-name-value').val($input.val());
    });

    function collectFormData() {
        var conceptual = $('#builder-conceptual-event').val();
        var customName = $('#builder-custom-name').val();
        var conceptualToSave = (conceptual === '__CUSTOM__') ? (customName || 'Custom') : conceptual;
        var platforms = [];
        $('.builder-platform-row').each(function () {
            var $row = $(this);
            platforms.push({
                platform_id: $row.attr('data-platform-id'),
                include: $row.find('.platform-include').is(':checked') ? 1 : 0,
                event_name: $row.find('.platform-event-name-value').val(),
                send_client: $row.find('.platform-send-client').is(':checked') ? 1 : 0,
                send_server: $row.find('.platform-send-server').is(':checked') ? 1 : 0,
                send_server_log_response: $row.find('.platform-log-response').is(':checked') ? 1 : 0
            });
        });
        return {
            event_trigger: $('#builder-event-trigger').val(),
            trigger_target: $('#builder-trigger-target').val(),
            conceptual_event: conceptualToSave,
            description: $('#builder-description').val(),
            platforms: platforms
        };
    }

    function onBuilderSubmit(e) {
        e.preventDefault();
        var data = collectFormData();
        if (!data.event_trigger || !data.trigger_target || !data.conceptual_event) {
            showFeedback('Please fill in trigger, target, and conversion type.', 'error');
            return;
        }
        var includedPlatforms = data.platforms.filter(function (p) { return p.include; });
        if (includedPlatforms.length === 0) {
            showFeedback('Include at least one platform.', 'error');
            return;
        }
        for (var i = 0; i < includedPlatforms.length; i++) {
            if (!includedPlatforms[i].event_name) {
                showFeedback('Each included platform needs an event name.', 'error');
                return;
            }
        }
        $('#builder-save-btn').prop('disabled', true).text('Saving…');
        var action = state.groupId > 0 ? 'unipixel_conversions_update' : 'unipixel_conversions_create';
        var payload = $.extend({}, data);
        if (state.groupId > 0) payload.group_id = state.groupId;
        postAjax(action, payload).done(function (resp) {
            if (resp && resp.success) {
                showFeedback(state.groupId > 0 ? 'Conversion saved.' : 'Conversion created.', 'success');
                setTimeout(function () { window.location.href = 'admin.php?page=unipixel_conversions'; }, 700);
            } else {
                showFeedback((resp && resp.data && resp.data.message) || 'Save failed.', 'error');
                $('#builder-save-btn').prop('disabled', false).text(state.groupId > 0 ? 'Save changes' : 'Create conversion');
            }
        }).fail(function () {
            showFeedback('Network error during save.', 'error');
            $('#builder-save-btn').prop('disabled', false).text(state.groupId > 0 ? 'Save changes' : 'Create conversion');
        });
    }

    function onBuilderDelete() {
        if (state.groupId <= 0) return;
        if (!window.confirm('Delete this conversion? All linked platform events will also be deleted.')) return;
        postAjax('unipixel_conversions_delete', {group_id: state.groupId}).done(function (resp) {
            if (resp && resp.success) {
                window.location.href = 'admin.php?page=unipixel_conversions';
            } else {
                showFeedback('Delete failed.', 'error');
            }
        });
    }

    // ============================== BOOTSTRAP ==============================
    if ($('.unipixel-conversions-list').length) initList();
    if ($('.unipixel-conversion-builder').length) initBuilder();

})(jQuery);
