<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Pinterest → Tag/Connection Setup page
 */
function unipixel_page_pinterest_setup() {
    global $wpdb;

    $platform_table     = $wpdb->prefix . 'unipixel_platform_settings';
    $platformId         = 2;
    $platformName       = 'Pinterest';
    $pixelNameFriendly  = 'Pinterest Tag';

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
        $additional_id              = isset($platform['additional_id']) ? $platform['additional_id'] : '';
        $access_token               = isset($platform['access_token']) ? $platform['access_token'] : '';
        $platform_enabled           = isset($platform['platform_enabled']) ? (int) $platform['platform_enabled'] : 0;
        $pixel_setting              = isset($platform['pixel_setting']) ? $platform['pixel_setting'] : 'include';
        $serverside_global_enabled  = isset($platform['serverside_global_enabled']) ? (int) $platform['serverside_global_enabled'] : 0;
    } else {
        $platform_id                = 0;
        $pixel_id                   = '';
        $additional_id              = '';
        $access_token               = '';
        $platform_enabled           = 1;
        $pixel_setting              = 'include';
        $serverside_global_enabled  = 0;
    }

    // Allowlist for inline help icons HTML
    $icon_allow = unipixel_get_popover_allowlist();

    // Two-section layout. Note: Pinterest's Ad Account ID is a SERVER-SIDE
    // ingredient (needed for the ad-account-access check in Test Connection),
    // not a client-side ingredient. It stays inside the server-side section.
    $client_side_active   = (!empty($pixel_id) && $platform_enabled === 1);
    $pinterest_conn_state = unipixel_get_platform_connection_state($platformId);
    ?>
    <div class="UniPixelShell position-relative">

        <div class="UniPixelSpinner d-none">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden"><?php echo esc_html__('Loading…', 'unipixel'); ?></span>
            </div>
        </div>

        <?php unipixel_render_platform_header_nav('pinterest',"setup");?>

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
                        <?php echo esc_html__('Send tracking to Pinterest?', 'unipixel'); ?>
                        <?php echo wp_kses(unipixel_get_help_icon('Pinterest_Enabled'), $icon_allow); ?>
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
                    <p class="small text-muted mb-3"><?php echo esc_html__('The fastest way to start tracking. Add your Pinterest Tag ID and events fire from the browser tag.', 'unipixel'); ?></p>

                    <?php if (empty($pixel_id)) : ?>
                        <div class="alert alert-info py-2 small mb-3" role="status">
                            <strong><?php echo esc_html__('Start simple.', 'unipixel'); ?></strong>
                            <?php echo esc_html__('Add your Pinterest Tag ID below to start tracking. Find it in Pinterest Ads Manager under Conversions → Manage tags.', 'unipixel'); ?>
                            <a href="https://ads.pinterest.com/" target="_blank" rel="noopener noreferrer" class="ms-1">
                                <?php echo esc_html__('Open Pinterest Ads Manager', 'unipixel'); ?> <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                            <div class="mt-1 small text-muted"><?php echo esc_html__('No Tag yet? The Manage tags page has a Create button.', 'unipixel'); ?></div>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3 row">
                        <label class="col-sm-3 col-form-label"><?php echo esc_html__('Pixel Setting:', 'unipixel'); ?></label>
                        <div class="col-sm-9">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="pixel_setting" id="pixel_setting_include" value="include" <?php checked($pixel_setting, 'include'); ?>>
                                <label class="form-check-label" for="pixel_setting_include">
                                    <?php echo esc_html__('Include', 'unipixel'); ?> <?php echo esc_html($platformName); ?><?php echo esc_html__('\'s Tracking Tag for me', 'unipixel'); ?>
                                    <?php echo wp_kses(unipixel_get_help_icon('Pinterest_Include'), $icon_allow); ?>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="pixel_setting" id="pixel_setting_already_included" value="already_included" <?php checked($pixel_setting, 'already_included'); ?>>
                                <label class="form-check-label" for="pixel_setting_already_included">
                                    <?php echo esc_html($platformName); ?><?php echo esc_html__('\'s Tracking Tag is already on my site', 'unipixel'); ?>
                                    <?php echo wp_kses(unipixel_get_help_icon('Pinterest_Already'), $icon_allow); ?>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-0 row">
                        <label for="pixel_id" class="col-sm-3 col-form-label">
                            <?php echo esc_html__('Pinterest Tag ID:', 'unipixel'); ?>
                            <?php echo wp_kses(unipixel_get_help_icon('Pinterest_TagId'), $icon_allow); ?>
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

                    <?php if ($pinterest_conn_state['state'] === 'not_started') : ?>
                        <div class="alert alert-secondary d-flex align-items-center mb-3" role="status">
                            <span class="badge bg-secondary me-2">&bull;</span>
                            <div class="flex-grow-1">
                                <strong><?php echo esc_html__('Server-side tracking not set up.', 'unipixel'); ?></strong>
                                <small class="d-block"><?php echo esc_html__('Enable Server-Side Tracking and add your Ad Account ID and Conversion Access Token below, or use the guided walkthrough.', 'unipixel'); ?></small>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm ms-3" data-bs-toggle="modal" data-bs-target="#pinterest-setup-wizard-modal">
                                <?php echo esc_html__('Start server-side setup', 'unipixel'); ?>
                            </button>
                        </div>
                    <?php elseif ($pinterest_conn_state['state'] === 'pasted_unverified') : ?>
                        <div class="alert alert-warning d-flex align-items-center mb-3" role="status">
                            <span class="badge bg-warning text-dark me-2">&bull;</span>
                            <div>
                                <strong><?php echo esc_html__('Server-side not yet verified.', 'unipixel'); ?></strong>
                                <small class="d-block"><?php echo esc_html__('Credentials saved. Click Test Connection below to verify, or wait for server events to confirm setup.', 'unipixel'); ?></small>
                                <small class="d-block mt-1">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#pinterest-setup-wizard-modal"><?php echo esc_html__('Re-walk server-side setup', 'unipixel'); ?></a>
                                </small>
                            </div>
                        </div>
                    <?php else :
                        $pin_freshness_at = ($pinterest_conn_state['last_test_at'] && $pinterest_conn_state['last_event_at'])
                            ? max($pinterest_conn_state['last_test_at'], $pinterest_conn_state['last_event_at'])
                            : ($pinterest_conn_state['last_test_at'] ? $pinterest_conn_state['last_test_at'] : $pinterest_conn_state['last_event_at']);
                    ?>
                        <div class="alert alert-success d-flex align-items-center mb-3" role="status">
                            <span class="badge bg-success me-2">&bull;</span>
                            <div>
                                <strong><?php echo esc_html__('Server-side ready.', 'unipixel'); ?></strong>
                                <?php if ($pin_freshness_at) : ?>
                                    <small class="d-block">
                                        <?php echo esc_html(sprintf(
                                            /* translators: %s is a human time diff, e.g. "5 minutes" */
                                            __('Verified %s ago.', 'unipixel'),
                                            human_time_diff($pin_freshness_at, time())
                                        )); ?>
                                    </small>
                                <?php endif; ?>
                                <small class="d-block mt-1">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#pinterest-setup-wizard-modal"><?php echo esc_html__('Re-walk server-side setup', 'unipixel'); ?></a>
                                </small>
                            </div>
                        </div>
                    <?php endif; ?>

                    <p class="small text-muted mb-3"><?php echo esc_html__('More accurate, bypasses ad blockers, dedupes with client-side. Requires an Ad Account ID and a Conversion Access Token on top of your Tag ID.', 'unipixel'); ?></p>

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
                            <label for="additional_id" class="col-sm-3 col-form-label">
                                <?php echo esc_html__('Ad Account ID:', 'unipixel'); ?>
                                <?php echo wp_kses(unipixel_get_help_icon('Pinterest_AdAccountId'), $icon_allow); ?>
                            </label>
                            <div class="col-sm-9">
                                <input type="text" id="additional_id" name="additional_id" class="form-control" value="<?php echo esc_attr($additional_id); ?>" autocomplete="off">
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="access_token" class="col-sm-3 col-form-label">
                                <?php echo esc_html__('Conversion Access Token:', 'unipixel'); ?>
                                <?php echo wp_kses(unipixel_get_help_icon('Pinterest_AccessToken'), $icon_allow); ?>
                            </label>
                            <div class="col-sm-9">
                                <input type="password" id="access_token" name="access_token" class="form-control" value="<?php echo esc_attr($access_token); ?>" autocomplete="new-password" readonly>
                            </div>
                        </div>

                        <div class="mb-3 row" id="pinterest-test-connection-row" style="display:none;">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-9">
                                <button type="button" id="pinterest-test-connection-btn" class="btn btn-outline-primary">
                                    <?php echo esc_html__('Test Connection', 'unipixel'); ?>
                                </button>
                                <div id="pinterest-test-connection-result" class="mt-2" role="status" style="display:none;"></div>
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

        <?php /* Pinterest Setup Wizard Modal (token-acquisition-ux Phase 8) */ ?>
        <div class="modal fade" id="pinterest-setup-wizard-modal" tabindex="-1" aria-labelledby="pinterestSetupWizardLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="pinterestSetupWizardLabel"><?php echo esc_html__('Set up server-side tracking for Pinterest', 'unipixel'); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo esc_attr__('Close', 'unipixel'); ?>"></button>
                    </div>
                    <div class="modal-body">

                        <div class="pinterest-wizard-step" data-step="1">
                            <h6 class="mb-3"><?php echo esc_html__("What you'll achieve", 'unipixel'); ?></h6>
                            <p><?php echo esc_html__("By the end of this guide, server-side events will fire to Pinterest via the Conversions API on top of any client-side tracking. You'll have your Tag ID, Ad Account ID, and a Conversion Access Token pasted into UniPixel, and a successful Test Connection confirming the wiring.", 'unipixel'); ?></p>
                            <p class="mt-3 mb-0"><small><a href="#" class="pinterest-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="pinterest-wizard-step d-none" data-step="2">
                            <h6 class="mb-3"><?php echo esc_html__('Prerequisites', 'unipixel'); ?></h6>
                            <p><?php echo esc_html__('You need: a Pinterest Business Account, an Ad Account inside that business, and a Pinterest Tag created in the Ad Account. If you don\'t have these yet, set them up in Pinterest Business Hub first, then come back.', 'unipixel'); ?></p>
                            <p class="mt-3 mb-0"><small><a href="#" class="pinterest-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="pinterest-wizard-step d-none" data-step="3">
                            <h6 class="mb-3"><?php echo esc_html__('What to ignore', 'unipixel'); ?></h6>
                            <p><?php echo esc_html__('Pinterest Ads has a lot of options. For UniPixel server-side setup, you can safely ignore:', 'unipixel'); ?></p>
                            <ul>
                                <li><?php echo esc_html__('Catalog setup. Separate product for shopping ads, not needed here.', 'unipixel'); ?></li>
                                <li><?php echo esc_html__('Audience builder.', 'unipixel'); ?></li>
                                <li><?php echo esc_html__('Pinterest partner integrations. UniPixel IS your integration.', 'unipixel'); ?></li>
                                <li><?php echo esc_html__('Advantage+ shopping campaign prompts.', 'unipixel'); ?></li>
                            </ul>
                            <p><?php echo esc_html__('You only need: your Pinterest Tag ID, your Ad Account ID, and a Conversion Access Token.', 'unipixel'); ?></p>
                            <p class="small text-muted"><strong><?php echo esc_html__('Pinterest quirk worth knowing (PIN-001):', 'unipixel'); ?></strong> <?php echo esc_html__('Pinterest only accepts 6 custom event-tier names: custom, lead, search, signup, view_category, watch_video. Anything else gets silently dropped. UniPixel\'s Event Manager constrains this for you.', 'unipixel'); ?></p>
                            <p class="mt-3 mb-0"><small><a href="#" class="pinterest-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="pinterest-wizard-step d-none" data-step="4">
                            <h6 class="mb-3"><?php echo esc_html__('Get your credentials', 'unipixel'); ?></h6>
                            <p><?php echo esc_html__("You'll need a Pinterest Tag and an Ad Account in Pinterest Ads Manager. If you don't have them, the Open Pinterest Ads Manager link below has Create buttons for both.", 'unipixel'); ?></p>
                            <p><?php echo esc_html__('You need three things from Pinterest: your Tag ID, your Ad Account ID, and a Conversion Access Token.', 'unipixel'); ?></p>

                            <div class="mb-3 p-3 border rounded">
                                <ol class="mb-2 ps-4">
                                    <li><?php echo esc_html__('Open Pinterest Ads Manager. Go to Conversions → Manage tags.', 'unipixel'); ?></li>
                                    <li><?php echo esc_html__('Pick your Web Tag (or create one). The Tag ID appears at the top of the tag detail page. Copy it.', 'unipixel'); ?></li>
                                    <li><?php echo esc_html__('Your Ad Account ID is in the URL of the Ads Manager (a long numeric string), or under Business → Ad accounts in Pinterest Business Hub.', 'unipixel'); ?></li>
                                    <li><?php echo esc_html__('Go to Conversions → Access Tokens (or Settings → Conversion API token). Click Create.', 'unipixel'); ?></li>
                                    <li><?php echo esc_html__('Copy the token immediately (Pinterest will only show it once).', 'unipixel'); ?></li>
                                </ol>
                                <a href="https://ads.pinterest.com/" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> <?php echo esc_html__('Open Pinterest Ads Manager', 'unipixel'); ?>
                                </a>
                            </div>

                            <p class="mb-0"><?php echo esc_html__('Keep all three copied somewhere safe, then continue.', 'unipixel'); ?></p>
                            <p class="mt-3 mb-0"><small><a href="#" class="pinterest-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="pinterest-wizard-step d-none" data-step="5">
                            <h6 class="mb-3"><?php echo esc_html__('Paste your credentials', 'unipixel'); ?></h6>
                            <div class="mb-3">
                                <label for="wizard-pinterest-tag-id" class="form-label"><?php echo esc_html__('Pinterest Tag ID', 'unipixel'); ?></label>
                                <input type="text" class="form-control" id="wizard-pinterest-tag-id" autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label for="wizard-pinterest-ad-account-id" class="form-label"><?php echo esc_html__('Ad Account ID', 'unipixel'); ?></label>
                                <input type="text" class="form-control" id="wizard-pinterest-ad-account-id" autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label for="wizard-pinterest-access-token" class="form-label"><?php echo esc_html__('Conversion Access Token', 'unipixel'); ?></label>
                                <input type="password" class="form-control" id="wizard-pinterest-access-token" autocomplete="off">
                            </div>
                            <button type="button" class="btn btn-primary" id="wizard-pinterest-save-btn"><?php echo esc_html__('Save and continue', 'unipixel'); ?></button>
                            <div id="wizard-pinterest-save-result" class="mt-3" role="status" style="display:none;"></div>
                            <p class="mt-3 mb-0"><small><a href="#" class="pinterest-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="pinterest-wizard-step d-none" data-step="6">
                            <h6 class="mb-3"><?php echo esc_html__('Test the connection', 'unipixel'); ?></h6>
                            <p><?php echo esc_html__("Now let's verify the connection works. UniPixel will format-check your credentials, validate the token against Pinterest's user_account endpoint, and confirm the token has access to your Ad Account.", 'unipixel'); ?></p>
                            <p class="small text-muted"><strong><?php echo esc_html__('Note:', 'unipixel'); ?></strong> <?php echo esc_html__('This verifies token + ad-account access. Once real events start flowing, you can also confirm tag-level event delivery in Pinterest Ads Manager → Conversions → your Tag → Event activity.', 'unipixel'); ?></p>
                            <button type="button" class="btn btn-primary" id="wizard-pinterest-test-connection-btn"><?php echo esc_html__('Test Connection', 'unipixel'); ?></button>
                            <div id="wizard-pinterest-test-connection-result" class="mt-3" role="status" style="display:none;"></div>
                            <p class="mt-3 mb-0"><small><a href="#" class="pinterest-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="pinterest-wizard-step d-none" data-step="7">
                            <h6 class="mb-3"><?php echo esc_html__("You're set up", 'unipixel'); ?></h6>
                            <p><?php echo esc_html__('Server-side events will start flowing on the next user action on your site. Open Pinterest Ads Manager → Conversions → your Tag → Event activity to watch them land.', 'unipixel'); ?></p>
                            <button type="button" class="btn btn-primary" id="wizard-pinterest-done-btn"><?php echo esc_html__('Close', 'unipixel'); ?></button>
                        </div>

                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <span class="text-muted small"><?php echo esc_html__('Step', 'unipixel'); ?> <span class="pinterest-wizard-current-step">1</span> <?php echo esc_html__('of', 'unipixel'); ?> 7</span>
                        <div>
                            <button type="button" class="btn btn-secondary pinterest-wizard-back" disabled><?php echo esc_html__('Back', 'unipixel'); ?></button>
                            <button type="button" class="btn btn-primary pinterest-wizard-next"><?php echo esc_html__('Next', 'unipixel'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
