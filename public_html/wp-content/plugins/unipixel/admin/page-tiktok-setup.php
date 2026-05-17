<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * TikTok → Tag/Connection Setup page (no Events UI here)
 */
function unipixel_page_tiktok_setup() {
    global $wpdb;

    $platform_table     = $wpdb->prefix . 'unipixel_platform_settings';
    $platformId         = 3;
    $platformName       = 'TikTok';
    $pixelNameFriendly  = 'TikTok Pixel';

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
        $platform_id                = (int) $platform['id'];
        $pixel_id                   = isset($platform['pixel_id']) ? $platform['pixel_id'] : '';
        $access_token               = isset($platform['access_token']) ? $platform['access_token'] : '';
        $platform_enabled           = isset($platform['platform_enabled']) ? (int) $platform['platform_enabled'] : 0;
        $pixel_setting              = isset($platform['pixel_setting']) ? $platform['pixel_setting'] : 'include';
        $serverside_global_enabled  = isset($platform['serverside_global_enabled']) ? (int) $platform['serverside_global_enabled'] : 0;
    } else {
        $platform_id                = 0;
        $pixel_id                   = '';
        $access_token               = '';
        $platform_enabled           = 1;
        $pixel_setting              = 'include';
        $serverside_global_enabled  = 0;
    }

    // Allowlist for inline help icons HTML
    $icon_allow = unipixel_get_popover_allowlist();

    // Two-section layout: client-side vs server-side.
    $client_side_active = (!empty($pixel_id) && $platform_enabled === 1);
    $tiktok_conn_state  = unipixel_get_platform_connection_state($platformId);
    ?>
    <div class="UniPixelShell position-relative">

        <div class="UniPixelSpinner d-none">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden"><?php echo esc_html__('Loading…', 'unipixel'); ?></span>
            </div>
        </div>

        <?php unipixel_render_platform_header_nav('tiktok',"setup");?>

        <h1 class="mb-0">Tag <?php echo esc_html__('Setup', 'unipixel'); ?></h1>
        <p><small><?php echo esc_html__('Configure your connection and core settings for', 'unipixel'); ?> <?php echo esc_html($platformName); ?>.</small></p>

        <!-- Feedback message container (used by ajax-platform-settings.js) -->
        <div id="platform-settings-feedback-message" class="alert" role="alert" style="display:none;"></div>

        <!-- Platform (Tag/Connection) form -->
        <form id="platform-settings-form" class="form-horizontal">
            <input type="hidden" id="platform_id" name="platform_id" value="<?php echo esc_attr((string) $platform_id); ?>">

            <div class="mb-3 row">
                <div class="col-12 col-sm-3">
                    <label class="form-check-label" for="platform_enabled">
                        <?php echo esc_html__('Send tracking to TikTok?', 'unipixel'); ?>
                        <?php echo wp_kses(unipixel_get_help_icon('TikTok_Enabled'), $icon_allow); ?>
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
                        <i class="fa-solid fa-bolt me-2 text-primary"></i>
                        <strong><?php echo esc_html__('Client-side tracking', 'unipixel'); ?></strong>
                        <?php if ($client_side_active) : ?>
                            <span class="badge bg-success ms-2"><?php echo esc_html__('Active', 'unipixel'); ?></span>
                        <?php else : ?>
                            <span class="badge bg-secondary ms-2"><?php echo esc_html__('Off', 'unipixel'); ?></span>
                        <?php endif; ?>
                    </div>
                    <p class="small text-muted mb-3"><?php echo esc_html__('The fastest way to start tracking. Add your TikTok Pixel ID and events fire from the browser pixel.', 'unipixel'); ?></p>

                    <?php if (empty($pixel_id)) : ?>
                        <div class="alert alert-info py-2 small mb-3" role="status">
                            <strong><?php echo esc_html__('Start simple.', 'unipixel'); ?></strong>
                            <?php echo esc_html__('Add your TikTok Pixel ID below to start tracking. Find it in TikTok Events Manager.', 'unipixel'); ?>
                            <a href="https://ads.tiktok.com/i18n/events_manager/" target="_blank" rel="noopener noreferrer" class="ms-1">
                                <?php echo esc_html__('Open TikTok Events Manager', 'unipixel'); ?> <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                            <div class="mt-1 small text-muted"><?php echo esc_html__('No Pixel yet? Events Manager has a Connect data source → Web option to create one.', 'unipixel'); ?></div>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3 row">
                        <label class="col-sm-3 col-form-label"><?php echo esc_html__('Pixel Setting:', 'unipixel'); ?></label>
                        <div class="col-sm-9">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="pixel_setting" id="pixel_setting_include" value="include" <?php checked($pixel_setting, 'include'); ?>>
                                <label class="form-check-label" for="pixel_setting_include">
                                    <?php echo esc_html__('Include', 'unipixel'); ?> <?php echo esc_html($platformName); ?><?php echo esc_html__('\'s Tracking Pixel for me', 'unipixel'); ?>
                                    <?php echo wp_kses(unipixel_get_help_icon('TikTok_Include'), $icon_allow); ?>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="pixel_setting" id="pixel_setting_already_included" value="already_included" <?php checked($pixel_setting, 'already_included'); ?>>
                                <label class="form-check-label" for="pixel_setting_already_included">
                                    <?php echo esc_html($platformName); ?><?php echo esc_html__('\'s Tracking Pixel is already on my site', 'unipixel'); ?>
                                    <?php echo wp_kses(unipixel_get_help_icon('TikTok_Already'), $icon_allow); ?>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-0 row">
                        <label for="pixel_id" class="col-sm-3 col-form-label">
                            <?php echo esc_html__('Pixel ID:', 'unipixel'); ?>
                            <?php echo wp_kses(unipixel_get_help_icon('TikTok_PixelId'), $icon_allow); ?>
                        </label>
                        <div class="col-sm-9">
                            <input type="text" id="pixel_id" name="pixel_id" class="form-control" value="<?php echo esc_attr($pixel_id); ?>" autocomplete="off" required>
                        </div>
                    </div>
                </div>

                <!-- ==================================================================
                     Server-side tracking section
                     ================================================================== -->
                <div id="serverside-well" class="unipixel-tracking-section bg-light-blue p-3 mb-3 rounded border">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fa-solid fa-bolt-lightning me-2"></i>
                        <strong><?php echo esc_html__('Server-side tracking', 'unipixel'); ?></strong>
                        <span class="text-muted small ms-2"><?php echo esc_html__('(recommended)', 'unipixel'); ?></span>
                    </div>

                    <?php if ($tiktok_conn_state['state'] === 'not_started') : ?>
                        <div class="alert alert-secondary d-flex align-items-center mb-3" role="status">
                            <span class="badge bg-secondary me-2">&bull;</span>
                            <div class="flex-grow-1">
                                <strong><?php echo esc_html__('Server-side tracking not set up.', 'unipixel'); ?></strong>
                                <small class="d-block"><?php echo esc_html__('Enable Server-Side Tracking and add an Access Token below, or use the guided walkthrough.', 'unipixel'); ?></small>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm ms-3" data-bs-toggle="modal" data-bs-target="#tiktok-setup-wizard-modal">
                                <?php echo esc_html__('Start server-side setup', 'unipixel'); ?>
                            </button>
                        </div>
                    <?php elseif ($tiktok_conn_state['state'] === 'pasted_unverified') : ?>
                        <div class="alert alert-warning d-flex align-items-center mb-3" role="status">
                            <span class="badge bg-warning text-dark me-2">&bull;</span>
                            <div>
                                <strong><?php echo esc_html__('Server-side not yet verified.', 'unipixel'); ?></strong>
                                <small class="d-block"><?php echo esc_html__('Credentials saved. Click Test Connection below to verify, or wait for server events to confirm setup.', 'unipixel'); ?></small>
                                <small class="d-block mt-1">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#tiktok-setup-wizard-modal"><?php echo esc_html__('Re-walk server-side setup', 'unipixel'); ?></a>
                                </small>
                            </div>
                        </div>
                    <?php else :
                        $tt_freshness_at = ($tiktok_conn_state['last_test_at'] && $tiktok_conn_state['last_event_at'])
                            ? max($tiktok_conn_state['last_test_at'], $tiktok_conn_state['last_event_at'])
                            : ($tiktok_conn_state['last_test_at'] ? $tiktok_conn_state['last_test_at'] : $tiktok_conn_state['last_event_at']);
                    ?>
                        <div class="alert alert-success d-flex align-items-center mb-3" role="status">
                            <span class="badge bg-success me-2">&bull;</span>
                            <div>
                                <strong><?php echo esc_html__('Server-side ready.', 'unipixel'); ?></strong>
                                <?php if ($tt_freshness_at) : ?>
                                    <small class="d-block">
                                        <?php echo esc_html(sprintf(
                                            /* translators: %s is a human time diff, e.g. "5 minutes" */
                                            __('Verified %s ago.', 'unipixel'),
                                            human_time_diff($tt_freshness_at, time())
                                        )); ?>
                                    </small>
                                <?php endif; ?>
                                <small class="d-block mt-1">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#tiktok-setup-wizard-modal"><?php echo esc_html__('Re-walk server-side setup', 'unipixel'); ?></a>
                                </small>
                            </div>
                        </div>
                    <?php endif; ?>

                    <p class="small text-muted mb-3"><?php echo esc_html__('More accurate, bypasses ad blockers, dedupes with client-side. Requires an Events API Access Token on top of your Pixel ID.', 'unipixel'); ?></p>

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
                                <?php echo esc_html__('Access Token:', 'unipixel'); ?>
                                <?php echo wp_kses(unipixel_get_help_icon('TikTok_AccessToken'), $icon_allow); ?>
                            </label>
                            <div class="col-sm-9">
                                <input type="password" id="access_token" name="access_token" class="form-control" value="<?php echo esc_attr($access_token); ?>" autocomplete="new-password">
                            </div>
                        </div>

                        <div class="mb-3 row" id="tiktok-test-connection-row" style="display:none;">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-9">
                                <button type="button" id="tiktok-test-connection-btn" class="btn btn-outline-primary">
                                    <?php echo esc_html__('Test Connection', 'unipixel'); ?>
                                </button>
                                <div id="tiktok-test-connection-result" class="mt-2" role="status" style="display:none;"></div>
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

        <?php /* TikTok Setup Wizard Modal (token-acquisition-ux Phase 8) */ ?>
        <div class="modal fade" id="tiktok-setup-wizard-modal" tabindex="-1" aria-labelledby="tiktokSetupWizardLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tiktokSetupWizardLabel"><?php echo esc_html__('Set up server-side tracking for TikTok', 'unipixel'); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo esc_attr__('Close', 'unipixel'); ?>"></button>
                    </div>
                    <div class="modal-body">

                        <div class="tiktok-wizard-step" data-step="1">
                            <h6 class="mb-3"><?php echo esc_html__("What you'll achieve", 'unipixel'); ?></h6>
                            <p><?php echo esc_html__("By the end of this guide, server-side events will fire to TikTok via the Events API on top of any client-side tracking. You'll have your Pixel ID and an Access Token pasted into UniPixel, and a successful Test Connection confirming the wiring.", 'unipixel'); ?></p>
                            <p class="mt-3 mb-0"><small><a href="#" class="tiktok-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="tiktok-wizard-step d-none" data-step="2">
                            <h6 class="mb-3"><?php echo esc_html__('Prerequisites', 'unipixel'); ?></h6>
                            <p><?php echo esc_html__('You need a TikTok Ads Manager account with a Web Pixel set up. If you don\'t have one, create it in TikTok Ads Manager → Tools → Events Manager → Web Events first, then come back.', 'unipixel'); ?></p>
                            <p class="mt-3 mb-0"><small><a href="#" class="tiktok-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="tiktok-wizard-step d-none" data-step="3">
                            <h6 class="mb-3"><?php echo esc_html__('What to ignore', 'unipixel'); ?></h6>
                            <p><?php echo esc_html__('TikTok Ads Manager pushes a lot of options at you. For UniPixel server-side setup, you can safely ignore:', 'unipixel'); ?></p>
                            <ul>
                                <li><?php echo esc_html__('Smart Performance Campaign suggestions. Those are ad-spend features, unrelated to tracking setup.', 'unipixel'); ?></li>
                                <li><?php echo esc_html__('Audience Insights and custom audience builders.', 'unipixel'); ?></li>
                                <li><?php echo esc_html__('Catalog setup. That is a separate product for Dynamic Showcase Ads.', 'unipixel'); ?></li>
                                <li><?php echo esc_html__('Spark Ads prompts.', 'unipixel'); ?></li>
                                <li><?php echo esc_html__('The TikTok partner integrations marketplace. UniPixel IS your integration.', 'unipixel'); ?></li>
                            </ul>
                            <p><?php echo esc_html__('You only need: your Web Pixel\'s Pixel ID, and an Events API Access Token for that Pixel.', 'unipixel'); ?></p>
                            <p class="small text-muted"><strong><?php echo esc_html__('TikTok quirk worth knowing:', 'unipixel'); ?></strong> <?php echo esc_html__('TikTok auto-maps certain "Reserved Event Names" to its Standard Events. If you create custom events with those reserved names, TikTok rolls them into the Standard event silently. Stick to clearly-custom event names if you want them tracked as custom.', 'unipixel'); ?></p>
                            <p class="mt-3 mb-0"><small><a href="#" class="tiktok-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="tiktok-wizard-step d-none" data-step="4">
                            <h6 class="mb-3"><?php echo esc_html__('Get your credentials', 'unipixel'); ?></h6>
                            <p><?php echo esc_html__("You'll need a Web Pixel in TikTok Events Manager. If you don't have one, the Open TikTok Events Manager link below has a Connect data source button.", 'unipixel'); ?></p>
                            <p><?php echo esc_html__('You need two things from TikTok: your Pixel ID and an Events API Access Token. Both come from TikTok Events Manager.', 'unipixel'); ?></p>

                            <div class="mb-3 p-3 border rounded">
                                <ol class="mb-2 ps-4">
                                    <li><?php echo esc_html__('Open TikTok Ads Manager and go to Tools → Events Manager.', 'unipixel'); ?></li>
                                    <li><?php echo esc_html__('Click your Web Pixel (or click "Connect data source" → "Web" to create one).', 'unipixel'); ?></li>
                                    <li><?php echo esc_html__('Your Pixel ID appears at the top of the pixel detail page (a string like C8C3JPS5R0L0CKHEJ8K0). Copy it.', 'unipixel'); ?></li>
                                    <li><?php echo esc_html__('Open the Settings tab on the same pixel.', 'unipixel'); ?></li>
                                    <li><?php echo esc_html__('Scroll to the Events API section. Click "Generate access token" and copy it immediately (TikTok will only show it once).', 'unipixel'); ?></li>
                                </ol>
                                <a href="https://ads.tiktok.com/i18n/events_manager/" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> <?php echo esc_html__('Open TikTok Events Manager', 'unipixel'); ?>
                                </a>
                            </div>

                            <p class="mb-0"><?php echo esc_html__('Keep both copied somewhere safe, then continue.', 'unipixel'); ?></p>
                            <p class="mt-3 mb-0"><small><a href="#" class="tiktok-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="tiktok-wizard-step d-none" data-step="5">
                            <h6 class="mb-3"><?php echo esc_html__('Paste your credentials', 'unipixel'); ?></h6>
                            <div class="mb-3">
                                <label for="wizard-tiktok-pixel-id" class="form-label"><?php echo esc_html__('Pixel ID', 'unipixel'); ?></label>
                                <input type="text" class="form-control" id="wizard-tiktok-pixel-id" autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label for="wizard-tiktok-access-token" class="form-label"><?php echo esc_html__('Access Token', 'unipixel'); ?></label>
                                <input type="password" class="form-control" id="wizard-tiktok-access-token" autocomplete="off">
                            </div>
                            <button type="button" class="btn btn-primary" id="wizard-tiktok-save-btn"><?php echo esc_html__('Save and continue', 'unipixel'); ?></button>
                            <div id="wizard-tiktok-save-result" class="mt-3" role="status" style="display:none;"></div>
                            <p class="mt-3 mb-0"><small><a href="#" class="tiktok-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="tiktok-wizard-step d-none" data-step="6">
                            <h6 class="mb-3"><?php echo esc_html__('Test the connection', 'unipixel'); ?></h6>
                            <p><?php echo esc_html__("Now let's verify the connection works. UniPixel will format-check your credentials and send a test event to TikTok's Events API with an auto-generated test_event_code, so it lands in TikTok Events Manager → Test Events tab (not in production reports).", 'unipixel'); ?></p>
                            <p class="small text-muted"><strong><?php echo esc_html__('How to confirm fully:', 'unipixel'); ?></strong> <?php echo esc_html__('A green result here means TikTok accepted the event with code 0 (OK). To see the test event in TikTok\'s own UI, open Events Manager → your Pixel → Test Events tab. The event name will be "unipixel_test_connection" and the test_event_code will be shown in the success message.', 'unipixel'); ?></p>
                            <button type="button" class="btn btn-primary" id="wizard-tiktok-test-connection-btn"><?php echo esc_html__('Test Connection', 'unipixel'); ?></button>
                            <div id="wizard-tiktok-test-connection-result" class="mt-3" role="status" style="display:none;"></div>
                            <p class="mt-3 mb-0"><small><a href="#" class="tiktok-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="tiktok-wizard-step d-none" data-step="7">
                            <h6 class="mb-3"><?php echo esc_html__("You're set up", 'unipixel'); ?></h6>
                            <p><?php echo esc_html__('Server-side events will start flowing on the next user action on your site. Open TikTok Events Manager → your Pixel → Events to watch them land in production reports.', 'unipixel'); ?></p>
                            <button type="button" class="btn btn-primary" id="wizard-tiktok-done-btn"><?php echo esc_html__('Close', 'unipixel'); ?></button>
                        </div>

                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <span class="text-muted small"><?php echo esc_html__('Step', 'unipixel'); ?> <span class="tiktok-wizard-current-step">1</span> <?php echo esc_html__('of', 'unipixel'); ?> 7</span>
                        <div>
                            <button type="button" class="btn btn-secondary tiktok-wizard-back" disabled><?php echo esc_html__('Back', 'unipixel'); ?></button>
                            <button type="button" class="btn btn-primary tiktok-wizard-next"><?php echo esc_html__('Next', 'unipixel'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
