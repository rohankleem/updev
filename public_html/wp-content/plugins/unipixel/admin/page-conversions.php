<?php
// File: public_html/wp-content/plugins/unipixel/admin/page-conversions.php
//
// Phase 3 — Centralised conversion builder.
// Routes between the conversions list view and the create/edit builder based on ?action=.
// Renders the shell HTML; data is loaded via AJAX in admin/js/ajax-conversion-groups.js.

if (!defined('ABSPATH')) exit;

function unipixel_conversions_router()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Unauthorized', 'unipixel'));
    }

    $action = isset($_GET['action']) ? sanitize_key($_GET['action']) : 'list';

    switch ($action) {
        case 'builder':
            unipixel_page_conversions_builder();
            break;
        case 'list':
        default:
            unipixel_page_conversions_list();
            break;
    }
}

function unipixel_page_conversions_list()
{
    $builder_url = admin_url('admin.php?page=unipixel_conversions&action=builder');
    $meta_events_url      = admin_url('admin.php?page=unipixel_meta&section=events');
    $google_events_url    = admin_url('admin.php?page=unipixel_google&section=events');
    $tiktok_events_url    = admin_url('admin.php?page=unipixel_tiktok&section=events');
    $pinterest_events_url = admin_url('admin.php?page=unipixel_pinterest&section=events');
    $microsoft_events_url = admin_url('admin.php?page=unipixel_microsoft&section=events');
    ?>
    <div class="wrap UniPixelShell unipixel-conversions-list">
        <div class="d-flex justify-content-between align-items-start">
            <h1 class="mb-3"><i class="fa-solid fa-bullseye"></i> Centralised Event Manager</h1>
            <?php if (function_exists('unipixel_render_feedback_buttons')) unipixel_render_feedback_buttons(); ?>
        </div>

        <p class="text-muted">
            Add a <strong>site event</strong> (form submission, click, page URL match) once here and it fires across every platform you choose.
            Events created here are <strong>grouped</strong>: edit a shared field (trigger, URL pattern) once and it propagates to every linked platform.
            Per-platform overrides (Platform Event Reference, send-mode, log response) stay independent.
            Site events can also be added <strong>per-platform individually</strong> from each platform's Events Setup page.
        </p>

        <p class="text-muted">
            <strong>Looking for eCommerce events?</strong>
            WooCommerce events (AddToCart, InitiateCheckout, Purchase, etc.) are auto-tracked and managed with toggles plus an Apply Recommended Settings button on each platform's Events Setup page:
            <a href="<?php echo esc_url($meta_events_url); ?>">Meta</a>,
            <a href="<?php echo esc_url($google_events_url); ?>">Google</a>,
            <a href="<?php echo esc_url($tiktok_events_url); ?>">TikTok</a>,
            <a href="<?php echo esc_url($pinterest_events_url); ?>">Pinterest</a>,
            <a href="<?php echo esc_url($microsoft_events_url); ?>">Microsoft</a>.
        </p>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <span id="conversions-count" class="text-muted">Loading…</span>
            <a href="<?php echo esc_url($builder_url); ?>" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Create new event
            </a>
        </div>

        <div id="conversions-list-container">
            <div class="text-muted py-4 text-center" id="conversions-loading">Loading events…</div>
        </div>
    </div>
    <?php
}

