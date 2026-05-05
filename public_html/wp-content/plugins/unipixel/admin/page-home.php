<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly
function unipixel_home_page()
{
    global $wpdb;
    $platform_table = $wpdb->prefix . 'unipixel_platform_settings';
    $log_table = $wpdb->prefix . 'unipixel_event_log';

    // Fetch Meta platform settings
    $query = $wpdb->prepare(
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        "SELECT * FROM %i WHERE id = %d",
        $platform_table,
        1
    );
    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $meta_settings = $wpdb->get_row($query, ARRAY_A);

    // Fetch Pinterest platform settings
    $query = $wpdb->prepare(
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        "SELECT * FROM %i WHERE id = %d",
        $platform_table,
        2
    );
    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $pinterest_settings = $wpdb->get_row($query, ARRAY_A);

    // Fetch TikTok platform settings
    $query = $wpdb->prepare(
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        "SELECT * FROM %i WHERE id = %d",
        $platform_table,
        3
    );
    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $tiktok_settings = $wpdb->get_row($query, ARRAY_A);

    // Fetch Google platform settings
    $query = $wpdb->prepare(
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        "SELECT * FROM %i WHERE id = %d",
        $platform_table,
        4
    );
    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $google_settings = $wpdb->get_row($query, ARRAY_A);

    // Check if platform settings are set up
    $is_meta_setup = !empty($meta_settings['pixel_id']) && !empty($meta_settings['access_token']);
    $is_pinterest_setup = !empty($pinterest_settings['pixel_id']) && !empty($pinterest_settings['additional_id']) && !empty($pinterest_settings['access_token']);
    $is_tiktok_setup = !empty($tiktok_settings['pixel_id']) && !empty($tiktok_settings['access_token']);
    $is_google_setup = !empty($google_settings['pixel_id']) && !empty($google_settings['access_token']);

    // Fetch recent log entries
    $query = $wpdb->prepare(
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        "SELECT * FROM %i ORDER BY log_time DESC LIMIT %d",
        $log_table,
        40
    );
    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $logs = $wpdb->get_results($query, ARRAY_A);

?>
    <div class="UniPixelShell position-relative pt-4">

        <div class="row mb-4">
            <div class="col-6">
                <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'img/unipixel-logo-landscape-3.svg'); ?>" alt="UniPixel Logo" style="width:330px" class="img-fluid">
            </div>
            <div class="col-6 d-flex justify-content-end align-items-start">
                <?php unipixel_render_feedback_buttons(); ?>
            </div>
        </div>

        <div class="row">

            <!-- META -->
            <div class="col-md-6 col-lg-3 mb-4">
                <?php if ($is_meta_setup) : ?>
                    <div class="card bg-light-green borderless w-100 h-100">
                        <div class="card-body pb-0">
                            <h1>Pixel for Meta (Facebook)</h1>
                            <p>Manage Meta pixel with Conversions API, including site events.</p>
                            <a href="<?php echo esc_url(menu_page_url('unipixel_meta', false)); ?>" class="btn btn-primary">View Pixel Setup</a>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="card bg-light-green borderless w-100 h-100">
                        <div class="card-body pb-0">
                            <h1>Setup a pixel for Meta (Facebook)</h1>
                            <p>Get started by setting up your Meta pixel.</p>
                            <a href="<?php echo esc_url(menu_page_url('unipixel_meta', false)); ?>" class="btn btn-primary">Get Started</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- TIKTOK -->
            <div class="col-md-6 col-lg-3 mb-4">
                <?php if ($is_tiktok_setup) : ?>
                    <div class="card bg-light-purple borderless w-100 h-100">
                        <div class="card-body pb-0">
                            <h1>TikTok Pixel</h1>
                            <p>Manage TikTok tracking with server-side and client-side events.</p>
                            <a href="<?php echo esc_url(menu_page_url('unipixel_tiktok', false)); ?>" class="btn btn-primary">View Pixel Setup</a>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="card bg-light-purple borderless w-100 h-100">
                        <div class="card-body pb-0">
                            <h1>Setup TikTok Pixel</h1>
                            <p>Add your TikTok Pixel ID to get started.</p>
                            <a href="<?php echo esc_url(menu_page_url('unipixel_tiktok', false)); ?>" class="btn btn-primary">Get Started</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- GOOGLE -->
            <div class="col-md-6 col-lg-3 mb-4">
                <?php if ($is_google_setup) : ?>
                    <div class="card bg-light-blue borderless w-100 h-100">
                        <div class="card-body pb-0">
                            <h1>Google Analytics</h1>
                            <p>Manage Google Analytics with Measurement Protocol.</p>
                            <a href="<?php echo esc_url(menu_page_url('unipixel_google', false)); ?>" class="btn btn-primary">View Tag Setup</a>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="card bg-light-blue borderless w-100 h-100">
                        <div class="card-body pb-0">
                            <h1>Setup Google Analytics</h1>
                            <p>Get started by adding your Google Measurement ID.</p>
                            <a href="<?php echo esc_url(menu_page_url('unipixel_google', false)); ?>" class="btn btn-primary">Get Started</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- PINTEREST -->
            <div class="col-md-6 col-lg-3 mb-4">
                <?php if ($is_pinterest_setup) : ?>
                    <div class="card bg-pinterest-pink borderless w-100 h-100">
                        <div class="card-body pb-0">
                            <h1>Pinterest Tag</h1>
                            <p>Manage Pinterest tracking with Conversions API, including site events.</p>
                            <a href="<?php echo esc_url(menu_page_url('unipixel_pinterest', false)); ?>" class="btn btn-primary">View Tag Setup</a>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="card bg-pinterest-pink borderless w-100 h-100">
                        <div class="card-body pb-0">
                            <h1>Setup Pinterest Tag</h1>
                            <p>Add your Pinterest Tag ID to get started.</p>
                            <a href="<?php echo esc_url(menu_page_url('unipixel_pinterest', false)); ?>" class="btn btn-primary">Get Started</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>


        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-primary shadow-sm">
                    <div class="card-body">
                        <h4 class="mb-2"><i class="fa-solid fa-bullseye text-primary"></i> Centralised Event Manager</h4>
                        <p class="text-muted mb-3">
                            Beyond WooCommerce events (which are tracked automatically), manage your <strong>site events</strong> in one place across every enabled platform.
                            Set up a Lead, Newsletter Signup, or any bespoke event once. UniPixel applies the right Standard event names and dedup for each platform you choose.
                        </p>
                        <a href="admin.php?page=unipixel_conversions" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-arrow-right"></i> Open Event Manager
                        </a>
                    </div>
                </div>
            </div>
        </div>


        <div class="row mt-2">

            <!-- View Stored Event Logs -->
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title mb-2">View Stored Event Logs</h6>
                        <p class="small text-muted mb-3">
                            Review all server-stored event logs to verify tracking behavior and troubleshoot platform data consistency.
                        </p>
                        <a href="admin.php?page=unipixel_event_logs" class="btn btn-sm btn-outline-primary">Open Logs</a>
                    </div>
                </div>
            </div>

            <!-- View Live Event Log -->
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title mb-2">View Live Event Log</h6>
                        <p class="small text-muted mb-3">
                            Observe live client-side events in real time for debugging and verification. Visit the Test Console
                            for an interactive live view.
                        </p>
                        <a href="admin.php?page=unipixel_console_logger" class="btn btn-sm btn-outline-primary">Open Live Console</a>
                    </div>
                </div>
            </div>

            <!-- General Settings -->
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title mb-2">General Settings</h6>
                        <p class="small text-muted mb-3">
                            Adjust event logging preferences, enable or disable database storage, and manage DebugView or console visibility options.
                        </p>
                        <a href="admin.php?page=unipixel_general_settings" class="btn btn-sm btn-outline-primary">Open Settings</a>
                    </div>
                </div>
            </div>

            <!-- Consent Settings -->
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Consent Settings</h6>
                        <p class="small text-muted mb-3">
                            <b>Turn on your Cookie Consent banner.</b> Manage how user consent is collected. Ensure compliance while maintaining event accuracy.
                        </p>
                        <a href="admin.php?page=unipixel_consent_settings" class="btn btn-sm btn-outline-primary">Open Consent Settings</a>
                    </div>
                </div>
            </div>

        </div>


    </div>
<?php
}

?>