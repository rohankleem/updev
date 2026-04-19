<?php

//File: public_html\wp-content\plugins\unipixel\functions\unipixel-functions.php

if (! defined('ABSPATH')) exit; // Exit if accessed directly

function unipixel_get_events_for_platform($platform_id)
{
    global $wpdb;
    $events_table = $wpdb->prefix . 'unipixel_events_settings';

    // Prepare the query with placeholders
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $query = $wpdb->prepare(
        "SELECT * FROM %i WHERE platform_id = %d",
        $events_table,
        $platform_id
    );
    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $events = $wpdb->get_results($query, ARRAY_A);

    return $events;
}

function unipixel_get_platform_settings($platform_id)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'unipixel_platform_settings';

    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $query = $wpdb->prepare(
        "SELECT * FROM %i WHERE id = %d",
        $table_name,
        $platform_id
    );

    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $row = $wpdb->get_row($query); // returns stdClass or null
    return $row ?: new stdClass(); // ensure it always returns an object
}

/**
 * Retrieves settings for a specific event by platform ID and event name.
 *
 * @param int $platform_id The ID of the platform (e.g., 1 for Meta, 4 for Google).
 * @param string $event_name The event name (e.g., 'page_view', 'purchase').
 * @return object|null Event settings row as object, or null if not found.
 */
function unipixel_get_event_settings($platform_id, $event_name)
{
    global $wpdb;
    $table = $wpdb->prefix . 'unipixel_events_settings';

    $query = $wpdb->prepare(
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        "SELECT * FROM %i WHERE platform_id = %d AND event_name = %s LIMIT 1",
        $table,
        $platform_id,
        $event_name
    );

    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $row = $wpdb->get_row($query); // returns stdClass or null
    return $row ?: new stdClass(); // ensure it always returns an object
}



function unipixel_build_client_result_mock($consentBlocked = false, $params = [], $platformId = 1, $event_name = '')
{
    $code = $consentBlocked ? 1 : 0;
    $log_payload = unipixel_construct_client_side_send_log($platformId, $event_name, $params);

    return [
        'response' => [
            'response' => [
                'code'    => $code,
                'message' => $consentBlocked
                    ? 'Event blocked: Consent not provided'
                    : 'Client-side only – no server response',
            ]
        ],
        'args' => $log_payload
    ];
}


