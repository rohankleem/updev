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
        <form id="platform-settings-form" class="form-horizontal">
            <input type="hidden" id="platform_id" name="platform_id" value="<?php echo esc_attr((string) $platform_id); ?>">

            <div class="mb-3 row">
                <div class="col-12 col-sm-3">
                    <label class="form-check-label" for="platform_enabled">
                        <?php echo esc_html__('Turn On/Enabled?', 'unipixel'); ?>
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

            <div class="mb-3 row">
                <label for="pixel_id" class="col-sm-3 col-form-label">
                    <?php echo esc_html__('Pinterest Tag ID:', 'unipixel'); ?>
                    <?php echo wp_kses(unipixel_get_help_icon('Pinterest_TagId'), $icon_allow); ?>
                </label>
                <div class="col-sm-9">
                    <input type="text" id="pixel_id" name="pixel_id" class="form-control" value="<?php echo esc_attr($pixel_id); ?>" required>
                </div>
            </div>

            <div id="serverside-well" class="bg-light-blue">
                <p class="mb-1"><i class="fa-solid fa-bolt-lightning"></i> <strong><?php echo esc_html__('Server-Side Tracking', 'unipixel'); ?></strong></p>
                <p class="mb-2"><small><?php echo esc_html__('Supercharge your event tracking with Pinterest\'s Conversions API. In addition to traditional client-side sending, events are sent directly from your server, bypassing ad blockers and browser restrictions. Events are matched using event_id to avoid double counting and improve your measurement and reporting.', 'unipixel'); ?></small></p>

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
                        <input type="text" id="additional_id" name="additional_id" class="form-control" value="<?php echo esc_attr($additional_id); ?>">
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="access_token" class="col-sm-3 col-form-label">
                        <?php echo esc_html__('Conversion Access Token:', 'unipixel'); ?>
                        <?php echo wp_kses(unipixel_get_help_icon('Pinterest_AccessToken'), $icon_allow); ?>
                    </label>
                    <div class="col-sm-9">
                        <input type="password" id="access_token" name="access_token" class="form-control" value="<?php echo esc_attr($access_token); ?>">
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
    </div>
    <?php
}
