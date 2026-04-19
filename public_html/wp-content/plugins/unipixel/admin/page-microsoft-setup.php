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
        <form id="platform-settings-form" class="form-horizontal">
            <input type="hidden" id="platform_id" name="platform_id" value="<?php echo esc_attr((string)$platform_id); ?>">

            <!-- Enabled toggle -->
            <div class="mb-3 row">
                <div class="col-12 col-sm-3">
                    <label class="form-check-label" for="platform_enabled">
                        <?php echo esc_html__('Turn On/Enabled?', 'unipixel'); ?>
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

                <!-- UET Tag ID -->
                <div class="mb-3 row">
                    <label for="pixel_id" class="col-sm-3 col-form-label">
                        <?php echo esc_html__('UET Tag ID:', 'unipixel'); ?>
                        <?php echo wp_kses(unipixel_get_help_icon('Microsoft_PixelId'), $icon_allow); ?>
                    </label>
                    <div class="col-sm-9">
                        <input type="text" id="pixel_id" name="pixel_id" class="form-control"
                            value="<?php echo esc_attr($pixel_id); ?>" required>
                    </div>
                </div>

                <!-- Server-Side Tracking -->
                <div id="serverside-well" class="bg-light-blue">
                    <p class="mb-1"><i class="fa-solid fa-bolt-lightning"></i> <strong><?php echo esc_html__('Server-Side Tracking', 'unipixel'); ?></strong></p>
                    <p class="mb-2"><small><?php echo esc_html__('Supercharge your event tracking with Microsoft\'s Conversions API (CAPI). In addition to traditional client-side sending via the UET tag, events are sent directly from your server, bypassing ad blockers and browser restrictions. Events are deduplicated using event IDs to avoid double counting.', 'unipixel'); ?></small></p>

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
                            <input type="password" id="access_token" name="access_token" class="form-control" value="<?php echo esc_attr($access_token); ?>">
                            <small class="form-text text-muted"><?php echo esc_html__('Obtain your token from the Microsoft Advertising UI under "Use Conversions API", or contact your account manager.', 'unipixel'); ?></small>
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
    </div>
<?php } ?>