function unipixel_get_help_icon($key)
{
    $help_texts = [
        "Meta_Enabled"   => [
            "title" => "Turning On Meta Tracking",
            "content" => "When set to off, Unipixel won't include Meta Pixel and won't send any events for this platform."
        ],
        "Meta_Include"   => [
            "title" => "Pixel Setting",
            "content" => "This will add Meta's tracking pixel to your site, and you just need to add Pixel ID below. This allows the sending of standard meta tracking events like page view, and any ecommerce or custom events you have setup below."
        ],
        "Meta_Already"   => [
            "title" => "Pixel Setting",
            "content" => "If you've already added a Meta Pixel to your site, UniPixel can piggy-back off this, sending additon events you have setup below."
        ],
        "Meta_PixelId"   => [
            "title" => "Pixel ID",
            "content" => "Your unique identifier for your Meta Pixel. Found in Meta Events Manager under your Pixel settings.<br/><br/><a href='https://buildio.dev/unipixel-docs/setting-up-unipixel-with-meta/' target='_blank'>See the full Meta setup guide</a>"
        ],
        "Meta_AccessToken"   => [
            "title" => "Access Token",
            "content" => "Used for Conversions API / Server side tracking. <br/>
                    1. Go to <b>Business Settings</b> in <b>Facebook Business Manager</b>.<br/>
                    2. Navigate to System Users and click <b>Add System User</b>.<br/>
                    3. Assign Admin permissions and select your <b>Business Account</b>.<br/>
                    4. In the <b>Permissions</b> tab, ensure access to <b>Manage Ads</b>.<br/>
                    5. Click <b>Generate Access Token</b>.<br/>
                    6. Copy and save the token securely.<br/><br/><a href='https://buildio.dev/unipixel-docs/setting-up-unipixel-with-meta/' target='_blank'>See the full Meta setup guide</a>"
        ],
        "Meta_PageView_ClientSideTurnOn"   => [
            "title" => "PageView Client-side event",
            "content" => "PageView event information is automatically sent via a client-side call to Meta using the native tracking script included when you enable Meta."
        ],
        "Meta_PageView_ServerSideTurnOn"   => [
            "title" => "PageView Server-side event",
            "content" => "Optionally on each page load, you can send the PageView event information to facebook via a <b>Server-side</b> call too."
        ],


        "TikTok_PageView_ClientSideTurnOn"   => [
            "title" => "TikTok PageView Client-side event",
            "content" => "PageView event is no longer a standard event at TikTok, however, you can turn it on if you want to track it as a custom evemt. This option allows it to be sent via a client-side call to using the native tracking script included when you enable TikTok."
        ],
        "TikTok_PageView_ServerSideTurnOn"   => [
            "title" => "TikTok PageView Server-side event",
            "content" => "Optionally on each page load, you can send the PageView event (as a custom event) to TikTok via a <b>Server-side</b>, first-party call too."
        ],




        "Google_Enabled"   => [
            "title" => "Turning On Google Tracking",
            "content" => "When set to off, Unipixel sends no events."
        ],
        "Google_Include"   => [
            "title" => "Pixel Setting",
            "content" => "Full tracking: includes the Google gtag script which sends standard events, only sends the custom events you have added here."
        ],
        "Google_Already"   => [
            "title" => "Pixel Setting",
            "content" => "Assumes the gtag script is already present, only sends the custom events you have added here."
        ],
        "Google_Gtm"   => [
            "title" => "Pixel Setting",
            "content" => "This setting assumes the gtag script is already present on your site but included via Google Tag Manager (GTM). In that case we also need the GTM Container ID. Unipixel send the events you have setup in this plugin, and the other events you may have setup in GTM continue to be fired through GTM. 
                <br/><br/>It is important to <a href='https://buildio.dev/unipixel-docs/using-unipixel-with-google-tag-manager-gtm/' target='_blank'>read the docs here</a> to setup your GTM tags and triggers correctly.
                <br/><br/><b>Note:</b> if using this option, you need to <b>set GTM's send_page_view setting to 'false'</b> in your Google tag configuration, to void doubling up this event - UniPixel already takes care of the page_view event, including consent checks, and logging. To do this, do the following:
                <br/><br/><b>1.</b> In Google Tag Manager, go to the Tags list and edit your GA4 Configuration Tag.
                <br/><b>2.</b> Edit the tag, and in configuration settings set the send_page_view parameter to 'false', or uncheck the option relating to this to prevent the page_view event running at GTM too when this configuration loads.
                <br/><b>3.</b> Save, then Submit & Publish your container."
        ],
        "Google_MeasurementId"   => [
            "title" => "Measurement ID",
            "content" => "This ID specifies your unique Google Analytics setup, ensuring data is sent to the correct property. It can be found in Google Analytics > Admin > Data Streams, under 'Measurement ID' (formatted as G-XXXXXXXXXX).<br/><br/><a href='https://buildio.dev/unipixel-docs/getting-ready-for-unipixel-what-you-need-from-google/' target='_blank'>See the full Google setup guide</a>"
        ],
        "Google_ContainerId"   => [
            "title" => "Tag Manager Container Id",
            "content" => "This ID identifies your specific Google Tag Manager container, which holds all your tracking tags, triggers, and variables. It can be found in Google Tag Manager > Admin > Container Settings, typically formatted as GTM-XXXXXXX. You'll need this in addition to your Measurement ID if your gtag is loaded via Google Tag Manager.<br/><br/><a href='https://buildio.dev/unipixel-docs/using-unipixel-with-google-tag-manager-gtm/' target='_blank'>See the GTM setup guide</a>"
        ],
        "Google_ApiSecret"   => [
            "title" => "API Secret",
            "content" => "Used for Google Server side tracking. <br/>
                    1. Go to <b>Google Analytics Admin</b>.<br/>
                    2. Under <b>Data Streams</b>, select your website stream.<br/>
                    3. Scroll down to <b>Measurement Protocol API Secret</b>.<br/>
                    4. Click <b>Create</b>, name it (e.g., 'Server-Side Tracking'), and copy the API Secret.<br/><br/><a href='https://buildio.dev/unipixel-docs/getting-ready-for-unipixel-what-you-need-from-google/' target='_blank'>See the full Google setup guide</a>"
        ],


        "SendClientSide"   => [
            "title" => "Send client-side",
            "content" => "This option sends the event through the platform's own tracking script that runs directly in the visitor's browser. Because the data is sent from the user's browser to the platform's domain (e.g., facebook.com or google-analytics.com), it is categorized as <b>Third Party</b> tracking.  
            <br/><br/>
            In contrast, <b>First Party</b> tracking -- like UniPixel's Server-side method -- sends the event from your own website's server to the platform's API. This means the data originates from your domain and can preserve more stable tracking even when browsers limit third-party cookies."
        ],

        "SendServerSide" => [
            "title" => "Send server-side",
            "content" => "This option sends the event from your website's server to the platform's API, rather than from the user's browser. Because the data originates from your domain, it is categorized as <b>First Party</b> tracking.
            <br/><br/>
            In contrast, <b>Third Party</b> tracking -- the client-side method -- sends the event from the browser directly to the platform's domain. Server-side can be more resilient to ad blockers and third-party cookie limits, and it can run alongside client-side with deduplication if desired."
        ],

        "Google_SendClientSide"   => [
            "title" => "Send client-side",
            "content" => "This option sends the event through the Google's own tracking script that runs directly in the visitor's browser. Because the data is sent from the user's browser to the platform's domain (e.g., facebook.com or google-analytics.com), it is categorized as <b>Third Party</b> tracking.  
            <br/><br/>
            In contrast, <b>First Party</b> tracking -- like UniPixel's Server-side method -- sends the event from your own website's server to the platform's API. This means the data originates from your domain and can preserve more stable tracking even when browsers limit third-party cookies.
            <br/><br/>
            Note: Google does not deduplicate (except for 'purchase') so this UI restricts you from having both client and server on for most events."
        ],

        "Google_SendServerSide" => [
            "title" => "Send server-side",
            "content" => "This option sends the event from your website's server to the platform's API, rather than from the user's browser. Because the data originates from your domain, it is categorized as <b>First Party</b> tracking.
            <br/><br/>
            In contrast, <b>Third Party</b> tracking -- the client-side method -- sends the event from the browser directly to the platform's domain. Server-side can be more resilient to ad blockers and third-party cookie limits.
            <br/><br/>
            Note: Google does not deduplicate (except for 'purchase') so this UI restricts you from having both client and server on for most events."
        ],

        "LogServerSideResponse"   => [
            "title" => "Log Server-side Response",
            "content" => "When sending events to a platform, for the Server-side event, you have the option of getting a response from the platform. This is useful for confirming that a Server-side version of the event was sent and received. For common events, like page view or product views, you may consider turning response logging off or using it temporarily for testing purposes. When turned off, UniPixel by-passes waiting for any response resulting in no delay in page load performance."
        ],


        "TikTok_Enabled" => [
            "title" => "Turning On TikTok Tracking",
            "content" => "When enabled, UniPixel loads the TikTok Pixel script and tracks events configured for this platform."
        ],
        "TikTok_Include" => [
            "title" => "Pixel Setting",
            "content" => "This includes TikTok's tracking pixel directly from UniPixel. You just need to provide your Pixel ID below."
        ],
        "TikTok_Already" => [
            "title" => "Pixel Setting",
            "content" => "Select this if you already have TikTok's base pixel installed elsewhere on your site. UniPixel will only handle events."
        ],
        "TikTok_PixelId" => [
            "title" => "Pixel ID",
            "content" => "Your unique TikTok Pixel ID, available in your TikTok Ads Manager under Events > Manage > Pixel Settings.<br/><br/><a href='https://buildio.dev/unipixel-docs/setting-up-unipixel-with-tiktok/' target='_blank'>See the full TikTok setup guide</a>"
        ],
        "TikTok_AccessToken" => [
            "title" => "Access Token",
            "content" => "Used for TikTok Events API / Server side tracking.<br/>
                    1. Go to <b>TikTok Ads Manager</b>.<br/>
                    2. Navigate to <b>Events</b> > <b>Manage</b> and select your Pixel.<br/>
                    3. Under <b>Settings</b>, find <b>Events API</b>.<br/>
                    4. Click <b>Generate Access Token</b>.<br/>
                    5. Copy and save the token securely.<br/><br/><a href='https://buildio.dev/unipixel-docs/setting-up-unipixel-with-tiktok/' target='_blank'>See the full TikTok setup guide</a>"
        ],

        "Pinterest_Enabled" => [
            "title" => "Turning On Pinterest Tracking",
            "content" => "When enabled, UniPixel loads the Pinterest Tag and tracks events configured for this platform.<br/><br/><a href='https://buildio.dev/unipixel-docs/setting-up-unipixel-with-pinterest/' target='_blank'>See the full Pinterest setup guide</a>"
        ],
        "Pinterest_Include" => [
            "title" => "Pixel Setting",
            "content" => "This includes Pinterest's tracking tag directly from UniPixel. You just need to provide your Tag ID below."
        ],
        "Pinterest_Already" => [
            "title" => "Pixel Setting",
            "content" => "Select this if you already have the Pinterest Tag installed elsewhere on your site. UniPixel will only handle event sending."
        ],
        "Pinterest_TagId" => [
            "title" => "Pinterest Tag ID",
            "content" => "Your unique Pinterest Tag ID. Found in Pinterest Ads Manager under <b>Conversions</b> > <b>Set Up the Pinterest Tag</b>, or in your Tag settings.<br/><br/><a href='https://buildio.dev/unipixel-docs/setting-up-unipixel-with-pinterest/' target='_blank'>See the full Pinterest setup guide</a>"
        ],
        "Pinterest_AdAccountId" => [
            "title" => "Ad Account ID",
            "content" => "Your Pinterest Ad Account ID. Found in Pinterest Ads Manager -- visible in the URL or under <b>Ads</b> > <b>Overview</b> in the account dropdown. Required for server-side Conversions API calls.<br/><br/><a href='https://buildio.dev/unipixel-docs/setting-up-unipixel-with-pinterest/' target='_blank'>See the full Pinterest setup guide</a>"
        ],
        "Pinterest_AccessToken" => [
            "title" => "Conversion Access Token",
            "content" => "Used for Pinterest Conversions API / Server-side tracking.<br/>
                    1. Go to <b>Pinterest Ads Manager</b>.<br/>
                    2. Navigate to <b>Conversions</b> and select your Tag.<br/>
                    3. Under <b>Set Up Conversions API</b>, generate an access token.<br/>
                    4. Copy and save the token securely.<br/><br/><a href='https://buildio.dev/unipixel-docs/setting-up-unipixel-with-pinterest/' target='_blank'>See the full Pinterest setup guide</a>"
        ],
        "Pinterest_PageView_ClientSideTurnOn" => [
            "title" => "Pinterest PageView Client-side",
            "content" => "PageView event information is automatically sent via a client-side call to Pinterest using the tracking tag included when you enable Pinterest."
        ],
        "Pinterest_PageView_ServerSideTurnOn" => [
            "title" => "Pinterest PageView Server-side",
            "content" => "Optionally on each page load, you can send the PageView event to Pinterest via a <b>Server-side</b>, first-party call too."
        ],

        "ServerSideGlobalEnabled" => [
            "title" => "Enable Server-Side Tracking",
            "content" => "Turn this on to send events from your server directly to the platform's API, in addition to (or instead of) the browser pixel. Requires an Access Token. When off, only client-side pixel tracking is active -- no server API calls are made for this platform."
        ],
        "Microsoft_Enabled" => [
            "title" => "Turning On Microsoft Tracking",
            "content" => "When enabled, UniPixel loads the Microsoft UET tag and tracks events configured for this platform. When set to off, no Microsoft events are sent.<br/><br/><a href='https://buildio.dev/unipixel-docs/setting-up-unipixel-with-microsoft/' target='_blank'>See the full Microsoft setup guide</a>"
        ],
        "Microsoft_PixelId" => [
            "title" => "UET Tag ID",
            "content" => "Your unique Microsoft UET Tag ID. Found in Microsoft Advertising under <b>Tools</b> > <b>UET tag</b>. It is the numeric ID shown next to your tag name, also visible in the UET code snippet as the value after <b>ti=</b>.<br/><br/><a href='https://buildio.dev/unipixel-docs/setting-up-unipixel-with-microsoft/' target='_blank'>See the full Microsoft setup guide</a>"
        ],
        "Microsoft_AccessToken" => [
            "title" => "CAPI Access Token",
            "content" => "Used for Microsoft Conversions API / Server-side tracking.<br/>
                    1. Go to <b>Microsoft Advertising</b>.<br/>
                    2. Navigate to <b>Tools</b> > <b>UET tag</b> and select your tag.<br/>
                    3. Look for the <b>Conversions API</b> section.<br/>
                    4. Generate or copy your access token.<br/>
                    5. Save the token securely -- treat it like a password.<br/><br/>If you do not see the Conversions API option, your account may need to be opted in. Contact your Microsoft Advertising account manager.<br/><br/><a href='https://buildio.dev/unipixel-docs/setting-up-unipixel-with-microsoft/' target='_blank'>See the full Microsoft setup guide</a>"
        ],



        //   You'll need to go into GTM and set it up to mark hits as "debug" -- the plugin can't toggle this for you. Once you've finished testing, just remove the setting to stop filling DebugView<br/><br/>1. Open your GTM container in the GTM web interface.
        //     <br/>2. Find your GA4 Configuration tag (the one with your Measurement ID).
        //     <br/>3. Click Fields to Set (under Tag Configuration).
        //     <br/>4. Click Add parameter and enter:
        //     <br/>5. Configuration parameter: debug_
        //     <br/>6. Value: true
        //     <br/>7. Save, Submit, and Publish your container.
        //     <br/>8. When you're done testing, open the same tag, delete that debug_mode row, and Publish again.





    ];

    if (!isset($help_texts[$key])) {
        return ''; // Return empty if key doesn't exist
    }

    $title = esc_attr($help_texts[$key]['title']);
    $content = esc_attr($help_texts[$key]['content']);

    return '<i class="fa-solid fa-circle-info" data-bs-custom-class="UniPixelPopover" data-bs-toggle="popover" data-bs-placement="right" data-bs-title="' . $title . '" data-bs-content="' . $content . '"></i>';
}