function unipixel_page_conversions_builder()
{
    $list_url = admin_url('admin.php?page=unipixel_conversions');
    $group_id = isset($_GET['group_id']) ? intval($_GET['group_id']) : 0;
    $is_edit = $group_id > 0;
    ?>
    <div class="wrap UniPixelShell unipixel-conversion-builder" data-group-id="<?php echo esc_attr($group_id); ?>">
        <div class="d-flex justify-content-between align-items-start">
            <h1 class="mb-3">
                <a href="<?php echo esc_url($list_url); ?>" class="text-decoration-none text-muted me-2"><i class="fa-solid fa-arrow-left"></i></a>
                <?php echo $is_edit ? 'Edit Event' : 'New Event'; ?>
            </h1>
            <?php if (function_exists('unipixel_render_feedback_buttons')) unipixel_render_feedback_buttons(); ?>
        </div>

        <div id="builder-loading" class="text-muted py-4 text-center" <?php echo $is_edit ? '' : 'style="display:none"'; ?>>Loading event…</div>

        <form id="unipixel-conversion-builder-form" class="unipixel-builder-form" <?php echo $is_edit ? 'style="display:none"' : ''; ?>>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">1. Trigger</h5>
                    <p class="card-text text-muted small">When does this event fire? Pick what action triggers it, then specify the target (the element or URL it watches).</p>

                    <div class="mb-3">
                        <label class="form-label">Trigger type</label>
                        <select class="form-control" id="builder-event-trigger" required>
                            <option value="">Choose trigger…</option>
                            <option value="click">On Element Clicked</option>
                            <option value="shown">On Element Shown</option>
                            <option value="url">On Page URL Match</option>
                        </select>
                    </div>

                    <div class="mb-3" id="builder-trigger-target-wrap" style="display:none">
                        <label class="form-label" id="builder-trigger-target-label">Acts On</label>

                        <div id="builder-url-modes" style="display:none">
                            <div class="form-check">
                                <input class="form-check-input url-mode-radio" type="radio" name="builder-url-mode" id="url-mode-page" value="page" checked>
                                <label class="form-check-label" for="url-mode-page">Pick a specific page from your site</label>
                            </div>
                            <select class="form-control mt-1 mb-2" id="builder-page-picker">
                                <option value="">Pick a page…</option>
                            </select>

                            <div class="form-check">
                                <input class="form-check-input url-mode-radio" type="radio" name="builder-url-mode" id="url-mode-any" value="any">
                                <label class="form-check-label" for="url-mode-any">Match any page on your site (fires on every URL)</label>
                            </div>

                            <div class="form-check mt-2">
                                <input class="form-check-input url-mode-radio" type="radio" name="builder-url-mode" id="url-mode-custom" value="custom">
                                <label class="form-check-label" for="url-mode-custom">Write a custom URL pattern</label>
                            </div>
                        </div>

                        <input type="text" class="form-control mt-2" id="builder-trigger-target" required>
                        <small class="text-muted" id="builder-trigger-target-help"></small>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">2. Platform Event Reference</h5>
                    <p class="card-text text-muted small">The name each platform receives. Pick a <strong>Standard</strong> event type to get full reporting in each platform's Events Manager, or a <strong>Bespoke</strong> name to track something the platforms don't recognise as a known type. Examples appear next to each platform below.</p>

                    <div class="mb-3">
                        <label class="form-label">Event type</label>
                        <select class="form-control" id="builder-conceptual-event" required>
                            <option value="">Choose an event type…</option>
                            <option value="Lead">Lead (generic lead generation)</option>
                            <option value="ContactFormSubmitted">Contact Form Submitted</option>
                            <option value="NewsletterSignup">Newsletter Signup</option>
                            <option value="Registration">Registration / Sign Up</option>
                            <option value="Search">Search</option>
                            <option value="ViewContent">View Content / Page</option>
                            <option value="__CUSTOM__">Bespoke (your own name)…</option>
                        </select>
                    </div>

                    <div class="mb-3" id="builder-custom-name-wrap" style="display:none">
                        <label class="form-label">Bespoke event name</label>
                        <input type="text" class="form-control" id="builder-custom-name" placeholder="MyBespokeEvent">
                        <small class="text-muted">This name is sent to every platform unless you override it on a row below. The plugin sends it as-is, no case conversion.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description (optional)</label>
                        <input type="text" class="form-control" id="builder-description" placeholder="Internal note for your team">
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">3. Platforms</h5>
                    <p class="card-text text-muted small">Each enabled platform will send this event. Untick a platform to skip it. The Platform Event Reference can be overridden per platform.</p>

                    <div id="builder-platforms-rows" class="text-muted">Pick an event type above to populate platform rows.</div>

                    <div class="text-muted small mt-3" id="builder-platforms-disabled-hint" style="display:none">
                        <em>Don't see a platform?</em> Enable it in:
                        <span id="builder-disabled-links"></span>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="<?php echo esc_url($list_url); ?>" class="btn btn-outline-secondary">Cancel</a>
                <div>
                    <button type="button" id="builder-delete-btn" class="btn btn-outline-danger me-2" <?php echo $is_edit ? '' : 'style="display:none"'; ?>>
                        <i class="fa-solid fa-trash"></i> Delete event
                    </button>
                    <button type="submit" class="btn btn-primary" id="builder-save-btn">
                        <?php echo $is_edit ? 'Save changes' : 'Create event'; ?>
                    </button>
                </div>
            </div>
        </form>

        <div id="builder-feedback" class="mt-3"></div>
    </div>
    <?php
}
