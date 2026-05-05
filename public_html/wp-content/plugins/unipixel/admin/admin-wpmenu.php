<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

function unipixel_add_admin_menus()
{
    add_menu_page(
        'Meta Pixel UniTrack Dashboard',
        'UniPixel',
        'manage_options',
        'unipixel',
        'unipixel_home_page',
        plugin_dir_url(__FILE__) . 'img/unipixel-wpmenu-icon-mono.svg', //'dashicons-list-view', 
        65
    );


    //parent slug, page title, menu item title
    add_submenu_page('unipixel', 'UniPixel | Meta Tracking Setup', 'Meta Setup', 'manage_options', 'unipixel_meta', 'unipixel_meta_router');
    add_submenu_page('unipixel', 'UniPixel | Pinterest Tracking Setup', 'Pinterest Setup', 'manage_options', 'unipixel_pinterest', 'unipixel_pinterest_router');
    add_submenu_page('unipixel', 'UniPixel | TikTok Tracking Setup', 'TikTok Setup', 'manage_options','unipixel_tiktok', 'unipixel_tiktok_router');

    add_submenu_page('unipixel', 'UniPixel | Google Tracking Setup', 'Google Setup', 'manage_options', 'unipixel_google', 'unipixel_google_router');
    add_submenu_page('unipixel', 'UniPixel | Microsoft Tracking Setup', 'Microsoft Setup', 'manage_options', 'unipixel_microsoft', 'unipixel_microsoft_router');


    add_submenu_page('unipixel', 'UniPixel | Event Manager', 'Event Manager', 'manage_options', 'unipixel_conversions', 'unipixel_conversions_router');

    add_submenu_page('unipixel', 'UniPixel | Event Test Console', 'Event Test Console', 'manage_options', 'unipixel_console_logger', 'unipixel_page_console_logger');
    add_submenu_page('unipixel', 'UniPixel | Stored Event Logs', 'Stored Event Logs', 'manage_options', 'unipixel_event_logs', 'unipixel_page_event_logs');
    add_submenu_page('unipixel', 'UniPixel | General Settings', 'General Settings', 'manage_options', 'unipixel_general_settings', 'unipixel_page_general_settings');
    add_submenu_page('unipixel', 'UniPixel | Consent Settings', 'Consent Settings', 'manage_options', 'unipixel_consent_settings', 'unipixel_page_consent_settings');


    //add_submenu_page('unipixel', 'Pinterest Tracking Setup', 'Pinterest Setup', 'manage_options', 'unipixel_page_setup_pinterest', 'unipixel_page_setup_pinterest');
    //add_submenu_page('unipixel', 'TikTok Tracking Setup', 'TikTok Setup', 'manage_options', 'unipixel_page_setup_tiktok', 'unipixel_page_setup_tiktok');
    //add_submenu_page('unipixel', 'Microsoft Tracking Setup', 'Microsoft Setup', 'manage_options', 'unipixel_setup_microsoft', 'unipixel_page_setup_microsoft');
}
add_action('admin_menu', 'unipixel_add_admin_menus');




function unipixel_meta_router()
{
    if (! current_user_can('manage_options')) {
        wp_die(esc_html__('Unauthorized', 'unipixel'));
    }
    $section = isset($_GET['section']) ? sanitize_key($_GET['section']) : 'setup';

    switch ($section) {
        case 'events':
            unipixel_page_meta_events();
            break;
        case 'setup':
        default:
            unipixel_page_meta_setup();
            break;
    }
}


function unipixel_pinterest_router()
{
    if (! current_user_can('manage_options')) {
        wp_die(esc_html__('Unauthorized', 'unipixel'));
    }

    $section = isset($_GET['section']) ? sanitize_key($_GET['section']) : 'setup';

    switch ($section) {
        case 'events':
            unipixel_page_pinterest_events();
            break;
        case 'setup':
        default:
            unipixel_page_pinterest_setup();
            break;
    }
}


function unipixel_tiktok_router()
{
    if (! current_user_can('manage_options')) {
        wp_die(esc_html__('Unauthorized', 'unipixel'));
    }

    $section = isset($_GET['section']) ? sanitize_key($_GET['section']) : 'setup';

    switch ($section) {
        case 'events':
            unipixel_page_tiktok_events();
            break;
        case 'setup':
        default:
            unipixel_page_tiktok_setup();
            break;
    }
}




function unipixel_google_router()
{
    if (! current_user_can('manage_options')) {
        wp_die(esc_html__('Unauthorized', 'unipixel'));
    }
    $section = isset($_GET['section']) ? sanitize_key($_GET['section']) : 'setup';

    switch ($section) {
        case 'events':
            unipixel_page_google_events();
            break;
        case 'setup':
        default:
            unipixel_page_google_setup();
            break;
    }
}