function unipixel_get_popover_allowlist()
{
    return [
        'span' => ['class' => []],
        'i'    => [
            'class'             => [],
            'aria-hidden'       => [],
            // Needed for Bootstrap 5 popovers:
            'data-bs-toggle'    => [],
            'data-bs-placement' => [],
            'data-bs-title'     => [],
            'data-bs-content'   => [],
            'data-bs-trigger'   => [], // optional
        ],
        'svg'  => ['class' => [], 'width' => [], 'height' => [], 'viewBox' => [], 'role' => []],
        'path' => ['d' => [], 'fill' => []],
        'a'    => ['href' => [], 'target' => [], 'rel' => [], 'class' => []],
        'code' => [],
        'small' => ['class' => []],
        'br'   => [],
        'ul'   => ['class' => []],
        'li'   => [],
        'strong' => [],
    ];
}



/**
 * Retrieve a logging setting for Unipixel.
 *
 * @param string $key One of: 'enableLogging_Admin', 'enableLogging_InitiateEvents', or 'enableLogging_SendEvents'.
 * @return bool The setting; defaults to true if not set.
 */
function unipixel_get_logging_setting($key)
{
    $defaults = array(
        'enableLogging_Admin'         => true,
        'enableLogging_InitiateEvents'  => true,
        'enableLogging_SendEvents'      => true,
    );
    $options = get_option('unipixel_logging_options', $defaults);

    // Safety check: if $options is not an array, fall back to defaults.
    if (! is_array($options)) {
        $options = $defaults;
    }

    return isset($options[$key]) ? (bool) $options[$key] : $defaults[$key];
}


