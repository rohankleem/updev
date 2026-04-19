<?php
if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Render the General Settings page.
 */
function unipixel_page_general_settings()
{
    // Set default logging options.
    $loggingDefaults = array(
        'enableLogging_Admin'               => false,
        'enableLogging_InitiateEvents'      => false,
        'enableLogging_SendEvents'          => false,
        'enableGoogleDebugViewClientSide'   => false,
        'enableGoogleDebugViewServerSide'   => false,
    );

    // Retrieve logging options from the database; if none exist, use defaults.
    $loggingOptions = get_option('unipixel_logging_options', false);
    if (false === $loggingOptions) {
        $loggingOptions = $loggingDefaults;
        update_option('unipixel_logging_options', $loggingOptions);
    }

    $wooEventLabelMap = [
        'AddToCart'        => 'Meta',
        'add_to_cart'      => 'Google',
        'InitiateCheckout' => 'Meta',
        'begin_checkout'   => 'Google',
        'Purchase'         => 'Meta',
        'purchase'         => 'Google',
        'ViewContent'      => 'Meta',
        'view_item'        => 'Google',
    ];
?>
    <div class="UniPixelShell position-relative">
        <div class="d-flex justify-content-between align-items-start">
            <h1 class="mb-0">General Settings</h1>
            <?php unipixel_render_feedback_buttons(); ?>
        </div>

        <!-- Feedback Message Container -->
        <div id="general-settings-feedback-message" class="alert" role="alert" style="display: none;"></div>

        <!-- Loader Element with Spinner -->
        <div id="general-settings-form-loader" class="d-flex justify-content-center align-items-center position-absolute w-100 h-100 d-none" style="top: 0; left: 0; z-index:1000;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>



        <form id="general-settings-form" class="form-horizontal">

            <?php
            $advancedMatchingEnabled = get_option('unipixel_advanced_matching_enabled', true);
            ?>

            <hr />
            <h2><i class="fa-solid fa-shield-halved"></i> Advanced Matching</h2>
            <p><small>Sends hashed user data (email, phone, name, address) alongside events to Meta, TikTok and Pinterest to improve Event Match Quality. Data comes from order billing details or logged-in user profiles where available.
                <a href="https://buildio.dev/unipixel-docs/advanced-matching-setting-with-unipixel/" target="_blank">Learn more</a></small></p>

            <div class="mb-3 row">
                <div class="col-3 col-sm-3">
                    <label for="advanced_matching_enabled" class="form-check-label">
                        Enable Advanced Matching
                    </label>
                </div>
                <div class="col-9 col-sm-9">
                    <div class="form-check form-switch">
                        <input type="checkbox"
                            id="advanced_matching_enabled"
                            name="advanced_matching_enabled"
                            value="1"
                            class="form-check-input"
                            <?php checked($advancedMatchingEnabled, true); ?>>
                    </div>
                </div>
            </div>

            <?php
            $dbstoreSettings = unipixel_get_dbstore_event_settings();
            ?>

            <hr />
            <h2><i class="fa-solid fa-database"></i> Store Records of Events to Database</h2>
            <p><small>Choose which types of events should be persisted to the database for review, insights and debugging. Covers all events from all visitors.</small></p>

            <!-- PageView Events -->
            <div class="mb-3 row">
                <div class="col-3 col-sm-3">
                    <label for="dbstore_pageview_events" class="form-check-label">
                        Store PageView Events
                    </label>
                </div>
                <div class="col-9 col-sm-9">
                    <div class="form-check form-switch">
                        <input type="checkbox"
                            id="dbstore_pageview_events"
                            name="dbstore_pageview_events"
                            value="1"
                            class="form-check-input"
                            <?php checked($dbstoreSettings['dbstore_pageview_events'], true); ?>>
                    </div>
                </div>
            </div>

            <!-- Custom Events -->
            <div class="mb-3 row">
                <div class="col-3 col-sm-3">
                    <label for="dbstore_custom_events" class="form-check-label">
                        Store Custom Events
                    </label>
                </div>
                <div class="col-9 col-sm-9">
                    <div class="form-check form-switch">
                        <input type="checkbox"
                            id="dbstore_custom_events"
                            name="dbstore_custom_events"
                            value="1"
                            class="form-check-input"
                            <?php checked($dbstoreSettings['dbstore_custom_events'], true); ?>>
                    </div>
                </div>
            </div>

            <!-- WooCommerce Events -->

            <div><b>Store WooCommerce Events</b></div>
            <p><small>Select which WooCommerce events should be stored in the database. These may include variants like Meta vs Google naming.</small></p>

            <div class="row">
                <?php foreach ($dbstoreSettings['dbstore_woocommerce_events'] as $eventKey => $isEnabled): ?>
                    <div class="col-12 col-md-4 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="form-check form-switch">
                                <input type="checkbox"
                                    id="woo_<?php echo esc_attr($eventKey); ?>"
                                    name="dbstore_woocommerce_events[<?php echo esc_attr($eventKey); ?>]"
                                    value="1"
                                    class="form-check-input"
                                    <?php checked($isEnabled, true); ?>>
                            </div>
                            <label for="woo_<?php echo esc_attr($eventKey); ?>" class="form-check-label ms-2">
                                <?php
                                echo esc_html($eventKey);
                                if (!empty($wooEventLabelMap[$eventKey])) {
                                    echo ' <small class="text-muted">(' . esc_html($wooEventLabelMap[$eventKey]) . ')</small>';
                                }
                                ?>
                            </label>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>




            <div class="browserConsoleSettings obsolete d-none">



                <hr />
                <h2>Log Event Info to the Browser Console</h2>
                <p><small>Toggle these settings to get information on what's happening behind the scenes. Several processes can be viewed in your Browser's "Console Log"</small></p>
                <!-- Admin Logging Option -->
                <div class="mb-3 row d-none">
                    <div class="col-3 col-sm-3">
                        <label for="enableLogging_Admin" class="form-check-label">
                            Admin Logging <?php echo unipixel_get_help_icon("Logging_Admin"); ?>
                        </label>
                    </div>
                    <div class="col-9 col-sm-9">
                        <div class="form-check form-switch">
                            <input type="checkbox"
                                id="enableLogging_Admin"
                                name="enableLogging_Admin"
                                value="1"
                                class="form-check-input"
                                <?php checked($loggingOptions['enableLogging_Admin'], true); ?>>
                        </div>
                    </div>
                </div>
                <!-- Initiate Events Logging Option -->
                <div class="mb-3 row">
                    <div class="col-3 col-sm-3">
                        <label for="enableLogging_InitiateEvents" class="form-check-label">
                            See Initiation (Setup) of Custom Events <?php echo unipixel_get_help_icon("Logging_InitiateEvents"); ?>
                        </label>
                    </div>
                    <div class="col-9 col-sm-9">
                        <div class="form-check form-switch">
                            <input type="checkbox"
                                id="enableLogging_InitiateEvents"
                                name="enableLogging_InitiateEvents"
                                value="1"
                                class="form-check-input"
                                <?php checked($loggingOptions['enableLogging_InitiateEvents'], true); ?>>
                        </div>
                    </div>
                </div>
                <!-- Send Events Logging Option -->
                <div class="mb-3 row">
                    <div class="col-3 col-sm-3">
                        <label for="enableLogging_SendEvents" class="form-check-label">
                            See Details of Events Sent <?php echo unipixel_get_help_icon("Logging_SendEvents"); ?>
                        </label>
                    </div>
                    <div class="col-9 col-sm-9">
                        <div class="form-check form-switch">
                            <input type="checkbox"
                                id="enableLogging_SendEvents"
                                name="enableLogging_SendEvents"
                                value="1"
                                class="form-check-input"
                                <?php checked($loggingOptions['enableLogging_SendEvents'], true); ?>>
                        </div>
                    </div>
                </div>
            </div>


            <hr />
            <h2><i class="fa-brands fa-google"></i> Google's DebugView</h2>
            <p><small>Google allows you to see incoming events in real time if you tag events with a "debug" flag. You can setup your outgoing Google events to do this below.</small></p>
            <!-- DebugView Toggle -->
            <div class="mb-3 row">
                <div class="col-3 col-sm-3">
                    <label for="enableGoogleDebugViewClientSide" class="form-check-label">
                        Enable Google DebugView (Client Side Events) <?php echo unipixel_get_help_icon("DebugViewClient"); ?> <br />
                        <small>Note for GTM: <?php echo unipixel_get_help_icon("Google_Debug_View_ClientSide_GtmNote"); ?></small>
                    </label>
                </div>
                <div class="col-9 col-sm-9">
                    <div class="form-check form-switch">
                        <input
                            type="checkbox"
                            id="enableGoogleDebugViewClientSide"
                            name="enableGoogleDebugViewClientSide"
                            value="1"
                            class="form-check-input"
                            <?php checked($loggingOptions['enableGoogleDebugViewClientSide'], true); ?>>
                    </div>
                </div>
            </div>
            <div class="mb-3 row">
                <div class="col-3 col-sm-3">
                    <label for="enableGoogleDebugViewServerSide" class="form-check-label">
                        Enable Google DebugView (Server Side Events) <?php echo unipixel_get_help_icon("DebugViewServer"); ?>
                    </label>
                </div>
                <div class="col-9 col-sm-9">
                    <div class="form-check form-switch">
                        <input
                            type="checkbox"
                            id="enableGoogleDebugViewServerSide"
                            name="enableGoogleDebugViewServerSide"
                            value="1"
                            class="form-check-input"
                            <?php checked($loggingOptions['enableGoogleDebugViewServerSide'], true); ?>>
                    </div>
                </div>
            </div>
            <!-- Submit Button -->
            <hr />
            <div class="mb-3 row">
                <div class="col-sm-12">
                    <input type="submit" value="Save Settings" class="btn btn-primary">
                </div>
            </div>
        </form>
    </div>
<?php
}
?>