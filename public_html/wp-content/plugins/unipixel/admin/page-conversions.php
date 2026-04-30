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
    ?>
    <div class="wrap UniPixelShell unipixel-conversions-list">
        <div class="d-flex justify-content-between align-items-start">
            <h1 class="mb-3"><i class="fa-solid fa-bullseye"></i> Centralised Event Manager</h1>
            <?php if (function_exists('unipixel_render_feedback_buttons')) unipixel_render_feedback_buttons(); ?>
        </div>

        <p class="text-muted">
            "Events" and "conversions" mean the same thing here. Pick whichever term you're used to.
            Custom events can be added two ways: <strong>per-platform individually</strong> in each platform's Events Setup,
            or <strong>centrally from here</strong>. Events created here are <strong>grouped</strong> across the platforms
            you choose. Edit a shared field (trigger, URL pattern) once and it propagates to every linked platform.
            Per-platform overrides (event name, send-mode, log response) stay independent.
        </p>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <span id="conversions-count" class="text-muted">Loading…</span>
            <a href="<?php echo esc_url($builder_url); ?>" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Create new conversion
            </a>
        </div>

        <div id="conversions-list-container">
            <div class="text-muted py-4 text-center" id="conversions-loading">Loading conversions…</div>
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
                <?php echo $is_edit ? 'Edit Conversion' : 'New Conversion'; ?>
            </h1>
            <?php if (function_exists('unipixel_render_feedback_buttons')) unipixel_render_feedback_buttons(); ?>
        </div>

        <div id="builder-loading" class="text-muted py-4 text-center" <?php echo $is_edit ? '' : 'style="display:none"'; ?>>Loading conversion…</div>

        <form id="unipixel-conversion-builder-form" class="unipixel-builder-form" <?php echo $is_edit ? 'style="display:none"' : ''; ?>>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">1. Trigger</h5>
                    <p class="card-text text-muted small">When does this conversion fire?</p>

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
                        <label class="form-label" id="builder-trigger-target-label">Target</label>

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
                    <h5 class="card-title">2. Conversion</h5>
                    <p class="card-text text-muted small">What is this conversion called? Pick a standard name to get full reporting in each platform's Events Manager.</p>

                    <div class="mb-3">
                        <label class="form-label">Conceptual event</label>
                        <select class="form-control" id="builder-conceptual-event" required>
                            <option value="">Choose a conversion type…</option>
                            <option value="Lead">Lead — generic lead generation</option>
                            <option value="ContactFormSubmitted">Contact Form Submitted</option>
                            <option value="NewsletterSignup">Newsletter Signup</option>
                            <option value="Registration">Registration / Sign Up</option>
                            <option value="Search">Search</option>
                            <option value="ViewContent">View Content / Page</option>
                            <option value="__CUSTOM__">Custom…</option>
                        </select>
                    </div>

                    <div class="mb-3" id="builder-custom-name-wrap" style="display:none">
                        <label class="form-label">Custom event name</label>
                        <input type="text" class="form-control" id="builder-custom-name" placeholder="myConversion">
                        <small class="text-muted">This name is used for every platform unless you override it below.</small>
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
                    <p class="card-text text-muted small">Each enabled platform will send this conversion. Untick a platform to skip it.</p>

                    <div id="builder-platforms-rows" class="text-muted">Pick a conversion type above to populate platform rows.</div>

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
                        <i class="fa-solid fa-trash"></i> Delete conversion
                    </button>
                    <button type="submit" class="btn btn-primary" id="builder-save-btn">
                        <?php echo $is_edit ? 'Save changes' : 'Create conversion'; ?>
                    </button>
                </div>
            </div>
        </form>

        <div id="builder-feedback" class="mt-3"></div>
    </div>
    <?php
}