function unipixel_should_dbstore_event($eventName)
{


    $settings = unipixel_get_dbstore_event_settings();


    // Define known event name groups
    $wooCommEvents = [
        'AddToCart',
        'add_to_cart',
        'InitiateCheckout',
        'begin_checkout',
        'Purchase',
        'purchase',
        'ViewContent',
        'view_item'
    ];

    $pageViewEvents = [
        'PageView',
        'page_view'
    ];

    // 1. Check if this is a PageView event
    if (in_array($eventName, $pageViewEvents, true)) {
        if (!empty($settings['dbstore_pageview_events'])) {
            return true;
        } else {
            return false;
        }
    }

    // 2. Check if this is a WooCommerce-related event (Meta or Google variant)
    if (in_array($eventName, $wooCommEvents, true)) {
        // Check if this specific event is enabled in the dbstore_woocommerce_events setting
        if (!empty($settings['dbstore_woocommerce_events'][$eventName])) {
            return true; // Store this WooCommerce event in the database
        } else {
            return false; // Skip storing this WooCommerce event
        }
    }


    // 3. Otherwise treat it as a custom event
    if (!empty($settings['dbstore_custom_events'])) {
        return true;
    } else {
        return false;
    }
}


function unipixel_get_dbstore_event_settings()
{
    $schema = unipixel_get_dbstore_events_schema();
    $saved  = get_option('unipixel_dbstore_settings', null);

    if (!is_array($saved)) {
        update_option('unipixel_dbstore_settings', $schema);
        return $schema;
    }

    return array_replace_recursive($schema, $saved);
}





function unipixel_metric_log($action, $platform, $detail = array(), $version = '1.0.0')
{

    $listener_url = 'https://buildio.dev/wp-json/unipixelmetrics/v1/log';

    $payload = array(
        //'site_id'        => md5(site_url()),
        'site_id'        => site_url(),
        'plugin_version' => UNIPIXEL_VERSION,
        'action'         => $action,
        'platform'       => $platform,
        'detail'         => wp_json_encode($detail),
    );

    $args = array(
        'method'  => 'POST',
        'headers' => array('Content-Type' => 'application/json'),
        'body'    => wp_json_encode($payload),
        'timeout'  => 0.01,
        'blocking' => false,
    );

    wp_remote_post($listener_url, $args);
}

/**
 * Returns a platform name based on a given integer ID.
 *
 * @param int $platform_id
 * @return string
 */
function unipixel_get_platform_name($platform_id)
{
    switch ($platform_id) {
        case 1:
            return 'Meta';
        case 2:
            return 'Pinterest';
        case 3:
            return 'TikTok';
        case 4:
            return 'Google';
        case 5:
            return 'Microsoft';
        default:
            return 'Unknown';
    }
}

/**
 * Return an fbc value for CAPI:
 * 1) If we captured a raw fbclid in `unipixel_fbclid`, detect domainIndex and build: fb.<domainIndex>.<timestampMs>.<fbclid>
 * 2) Otherwise, if Facebook's own _fbc exists, return it.
 * 3) Else return empty.
 *
 * @param int|null $eventTimeStampMs_in Optional ms timestamp to use; defaults to now().
 * @return string The fbc value or '' if none available.
 */
function unipixel_get_fbc_value($eventTimeStampMs_in = null)
{

    if (! empty($_COOKIE['_fbc'])) {
        return sanitize_text_field($_COOKIE['_fbc']);
    }

    // Fallback If we captured fbclid ourselves, build a new fb.* string
    if (! empty($_COOKIE['unipixel_fbclid'])) {
        $raw_fbclid = sanitize_text_field($_COOKIE['unipixel_fbclid']);

        // Detect domainIndex from existing _fbc, else default to 1
        $domainIndex = '1';
        if (! empty($_COOKIE['_fbc'])) {
            $parts = explode('.', sanitize_text_field($_COOKIE['_fbc']));
            if (count($parts) >= 3 && in_array($parts[1], ['0', '1', '2'], true)) {
                $domainIndex = $parts[1];
            }
        }

        // Millisecond timestamp
        $tsMs = $eventTimeStampMs_in
            ? (int) $eventTimeStampMs_in
            : (int) round(microtime(true) * 1000);

        return "fb.{$domainIndex}.{$tsMs}.{$raw_fbclid}";
    }



    // 3) Nothing available
    return '';
}



