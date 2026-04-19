(function ($) {

    var enableLogging_Admin = false;

    function log_Admin(message, data) {
        if (enableLogging_Admin) {
            console.log(message, data);
        }
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

            return `
            <tr data-id="${event.id}">
                <td><input type="text" class="form-control" name="element_ref[]" value="${event.element_ref}" required></td>
                <td>
                    <select class="form-control" name="event_trigger[]" required>
                        <option value="click" ${event.event_trigger === 'click' ? 'selected' : ''}>On Element Clicked</option>
                        <option value="shown" ${event.event_trigger === 'shown' ? 'selected' : ''}>On Element Shown</option>
                    </select>
                </td>
                <td><input type="text" class="form-control" name="event_name[]" value="${event.event_name}" required></td>
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

            var newRow = `
            <tr>
                <td><input type="text" class="form-control" name="element_ref[]" required></td>
                <td>
                    <select class="form-control" name="event_trigger[]" required>
                        <option value="click">On Element Clicked</option>
                        <option value="shown">On Element Shown</option>
                    </select>
                </td>
                <td><input type="text" class="form-control" name="event_name[]" required></td>
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
