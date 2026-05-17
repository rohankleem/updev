<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Microsoft → Tag/Connection Setup page
 */
function unipixel_page_microsoft_setup()
{
    global $wpdb;

    $platform_table     = $wpdb->prefix . 'unipixel_platform_settings';
    $platformId         = 5;
    $platformName       = 'Microsoft';
    $pixelNameFriendly  = 'Microsoft UET Tag';

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
        $serverside_global_enabled  = isset($platform['serverside_global_enabled']) ? (int) $platform['serverside_global_enabled'] : 0;
    } else {
        $platform_id                = 0;
        $pixel_id                   = '';
        $access_token               = '';
        $platform_enabled           = 1;
        $serverside_global_enabled  = 0;
    }

    // Allowlist for inline help icons HTML
    $icon_allow = unipixel_get_popover_allowlist();

    // Two-section layout. Note: Microsoft setup has no Pixel Setting radio (no
    // client-side script-delivery choice). Just the UET Tag ID for client-side.
    $client_side_active   = (!empty($pixel_id) && $platform_enabled === 1);
    $microsoft_conn_state = unipixel_get_platform_connection_state($platformId);
?>
    <div class="UniPixelShell position-relative" data-platform="microsoft">

        <div class="UniPixelSpinner d-none">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden"><?php echo esc_html__('Loading…', 'unipixel'); ?></span>
            </div>
        </div>

        <?php unipixel_render_platform_header_nav('microsoft', 'setup'); ?>

        <h1 class="mb-0">Tag <?php echo esc_html__('Setup', 'unipixel'); ?></h1>
        <p>
            <small>
                <?php echo esc_html__('Configure your connection and core settings for', 'unipixel'); ?>
                <?php echo esc_html($platformName); ?>.
            </small>
        </p>

        <!-- Feedback message container -->
        <div id="platform-settings-feedback-message" class="alert" role="alert" style="display:none;"></div>

        <!-- Platform (Tag/Connection) form -->
        <form id="platform-settings-form" class="form-horizontal" autocomplete="off">
            <input type="hidden" id="platform_id" name="platform_id" value="<?php echo esc_attr((string)$platform_id); ?>">

            <?php /* Anti-autofill honeypot: see page-meta-setup.php for rationale. */ ?>
            <div class="unipixel-autofill-honeypot" aria-hidden="true" style="position:absolute;height:0;width:0;overflow:hidden;opacity:0;">
                <input type="text" name="__autofill_decoy_user" autocomplete="username" tabindex="-1">
                <input type="password" name="__autofill_decoy_pass" autocomplete="new-password" tabindex="-1">
            </div>

            <!-- Enabled toggle -->
            <div class="mb-3 row">
                <div class="col-12 col-sm-3">
                    <label class="form-check-label" for="platform_enabled">
                        <?php echo esc_html__('Send tracking to Microsoft?', 'unipixel'); ?>
                        <?php echo wp_kses(unipixel_get_help_icon('Microsoft_Enabled'), $icon_allow); ?>
                    </label>
                </div>
                <div class="col-12 col-sm-9">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch"
                            id="platform_enabled" name="platform_enabled"
                            value="1" <?php checked($platform_enabled, 1); ?>>
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
                    <p class="small text-muted mb-3"><?php echo esc_html__('The fastest way to start tracking. Add your UET Tag ID and events fire via the UET tag in the browser.', 'unipixel'); ?></p>

                    <?php if (empty($pixel_id)) : ?>
                        <div class="alert alert-info py-2 small mb-3" role="status">
                            <strong><?php echo esc_html__('Start simple.', 'unipixel'); ?></strong>
                            <?php echo esc_html__('Add your UET Tag ID below to start tracking. Find it in Microsoft Advertising under Tools → UET tag.', 'unipixel'); ?>
                            <a href="https://ui.ads.microsoft.com/" target="_blank" rel="noopener noreferrer" class="ms-1">
                                <?php echo esc_html__('Open Microsoft Advertising', 'unipixel'); ?> <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                            <div class="mt-1 small text-muted"><?php echo esc_html__('No UET tag yet? The Tools → UET tag page has a Create button.', 'unipixel'); ?></div>
                        </div>
                    <?php endif; ?>

                    <div class="mb-0 row">
                        <label for="pixel_id" class="col-sm-3 col-form-label">
                            <?php echo esc_html__('UET Tag ID:', 'unipixel'); ?>
                            <?php echo wp_kses(unipixel_get_help_icon('Microsoft_PixelId'), $icon_allow); ?>
                        </label>
                        <div class="col-sm-9">
                            <input type="text" id="pixel_id" name="pixel_id" class="form-control"
                                value="<?php echo esc_attr($pixel_id); ?>" autocomplete="off" required>
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
                        <span class="text-muted small ms-2"><?php echo esc_html__('(recommended, if available)', 'unipixel'); ?></span>
                    </div>

                    <?php if ($microsoft_conn_state['state'] === 'not_started') : ?>
                        <div class="alert alert-secondary d-flex align-items-center mb-3" role="status">
                            <span class="badge bg-secondary me-2">&bull;</span>
                            <div class="flex-grow-1">
                                <strong><?php echo esc_html__('Server-side tracking not set up.', 'unipixel'); ?></strong>
                                <small class="d-block"><?php echo esc_html__('Enable Server-Side Tracking and add a CAPI Access Token below, or use the guided walkthrough. Note: Microsoft CAPI tokens are gated. You may need to contact your Microsoft Ads account manager to get one.', 'unipixel'); ?></small>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm ms-3" data-bs-toggle="modal" data-bs-target="#microsoft-setup-wizard-modal">
                                <?php echo esc_html__('Start server-side setup', 'unipixel'); ?>
                            </button>
                        </div>
                    <?php elseif ($microsoft_conn_state['state'] === 'pasted_unverified') : ?>
                        <div class="alert alert-warning d-flex align-items-center mb-3" role="status">
                            <span class="badge bg-warning text-dark me-2">&bull;</span>
                            <div>
                                <strong><?php echo esc_html__('Server-side not yet verified.', 'unipixel'); ?></strong>
                                <small class="d-block"><?php echo esc_html__('Credentials saved. Click Test Connection below to verify, or wait for server events to confirm setup.', 'unipixel'); ?></small>
                                <small class="d-block mt-1">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#microsoft-setup-wizard-modal"><?php echo esc_html__('Re-walk server-side setup', 'unipixel'); ?></a>
                                </small>
                            </div>
                        </div>
                    <?php else :
                        $ms_freshness_at = ($microsoft_conn_state['last_test_at'] && $microsoft_conn_state['last_event_at'])
                            ? max($microsoft_conn_state['last_test_at'], $microsoft_conn_state['last_event_at'])
                            : ($microsoft_conn_state['last_test_at'] ? $microsoft_conn_state['last_test_at'] : $microsoft_conn_state['last_event_at']);
                    ?>
                        <div class="alert alert-success d-flex align-items-center mb-3" role="status">
                            <span class="badge bg-success me-2">&bull;</span>
                            <div>
                                <strong><?php echo esc_html__('Server-side ready.', 'unipixel'); ?></strong>
                                <?php if ($ms_freshness_at) : ?>
                                    <small class="d-block">
                                        <?php echo esc_html(sprintf(
                                            /* translators: %s is a human time diff, e.g. "5 minutes" */
                                            __('Verified %s ago.', 'unipixel'),
                                            human_time_diff($ms_freshness_at, time())
                                        )); ?>
                                    </small>
                                <?php endif; ?>
                                <small class="d-block mt-1">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#microsoft-setup-wizard-modal"><?php echo esc_html__('Re-walk server-side setup', 'unipixel'); ?></a>
                                </small>
                            </div>
                        </div>
                    <?php endif; ?>

                    <p class="small text-muted mb-3"><?php echo esc_html__('More accurate, bypasses ad blockers, dedupes with client-side via event IDs. Requires a CAPI Access Token on top of your UET Tag ID. Microsoft CAPI is gated by Microsoft, so you may need to request access from your account manager.', 'unipixel'); ?></p>

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
                                <?php echo esc_html__('CAPI Access Token:', 'unipixel'); ?>
                                <?php echo wp_kses(unipixel_get_help_icon('Microsoft_AccessToken'), $icon_allow); ?>
                            </label>
                            <div class="col-sm-9">
                                <input type="password" id="access_token" name="access_token" class="form-control" value="<?php echo esc_attr($access_token); ?>" autocomplete="new-password">
                                <small class="form-text text-muted"><?php echo esc_html__('Obtain your token from the Microsoft Advertising UI under "Use Conversions API", or contact your account manager.', 'unipixel'); ?></small>
                            </div>
                        </div>

                        <div class="mb-3 row" id="microsoft-test-connection-row" style="display:none;">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-9">
                                <button type="button" id="microsoft-test-connection-btn" class="btn btn-outline-primary">
                                    <?php echo esc_html__('Test Connection', 'unipixel'); ?>
                                </button>
                                <div id="microsoft-test-connection-result" class="mt-2" role="status" style="display:none;"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Submit -->
            <div class="mb-3 row">
                <div class="col-sm-9 offset-sm-3">
                    <input type="submit"
                        value="<?php echo esc_attr__('Update Settings', 'unipixel'); ?>"
                        id="btnUniPixelUpdatePlatformSettings"
                        name="btnUniPixelUpdatePlatformSettings"
                        class="btn btn-primary">
                </div>
            </div>
        </form>

        <?php /* Microsoft Setup Wizard Modal (token-acquisition-ux Phase 8) */ ?>
        <div class="modal fade" id="microsoft-setup-wizard-modal" tabindex="-1" aria-labelledby="microsoftSetupWizardLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="microsoftSetupWizardLabel"><?php echo esc_html__('Set up server-side tracking for Microsoft', 'unipixel'); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo esc_attr__('Close', 'unipixel'); ?>"></button>
                    </div>
                    <div class="modal-body">
                        <div class="microsoft-wizard-step" data-step="1">
                            <h6 class="mb-3"><?php echo esc_html__("What you'll achieve", 'unipixel'); ?></h6>
                            <p><?php echo esc_html__("By the end of this guide, server-side events will fire to Microsoft Advertising via the Conversions API (CAPI) on top of any client-side tracking. You'll have your UET Tag ID and a CAPI Access Token pasted into UniPixel, and a successful Test Connection confirming the wiring.", 'unipixel'); ?></p>
                            <p class="mt-3 mb-0"><small><a href="#" class="microsoft-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="microsoft-wizard-step d-none" data-step="2">
                            <h6 class="mb-3"><?php echo esc_html__('Prerequisites', 'unipixel'); ?></h6>
                            <p><?php echo esc_html__('You need: a Microsoft Advertising account with an active UET Tag, AND a CAPI Access Token. Microsoft CAPI is gated. Most accounts don\'t get a CAPI token automatically. You may need to request access from your Microsoft Advertising account manager. If you only have a UET Tag (no CAPI token), you can still do client-side tracking with UniPixel; just skip this wizard and add only the UET Tag ID in the main form.', 'unipixel'); ?></p>
                            <p class="mt-3 mb-0"><small><a href="#" class="microsoft-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="microsoft-wizard-step d-none" data-step="3">
                            <h6 class="mb-3"><?php echo esc_html__('What to ignore', 'unipixel'); ?></h6>
                            <p><?php echo esc_html__('Microsoft Advertising has a lot of features. For UniPixel server-side setup, you can safely ignore:', 'unipixel'); ?></p>
                            <ul>
                                <li><?php echo esc_html__('Microsoft Audience Network targeting setup.', 'unipixel'); ?></li>
                                <li><?php echo esc_html__('Smart Shopping campaign suggestions.', 'unipixel'); ?></li>
                                <li><?php echo esc_html__('LinkedIn audience integration.', 'unipixel'); ?></li>
                                <li><?php echo esc_html__('Microsoft Ads agency partnerships and third-party integrations. UniPixel IS your integration.', 'unipixel'); ?></li>
                            </ul>
                            <p><?php echo esc_html__('You only need: your UET Tag ID, and a CAPI Access Token tied to that account.', 'unipixel'); ?></p>
                            <p class="mt-3 mb-0"><small><a href="#" class="microsoft-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="microsoft-wizard-step d-none" data-step="4">
                            <h6 class="mb-3"><?php echo esc_html__('Get your credentials', 'unipixel'); ?></h6>
                            <p><?php echo esc_html__("You'll need a UET tag in Microsoft Advertising. If you don't have one, the Open Microsoft Advertising link below has a Create button under Tools → UET tag.", 'unipixel'); ?></p>
                            <p><?php echo esc_html__('You need two things from Microsoft: your UET Tag ID and a CAPI Access Token.', 'unipixel'); ?></p>

                            <div class="mb-3 p-3 border rounded">
                                <p class="mb-2"><strong><?php echo esc_html__('UET Tag ID', 'unipixel'); ?></strong></p>
                                <p class="mb-2"><?php echo esc_html__('Open Microsoft Advertising → Tools → UET tag. The Tag ID is a 7-9 digit number shown at the top of the tag detail. Copy it.', 'unipixel'); ?></p>
                                <a href="https://ui.ads.microsoft.com/" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> <?php echo esc_html__('Open Microsoft Advertising', 'unipixel'); ?>
                                </a>
                            </div>

                            <div class="mb-3 p-3 border rounded">
                                <p class="mb-2"><strong><?php echo esc_html__('CAPI Access Token', 'unipixel'); ?></strong></p>
                                <p class="mb-2"><?php echo esc_html__('In the same UET Tag detail view, look for "Use Conversions API". If you see "Generate token", click it and copy. If you do not see this option, your account is not yet enabled for CAPI. Request access through your Microsoft Advertising account manager.', 'unipixel'); ?></p>
                            </div>

                            <p class="mb-0"><?php echo esc_html__('Keep both copied somewhere safe, then continue.', 'unipixel'); ?></p>
                            <p class="mt-3 mb-0"><small><a href="#" class="microsoft-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="microsoft-wizard-step d-none" data-step="5">
                            <h6 class="mb-3"><?php echo esc_html__('Paste your credentials', 'unipixel'); ?></h6>
                            <div class="mb-3">
                                <label for="wizard-microsoft-tag-id" class="form-label"><?php echo esc_html__('UET Tag ID', 'unipixel'); ?></label>
                                <input type="text" class="form-control" id="wizard-microsoft-tag-id" autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label for="wizard-microsoft-access-token" class="form-label"><?php echo esc_html__('CAPI Access Token', 'unipixel'); ?></label>
                                <input type="password" class="form-control" id="wizard-microsoft-access-token" autocomplete="off">
                            </div>
                            <button type="button" class="btn btn-primary" id="wizard-microsoft-save-btn"><?php echo esc_html__('Save and continue', 'unipixel'); ?></button>
                            <div id="wizard-microsoft-save-result" class="mt-3" role="status" style="display:none;"></div>
                            <p class="mt-3 mb-0"><small><a href="#" class="microsoft-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="microsoft-wizard-step d-none" data-step="6">
                            <h6 class="mb-3"><?php echo esc_html__('Test the connection', 'unipixel'); ?></h6>
                            <p><?php echo esc_html__("Now let's verify the connection works. UniPixel will format-check your credentials and send a test event to Microsoft's CAPI endpoint, then parse Microsoft's response to give you specific feedback if anything is wrong.", 'unipixel'); ?></p>
                            <button type="button" class="btn btn-primary" id="wizard-microsoft-test-connection-btn"><?php echo esc_html__('Test Connection', 'unipixel'); ?></button>
                            <div id="wizard-microsoft-test-connection-result" class="mt-3" role="status" style="display:none;"></div>
                            <p class="mt-3 mb-0"><small><a href="#" class="microsoft-wizard-looks-different"><?php echo esc_html__('Looks different in your dashboard? Tell us.', 'unipixel'); ?></a></small></p>
                        </div>

                        <div class="microsoft-wizard-step d-none" data-step="7">
                            <h6 class="mb-3"><?php echo esc_html__("You're set up", 'unipixel'); ?></h6>
                            <p><?php echo esc_html__('Server-side events will start flowing on the next user action on your site. Open Microsoft Advertising → Tools → UET tag → your tag to watch them land.', 'unipixel'); ?></p>
                            <button type="button" class="btn btn-primary" id="wizard-microsoft-done-btn"><?php echo esc_html__('Close', 'unipixel'); ?></button>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <span class="text-muted small"><?php echo esc_html__('Step', 'unipixel'); ?> <span class="microsoft-wizard-current-step">1</span> <?php echo esc_html__('of', 'unipixel'); ?> 7</span>
                        <div>
                            <button type="button" class="btn btn-secondary microsoft-wizard-back" disabled><?php echo esc_html__('Back', 'unipixel'); ?></button>
                            <button type="button" class="btn btn-primary microsoft-wizard-next"><?php echo esc_html__('Next', 'unipixel'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