/**
 * Retrieve TikTok identifiers (_ttp and ttclid).
 *
 * Ensures ttclid is persisted in unipixel_ttclid (like unipixel_fbclid/gclid)
 * and returns a unified array for consistent use across server and client logic.
 *
 * @return array {
 *     @type string $ttp_cookie TikTok Pixel cookie (_ttp)
 *     @type string $ttclid     TikTok click ID (persisted in unipixel_ttclid)
 * }
 */
function unipixel_get_tt_value()
{
    // --- 1. Capture ttclid from query if present ---
    if (!empty($_GET['ttclid'])) {
        $ttclid_raw = sanitize_text_field($_GET['ttclid']);
        setcookie(
            'unipixel_ttclid',
            $ttclid_raw,
            [
                'expires'  => time() + (90 * DAY_IN_SECONDS),
                'path'     => '/',
                'secure'   => is_ssl(),
                'httponly' => false,
                'samesite' => 'Lax',
            ]
        );

        $_COOKIE['unipixel_ttclid'] = $ttclid_raw; // make immediately available
    }

    // --- 2. Read identifiers ---
    $ttp_cookie = '';
    if (!empty($_COOKIE['_ttp'])) {
        $ttp_cookie = sanitize_text_field(urldecode($_COOKIE['_ttp']));
    }

    $ttclid_value = '';
    if (!empty($_COOKIE['unipixel_ttclid'])) {
        $ttclid_value = sanitize_text_field($_COOKIE['unipixel_ttclid']);
    } elseif (!empty($_COOKIE['ttclid'])) {
        $ttclid_value = sanitize_text_field($_COOKIE['ttclid']);
    } elseif (!empty($_GET['ttclid'])) {
        $ttclid_value = sanitize_text_field($_GET['ttclid']);
    }

    // --- 3. Return unified structure ---
    return [
        'ttp_cookie' => $ttp_cookie,
        'ttclid'     => $ttclid_value,
    ];
}



/**
 * Grab the raw GCLID for Measurement Protocol or client-side payloads.
 *
 * @return string
 */
function unipixel_get_gclid_value()
{
    // 1) Prefer our own cookie if set
    if (! empty($_COOKIE['unipixel_gclid'])) {
        return sanitize_text_field(wp_unslash($_COOKIE['unipixel_gclid']));
    }

    // 2) Fallback to conversion-linker cookie (_gcl_aw)
    if (! empty($_COOKIE['_gcl_aw'])) {
        $parts = explode('.', sanitize_text_field(wp_unslash($_COOKIE['_gcl_aw'])));
        if (isset($parts[2])) {
            return $parts[2];
        }
    }

    return '';
}



function unipixel_normalize_ga_client_id($value)
{
    $value = trim((string)$value);
    if ($value === '') return '';
    if (preg_match('/^\d+\.\d+$/', $value)) return $value;                 // already X.Y
    if (preg_match('/^GA\d+\.\d+\.(\d+\.\d+)$/', $value, $m)) return $m[1]; // strip GAx.y.
    return ''; // unknown format
}


function unipixel_get_msclkid_value()
{
    if (! empty($_COOKIE['unipixel_msclkid'])) {
        return sanitize_text_field(wp_unslash($_COOKIE['unipixel_msclkid']));
    }

    return '';
}


/**
 * Return the full current page URL, with query string, in WordPress.
 * Falls back to $_SERVER vars if WP globals aren't available.
 *
 * @return string Current page URL, sanitized.
 */
