<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Meta → Events page (PageView / WooCommerce / Custom)
 * Flat layout, single form + single "Update All" button.
 */
function unipixel_page_meta_events()
{
    global $wpdb;

    $platform_table     = $wpdb->prefix . 'unipixel_platform_settings';
    $woo_table          = $wpdb->prefix . 'unipixel_woocomm_event_settings';
    $platformId         = 1; // Meta
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
    $platformSettings = $wpdb->get_row($query, ARRAY_A);

    // Extract / defaults
    if ($platformSettings) {
        $pageview_send_serverside          = isset($platformSettings['pageview_send_serverside']) ? (int) $platformSettings['pageview_send_serverside'] : 1;
        $pageview_send_clientside          = isset($platformSettings['pageview_send_clientside']) ? (int) $platformSettings['pageview_send_clientside'] : 1;
        $pageview_send_server_log_response = isset($platformSettings['send_server_log_response']) ? (int) $platformSettings['send_server_log_response'] : 1;
    } else {
        $pageview_send_serverside          = 0;
        $pageview_send_clientside          = 0;
        $pageview_send_server_log_response = 0;
    }

    // Woo events
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $woo_events = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM %i WHERE platform_id = %d", $woo_table, $platformId),
        ARRAY_A
    );

    // Allowlist for inline help icons HTML
    $icon_allow = unipixel_get_popover_allowlist();
