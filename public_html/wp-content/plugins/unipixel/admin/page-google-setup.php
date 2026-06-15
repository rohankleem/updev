<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Google → Tag/Connection Setup page (no Events UI here)
 */
function unipixel_page_google_setup() {
    global $wpdb;

    $platform_table    = $wpdb->prefix . 'unipixel_platform_settings';
    $platformId        = 4;
    $platformName      = 'Google';
    $pixelNameFriendly = 'Analytics Tracking Code';

    // Fetch platform row
    $query = $wpdb->prepare(
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        "SELECT * FROM %i WHERE id = %d",
        $platform_table,
        $platformId
    );
    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $platform = $wpdb->get_row($query, ARRAY_A);

    // Extract / defaults
    if ($platform) {
        $platform_id      = (int) ($platform['id'] ?? 0);
        $pixel_id         = isset($platform['pixel_id']) ? $platform['pixel_id'] : '';
        $access_token     = isset($platform['access_token']) ? $platform['access_token'] : '';
        $platform_enabled = isset($platform['platform_enabled']) ? (int) $platform['platform_enabled'] : 0;
        $additional_id    = isset($platform['additional_id']) ? $platform['additional_id'] : '';
        $pixel_setting    = isset($platform['pixel_setting']) ? $platform['pixel_setting'] : 'include';
        $serverside_global_enabled = isset($platform['serverside_global_enabled']) ? (int) $platform['serverside_global_enabled'] : 0;
    } else {
        $platform_id      = 0;
        $pixel_id         = '';
        $access_token     = '';
        $platform_enabled = 1;
        $additional_id    = '';
        $pixel_setting    = 'include';
        $serverside_global_enabled = 0;
    }

    // Allowlist for inline help icon HTML
    $icon_allow = unipixel_get_popover_allowlist();

    // Two-section layout: client-side vs server-side.
    $client_side_active = (!empty($pixel_id) && $platform_enabled === 1);
    $google_conn_state  = unipixel_get_platform_connection_state($platformId);
    ?>
    <div class="UniPixelShell position-relative">

        <div class="UniPixelSpinner d-none">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden"><?php echo esc_html__('Loading…', 'unipixel'); ?></span>
            </div>
        </div>

        <?php unipixel_render_platform_header_nav('google', 'setup'); ?>

        <h1 class="mb-0">Tag <?php echo esc_html__('Setup', 'unipixel'); ?></h1>
        <p><small><?php echo esc_html__('Configure your connection and core settings for', 'unipixel'); ?> <?php echo esc_html($platformName); ?>.</small></p>

        <!-- Feedback message container (used by ajax-platform-settings.js) -->
        <div id="platform-settings-feedback-message" class="alert" role="alert" style="display:none;"></div>

        <!-- Platform (Tag/Connection) form -->
        <form id="platform-settings-form" class="form-horizontal" autocomplete="off">
            <input type="hidden" id="platform_id" name="platform_id" value="<?php echo esc_attr((string) $platform_id); ?>">

            <?php /* Anti-autofill honeypot: see page-meta-setup.php for rationale. */ ?>
            <div class="unipixel-autofill-honeypot" aria-hidden="true" style="position:absolute;height:0;width:0;overflow:hidden;opacity:0;">
                <input type="text" name="__autofill_decoy_user" autocomplete="username" tabindex="-1">
                <input type="password" name="__autofill_decoy_pass" autocomplete="new-password" tabindex="-1">
            </div>

            <div class="mb-3 row">
                <div class="col-12 col-sm-3">
                    <label class="form-check-label" for="platform_enabled">
                        <?php echo esc_html__('Send tracking to Google?', 'unipixel'); ?>
                        <?php echo wp_kses(unipixel_get_help_icon('Google_Enabled'), $icon_allow); ?>
                    </label>
                </div>
                <div class="col-12 col-sm-9">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="platform_enabled" name="platform_enabled" value="1" <?php checked($platform_enabled, 1); ?>>
                    </div>
                </div>
            </div>

            <div id="platform-fields">

                <!-- ==================================================================
                     Client-side tracking section
                     ================================================================== -->
                <div class="unipixel-tracking-section bg-light p-3 mb-3 rounded border">
                    <div class="d-flex align-items-center mb-2">
                        <strong><?php echo esc_html__('Client-side tracking', 'unipixel'); ?></strong>
                        <span class="badge unipixel-client-side-pill <?php echo $client_side_active ? 'bg-success' : 'bg-secondary'; ?> ms-2"
                              data-label-active="<?php echo esc_attr__('Active', 'unipixel'); ?>"
                              data-label-off="<?php echo esc_attr__('Off', 'unipixel'); ?>"><?php echo $client_side_active ? esc_html__('Active', 'unipixel') : esc_html__('Off', 'unipixel'); ?></span>
                    </div>
                    <p class="small text-muted mb-3"><?php echo esc_html__('The fastest way to start tracking. Add your GA4 Measurement ID and events fire via gtag.js in the browser.', 'unipixel'); ?></p>

                    <?php if (empty($pixel_id)) : ?>
                        <div class="alert alert-info py-2 small mb-3" role="status">
                            <strong><?php echo esc_html__('Start simple.', 'unipixel'); ?></strong>
                            <?php echo esc_html__('Add your Measurement ID below to start tracking. Find it in GA4 Admin under Data Streams.', 'unipixel'); ?>
                            <a href="https://analytics.google.com/" target="_blank" rel="noopener noreferrer" class="ms-1">
                                <?php echo esc_html__('Open Google Analytics', 'unipixel'); ?> <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                            <div class="mt-1 small text-muted"><?php echo esc_html__('No GA4 property yet? Google Analytics walks you through creating one when you arrive.', 'unipixel'); ?></div>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3 row">
                        <label class="col-sm-3 col-form-label"><?php echo esc_html__('Pixel Setting:', 'unipixel'); ?></label>
                        <div class="col-sm-9">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="pixel_setting" id="pixel_setting_include" value="include" <?php checked($pixel_setting, 'include'); ?>>
                                <label class="form-check-label" for="pixel_setting_include">
                                    <?php echo esc_html__('Include gtag.js script for me', 'unipixel'); ?>
                                    <?php echo wp_kses(unipixel_get_help_icon('Google_Include'), $icon_allow); ?>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="pixel_setting" id="pixel_setting_already_included" value="already_included" <?php checked($pixel_setting, 'already_included'); ?>>
                                <label class="form-check-label" for="pixel_setting_already_included">
                                    <?php echo esc_html__('gtag.js script is already on my site', 'unipixel'); ?>
                                    <?php echo wp_kses(unipixel_get_help_icon('Google_Already'), $icon_allow); ?>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="pixel_setting" id="pixel_setting_gtm" value="gtm" <?php checked($pixel_setting, 'gtm'); ?>>
                                <label class="form-check-label" for="pixel_setting_gtm">
                                    <?php echo esc_html__('gtag.js is loaded via Google Tag Manager', 'unipixel'); ?>
                                    <?php echo wp_kses(unipixel_get_help_icon('Google_Gtm'), $icon_allow); ?>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="pixel_id" class="col-sm-3 col-form-label">
                            <?php echo esc_html__('Gtag Measurement ID:', 'unipixel'); ?>
                            <?php echo wp_kses(unipixel_get_help_icon('Google_MeasurementId'), $icon_allow); ?>
                        </label>
                        <div class="col-sm-9">
                            <input type="text" id="pixel_id" name="pixel_id" class="form-control" value="<?php echo esc_attr($pixel_id); ?>" autocomplete="off" required>
                        </div>
                    </div>

                    <div class="mb-0 row" id="gtm_container_id_row">
                        <label for="additional_id" class="col-sm-3 col-form-label">
                            <?php echo esc_html__('GTM Container ID:', 'unipixel'); ?>
                            <?php echo wp_kses(unipixel_get_help_icon('Google_ContainerId'), $icon_allow); ?>
                        </label>
                        <div class="col-sm-9">
                            <input type="text" id="additional_id" name="additional_id" class="form-control" value="<?php echo esc_attr($additional_id); ?>" autocomplete="off">
                        </div>
                    </div>
                </div>

                <!-- ==================================================================
                     Server-side tracking section
                     ================================================================== -->
                <div id="serverside-well" class="unipixel-tracking-section bg-light p-3 mb-3 rounded border">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fa-solid fa-bolt-lightning me-2"></i>
                        <strong><?php echo esc_html__('Server-side tracking', 'unipixel'); ?></strong>
                        <span class="text-muted small ms-2"><?php echo esc_html__('(recommended)', 'unipixel'); ?></span>
                    </div>

                    <?php if ($google_conn_state['state'] === 'not_started') : ?>
                        <div class="alert alert-secondary d-flex align-items-center mb-3" role="status">
                            <span class="badge bg-secondary me-2">&bull;</span>
                            <div class="flex-grow-1">
                                <strong><?php echo esc_html__('Server-side tracking not set up.', 'unipixel'); ?></strong>
                                <small class="d-block"><?php echo esc_html__('Enable Server-Side Tracking and add an API Secret below, or use the guided walkthrough.', 'unipixel'); ?></small>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm ms-3" data-bs-toggle="modal" data-bs-target="#google-setup-wizard-modal">
                                <?php echo esc_html__('Start server-side setup', 'unipixel'); ?>
                            </button>
                        </div>
                    <?php elseif ($google_conn_state['state'] === 'pasted_unverified') : ?>
                        <div class="alert alert-warning d-flex align-items-center mb-3" role="status">
                            <span class="badge bg-warning text-dark me-2">&bull;</span>
                            <div>
                                <strong><?php echo esc_html__('Server-side not yet verified.', 'unipixel'); ?></strong>
                                <small class="d-block"><?php echo esc_html__('Credentials saved. Click Test Connection below to verify, or wait for server events to confirm setup.', 'unipixel'); ?></small>
                                <small class="d-block mt-1">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#google-setup-wizard-modal"><?php echo esc_html__('Re-walk server-side setup', 'unipixel'); ?></a>
                                </small>
                            </div>
                        </div>
                    <?php else :
                        $g_freshness_at = ($google_conn_state['last_test_at'] && $google_conn_state['last_event_at'])
                            ? max($google_conn_state['last_test_at'], $google_conn_state['last_event_at'])
                            : ($google_conn_state['last_test_at'] ? $google_conn_state['last_test_at'] : $google_conn_state['last_event_at']);
                    ?>
                        <div class="alert alert-success d-flex align-items-center mb-3" role="status">
                            <span class="badge bg-success me-2">&bull;</span>
                            <div>
                                <strong><?php echo esc_html__('Format checks passed.', 'unipixel'); ?></strong>
                                <small class="d-block"><?php echo esc_html__('Confirm delivery in GA4 DebugView. Google does not let us verify the API Secret value directly.', 'unipixel'); ?></small>
                                <?php if ($g_freshness_at) : ?>
                                    <small class="d-block">
                                        <?php echo esc_html(sprintf(
                                            /* translators: %s is a human time diff, e.g. "5 minutes" */
                                            __('Last tested %s ago.', 'unipixel'),
                                            human_time_diff($g_freshness_at, time())
                                        )); ?>
                                    </small>
                                <?php endif; ?>
                                <small class="d-block mt-1">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#google-setup-wizard-modal"><?php echo esc_html__('Re-walk server-side setup', 'unipixel'); ?></a>
                                </small>
                            </div>
                        </div>
                    <?php endif; ?>

                    <p class="small text-muted mb-3"><?php echo esc_html__('More accurate, bypasses ad blockers. Note: Google only deduplicates the Purchase event. For all other events, use the event-level toggles to choose either client-side or server-side. Requires an API Secret on top of your Measurement ID.', 'unipixel'); ?></p>

                    <div class="mb-3 row">
                        <div class="col-12 col-sm-3">
                            <label class="form-check-label" for="serverside_global_enabled">
                                <?php echo esc_html__('Enable Server-Side Tracking', 'unipixel'); ?>
                                <?php echo wp_kses(unipixel_get_help_icon('ServerSideGlobalEnabled'), $icon_allow); ?>
                            </label>
                        </div>
                        <div class="col-12 col-sm-9">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="serverside_global_enabled" name="serverside_global_enabled" value="1" <?php checked($serverside_global_enabled, 1); ?>>
                            </div>
                        </div>
                    </div>

                    <div id="serverside-fields">
                        <div class="mb-3 row">
                            <label for="access_token" class="col-sm-3 col-form-label">
                                <?php echo esc_html__('API Secret:', 'unipixel'); ?>
                                <?php echo wp_kses(unipixel_get_help_icon('Google_ApiSecret'), $icon_allow); ?>
                            </label>
                            <div class="col-sm-9">
                                <input type="password" id="access_token" name="access_token" class="form-control" value="<?php echo esc_attr($access_token); ?>" autocomplete="new-password" readonly>
                            </div>
                        </div>

                        <div class="mb-3 row" id="google-test-connection-row" style="display:none;">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-9">
                                <button type="button" id="google-test-connection-btn" class="btn btn-outline-primary">
                                    <?php echo esc_html__('Test Connection', 'unipixel'); ?>
                                </button>
                                <div id="google-test-connection-result" class="mt-2" role="status" style="display:none;"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="mb-3 row">
                <div class="col-sm-9 offset-sm-3">
                    <input type="submit" value="<?php echo esc_attr__('Update Settings', 'unipixel'); ?>" id="btnUniPixelUpdatePlatformSettings" name="btnUniPixelUpdatePlatformSettings" class="btn btn-primary">
                </div>
            </div>
        </form>

        <?php /* Google Setup Wizard Modal (token-acquisition-ux Phase 7) */ ?>
        <div class="modal fade" id="google-setup-wizard-modal" tabindex="-1" aria-labelledby="googleSetupWizardLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="googleSetupWizardLabel"><?php echo esc_html__('Set up server-side tracking for Google', 'unipixel'); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo esc_attr__('Close', 'unipixel'); ?>"></button>
                    </div>
                    <div class="modal-body">

                        <div class="google-wizard-step" data-step="1">
                            <h6 class="mb-3"><?php echo esc_html__("What you'll achieve", 'unipixel'); ?></h6>
                            <p><?php echo esc_html__("By the end of this guide, server-side events will fire to GA4 via the Measurement Protocol on top of any client-side tracking. You'll have your Measurement ID and an API Secret pasted into UniPixel, and a successful Test Connection confirming the wiring.", 'unipixel'); ?></p>
                            <p class="mt-3 mb-0"><small><a href="#" class="google-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="google-wizard-step d-none" data-step="2">
                            <h6 class="mb-3"><?php echo esc_html__('Prerequisites', 'unipixel'); ?></h6>
                            <p><?php echo esc_html__("You need a GA4 property with a Web data stream already set up. If you don't have one, create the property at analytics.google.com first, then come back.", 'unipixel'); ?></p>
                            <p class="mt-3 mb-0"><small><a href="#" class="google-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="google-wizard-step d-none" data-step="3">
                            <h6 class="mb-3"><?php echo esc_html__('What to ignore', 'unipixel'); ?></h6>
                            <p><?php echo esc_html__('Google Analytics has a lot of options. For UniPixel server-side setup, you can safely ignore:', 'unipixel'); ?></p>
                            <ul>
                                <li><?php echo esc_html__('Enhanced Measurement toggles. These are client-side tracking concerns, separate from Measurement Protocol.', 'unipixel'); ?></li>
                                <li><?php echo esc_html__('Audience definitions.', 'unipixel'); ?></li>
                                <li><?php echo esc_html__('Conversion event marking.', 'unipixel'); ?></li>
                                <li><?php echo esc_html__('Custom dimensions and metrics.', 'unipixel'); ?></li>
                                <li><?php echo esc_html__('Google Tag Manager, unless you are already using it on your site.', 'unipixel'); ?></li>
                                <li><?php echo esc_html__('Server-Side Google Tag Manager. That is a different paid product, not needed for UniPixel.', 'unipixel'); ?></li>
                            </ul>
                            <p><?php echo esc_html__('You only need: a Measurement ID, and a Measurement Protocol API Secret from your Web data stream.', 'unipixel'); ?></p>
                            <p class="small text-muted"><strong><?php echo esc_html__('Important Google quirk (G-001):', 'unipixel'); ?></strong> <?php echo esc_html__('Google deduplicates only the Purchase event. For every other event, send EITHER client OR server, not both. UniPixel\'s Event Manager enforces this automatically.', 'unipixel'); ?></p>
                            <p class="mt-3 mb-0"><small><a href="#" class="google-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="google-wizard-step d-none" data-step="4">
                            <h6 class="mb-3"><?php echo esc_html__('Get your credentials', 'unipixel'); ?></h6>
                            <p><?php echo esc_html__("You'll need a GA4 property with a Web Data Stream. If you don't have one, the Open Google Analytics link below will walk you through creating one.", 'unipixel'); ?></p>
                            <p><?php echo esc_html__('You need two things from Google: your Measurement ID and a Measurement Protocol API Secret. Both come from your GA4 Web data stream.', 'unipixel'); ?></p>

                            <div class="mb-3 p-3 border rounded">
                                <ol class="mb-2 ps-4">
                                    <li><?php echo esc_html__('Open Google Analytics. Click the Admin gear (bottom-left).', 'unipixel'); ?></li>
                                    <li><?php echo esc_html__('Under the property column, click Data Streams.', 'unipixel'); ?></li>
                                    <li><?php echo esc_html__('Click your Web stream.', 'unipixel'); ?></li>
                                    <li><?php echo esc_html__('The Measurement ID (G-XXXXXXXXXX) appears at the top of the stream details. Copy it.', 'unipixel'); ?></li>
                                    <li><?php echo esc_html__('Scroll down to "Measurement Protocol API secrets". Click Create.', 'unipixel'); ?></li>
                                    <li><?php echo esc_html__('Give it a nickname (e.g. "UniPixel WordPress"), click Create, and copy the secret.', 'unipixel'); ?></li>
                                </ol>
                                <a href="https://analytics.google.com/" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> <?php echo esc_html__('Open Google Analytics', 'unipixel'); ?>
                                </a>
                                <p class="mb-0 mt-2 small text-muted"><?php echo esc_html__('The API Secret is data-stream-specific, so it cannot be reused across streams or properties. Treat it as a secret, like a password.', 'unipixel'); ?></p>
                            </div>

                            <p class="mb-0"><?php echo esc_html__('Keep both copied somewhere safe, then continue.', 'unipixel'); ?></p>
                            <p class="mt-3 mb-0"><small><a href="#" class="google-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="google-wizard-step d-none" data-step="5">
                            <h6 class="mb-3"><?php echo esc_html__('Paste your credentials', 'unipixel'); ?></h6>
                            <div class="mb-3">
                                <label for="wizard-google-measurement-id" class="form-label"><?php echo esc_html__('Measurement ID', 'unipixel'); ?></label>
                                <input type="text" class="form-control" id="wizard-google-measurement-id" autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label for="wizard-google-api-secret" class="form-label"><?php echo esc_html__('API Secret', 'unipixel'); ?></label>
                                <input type="password" class="form-control" id="wizard-google-api-secret" autocomplete="off">
                            </div>
                            <button type="button" class="btn btn-primary" id="wizard-google-save-btn"><?php echo esc_html__('Save and continue', 'unipixel'); ?></button>
                            <div id="wizard-google-save-result" class="mt-3" role="status" style="display:none;"></div>
                            <p class="mt-3 mb-0"><small><a href="#" class="google-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="google-wizard-step d-none" data-step="6">
                            <h6 class="mb-3"><?php echo esc_html__('Test the connection', 'unipixel'); ?></h6>
                            <p><?php echo esc_html__("Now let's verify the connection works. UniPixel will format-check your credentials, validate the payload at Google's debug endpoint, and fire a debug-mode test event to Google's production endpoint so it appears in GA4 DebugView.", 'unipixel'); ?></p>
                            <p class="small text-muted"><strong><?php echo esc_html__('How to confirm fully:', 'unipixel'); ?></strong> <?php echo esc_html__('Google does not surface API Secret errors directly. A green result here means the structural validation passed; to confirm the API Secret value is actually correct, open GA4 → Admin → DebugView and look for "unipixel_test_connection" within 60 seconds. If it appears there, your setup is fully working. If it does not, the API Secret is likely wrong.', 'unipixel'); ?></p>
                            <button type="button" class="btn btn-primary" id="wizard-google-test-connection-btn"><?php echo esc_html__('Test Connection', 'unipixel'); ?></button>
                            <div id="wizard-google-test-connection-result" class="mt-3" role="status" style="display:none;"></div>
                            <p class="mt-3 mb-0"><small><a href="#" class="google-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="google-wizard-step d-none" data-step="7">
                            <h6 class="mb-3"><?php echo esc_html__("You're set up", 'unipixel'); ?></h6>
                            <p><?php echo esc_html__('Server-side events will start flowing on the next user action on your site. Open GA4 Realtime to watch them land.', 'unipixel'); ?></p>
                            <button type="button" class="btn btn-primary" id="wizard-google-done-btn"><?php echo esc_html__('Close', 'unipixel'); ?></button>
                        </div>

                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <span class="text-muted small"><?php echo esc_html__('Step', 'unipixel'); ?> <span class="google-wizard-current-step">1</span> <?php echo esc_html__('of', 'unipixel'); ?> 7</span>
                        <div>
                            <button type="button" class="btn btn-secondary google-wizard-back" disabled><?php echo esc_html__('Back', 'unipixel'); ?></button>
                            <button type="button" class="btn btn-primary google-wizard-next"><?php echo esc_html__('Next', 'unipixel'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
