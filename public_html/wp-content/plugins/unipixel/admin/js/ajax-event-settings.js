(function ($) {

    var enableLogging_Admin = false;

    function log_Admin(message, data) {
        if (enableLogging_Admin) {
            console.log(message, data);
        }
    }

    function elementRefUiForTrigger(trigger) {
        if (trigger === 'url') {
            return {
                placeholder: '/thank-you* or *',
                help: 'URL pattern. Use * as wildcard. Examples: /thank-you/, /thank-you*, *thank*, *'
            };
        }
        return {
            placeholder: '#contact-form or .cta-button',
            help: 'CSS selector for the element to track.'
        };
    }

    // Per-platform standard event names. Picking a standard name means the platform's
    // Events Manager will recognise the event and apply standard reporting/optimisation.
    // Source: project doc Phase 2 spec.
    var STANDARD_EVENTS_BY_PLATFORM = {
        1: ['AddPaymentInfo', 'AddToCart', 'AddToWishlist', 'CompleteRegistration', 'Contact', 'CustomizeProduct', 'Donate', 'FindLocation', 'InitiateCheckout', 'Lead', 'Purchase', 'Schedule', 'Search', 'StartTrial', 'SubmitApplication', 'Subscribe', 'ViewContent'], // Meta
        2: ['AddToCart', 'Checkout', 'Lead', 'PageVisit', 'Search', 'Signup', 'ViewCategory', 'WatchVideo'], // Pinterest
        3: ['AddPaymentInfo', 'AddToCart', 'AddToWishlist', 'ClickButton', 'CompletePayment', 'CompleteRegistration', 'Contact', 'Download', 'InitiateCheckout', 'PlaceAnOrder', 'Search', 'SubmitForm', 'Subscribe', 'ViewContent'], // TikTok
        4: ['generate_lead', 'sign_up', 'login', 'search', 'select_content', 'share', 'begin_checkout', 'add_to_cart', 'view_item', 'purchase', 'view_promotion', 'select_promotion'], // Google GA4
        5: ['add_to_cart', 'begin_checkout', 'purchase', 'subscribe', 'sign_up', 'lead', 'contact', 'search']  // Microsoft (UET — recommended set, not strictly enforced)
    };

    var PLATFORM_NAMES = { 1: 'Meta', 2: 'Pinterest', 3: 'TikTok', 4: 'Google', 5: 'Microsoft' };

    var CUSTOM_SENTINEL = '__CUSTOM__';

    function escapeHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    // Build the event_name cell. Renders a select with standard events + Custom..., a
    // conditionally-visible text input for the custom value, and a hidden field that
    // carries the actual submitted value (kept in sync by the change handlers below).
    function eventNameCellHtml(value, platformId) {
        var standardEvents = STANDARD_EVENTS_BY_PLATFORM[platformId] || [];
        var platformName = PLATFORM_NAMES[platformId] || 'platform';
        var safeValue = value || '';
        var isStandard = standardEvents.indexOf(safeValue) !== -1;
        var inCustomMode = safeValue !== '' && !isStandard;
        var selectValue = inCustomMode ? CUSTOM_SENTINEL : safeValue;

        var optionsHtml = standardEvents.map(function (name) {
            return '<option value="' + escapeHtml(name) + '"' + (name === selectValue ? ' selected' : '') + '>' + escapeHtml(name) + '</option>';
        }).join('');

        return ''
            + '<select class="form-control event-name-select" required>'
            +   '<option value="" disabled' + (selectValue === '' ? ' selected' : '') + '>Choose event…</option>'
            +   optionsHtml
            +   '<option value="' + CUSTOM_SENTINEL + '"' + (selectValue === CUSTOM_SENTINEL ? ' selected' : '') + '>Custom…</option>'
            + '</select>'
            + '<input type="text" class="form-control event-name-custom mt-1" placeholder="Enter custom event name" value="' + escapeHtml(inCustomMode ? safeValue : '') + '"' + (inCustomMode ? '' : ' style="display:none"') + '>'
            + '<input type="hidden" name="event_name[]" class="event-name-value" value="' + escapeHtml(safeValue) + '">'
            + '<small class="text-muted d-block mt-1">Standard events get full reporting in ' + escapeHtml(platformName) + '’s Events Manager. Pick “Custom…” for any other name.</small>';
    }

    var UniPixelEventSettings = {
        init: function () {
            this.bindEvents();
            this.loadEvents(); // loads Custom Events into #event-settings-table
        },

        bindEvents: function () {
            log_Admin('bindEvents...');
            // Unified submit (single form)
            $('#unipixel-events-all-form').on('submit', this.handleSubmitAll.bind(this));

            // Keep "Add Event" & row delete
            $('#add-event').on('click', this.addEventRow.bind(this));
            $(document).on('click', '.delete-event', this.handleDelete.bind(this));

            // Trigger select change → update element_ref placeholder + helper text on the same row.
            $(document).on('change', '.event-trigger-select', function () {
                var $select = $(this);
                var ui = elementRefUiForTrigger($select.val());
                var $row = $select.closest('tr');
                $row.find('.element-ref-input').attr('placeholder', ui.placeholder);
                $row.find('.element-ref-help').text(ui.help);
            });

            // Event name select change → toggle custom input visibility and sync hidden value.
            $(document).on('change', '.event-name-select', function () {
                var $select = $(this);
                var $cell = $select.closest('td');
                var $custom = $cell.find('.event-name-custom');
                var $hidden = $cell.find('.event-name-value');
                var v = $select.val();
                if (v === CUSTOM_SENTINEL) {
                    $custom.show().focus();
                    $hidden.val($custom.val());
                } else {
                    $custom.hide();
                    $hidden.val(v || '');
                }
            });

            // Custom event name input → sync hidden value.
            $(document).on('input', '.event-name-custom', function () {
                var $input = $(this);
                $input.closest('td').find('.event-name-value').val($input.val());
            });

            // NOTE: remove legacy bindings:
            // - $('#btnUniPixelUpdatePageViewSettings')
            // - $('#event-settings-form').on('submit', ...)
        },

        // ------- Load & render Custom Events -------
        loadEvents: function () {
            this.toggleLoading(true);
            log_Admin('Loading events...');

            var platform_id = $('#platform_id').val();
            var formData = {
                'action': 'unipixel_get_events',
                'nonce': unipixel_ajax_obj.nonce,
                'platform_id': platform_id
            };

            $.ajax({
                type: 'POST',
                url: unipixel_ajax_obj.ajaxurl,
                data: formData
            })
                .done(function (response) {
                    log_Admin('Events loaded:', response);
                    if (response && response.success) {
                        UniPixelEventSettings.renderEvents(response.data.events);
                    } else {
                        UniPixelEventSettings.showFeedbackMessage(
                            (response && response.data && response.data.message) || 'Failed to load events.',
                            'danger'
                        );
                    }
                })
                .fail(function (xhr, textStatus, errorThrown) {
                    log_Admin('AJAX Error:', textStatus, errorThrown);
                    UniPixelEventSettings.showFeedbackMessage('Ajax request failed: ' + textStatus + ' ' + errorThrown, 'danger');
                })
                .always(function () {
                    log_Admin('Loading complete');
                    UniPixelEventSettings.toggleLoading(false);
                });
        },

        renderEvents: function (events) {
            var $tableBody = $('#event-settings-table tbody');
            $tableBody.empty();
            (events || []).forEach(function (event) {
                $tableBody.append(UniPixelEventSettings.getEventRowHtml(event));
            });

        },



        getEventRowHtml: function (event) {
            const platformId = Number($('#platform_id').val());
            const defaultClient = 1;
            const defaultServer = (platformId === 4) ? 0 : 1; // google = 0

            const asChecked = v => (v === 1 || v === '1' || v === true || v === 'true') ? 'checked' : '';

            const clientChecked = asChecked(event.send_client == null ? defaultClient : event.send_client);
            const serverChecked = asChecked(event.send_server == null ? defaultServer : event.send_server);
            const logResponseChecked = asChecked(event.send_server_log_response);

            const trigger = event.event_trigger || 'click';
            const refUi = elementRefUiForTrigger(trigger);

            return `
            <tr data-id="${event.id}">
                <td>
                    <input type="text" class="form-control element-ref-input" name="element_ref[]" value="${event.element_ref || ''}" placeholder="${refUi.placeholder}" required>
                    <small class="element-ref-help text-muted">${refUi.help}</small>
                </td>
                <td>
                    <select class="form-control event-trigger-select" name="event_trigger[]" required>
                        <option value="click" ${trigger === 'click' ? 'selected' : ''}>On Element Clicked</option>
                        <option value="shown" ${trigger === 'shown' ? 'selected' : ''}>On Element Shown</option>
                        <option value="url"   ${trigger === 'url'   ? 'selected' : ''}>On Page URL Match</option>
                    </select>
                </td>
                <td>${eventNameCellHtml(event.event_name, platformId)}</td>
                <td><input type="text" class="form-control" name="event_description[]" value="${event.event_description ?? ''}"></td>

                <td>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="send_client[]" ${clientChecked}>
                    </div>
                </td>
                <td>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="send_server[]" ${serverChecked}>
                    </div>
                </td>
                <td>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="send_server_log_response[]" ${logResponseChecked}>
                    </div>
                </td>
                <td>
                    <i role="button" class="fa-solid fa-trash delete-event text-danger"></i>
                    <input type="hidden" name="id[]" value="${event.id}">
                </td>
            </tr>
            `;
        },

        addEventRow: function () {
            log_Admin('Adding new event row');

            const platformId = Number($('#platform_id').val());
            const clientDefault = 1;
            const serverDefault = (platformId === 4) ? 0 : 1;

            const refUi = elementRefUiForTrigger('click');

            var newRow = `
            <tr>
                <td>
                    <input type="text" class="form-control element-ref-input" name="element_ref[]" placeholder="${refUi.placeholder}" required>
                    <small class="element-ref-help text-muted">${refUi.help}</small>
                </td>
                <td>
                    <select class="form-control event-trigger-select" name="event_trigger[]" required>
                        <option value="click">On Element Clicked</option>
                        <option value="shown">On Element Shown</option>
                        <option value="url">On Page URL Match</option>
                    </select>
                </td>
                <td>${eventNameCellHtml('', platformId)}</td>
                <td><input type="text" class="form-control" name="event_description[]"></td>

                <td>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="send_client[]" ${clientDefault ? 'checked' : ''}>
                    </div>
                </td>
                <td>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="send_server[]" ${serverDefault ? 'checked' : ''}>
                    </div>
                </td>
                <td>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="send_server_log_response[]">
                    </div>
                </td>

                <td>
                    <i role="button" class="fa-solid fa-trash delete-event text-danger"></i>
                    <input type="hidden" name="id[]" value="">
                </td>
            </tr>
            `;
            $('#event-settings-table tbody').append(newRow);
        },

        // ------- Unified "Update All" -------
        handleSubmitAll: function (e) {

            log_Admin('handleSubmitAll...');

            e.preventDefault();
            this.toggleLoading(true);

            const steps = [];

            steps.push(
                this.updatePageView.bind(this),
                this.updateWooEventsBatch.bind(this),
                this.updateCustomEvents.bind(this)
            );


            // Run sequentially for clearer error attribution
            const runSequential = async () => {
                for (const step of steps) {
                    await step();
                }
            };

            runSequential()
                .then(() => {
                    this.showFeedbackMessage('All settings updated successfully.', 'success');
                })
                .catch((err) => {
                    const msg = (err && err.message) ? err.message : 'An error occurred while saving.';
                    this.showFeedbackMessage(msg, 'danger');
                })
                .finally(() => {
                    this.toggleLoading(false);
                });
        },

        // ------- PageView update (returns Promise) -------
        updatePageView: function () {
            const platformId = $('#platform_id').val();
            if (!platformId) {
                return Promise.reject(new Error('Missing platform id.'));
            }
            const data = {
                action: 'unipixel_update_platform_pageview',
                nonce: unipixel_ajax_obj.nonce,
                platform_id: platformId,
                pageview_send_clientside: $('#pageview_send_clientside').is(':checked') ? 1 : 0,
                pageview_send_serverside: $('#pageview_send_serverside').is(':checked') ? 1 : 0,
                send_server_log_response: $('#send_server_log_response').is(':checked') ? 1 : 0
            };
            return new Promise((resolve, reject) => {
                $.ajax({
                    type: 'POST',
                    url: unipixel_ajax_obj.ajaxurl,
                    data: data
                })
                    .done((res) => {
                        if (res && res.success) {
                            resolve(res);
                        } else {
                            reject(new Error((res && res.data && res.data.message) || 'PageView update failed.'));
                        }
                    })
                    .fail((xhr, status, err) => {
                        reject(new Error('PageView ajax failed: ' + status + ' ' + err));
                    });
            });
        },

        // ------- Woo batch update (returns Promise) -------
        updateWooEventsBatch: function () {
            const rows = $('#woo-events-table tbody tr');
            if (!rows.length) {
                // Nothing to update is not an error
                return Promise.resolve();
            }

            const eventsData = [];
            rows.each(function () {
                const $r = $(this);
                eventsData.push({
                    id: $r.data('id'),
                    event_platform_ref: $r.data('event-platform-ref'),
                    send_client: $r.find('input[name^="woo_send_client"]').is(':checked') ? 1 : 0,
                    send_server: $r.find('input[name^="woo_send_server"]').is(':checked') ? 1 : 0,
                    logresponse: $r.find('input[name^="woo_event_logresponse"]').is(':checked') ? 1 : 0
                });
            });

            return new Promise((resolve, reject) => {
                $.ajax({
                    type: 'POST',
                    url: unipixel_ajax_obj.ajaxurl,
                    data: {
                        action: 'unipixel_update_woo_events_batch',
                        nonce: unipixel_ajax_obj.nonce,
                        eventsData: eventsData
                    }
                })
                    .done((res) => {
                        if (res && res.success) {
                            resolve(res);
                        } else {
                            reject(new Error((res && res.data && res.data.message) || 'WooCommerce update failed.'));
                        }
                    })
                    .fail((xhr, status, err) => {
                        reject(new Error('WooCommerce ajax failed: ' + status + ' ' + err));
                    });
            });
        },

        // ------- Custom events update (returns Promise) -------
        updateCustomEvents: function () {
            const self = this;
            const rows = $('#event-settings-table tbody tr');
            if (!rows.length) {
                // Nothing to update is not an error
                return Promise.resolve();
            }

            const platform_id = $('#platform_id').val();
            const requests = [];

            rows.each(function () {
                const $row = $(this);
                const id = $row.find('input[name="id[]"]').val();
                const element_ref = $row.find('input[name="element_ref[]"]').val();
                const event_trigger = $row.find('select[name="event_trigger[]"]').val();
                const event_name = $row.find('input[name="event_name[]"]').val();
                const event_description = $row.find('input[name="event_description[]"]').val();
                const send_client = $row.find('input[name="send_client[]"]').is(':checked') ? 1 : 0;
                const send_server = $row.find('input[name="send_server[]"]').is(':checked') ? 1 : 0;
                const send_server_log_response = $row.find('input[name="send_server_log_response[]"]').is(':checked') ? 1 : 0;

                const action = id ? 'unipixel_update_event' : 'unipixel_add_event';

                const payload = {
                    action,
                    nonce: unipixel_ajax_obj.nonce,
                    id,
                    platform_id,
                    element_ref,
                    event_trigger,
                    event_name,
                    event_description,
                    send_client,
                    send_server,
                    send_server_log_response
                };

                const req = new Promise((resolve, reject) => {
                    $.ajax({
                        type: 'POST',
                        url: unipixel_ajax_obj.ajaxurl,
                        data: payload
                    })
                        .done(function (response) {
                            log_Admin('Custom event save:', response);
                            if (response && response.success) {
                                // If this was an "add", set the returned id
                                if (!id && response.data && typeof response.data.id !== 'undefined') {
                                    $row.find('input[name="id[]"]').val(response.data.id);
                                    $row.attr('data-id', response.data.id);
                                }
                                resolve(response);
                            } else {
                                reject(new Error((response && response.data && response.data.message) || 'Failed to save custom event.'));
                            }
                        })
                        .fail(function (xhr, textStatus, errorThrown) {
                            reject(new Error('Ajax request failed: ' + textStatus + ' ' + errorThrown));
                        });
                });

                requests.push(req);
            });

            // Run all rows in parallel
            return Promise.all(requests);
        },

        // ------- Delete (unchanged UI, improved UX feedback) -------
        handleDelete: function (e) {
            e.preventDefault();
            const self = this;
            const $icon = $(e.currentTarget);
            const $row = $icon.closest('tr');
            const id = $row.find('input[name="id[]"]').val();

            if (!window.confirm('Delete this item?')) return;

            // Unsaved row: remove locally
            if (!id) {
                self._setRowDeleting($row, $icon);
                $row.fadeOut(200, function () {
                    $(this).remove();
                    self.showFeedbackMessage('Item removed.', 'success');
                });
                return;
            }

            // Saved row: call AJAX
            self._setRowDeleting($row, $icon);

            const formData = {
                action: 'unipixel_delete_event',
                nonce: unipixel_ajax_obj.nonce,
                id: id
            };

            $.ajax({
                type: 'POST',
                url: unipixel_ajax_obj.ajaxurl,
                data: formData
            })
                .done(function (response) {
                    if (response && response.success) {
                        $row.fadeOut(200, function () {
                            $(this).remove();
                            self.showFeedbackMessage((response.data && response.data.message) || 'Item deleted.', 'success');
                        });
                    } else {
                        self._clearRowDeleting($row, $icon);
                        self.showFeedbackMessage((response.data && response.data.message) || 'Failed to delete item.', 'danger');
                    }
                })
                .fail(function (xhr, textStatus, errorThrown) {
                    self._clearRowDeleting($row, $icon);
                    self.showFeedbackMessage('Ajax error: ' + textStatus + ' ' + errorThrown, 'danger');
                });
        },

        // ------- UI helpers -------
        toggleLoading: function (show) {
            log_Admin('Toggle loading:', show);

            var $generalMask = $('.UniPixelShell');
            var $spinner = $('.UniPixelSpinner');

            if (show) {
                $generalMask.addClass('loading-mask');
                $spinner.removeClass('d-none');
            } else {
                $generalMask.removeClass('loading-mask');
                $spinner.addClass('d-none');
            }
        },

        showFeedbackMessage: function (message, type) {
            var $messageContainer = $('#event-settings-feedback-message');
            $messageContainer
                .removeClass('alert-success alert-danger')
                .addClass('alert-' + type)
                .text(message)
                .show();
        },

        _setRowDeleting: function ($row, $icon) {
            $icon.data('orig-class', $icon.attr('class'));
            $icon
                .removeClass('fa-trash text-danger')
                .addClass('fa-spinner fa-spin text-muted')
                .attr('aria-busy', 'true')
                .css('pointer-events', 'none');
            $row.addClass('opacity-50');
        },

        _clearRowDeleting: function ($row, $icon) {
            const orig = $icon.data('orig-class') || 'fa-solid fa-trash delete-event text-danger';
            $icon.attr('class', orig)
                .removeAttr('aria-busy')
                .css('pointer-events', '');
            $row.removeClass('opacity-50');
        }
    };

    $(document).ready(function () {
        UniPixelEventSettings.init();
    });
})(jQuery);