?>
    <div class="UniPixelShell position-relative" data-platform="meta">

        <div class="UniPixelSpinner d-none">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden"><?php echo esc_html__('Loading…', 'unipixel'); ?></span>
            </div>
        </div>

        <?php unipixel_render_platform_header_nav('meta', "events"); ?>

        <h1 class="mb-0"><?php echo esc_html($platformName); ?> <?php echo esc_html__('Events', 'unipixel'); ?></h1>
        <p><small><?php echo esc_html__('Setup the events you want to track at', 'unipixel'); ?> <?php echo esc_html($platformName); ?>.</small></p>


        <div class="p-3 rounded bg-light-light-green mb-4">
            <button type="button" id="btnApplyRecommended" class="btn btn-primary me-2">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Apply Recommended Settings for <?php echo esc_html($platformName); ?>
            </button>

            <a href="#" id="btnRecommendedHelp" class="small text-decoration-none d-block mt-2">
                <?php echo esc_html__('What do these recommended settings mean?', 'unipixel'); ?>
            </a>
        </div>

        <!-- Feedback container (unified) used by AJAX -->
        <div id="event-settings-feedback-message" class="alert" role="alert" style="display:none;"></div>

        <!-- SINGLE WRAPPING FORM -->
        <form id="unipixel-events-all-form" class="position-relative">
            <input type="hidden" id="platform_id" name="platform_id" value="<?php echo esc_attr((string) $platformId); ?>">


            <!-- ============================= -->
            <!-- Section 1: PageView settings  -->
            <!-- ============================= -->
            <section id="meta-pageview-section" class="mb-5">
                <h2 class="mb-2"><?php echo esc_html__('PageView Event for', 'unipixel'); ?> <?php echo esc_html($platformName); ?></h2>
                <p class="mb-3">
                    <strong><?php echo esc_html__('page_view', 'unipixel'); ?></strong>
                    <?php echo esc_html__(' is a common standard event that can send rich tracking data to ', 'unipixel'); ?>
                    <?php echo esc_html($platformName); ?>
                    <?php echo esc_html__(' on the loading of each page.', 'unipixel'); ?>

                </p>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('Event', 'unipixel'); ?></th>
                            <th><?php echo esc_html__('Description', 'unipixel'); ?></th>
                            <th class="colSendClientSide">Send<br />Client-side <?php echo wp_kses(unipixel_get_help_icon('SendClientSide'), $icon_allow); ?></th>
                            <th class="colSendServerSide">Send<br />Server-side <?php echo wp_kses(unipixel_get_help_icon('SendServerSide'), $icon_allow); ?></th>
                            <th class="colLogResponse">Log Server-side Response <?php echo wp_kses(unipixel_get_help_icon('LogServerSideResponse'), $icon_allow); ?></th>
                            <th class="colDelete"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo esc_html__('PageView', 'unipixel'); ?></td>
                            <td><?php echo esc_html__('Send a page_view event with product details when a page is loaded.', 'unipixel'); ?></td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="pageview_send_clientside" name="pageview_send_clientside" value="1" <?php checked((int) $pageview_send_clientside, 1); ?>>
                                </div>
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="pageview_send_serverside" name="pageview_send_serverside" value="1" <?php checked((int) $pageview_send_serverside, 1); ?>>
                                </div>
                            </td>

                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="send_server_log_response" name="send_server_log_response" value="1" <?php checked((int) $pageview_send_server_log_response, 1); ?>>
                                </div>
                            </td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <hr />
            <!-- ===================================== -->
            <!-- Section 2: WooCommerce event toggles  -->
            <!-- ===================================== -->
            <section id="meta-woo-section" class="mb-5">
                <h2 class="mb-2"><?php echo esc_html__('WooCommerce Events for', 'unipixel'); ?> <?php echo esc_html($platformName); ?></h2>
                <p class="mb-3">
                    <?php echo esc_html__('Enable automatic tracking like AddToCart, InitiateCheckout, Purchase, etc. Product/order data is sent where relevant.', 'unipixel'); ?>
                </p>

                <table class="table table-striped" id="woo-events-table">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('eCommerce Event', 'unipixel'); ?></th>
                            <th><?php echo esc_html__('Trigger', 'unipixel'); ?></th>
                            <th><?php echo esc_html__('Event Name Sent to', 'unipixel'); ?> <?php echo esc_html($platformName); ?></th>
                            <th><?php echo esc_html__('Description', 'unipixel'); ?></th>
                            <th class="colSendClientSide">Send<br />Client-side <?php echo wp_kses(unipixel_get_help_icon('SendClientSide'), $icon_allow); ?></th>
                            <th class="colSendServerSide">Send<br />Server-side <?php echo wp_kses(unipixel_get_help_icon('SendServerSide'), $icon_allow); ?></th>
                            <th class="colLogResponse">Log Server-side Response <?php echo wp_kses(unipixel_get_help_icon('LogServerSideResponse'), $icon_allow); ?></th>
                            <th class="colDelete"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($woo_events)) : ?>
                            <?php foreach ($woo_events as $event) :
                                $sc = isset($event['send_client']) ? (int) $event['send_client'] : ((int) $event['event_enabled'] ? 1 : 0);
                                $ss = isset($event['send_server']) ? (int) $event['send_server'] : ((int) $event['event_enabled'] ? 1 : 0);
                            ?>
                                <tr data-id="<?php echo esc_attr($event['id']); ?>"
                                    data-event-platform-ref="<?php echo esc_attr($event['event_platform_ref']); ?>">
                                    <td><?php echo esc_html($event['event_local_ref']); ?></td>
                                    <td><?php echo esc_html__('Hook-based (automatic)', 'unipixel'); ?></td>
                                    <td><?php echo esc_html($event['event_platform_ref']); ?></td>
                                    <td><?php echo esc_html($event['event_description']); ?></td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                name="woo_send_client[<?php echo esc_attr($event['id']); ?>]"
                                                value="1" <?php checked($sc, 1); ?>>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                name="woo_send_server[<?php echo esc_attr($event['id']); ?>]"
                                                value="1" <?php checked($ss, 1); ?>>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                name="woo_event_logresponse[<?php echo esc_attr($event['id']); ?>]"
                                                value="1" <?php checked((int) $event['send_server_log_response'], 1); ?> />
                                        </div>
                                    </td>
                                    <td></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="7">
                                    <?php
                                    printf(
                                        /* translators: %s: Platform name */
                                        esc_html__('No WooCommerce events found for %s.', 'unipixel'),
                                        esc_html($platformName)
                                    );
                                    ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>

            <hr />
            <!-- ================================= -->
            <!-- Section 3: Custom events (table)  -->
            <!-- ================================= -->
            <section id="meta-custom-section" class="mb-3">
                <h2 class="mb-2"><?php echo esc_html__('Custom Events for', 'unipixel'); ?> <?php echo esc_html($platformName); ?></h2>
                <p class="mb-3">
                    <?php echo esc_html__('Manage your own Custom Events and track user actions (form submissions, clicks, visible elements). Define a CSS selector, trigger, and event name to send.', 'unipixel'); ?>
                    <br><small><?php echo esc_html__('Need help?', 'unipixel'); ?> <a href="https://unipixelhq.com/unipixel-docs/custom-event-tracking/" target="_blank" rel="noopener"><?php echo esc_html__('Learn how to set up custom events', 'unipixel'); ?></a></small>
                </p>

                <table id="event-settings-table" class="table">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('Element Reference', 'unipixel'); ?><br><small class="fw-normal"><?php echo esc_html__('Element to track e.g. #signupForm, .cta-button', 'unipixel'); ?></small></th>
                            <th><?php echo esc_html__('Event Trigger', 'unipixel'); ?><br><small class="fw-normal"><?php echo esc_html__('Action that triggers the event', 'unipixel'); ?></small></th>
                            <th><?php echo esc_html__('Event Name', 'unipixel'); ?><br><small class="fw-normal"><?php echo esc_html__('Reference logged to', 'unipixel'); ?> <?php echo esc_html($platformName); ?></small></th>
                            <th><?php echo esc_html__('Event Description', 'unipixel'); ?><br><small class="fw-normal"><?php echo esc_html__('Your note', 'unipixel'); ?></small></th>
                            <th class="colSendClientSide">Send<br />Client-side <?php echo wp_kses(unipixel_get_help_icon('SendClientSide'), $icon_allow); ?></th>
                            <th class="colSendServerSide">Send<br />Server-side <?php echo wp_kses(unipixel_get_help_icon('SendServerSide'), $icon_allow); ?></th>
                            <th class="colLogResponse">Log Server-side Response <?php echo wp_kses(unipixel_get_help_icon('LogServerSideResponse'), $icon_allow); ?></th>
                            <th class="colDelete"><?php echo esc_html__('Delete', 'unipixel'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Existing events populated by ajax-event-settings.js (loadEvents) -->
                    </tbody>
                </table>

                <button type="button" id="add-event" class="btn btn-secondary">
                    <i class="fa-solid fa-plus"></i> <?php echo esc_html__('Add Event', 'unipixel'); ?>
                </button>
            </section>

            <!-- Unified submit -->
            <div class="mt-2 text-end">
                <button type="submit" class="btn btn-primary btn-lg" id="btnUniPixelUpdateAll">
                    <i class="fa-solid fa-floppy-disk"></i> <?php echo esc_html__('Save All Tracking Settings', 'unipixel'); ?>
                </button>
            </div>
        </form>
    </div>



    <!-- Recommended Settings Help Modal -->
    <div class="modal fade" id="recommendedHelpModal" tabindex="-1" aria-labelledby="recommendedHelpLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="recommendedHelpLabel">Recommended Settings Explained</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="recommendedHelpBody">
                    <!-- dynamically filled -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

<?php
}