function unipixel_microsoft_router()
{
    if (! current_user_can('manage_options')) {
        wp_die(esc_html__('Unauthorized', 'unipixel'));
    }

    $section = isset($_GET['section']) ? sanitize_key($_GET['section']) : 'setup';

    switch ($section) {
        case 'events':
            unipixel_page_microsoft_events();
            break;
        case 'setup':
        default:
            unipixel_page_microsoft_setup();
            break;
    }
}



function unipixel_render_platform_header_nav($platform, $active)
{
    // Normalize inputs
    $platform_key   = sanitize_key($platform);                 // 'meta' | 'google'
    // Brand-correct labels (ucfirst breaks TikTok's two-capital casing).
    $platform_labels = [
        'meta'      => 'Meta',
        'pinterest' => 'Pinterest',
        'tiktok'    => 'TikTok',
        'google'    => 'Google',
        'microsoft' => 'Microsoft',
    ];
    $platform_label = $platform_labels[$platform_key] ?? ucfirst($platform_key);
    $active_key     = sanitize_key($active);                   // 'setup' | 'events'

    // Page slug per platform
    $page_slug = match ($platform_key) {
        'meta'      => 'unipixel_meta',
        'pinterest' => 'unipixel_pinterest',
        'tiktok'    => 'unipixel_tiktok',
        'google'    => 'unipixel_google',
        'microsoft' => 'unipixel_microsoft',
        default     => 'unipixel',
    };

    // Build server URLs
    $base       = admin_url('admin.php?page=' . $page_slug);
    $setup_url  = add_query_arg(['section' => 'setup'], $base);
    $events_url = add_query_arg(['section' => 'events'], $base);

    // Active states
    $is_setup_active  = ($active_key === 'setup');
    $is_events_active = ($active_key === 'events');

    // IDs for the nav (unique per platform)
    $nav_id = 'unipixel-' . $platform_key . '-server-tabs';

    // Font Awesome icons per platform
    $icons = [
        'google'    => '<i class="fa-brands fa-google"></i>',
        'meta'      => '<i class="fa-brands fa-meta"></i>',
        'microsoft' => '<i class="fa-brands fa-microsoft"></i>',
        'pinterest' => '<i class="fa-brands fa-pinterest"></i>',
        'tiktok'    => '<i class="fa-brands fa-tiktok"></i>'
    ];

    // Fallback icon
    $platform_icon = isset($icons[$platform_key]) ? $icons[$platform_key] : '<i class="fa-solid fa-bolt" aria-hidden="true"></i>';

?>

    <div class="d-flex justify-content-between align-items-start">
        <h1 class="mb-3">
            <?php echo wp_kses($platform_icon, unipixel_get_popover_allowlist()); ?>
            <?php echo esc_html($platform_label); ?> Setup
        </h1>
        <?php unipixel_render_feedback_buttons(); ?>
    </div>

    <div class="underline-tabs mb-4">
        <!-- Server-nav styled as Bootstrap tabs (no data-bs-toggle) -->
        <ul class="nav nav-tabs" id="<?php echo esc_attr($nav_id); ?>" role="tablist">
            <li class="nav-item" role="presentation">
                <a
                    class="nav-link <?php echo $is_setup_active ? 'active' : ''; ?>"
                    id="<?php echo esc_attr($platform_key . '-setup-tab'); ?>"
                    href="<?php echo esc_url($setup_url); ?>"
                    role="tab"
                    aria-controls="<?php echo esc_attr($platform_key . '-setup-panel'); ?>"
                    aria-selected="<?php echo $is_setup_active ? 'true' : 'false'; ?>">
                    <i class="fa-solid fa-code"></i> <?php echo esc_html__('Tag Setup', 'unipixel'); ?>
                </a>
            </li>

            <li class="nav-item" role="presentation">
                <a
                    class="nav-link <?php echo $is_events_active ? 'active' : ''; ?>"
                    id="<?php echo esc_attr($platform_key . '-events-tab'); ?>"
                    href="<?php echo esc_url($events_url); ?>"
                    role="tab"
                    aria-controls="<?php echo esc_attr($platform_key . '-events-panel'); ?>"
                    aria-selected="<?php echo $is_events_active ? 'true' : 'false'; ?>">
                    <i class="fa-solid fa-rss"></i> <?php echo esc_html__('Events Setup', 'unipixel'); ?>
                </a>
            </li>
        </ul>
    </div>
<?php
}



function unipixel_render_notice_from_query()
{
    $map = [
        'pv_updated'     => __('PageView settings updated.', 'unipixel'),
        'woo_updated'    => __('Woo events updated.', 'unipixel'),
        'custom_updated' => __('Site events updated.', 'unipixel'),
    ];
    foreach ($map as $key => $msg) {
        if (isset($_GET[$key])) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($msg) . '</p></div>';
        }
    }
    if (isset($_GET['err'])) {
        // If you ever pass specific error codes, map them to friendly strings instead of echoing raw.
        echo '<div class="notice notice-error"><p>' . esc_html(wp_unslash($_GET['err'])) . '</p></div>';
    }
}