function unipixel_get_current_page_url()
{
    // If WP is routing, build via WP functions.
    if (did_action('init') && function_exists('home_url')) {
        // add_query_arg( null, null ) returns the current request URI+query
        $path = add_query_arg(null, null);
        $url  = home_url($path);
    }
    // Fallback: build from server variables
    else {
        $scheme = (is_ssl() || (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'))
            ? 'https://'
            : 'http://';
        $host = $_SERVER['HTTP_HOST']    ?? '';
        $uri  = $_SERVER['REQUEST_URI']  ?? '';
        $url  = $scheme . $host . $uri;
    }

    return esc_url_raw(wp_unslash($url));
}




/**
 * Return the client's IP address, preferring IPv6 if available.
 *
 * @return string|null IP address (IPv6 or IPv4), or null if none found.
 */
function unipixel_get_ip_address()
{
    // 1) If behind a proxy/CDN, check X-Forwarded-For first
    if (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $first = trim($parts[0]);
        if (filter_var($first, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $first;
        }
    }

    // 2) Fallback to REMOTE_ADDR
    $addr = $_SERVER['REMOTE_ADDR'] ?? '';
    if (filter_var($addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        return $addr;
    }

    // 3) Optionally return IPv4 if you want a fallback
    if (filter_var($addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return $addr;
    }

    return null;
}




/**
 * Given a WC_Product (simple or variation), return an array of its category names.
 *
 * @param WC_Product $product
 * @return string[] Sanitized category names
 */
function unipixel_get_product_categories($product)
{
    // If this is a variation, switch to its parent product ID
    if ($product instanceof WC_Product_Variation) {
        $product_id = $product->get_parent_id();
    } else {
        $product_id = $product->get_id();
    }

    // Fetch the category names
    $cats = wp_get_post_terms(
        $product_id,
        'product_cat',
        ['fields' => 'names']
    );

    // Sanitize and return
    return array_map('sanitize_text_field', (array) $cats);
}



function unipixel_get_product_variant($product)
{
    if (! $product instanceof WC_Product_Variation) {
        return '';
    }

    // get_attributes() returns selected variation attributes
    // e.g. ['pa_colour' => 'blue', 'pa_size' => 'large']
    $attributes = $product->get_attributes();
    if (empty($attributes) || ! is_array($attributes)) {
        return '';
    }

    // Build a readable string from attribute values, e.g. "blue / large"
    $parts = [];
    foreach ($attributes as $value) {
        $value = trim($value);
        if ($value !== '') {
            $parts[] = sanitize_text_field($value);
        }
    }

    return implode(' / ', $parts);
}


/**
 * Detect and deny common crawlers, bots, and non-human agents.
 *
 * Returns true if the current request appears to be from a bot,
 * crawler, feed fetcher, headless browser, or known preview agent.
 *
 * @return bool True if request should be denied/skipped.
 */
function unipixel_denyCrawlerBots(): bool
{
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    // Empty or missing UA → almost certainly automated
    if ($userAgent === '') {
        return true;
    }

    // Common crawler, bot, or headless identifiers
    $patterns = '/
        bot|
        crawl|
        spider|
        slurp|
        facebookexternalhit|
        pinterest|
        monitor|
        uptime|
        headless|
        feedfetcher|
        googlewebpreview|
        discordbot|
        embedly|
        crawler|
        wget|
        curl|
        python-requests|
        dataminr|
        preview|
        validator
    /ix'; // case-insensitive, verbose

    return (bool) preg_match($patterns, $userAgent);
}



function unipixel_construct_client_side_send_log($platformId, $event_name, $params)
{
    $log_payload = [
        'event_name' => $event_name,
    ];

    // Small helper to conditionally add values
    $add_if_present = function (&$arr, $key, $value) {
        if (isset($value) && $value !== '' && $value !== [] && $value !== null) {
            $arr[$key] = $value;
        }
    };


    $get_event_value = function ($params) {
        if (isset($params['plcehldr_value'])) {
            return (float)$params['plcehldr_value'];
        }

        if (isset($params['plcehldr_price'])) {
            return (float)$params['plcehldr_price'];
        }

        return 0.0;
    };

    switch ((int)$platformId) {

        // --------------------------------------------------------
        // META (Facebook)
        // --------------------------------------------------------
        case 1:
            $add_if_present($log_payload, 'event_id', $params['plcehldr_eventId'] ?? '');
            $add_if_present($log_payload, 'currency', $params['plcehldr_currency'] ?? '');
            $add_if_present($log_payload, 'value', $get_event_value($params));
            $add_if_present($log_payload, 'content_ids', [(string)($params['plcehldr_productId'] ?? '')]);
            $add_if_present($log_payload, 'content_type', 'product');
            $add_if_present($log_payload, 'client_ip', $params['plcehldr_clientIp'] ?? '');
            $add_if_present($log_payload, 'user_agent', $params['plcehldr_userAgent'] ?? '');
            $add_if_present($log_payload, 'fbp', $params['plcehldr_fbpCookie'] ?? '');
            $add_if_present($log_payload, 'fbc', $params['plcehldr_fbcCookie'] ?? '');
            $add_if_present($log_payload, 'email_hashed', $params['plcehldr_hashedEmail'] ?? '');
            $add_if_present($log_payload, 'phone_hashed', $params['plcehldr_hashedPhone'] ?? '');
            break;

        // --------------------------------------------------------
        // GOOGLE
        // --------------------------------------------------------
        case 4:
            $add_if_present($log_payload, 'event_id', $params['plcehldr_eventId'] ?? '');
            $add_if_present($log_payload, 'currency', $params['plcehldr_currency'] ?? '');
            $add_if_present($log_payload, 'value', $get_event_value($params));

            $add_if_present($log_payload, 'client_id', $params['client_id'] ?? '');
            $add_if_present($log_payload, 'user_agent', $params['plcehldr_userAgent'] ?? '');
            $add_if_present($log_payload, 'gclid', $params['plcehldr_gclid'] ?? '');
            if (!empty($params['plcehldr_lineItems']) && is_array($params['plcehldr_lineItems'])) {
                $items = [];

                foreach ($params['plcehldr_lineItems'] as $li) {
                    $items[] = [
                        'item_id'   => $li['plcehldr_itemId'] ?? '',
                        'item_name' => $li['plcehldr_itemName'] ?? '',
                        'quantity'  => (int)($li['plcehldr_quantity'] ?? 1),
                        'price'     => (float)($li['plcehldr_price'] ?? 0),
                    ];
                }

                $add_if_present($log_payload, 'items', $items);
            } elseif (!empty($params['plcehldr_productId'])) {
                $log_payload['items'] = [[
                    'item_id'   => $params['plcehldr_productId'],
                    'item_name' => $params['plcehldr_itemName'] ?? '',
                    'price'     => (float)($params['plcehldr_price'] ?? 0),
                ]];
            }

            break;

        // --------------------------------------------------------
        // TIKTOK
        // --------------------------------------------------------
        case 3:
            $add_if_present($log_payload, 'event_id', $params['plcehldr_eventId'] ?? '');
            $add_if_present($log_payload, 'currency', $params['plcehldr_currency'] ?? '');
            $add_if_present($log_payload, 'value', $get_event_value($params));

            $add_if_present($log_payload, 'ip', $params['plcehldr_clientIp'] ?? '');
            $add_if_present($log_payload, 'user_agent', $params['plcehldr_userAgent'] ?? '');
            $add_if_present($log_payload, 'ttclid', $params['plcehldr_ttclid'] ?? ($params['ttclid'] ?? ''));
            $add_if_present($log_payload, 'ttp', $params['plcehldr_ttp'] ?? ($params['ttp'] ?? ''));
            $add_if_present($log_payload, 'email_hashed', $params['plcehldr_hashedEmail'] ?? '');
            $add_if_present($log_payload, 'phone_hashed', $params['plcehldr_hashedPhone'] ?? '');
            if (!empty($params['plcehldr_lineItems']) && is_array($params['plcehldr_lineItems'])) {
                $contents = [];

                foreach ($params['plcehldr_lineItems'] as $li) {
                    $contents[] = [
                        'content_id'   => (string)($li['plcehldr_itemId'] ?? ''),
                        'content_name' => $li['plcehldr_itemName'] ?? '',
                        'content_type' => 'product',
                        'quantity'     => (int)($li['plcehldr_quantity'] ?? 1),
                        'price'        => (float)($li['plcehldr_price'] ?? 0),
                    ];
                }

                $add_if_present($log_payload, 'contents', $contents);
            } elseif (!empty($params['plcehldr_productId'])) {
                $log_payload['contents'] = [[
                    'content_id'   => (string)$params['plcehldr_productId'],
                    'content_name' => $params['plcehldr_itemName'] ?? '',
                    'content_type' => 'product',
                    'quantity'     => (int)($params['plcehldr_quantity'] ?? 1),
                    'price'        => (float)($params['plcehldr_price'] ?? 0),
                ]];
            }

            break;

        // --------------------------------------------------------
        // PINTEREST
        // --------------------------------------------------------
        case 2:
            $add_if_present($log_payload, 'event_id', $params['plcehldr_eventId'] ?? '');
            $add_if_present($log_payload, 'currency', $params['plcehldr_currency'] ?? '');
            $add_if_present($log_payload, 'value', $get_event_value($params));

            break;

        // --------------------------------------------------------
        // MICROSOFT
        // --------------------------------------------------------
        case 5:
            $add_if_present($log_payload, 'event_id', $params['plcehldr_eventId'] ?? '');
            $add_if_present($log_payload, 'currency', $params['plcehldr_currency'] ?? '');
            $add_if_present($log_payload, 'value', $get_event_value($params));
            $add_if_present($log_payload, 'client_ip', $params['plcehldr_clientIp'] ?? '');
            $add_if_present($log_payload, 'user_agent', $params['plcehldr_userAgent'] ?? '');
            $add_if_present($log_payload, 'msclkid', $params['plcehldr_msclkid'] ?? '');
            if (!empty($params['plcehldr_lineItems']) && is_array($params['plcehldr_lineItems'])) {
                $items = [];
                foreach ($params['plcehldr_lineItems'] as $li) {
                    $items[] = [
                        'item_id'   => $li['plcehldr_itemId'] ?? '',
                        'item_name' => $li['plcehldr_itemName'] ?? '',
                        'quantity'  => (int)($li['plcehldr_quantity'] ?? 1),
                        'price'     => (float)($li['plcehldr_price'] ?? 0),
                    ];
                }
                $add_if_present($log_payload, 'items', $items);
            } elseif (!empty($params['plcehldr_productId'])) {
                $log_payload['items'] = [[
                    'item_id'   => $params['plcehldr_productId'],
                    'item_name' => $params['plcehldr_itemName'] ?? '',
                    'price'     => (float)($params['plcehldr_price'] ?? 0),
                ]];
            }
            break;

        // --------------------------------------------------------
        // FALLBACK
        // --------------------------------------------------------
        default:
            $log_payload = $params;
    }

    return $log_payload;
}



function unipixel_get_user_identifier_for_transient(): string
{
    if (is_user_logged_in()) return (string) get_current_user_id();
    $ip  = $_SERVER['REMOTE_ADDR']      ?? '';
    $ua  = $_SERVER['HTTP_USER_AGENT']  ?? '';
    return md5($ip . $ua);
}




/**
 * Normalize phone number into digits-only E.164 format (no '+') for hashing.
 * Automatically adds the correct country code based on billing or base country.
 *
 * @param string $rawPhone        Raw phone number as entered by customer
 * @param string $billingCountry  2-letter ISO country code (e.g. "AU")
 * @param string $fallbackCountry 2-letter ISO fallback (e.g. store base country)
 * @return string Normalized digits-only number (e.g. "61412345678")
 */
function unipixel_normalize_phone_for_hashing($rawPhone, $billingCountry = '', $fallbackCountry = '')
{
    if (empty($rawPhone)) {
        return '';
    }

    // 1. Strip all non-digit characters
    $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);

    // 2. If already looks like an international number (starts 1–9, 10–15 digits), keep it
    if (preg_match('/^[1-9][0-9]{8,14}$/', $cleanPhone)) {
        return $cleanPhone;
    }

    // 3. Determine country for prefix lookup
    $country = strtoupper($billingCountry ?: $fallbackCountry ?: 'US');

    // 4. Country calling code map (full ISO list)
    static $prefixes = [
        'US' => '1',
        'CA' => '1',
        'MX' => '52',
        'BR' => '55',
        'AR' => '54',
        'CL' => '56',
        'CO' => '57',
        'PE' => '51',
        'VE' => '58',
        'GB' => '44',
        'IE' => '353',
        'FR' => '33',
        'DE' => '49',
        'IT' => '39',
        'ES' => '34',
        'PT' => '351',
        'NL' => '31',
        'BE' => '32',
        'CH' => '41',
        'AT' => '43',
        'DK' => '45',
        'SE' => '46',
        'NO' => '47',
        'FI' => '358',
        'PL' => '48',
        'CZ' => '420',
        'SK' => '421',
        'HU' => '36',
        'RO' => '40',
        'BG' => '359',
        'GR' => '30',
        'TR' => '90',
        'RU' => '7',
        'UA' => '380',
        'BY' => '375',
        'RS' => '381',
        'HR' => '385',
        'SI' => '386',
        'BA' => '387',
        'MK' => '389',
        'MD' => '373',
        'EE' => '372',
        'LV' => '371',
        'LT' => '370',
        'IS' => '354',
        'AU' => '61',
        'NZ' => '64',
        'SG' => '65',
        'MY' => '60',
        'TH' => '66',
        'VN' => '84',
        'PH' => '63',
        'ID' => '62',
        'KH' => '855',
        'MM' => '95',
        'IN' => '91',
        'PK' => '92',
        'BD' => '880',
        'LK' => '94',
        'NP' => '977',
        'HK' => '852',
        'TW' => '886',
        'JP' => '81',
        'KR' => '82',
        'CN' => '86',
        'SA' => '966',
        'AE' => '971',
        'IL' => '972',
        'IR' => '98',
        'JO' => '962',
        'KW' => '965',
        'QA' => '974',
        'OM' => '968',
        'BH' => '973',
        'EG' => '20',
        'ZA' => '27',
        'NG' => '234',
        'KE' => '254',
        'TZ' => '255',
        'UG' => '256',
        'GH' => '233',
        'DZ' => '213',
        'MA' => '212',
        'TN' => '216',
        'RU' => '7',
        'KZ' => '7',
        'UZ' => '998',
        'TM' => '993',
        'KG' => '996',
        'TJ' => '992',
        'AZ' => '994',
        'AM' => '374',
        'GE' => '995',
        'KR' => '82',
        'JP' => '81',
        'CN' => '86',
        'HK' => '852',
        'MO' => '853',
        'TW' => '886',
        'ID' => '62',
        'MY' => '60',
        'SG' => '65',
        'TH' => '66',
        'VN' => '84',
        'PH' => '63',
        'KH' => '855',
        'LA' => '856',
        'AF' => '93',
        'AE' => '971',
        'QA' => '974',
        'OM' => '968',
        'KW' => '965',
        'BH' => '973',
        'IQ' => '964',
        'SY' => '963',
        'YE' => '967',
        'IR' => '98',
        'IL' => '972',
        'JO' => '962',
        'PS' => '970',
        'LB' => '961',
        'SA' => '966',
        'ET' => '251',
        'SD' => '249',
        'DZ' => '213',
        'MA' => '212',
        'TN' => '216',
        'LY' => '218',
        'AO' => '244',
        'NA' => '264',
        'ZW' => '263',
        'ZM' => '260',
        'MW' => '265',
        'MZ' => '258',
        'BW' => '267',
        'LS' => '266',
        'SZ' => '268',
        'RW' => '250',
        'BI' => '257',
        'UG' => '256',
        'KE' => '254',
        'NG' => '234',
        'GH' => '233',
        'SN' => '221',
        'ML' => '223',
        'BF' => '226',
        'CI' => '225',
        'NE' => '227',
        'TD' => '235',
        'CM' => '237',
        'GA' => '241',
        'CG' => '242',
        'CD' => '243',
        // Americas extras
        'PR' => '1',
        'VI' => '1',
        'JM' => '1',
        'TT' => '1',
        'BB' => '1',
        'DO' => '1',
        'HT' => '509',
        'CU' => '53',
        // Oceania
        'PG' => '675',
        'FJ' => '679',
        'WS' => '685',
        'TO' => '676',
        'SB' => '677',
        'VU' => '678',
        'NC' => '687',
        'PF' => '689',
        // Europe microstates
        'LI' => '423',
        'LU' => '352',
        'MC' => '377',
        'AD' => '376',
        'SM' => '378',
        'VA' => '379',
        'GI' => '350',
        'MT' => '356',
        'CY' => '357',
        'AL' => '355',
        'ME' => '382',
        // fallback
        'DEFAULT' => '1'
    ];

    $prefix = $prefixes[$country] ?? $prefixes['DEFAULT'];

    // 5. If starts with 0 → assume local format, replace with prefix
    if (preg_match('/^0[0-9]{8,9}$/', $cleanPhone)) {
        $cleanPhone = $prefix . substr($cleanPhone, 1);
    } elseif (!preg_match('/^[1-9]/', $cleanPhone)) {
        // Remove leading zeros if any
        $cleanPhone = ltrim($cleanPhone, '0');
        $cleanPhone = $prefix . $cleanPhone;
    }

    return $cleanPhone;
}


/**
 * Returns hashed PII for advanced matching across all platforms.
 *
 * Data sources (in priority order):
 * 1. $order object (Purchase event) -- billing_email, billing_phone, etc.
 * 2. Logged-in user meta -- user_email, billing_phone, etc.
 * 3. Guest -- returns empty array.
 *
 * All values are SHA-256 hashed (lowercase, trimmed). Phone numbers are
 * normalised via unipixel_normalize_phone_for_hashing() before hashing.
 *
 * @param WC_Order|null $order  Optional WooCommerce order (pass for Purchase event).
 * @return array  Associative array of plcehldr_hashed* keys. Only non-empty values included.
 */
function unipixel_get_advanced_matching_data($order = null)
{
    $enabled = get_option('unipixel_advanced_matching_enabled', true);
    if (!$enabled) {
        return [];
    }

    $email   = '';
    $phone   = '';
    $fn      = '';
    $ln      = '';
    $ct      = '';
    $st      = '';
    $zp      = '';
    $country = '';

    // Source 1: Order billing data (highest priority -- Purchase event)
    if ($order instanceof WC_Order) {
        $email   = $order->get_billing_email();
        $phone   = $order->get_billing_phone();
        $fn      = $order->get_billing_first_name();
        $ln      = $order->get_billing_last_name();
        $ct      = $order->get_billing_city();
        $st      = $order->get_billing_state();
        $zp      = $order->get_billing_postcode();
        $country = $order->get_billing_country();
    }

    // Source 2: Logged-in user meta (fills gaps or used when no order)
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $uid  = $user->ID;

        if (empty($email))   $email   = $user->user_email;
        if (empty($phone))   $phone   = get_user_meta($uid, 'billing_phone', true);
        if (empty($fn))      $fn      = get_user_meta($uid, 'billing_first_name', true) ?: $user->first_name;
        if (empty($ln))      $ln      = get_user_meta($uid, 'billing_last_name', true) ?: $user->last_name;
        if (empty($ct))      $ct      = get_user_meta($uid, 'billing_city', true);
        if (empty($st))      $st      = get_user_meta($uid, 'billing_state', true);
        if (empty($zp))      $zp      = get_user_meta($uid, 'billing_postcode', true);
        if (empty($country)) $country = get_user_meta($uid, 'billing_country', true);
    }

    // Guest with no order -- nothing to hash
    if (empty($email) && empty($phone) && empty($fn)) {
        return [];
    }

    $result = [];

    // Email: lowercase, trim, SHA-256
    if (!empty($email)) {
        $result['plcehldr_hashedEmail'] = hash('sha256', strtolower(trim($email)));
    }

    // Phone: normalise then SHA-256 (reuse existing helper)
    if (!empty($phone)) {
        $storeBase = '';
        if (function_exists('wc_get_base_location')) {
            $storeBase = wc_get_base_location()['country'] ?? '';
        }
        $normalized = unipixel_normalize_phone_for_hashing($phone, $country, $storeBase);
        if ($normalized) {
            $result['plcehldr_hashedPhone'] = hash('sha256', $normalized);
        }
    }

    // Name and address fields: lowercase, trim, SHA-256
    if (!empty($fn))      $result['plcehldr_hashedFn']      = hash('sha256', strtolower(trim($fn)));
    if (!empty($ln))      $result['plcehldr_hashedLn']      = hash('sha256', strtolower(trim($ln)));
    if (!empty($ct))      $result['plcehldr_hashedCt']      = hash('sha256', strtolower(trim($ct)));
    if (!empty($st))      $result['plcehldr_hashedSt']      = hash('sha256', strtolower(trim($st)));
    if (!empty($zp))      $result['plcehldr_hashedZp']      = hash('sha256', strtolower(trim($zp)));
    if (!empty($country)) $result['plcehldr_hashedCountry'] = hash('sha256', strtolower(trim($country)));

    return $result;
}
