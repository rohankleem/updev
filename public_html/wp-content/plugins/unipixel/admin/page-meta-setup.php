<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Meta → Tag/Connection Setup page (no Events UI here)
 */
function unipixel_page_meta_setup() {
    global $wpdb;

    $platform_table     = $wpdb->prefix . 'unipixel_platform_settings';
    $platformId         = 1;
    $platformName       = 'Meta';
    $pixelNameFriendly  = 'Meta Pixel';

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

    // Two-section layout: client-side vs server-side. Each section gets its own status.
    $client_side_active = (!empty($pixel_id) && $platform_enabled === 1);
    $meta_conn_state    = unipixel_get_platform_connection_state($platformId);
    ?>
    <div class="UniPixelShell position-relative">

        <div class="UniPixelSpinner d-none">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden"><?php echo esc_html__('Loading…', 'unipixel'); ?></span>
            </div>
        </div>

        <?php unipixel_render_platform_header_nav('meta',"setup");?>

        <h1 class="mb-0">Tag <?php echo esc_html__('Setup', 'unipixel'); ?></h1>
        <p><small><?php echo esc_html__('Configure your connection and core settings for', 'unipixel'); ?> <?php echo esc_html($platformName); ?>.</small></p>

        <!-- Feedback message container (used by ajax-platform-settings.js) -->
        <div id="platform-settings-feedback-message" class="alert" role="alert" style="display:none;"></div>


        <!-- Platform (Tag/Connection) form -->
        <form id="platform-settings-form" class="form-horizontal" autocomplete="off">
            <input type="hidden" id="platform_id" name="platform_id" value="<?php echo esc_attr((string) $platform_id); ?>">

            <?php /* Anti-autofill honeypot: browsers ignore autocomplete="off" on text inputs that look username-shaped (e.g. when a password field appears later in the form). These hidden decoys catch the browser's autofill before it reaches the real credential inputs below. */ ?>
            <div class="unipixel-autofill-honeypot" aria-hidden="true" style="position:absolute;height:0;width:0;overflow:hidden;opacity:0;">
                <input type="text" name="__autofill_decoy_user" autocomplete="username" tabindex="-1">
                <input type="password" name="__autofill_decoy_pass" autocomplete="new-password" tabindex="-1">
            </div>

            <div class="mb-3 row">
                <div class="col-12 col-sm-3">
                    <label class="form-check-label" for="platform_enabled">
                        <?php echo esc_html__('Send tracking to Meta?', 'unipixel'); ?>
                        <?php echo wp_kses(unipixel_get_help_icon('Meta_Enabled'), $icon_allow); ?>
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
                    <p class="small text-muted mb-3"><?php echo esc_html__('The fastest way to start tracking. Add your Pixel ID and events fire from the browser pixel.', 'unipixel'); ?></p>

                    <?php if (empty($pixel_id)) : ?>
                        <div class="alert alert-info py-2 small mb-3" role="status">
                            <strong><?php echo esc_html__('Start simple.', 'unipixel'); ?></strong>
                            <?php echo esc_html__('Add your Pixel ID below to start tracking. Find it in Meta Events Manager.', 'unipixel'); ?>
                            <a href="https://business.facebook.com/events_manager2/" target="_blank" rel="noopener noreferrer" class="ms-1">
                                <?php echo esc_html__('Open Meta Events Manager', 'unipixel'); ?> <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                            <div class="mt-1 small text-muted"><?php echo esc_html__('No Pixel yet? Events Manager has a Create button for new Pixels.', 'unipixel'); ?></div>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3 row">
                        <label class="col-sm-3 col-form-label"><?php echo esc_html__('Pixel Setting:', 'unipixel'); ?></label>
                        <div class="col-sm-9">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="pixel_setting" id="pixel_setting_include" value="include" <?php checked($pixel_setting, 'include'); ?>>
                                <label class="form-check-label" for="pixel_setting_include">
                                    <?php echo esc_html__('Include', 'unipixel'); ?> <?php echo esc_html($platformName); ?><?php echo esc_html__('\'s Tracking Pixel for me', 'unipixel'); ?>
                                    <?php echo wp_kses(unipixel_get_help_icon('Meta_Include'), $icon_allow); ?>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="pixel_setting" id="pixel_setting_already_included" value="already_included" <?php checked($pixel_setting, 'already_included'); ?>>
                                <label class="form-check-label" for="pixel_setting_already_included">
                                    <?php echo esc_html($platformName); ?><?php echo esc_html__('\'s Tracking Pixel is already on my site', 'unipixel'); ?>
                                    <?php echo wp_kses(unipixel_get_help_icon('Meta_Already'), $icon_allow); ?>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-0 row">
                        <label for="pixel_id" class="col-sm-3 col-form-label">
                            <?php echo esc_html__('Pixel ID:', 'unipixel'); ?>
                            <?php echo wp_kses(unipixel_get_help_icon('Meta_PixelId'), $icon_allow); ?>
                        </label>
                        <div class="col-sm-9">
                            <input type="text" id="pixel_id" name="pixel_id" class="form-control" value="<?php echo esc_attr($pixel_id); ?>" autocomplete="off" required>
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

                    <?php
                    // Status strip lives INSIDE this section now.
                    if ($meta_conn_state['state'] === 'not_started') :
                    ?>
                        <div class="alert alert-secondary d-flex align-items-center mb-3" role="status">
                            <span class="badge bg-secondary me-2">&bull;</span>
                            <div class="flex-grow-1">
                                <strong><?php echo esc_html__('Server-side tracking not set up.', 'unipixel'); ?></strong>
                                <small class="d-block"><?php echo esc_html__('Enable Server-Side Tracking and add an Access Token below, or use the guided walkthrough.', 'unipixel'); ?></small>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm ms-3" data-bs-toggle="modal" data-bs-target="#meta-setup-wizard-modal">
                                <?php echo esc_html__('Start server-side setup', 'unipixel'); ?>
                            </button>
                        </div>
                    <?php elseif ($meta_conn_state['state'] === 'pasted_unverified') : ?>
                        <div class="alert alert-warning d-flex align-items-center mb-3" role="status">
                            <span class="badge bg-warning text-dark me-2">&bull;</span>
                            <div>
                                <strong><?php echo esc_html__('Server-side not yet verified.', 'unipixel'); ?></strong>
                                <small class="d-block"><?php echo esc_html__('Credentials saved. Click Test Connection below to verify, or wait for server events to confirm setup.', 'unipixel'); ?></small>
                                <small class="d-block mt-1">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#meta-setup-wizard-modal"><?php echo esc_html__('Re-walk server-side setup', 'unipixel'); ?></a>
                                </small>
                            </div>
                        </div>
                    <?php else :
                        $freshness_at = ($meta_conn_state['last_test_at'] && $meta_conn_state['last_event_at'])
                            ? max($meta_conn_state['last_test_at'], $meta_conn_state['last_event_at'])
                            : ($meta_conn_state['last_test_at'] ? $meta_conn_state['last_test_at'] : $meta_conn_state['last_event_at']);
                    ?>
                        <div class="alert alert-success d-flex align-items-center mb-3" role="status">
                            <span class="badge bg-success me-2">&bull;</span>
                            <div>
                                <strong><?php echo esc_html__('Server-side ready.', 'unipixel'); ?></strong>
                                <?php if ($freshness_at) : ?>
                                    <small class="d-block">
                                        <?php echo esc_html(sprintf(
                                            /* translators: %s is a human time diff, e.g. "5 minutes" */
                                            __('Verified %s ago.', 'unipixel'),
                                            human_time_diff($freshness_at, time())
                                        )); ?>
                                    </small>
                                <?php endif; ?>
                                <small class="d-block mt-1">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#meta-setup-wizard-modal"><?php echo esc_html__('Re-walk server-side setup', 'unipixel'); ?></a>
                                </small>
                            </div>
                        </div>
                    <?php endif; ?>

                    <p class="small text-muted mb-3"><?php echo esc_html__('More accurate, bypasses ad blockers, and dedupes with client-side. Requires an Access Token from Meta on top of your Pixel ID.', 'unipixel'); ?></p>

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
                                <?php echo wp_kses(unipixel_get_help_icon('Meta_AccessToken'), $icon_allow); ?>
                            </label>
                            <div class="col-sm-9">
                                <input type="password" id="access_token" name="access_token" class="form-control" value="<?php echo esc_attr($access_token); ?>" autocomplete="new-password" readonly>
                            </div>
                        </div>

                        <div class="mb-3 row" id="meta-test-connection-row" style="display:none;">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-9">
                                <button type="button" id="meta-test-connection-btn" class="btn btn-outline-primary">
                                    <?php echo esc_html__('Test Connection', 'unipixel'); ?>
                                </button>
                                <div id="meta-test-connection-result" class="mt-2" role="status" style="display:none;"></div>
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

        <?php /* Meta Setup Wizard Modal (token-acquisition-ux Phase 3) */ ?>
        <div class="modal fade" id="meta-setup-wizard-modal" tabindex="-1" aria-labelledby="metaSetupWizardLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="metaSetupWizardLabel"><?php echo esc_html__('Set up server-side tracking for Meta', 'unipixel'); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo esc_attr__('Close', 'unipixel'); ?>"></button>
                    </div>
                    <div class="modal-body">

                        <div class="meta-wizard-step" data-step="1">
                            <h6 class="mb-3"><?php echo esc_html__("What you'll achieve", 'unipixel'); ?></h6>
                            <p><?php echo esc_html__("By the end of this guide, server-side events will fire to Meta on top of any client-side tracking. You'll have your Pixel ID and an Access Token pasted into UniPixel, and a successful Test Connection confirming the wiring.", 'unipixel'); ?></p>
                            <p class="mt-3 mb-0"><small><a href="#" class="meta-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="meta-wizard-step d-none" data-step="2">
                            <h6 class="mb-3"><?php echo esc_html__('Prerequisites', 'unipixel'); ?></h6>
                            <p><?php echo esc_html__("You need admin access to your Meta Business Manager. If you don't have one yet, set one up at business.facebook.com first, then come back.", 'unipixel'); ?></p>
                            <p class="mt-3 mb-0"><small><a href="#" class="meta-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="meta-wizard-step d-none" data-step="3">
                            <h6 class="mb-3"><?php echo esc_html__('What to ignore', 'unipixel'); ?></h6>
                            <p><?php echo esc_html__("Meta's Business Manager pushes a lot of options at you. For UniPixel setup, you can safely ignore:", 'unipixel'); ?></p>
                            <ul>
                                <li><?php echo esc_html__('Advantage+ campaign suggestions. These are ad-spend features, unrelated to tracking setup.', 'unipixel'); ?></li>
                                <li><?php echo esc_html__('Conversions API Gateway prompts. UniPixel IS the integration, you do not need a separate Gateway.', 'unipixel'); ?></li>
                                <li><?php echo esc_html__('Audience template wizards. UniPixel handles your event setup.', 'unipixel'); ?></li>
                                <li><?php echo esc_html__('Lookalike Audience prompts.', 'unipixel'); ?></li>
                                <li><?php echo esc_html__('Anything in Datasets beyond confirming your Pixel exists.', 'unipixel'); ?></li>
                            </ul>
                            <p><?php echo esc_html__('You only need: a Pixel ID, and an Access Token tied to that Pixel.', 'unipixel'); ?></p>
                            <p class="mt-3 mb-0"><small><a href="#" class="meta-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="meta-wizard-step d-none" data-step="4">
                            <h6 class="mb-3"><?php echo esc_html__('Get your credentials', 'unipixel'); ?></h6>
                            <p><?php echo esc_html__("You'll need a Pixel in Meta Events Manager. If you don't have one, the Open Meta Events Manager link below has a Create button for new Pixels.", 'unipixel'); ?></p>
                            <p><?php echo esc_html__('You need two things from Meta: your Pixel ID and an Access Token. Both come from Meta Events Manager.', 'unipixel'); ?></p>

                            <div class="mb-3 p-3 border rounded">
                                <p class="mb-2"><strong><?php echo esc_html__('The easy path (recommended)', 'unipixel'); ?></strong></p>
                                <ol class="mb-2 ps-4">
                                    <li><?php echo esc_html__('Open Meta Events Manager and select your Pixel.', 'unipixel'); ?></li>
                                    <li><?php echo esc_html__('Your Pixel ID is shown at the top of the Pixel page. Copy it.', 'unipixel'); ?></li>
                                    <li><?php echo esc_html__('Open the Settings tab on the same Pixel.', 'unipixel'); ?></li>
                                    <li><?php echo esc_html__('Scroll to the Conversions API section and click "Generate Access Token". Meta will create an app and a system user behind the scenes for you. The token appears immediately. Copy it now, you will not be able to view it again later.', 'unipixel'); ?></li>
                                </ol>
                                <a href="https://business.facebook.com/events_manager2/" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> <?php echo esc_html__('Open Meta Events Manager', 'unipixel'); ?>
                                </a>
                                <p class="mb-0 mt-2 small text-muted"><?php echo esc_html__('If you do not see the "Generate Access Token" link, you may need Developer access in your Business Manager. Ask your Business Manager admin to grant it.', 'unipixel'); ?></p>
                            </div>

                            <details class="mb-3">
                                <summary class="small text-muted" style="cursor:pointer;"><?php echo esc_html__('Already have your own app and system user? (e.g. agency setup)', 'unipixel'); ?></summary>
                                <div class="p-3 mt-2 border rounded">
                                    <p class="mb-2"><?php echo esc_html__('If your team manages its own Meta apps and system users, you can generate the token manually instead:', 'unipixel'); ?></p>
                                    <ol class="mb-2 ps-4">
                                        <li><?php echo esc_html__('Open Business Settings, go to System Users.', 'unipixel'); ?></li>
                                        <li><?php echo esc_html__('Pick or add a system user.', 'unipixel'); ?></li>
                                        <li><?php echo esc_html__('Assign your Pixel as an asset for that system user.', 'unipixel'); ?></li>
                                        <li><?php echo esc_html__('Click Generate Token with the ads_management permission. Copy it immediately.', 'unipixel'); ?></li>
                                    </ol>
                                    <a href="https://business.facebook.com/settings/system-users/" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i> <?php echo esc_html__('Open System Users', 'unipixel'); ?>
                                    </a>
                                </div>
                            </details>

                            <p class="mb-0"><?php echo esc_html__('Keep your Pixel ID and Access Token copied somewhere safe, then continue.', 'unipixel'); ?></p>
                            <p class="mt-3 mb-0"><small><a href="#" class="meta-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="meta-wizard-step d-none" data-step="5">
                            <h6 class="mb-3"><?php echo esc_html__('Paste your credentials', 'unipixel'); ?></h6>
                            <div class="mb-3">
                                <label for="wizard-pixel-id" class="form-label"><?php echo esc_html__('Pixel ID', 'unipixel'); ?></label>
                                <input type="text" class="form-control" id="wizard-pixel-id" name="wizard-pixel-id" autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label for="wizard-access-token" class="form-label"><?php echo esc_html__('Access Token', 'unipixel'); ?></label>
                                <input type="password" class="form-control" id="wizard-access-token" name="wizard-access-token" autocomplete="off">
                            </div>
                            <button type="button" class="btn btn-primary" id="wizard-save-btn"><?php echo esc_html__('Save and continue', 'unipixel'); ?></button>
                            <div id="wizard-save-result" class="mt-3" role="status" style="display:none;"></div>
                            <p class="mt-3 mb-0"><small><a href="#" class="meta-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="meta-wizard-step d-none" data-step="6">
                            <h6 class="mb-3"><?php echo esc_html__('Test the connection', 'unipixel'); ?></h6>
                            <p><?php echo esc_html__("Now let's verify the connection works. UniPixel will check your token against Meta's API and confirm it has access to your Pixel.", 'unipixel'); ?></p>
                            <button type="button" class="btn btn-primary" id="wizard-test-connection-btn"><?php echo esc_html__('Test Connection', 'unipixel'); ?></button>
                            <div id="wizard-test-connection-result" class="mt-3" role="status" style="display:none;"></div>
                            <p class="mt-3 mb-0"><small><a href="#" class="meta-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="meta-wizard-step d-none" data-step="7">
                            <h6 class="mb-3"><?php echo esc_html__("You're set up", 'unipixel'); ?></h6>
                            <p><?php echo esc_html__('Server-side events will start flowing on the next user action on your site. Close this and visit Stored Event Logs to watch them land.', 'unipixel'); ?></p>
                            <button type="button" class="btn btn-primary" id="wizard-done-btn"><?php echo esc_html__('Close', 'unipixel'); ?></button>
                        </div>

                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <span class="text-muted small"><?php echo esc_html__('Step', 'unipixel'); ?> <span class="meta-wizard-current-step">1</span> <?php echo esc_html__('of', 'unipixel'); ?> 7</span>
                        <div>
                            <button type="button" class="btn btn-secondary meta-wizard-back" disabled><?php echo esc_html__('Back', 'unipixel'); ?></button>
                            <button type="button" class="btn btn-primary meta-wizard-next"><?php echo esc_html__('Next', 'unipixel'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
