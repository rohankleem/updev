<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

// Primary Key Changes: dbDelta does not handle primary key changes very well. If you need to change a primary key, it's often better to manually handle this.
// Column Renaming: dbDelta does not detect column renaming. Manually rename columns if needed.
// Dropping Columns: dbDelta does not drop columns. Need to handle this manually.

// config/schema.php

function unipixel_update_schema()
{
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // Define table schemas here (fresh installs get the new columns immediately)
    $table_schemas = [
        // Platforms
        "CREATE TABLE {$wpdb->prefix}unipixel_platform_settings (
            id INT NOT NULL,
            platform_name VARCHAR(255),
            pixel_id VARCHAR(255),
            access_token VARCHAR(512),
            platform_enabled TINYINT(1) DEFAULT 1,
            additional_id VARCHAR(255) DEFAULT '',
            pixel_setting VARCHAR(255) DEFAULT 'include',
            pageview_send_serverside TINYINT(1) DEFAULT 1,
            pageview_send_clientside TINYINT(1) DEFAULT 1,
            serverside_global_enabled TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (id)
        );",

        // Custom events
        "CREATE TABLE {$wpdb->prefix}unipixel_events_settings (
            id INT AUTO_INCREMENT,
            platform_id INT,
            element_ref VARCHAR(255),
            event_trigger VARCHAR(255),
            event_name VARCHAR(255),
            event_description TEXT,
            send_client TINYINT(1) NOT NULL DEFAULT 1,
            send_server TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (id)
        );",

        // Generic event log
        "CREATE TABLE {$wpdb->prefix}unipixel_event_log (
            id INT AUTO_INCREMENT,
            platform_id INT,
            platform_name VARCHAR(255),
            element_ref VARCHAR(255),
            event_trigger VARCHAR(255),
            event_name VARCHAR(255),
            response_message TEXT,
            sent_data TEXT,
            method VARCHAR(10),
            party VARCHAR(10),
            event_order VARCHAR(20),
            log_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        );",

        // Log count (gauge)
        "CREATE TABLE {$wpdb->prefix}unipixel_log_count (
            id INT AUTO_INCREMENT,
            count INT DEFAULT 0,
            PRIMARY KEY (id)
        );",

        // WooCommerce events
        "CREATE TABLE {$wpdb->prefix}unipixel_woocomm_event_settings (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            platform_id INT(11) NOT NULL,
            event_local_ref VARCHAR(255) NOT NULL,
            event_platform_ref VARCHAR(255) NOT NULL,
            event_description TEXT DEFAULT NULL,
            event_enabled TINYINT(1) NOT NULL DEFAULT 0,
            send_server_log_response TINYINT(1) NOT NULL DEFAULT 0,
            send_client TINYINT(1) NOT NULL DEFAULT 1, 
            send_server TINYINT(1) NOT NULL DEFAULT 0, 
            PRIMARY KEY (id)
        );"
    ];

    foreach ($table_schemas as $sql) {
        dbDelta($sql);
    }


    // (3) WooCommerce Events Setup and Data Check
    unipixel_insert_default_woo_events();
    unipixel_patch_existing_woo_event_settings();

    // (4) Ensure log count entry and initial platform settings
    unipixel_ensure_logcount_and_platform_settings();

    // (5) Ensure logging options are set
    unipixel_ensure_logging_settings_exist();

    // (6) One-time data back-fill for upgrades to separate transport settings
    if (function_exists('unipixel_setup_separate_transport_settings')) {
        unipixel_setup_separate_transport_settings();
    }

    unipixel_patch_event_log_columns();

    // (7) One-time back-fill for serverside_global_enabled column
    if (function_exists('unipixel_setup_serverside_global_enabled')) {
        unipixel_setup_serverside_global_enabled();
    }

    if ($wpdb->last_error) {
        error_log('WordPress Database Error: ' . $wpdb->last_error);
    }
}

/**
 * Insert or repair Woo defaults. On insert, also set send_client/send_server
 * using platform-guided defaults:
 * - Meta (1): both on
 * - Google (4): purchase -> both on; others -> client-only
 */
function unipixel_insert_default_woo_events()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'unipixel_woocomm_event_settings';

    // Ensure columns exist before populating (defensive for upgrades pre-dbDelta)
    $needs = [
        'send_server_log_response' => "ALTER TABLE %i ADD COLUMN send_server_log_response TINYINT(1) NOT NULL DEFAULT 0",
        'send_client'                 => "ALTER TABLE %i ADD COLUMN send_client TINYINT(1) NOT NULL DEFAULT 1",
        'send_server'                 => "ALTER TABLE %i ADD COLUMN send_server TINYINT(1) NOT NULL DEFAULT 0",
    ];
    foreach ($needs as $col => $ddl) {
        $exists = $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM %i LIKE %s", $table_name, $col));
        if (!$exists) {
            $wpdb->query($wpdb->prepare($ddl, $table_name));
        }
    }

    $default_events = unipixel_get_default_woo_event_definitions();

    foreach ($default_events as $event) {
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM %i WHERE platform_id = %d AND event_platform_ref = %s",
            $table_name,
            $event['platform_id'],
            $event['event_platform_ref']
        ));

        // Determine transport defaults by platform/event
        $send_client = 0;
        $send_server = 0;
        if ((int)$event['platform_id'] === 1) { // Meta
            $send_client = 1;
            $send_server = 1;
        } elseif ((int)$event['platform_id'] === 4) { // Google
            if ($event['event_platform_ref'] === 'purchase') {
                $send_client = 1;
                $send_server = 1;
            } else {
                $send_client = 1;
                $send_server = 0;
            }
        } elseif ((int)$event['platform_id'] === 3) { // TikTok
            // Default: send both client and server, same as Meta
            // You can adjust to client-only for non-purchase events if you prefer
            if ($event['event_platform_ref'] === 'Purchase') {
                $send_client = 1;
                $send_server = 1;
            } else {
                $send_client = 1;
                $send_server = 1;
            }
        } elseif ((int)$event['platform_id'] === 2) { // Pinterest
            // Full dedup supported — send both client and server
            $send_client = 1;
            $send_server = 1;
        } elseif ((int)$event['platform_id'] === 5) { // Microsoft
            // Full dedup supported via CAPI — send both client and server
            $send_client = 1;
            $send_server = 1;
        } // others remain 0/0

        if (!$existing) {
            $wpdb->insert(
                $table_name,
                [
                    'platform_id'                 => $event['platform_id'],
                    'event_local_ref'             => $event['event_local_ref'],
                    'event_platform_ref'          => $event['event_platform_ref'],
                    'event_description'           => $event['event_description'],
                    'event_enabled'               => $event['event_enabled'], // legacy mirror for now
                    'send_server_log_response' => isset($event['send_server_log_response']) ? $event['send_server_log_response'] : 1,
                    'send_client'                 => $send_client,
                    'send_server'                 => $send_server,
                ],
                ['%d', '%s', '%s', '%s', '%d', '%d', '%d', '%d']
            );
        }
    }

    // Clean-up: Delete any existing events NOT in the default set (keep table tidy)
    $valid_pairs = [];
    foreach ($default_events as $event) {
        $valid_pairs[] = $wpdb->prepare(
            "(%d, %s)",
            $event['platform_id'],
            $event['event_platform_ref']
        );
    }

    if (!empty($valid_pairs)) {
        $valid_pairs_sql = implode(', ', $valid_pairs);
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM %i
                 WHERE (platform_id, event_platform_ref) NOT IN ($valid_pairs_sql)",
                $table_name
            )
        );
    }
}

function unipixel_patch_existing_woo_event_settings()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'unipixel_woocomm_event_settings';

    // Check if column exists before proceeding (legacy patch remains)
    $column = $wpdb->get_results(
        $wpdb->prepare(
            "SHOW COLUMNS FROM %i LIKE %s",
            $table_name,
            'event_enableresponselogging'
        )
    );

    if (empty($column)) {
        return; // column not found — nothing to patch
    }

    $default_events = unipixel_get_default_woo_event_definitions();

    foreach ($default_events as $event) {
        $platform_id   = $event['platform_id'];
        $platform_ref  = $event['event_platform_ref'];
        $desired_value = (int) ($event['event_enableresponselogging'] ?? 0);

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE %i
                 SET event_enableresponselogging = %d
                 WHERE platform_id = %d
                   AND event_platform_ref = %s
                   AND event_enableresponselogging = 0",
                $table_name,
                $desired_value,
                $platform_id,
                $platform_ref
            )
        );
    }
}

function unipixel_get_default_woo_event_definitions()
{
    $default_events = [
        // Meta (platform_id=1)
        [
            'platform_id'                 => 1,
            'event_local_ref'             => 'AddToCart (WooCommerce)',
            'event_platform_ref'          => 'AddToCart',
            'event_description'           => 'Send an AddToCart event to Meta when a product is added to the cart.',
            'event_enabled'               => 1,
            'send_server_log_response' => 1
        ],
        [
            'platform_id'                 => 1,
            'event_local_ref'             => 'InitiateCheckout (WooCommerce)',
            'event_platform_ref'          => 'InitiateCheckout',
            'event_description'           => 'Send an InitiateCheckout event to Meta when checkout begins.',
            'event_enabled'               => 1,
            'send_server_log_response' => 1
        ],
        [
            'platform_id'                 => 1,
            'event_local_ref'             => 'Purchase (WooCommerce)',
            'event_platform_ref'          => 'Purchase',
            'event_description'           => 'Send a Purchase event with order details to Meta on successful order.',
            'event_enabled'               => 1,
            'send_server_log_response' => 1
        ],
        [
            'platform_id'                 => 1,
            'event_local_ref'             => 'ViewContent (Product)',
            'event_platform_ref'          => 'ViewContent',
            'event_description'           => 'Send a ViewContent event with product details when a product is viewed.',
            'event_enabled'               => 1,
            'event_enableresponselogging' => 0
        ],

        // Google (platform_id=4)
        [
            'platform_id'                 => 4,
            'event_local_ref'             => 'add_to_cart (WooCommerce)',
            'event_platform_ref'          => 'add_to_cart',
            'event_description'           => 'Send an add_to_cart event to Google when a product is added to the cart.',
            'event_enabled'               => 1,
            'send_server_log_response' => 1
        ],
        [
            'platform_id'                 => 4,
            'event_local_ref'             => 'begin_checkout (WooCommerce)',
            'event_platform_ref'          => 'begin_checkout',
            'event_description'           => 'Send a begin_checkout event to Google when the user initiates the checkout process.',
            'event_enabled'               => 1,
            'send_server_log_response' => 1
        ],
        [
            'platform_id'                 => 4,
            'event_local_ref'             => 'purchase (WooCommerce)',
            'event_platform_ref'          => 'purchase',
            'event_description'           => 'Send a purchase event to Google with order details on successful order.',
            'event_enabled'               => 1,
            'send_server_log_response' => 1
        ],
        [
            'platform_id'                 => 4,
            'event_local_ref'             => 'view_item (Product)',
            'event_platform_ref'          => 'view_item',
            'event_description'           => 'Send a view_item event with product details when a product is viewed.',
            'event_enabled'               => 1,
            'send_server_log_response' => 0
        ],

        // TikTok (platform_id=3)
        [
            'platform_id'                 => 3,
            'event_local_ref'             => 'AddToCart (WooCommerce)',
            'event_platform_ref'          => 'AddToCart',
            'event_description'           => 'Send an AddToCart event to TikTok when a product is added to the cart.',
            'event_enabled'               => 1,
            'send_server_log_response'    => 1,
        ],
        [
            'platform_id'                 => 3,
            'event_local_ref'             => 'InitiateCheckout (WooCommerce)',
            'event_platform_ref'          => 'InitiateCheckout',
            'event_description'           => 'Send an InitiateCheckout event to TikTok when checkout begins.',
            'event_enabled'               => 1,
            'send_server_log_response'    => 1,
        ],
        [
            'platform_id'                 => 3,
            'event_local_ref'             => 'Purchase (WooCommerce)',
            'event_platform_ref'          => 'Purchase',
            'event_description'           => 'Send a Purchase event to TikTok when a purchase is completed.',
            'event_enabled'               => 1,
            'send_server_log_response'    => 1,
        ],
        [
            'platform_id'                 => 3,
            'event_local_ref'             => 'ViewContent (Product)',
            'event_platform_ref'          => 'ViewContent',
            'event_description'           => 'Send a ViewContent event to TikTok with product details when a product is viewed.',
            'event_enabled'               => 1,
            'send_server_log_response'    => 0,
        ],

        // Pinterest (platform_id=2)
        [
            'platform_id'                 => 2,
            'event_local_ref'             => 'AddToCart (WooCommerce)',
            'event_platform_ref'          => 'add_to_cart',
            'event_description'           => 'Send an add_to_cart event to Pinterest when a product is added to the cart.',
            'event_enabled'               => 1,
            'send_server_log_response'    => 1,
        ],
        [
            'platform_id'                 => 2,
            'event_local_ref'             => 'InitiateCheckout (WooCommerce)',
            'event_platform_ref'          => 'initiate_checkout',
            'event_description'           => 'Send an initiate_checkout event to Pinterest when checkout begins.',
            'event_enabled'               => 1,
            'send_server_log_response'    => 1,
        ],
        [
            'platform_id'                 => 2,
            'event_local_ref'             => 'Purchase (WooCommerce)',
            'event_platform_ref'          => 'checkout',
            'event_description'           => 'Send a checkout event to Pinterest when a purchase is completed.',
            'event_enabled'               => 1,
            'send_server_log_response'    => 1,
        ],
        [
            'platform_id'                 => 2,
            'event_local_ref'             => 'ViewContent (Product)',
            'event_platform_ref'          => 'view_content',
            'event_description'           => 'Send a view_content event to Pinterest with product details when a product is viewed.',
            'event_enabled'               => 1,
            'send_server_log_response'    => 0,
        ],

        // Microsoft (platform_id=5)
        [
            'platform_id'                 => 5,
            'event_local_ref'             => 'AddToCart (WooCommerce)',
            'event_platform_ref'          => 'add_to_cart',
            'event_description'           => 'Send an add_to_cart event to Microsoft when a product is added to the cart.',
            'event_enabled'               => 1,
            'send_server_log_response'    => 1,
        ],
        [
            'platform_id'                 => 5,
            'event_local_ref'             => 'InitiateCheckout (WooCommerce)',
            'event_platform_ref'          => 'begin_checkout',
            'event_description'           => 'Send a begin_checkout event to Microsoft when checkout begins.',
            'event_enabled'               => 1,
            'send_server_log_response'    => 1,
        ],
        [
            'platform_id'                 => 5,
            'event_local_ref'             => 'Purchase (WooCommerce)',
            'event_platform_ref'          => 'purchase',
            'event_description'           => 'Send a purchase event to Microsoft when a purchase is completed.',
            'event_enabled'               => 1,
            'send_server_log_response'    => 1,
        ],
        [
            'platform_id'                 => 5,
            'event_local_ref'             => 'ViewContent (Product)',
            'event_platform_ref'          => 'view_item',
            'event_description'           => 'Send a view_item event to Microsoft with product details when a product is viewed.',
            'event_enabled'               => 1,
            'send_server_log_response'    => 0,
        ],

    ];

    return $default_events;
}

/**
 * Ensures there's an initial log count entry
 * and inserts the default platform settings (Meta, Google, etc.) if they don't already exist.
 * Now seeds pageview_send_clientside = 1 as part of initial rows.
 */
function unipixel_ensure_logcount_and_platform_settings()
{
    global $wpdb;

    // Ensure there's an initial log count entry
    $log_count_table = esc_sql($wpdb->prefix . 'unipixel_log_count');
    $query = $wpdb->prepare("SELECT COUNT(*) FROM %i WHERE 1 = %d", $log_count_table, 1);
    $count = $wpdb->get_var($query);

    if ($count == 0) {
        $wpdb->insert($log_count_table, ['count' => 0], ['%d']);
    }

    if ($wpdb->last_error) {
        error_log('WordPress Database Error (log_count): ' . $wpdb->last_error);
    }

    // Insert initial platform settings if they don't exist
    $platform_table = $wpdb->prefix . 'unipixel_platform_settings';
    $default_platforms = [
        [
            'id' => 1,
            'platform_name' => 'Meta',
            'pixel_id' => '',
            'access_token' => '',
            'platform_enabled' => 0,
            'additional_id' => '',
            'pixel_setting' => 'include',
            'pageview_send_serverside' => 1,
            'pageview_send_clientside' => 1,
            'serverside_global_enabled' => 0
        ],
        [
            'id' => 2,
            'platform_name' => 'Pinterest',
            'pixel_id' => '',
            'access_token' => '',
            'platform_enabled' => 0,
            'additional_id' => '',
            'pixel_setting' => 'include',
            'pageview_send_serverside' => 0,
            'pageview_send_clientside' => 1,
            'serverside_global_enabled' => 0
        ],
        [
            'id' => 3,
            'platform_name' => 'TikTok',
            'pixel_id' => '',
            'access_token' => '',
            'platform_enabled' => 0,
            'additional_id' => '',
            'pixel_setting' => 'include',
            'pageview_send_serverside' => 1,
            'pageview_send_clientside' => 1,
            'serverside_global_enabled' => 0
        ],
        [
            'id' => 4,
            'platform_name' => 'Google',
            'pixel_id' => '',
            'access_token' => '',
            'platform_enabled' => 0,
            'additional_id' => '',
            'pixel_setting' => 'include',
            'pageview_send_serverside' => 1,
            'pageview_send_clientside' => 1,
            'serverside_global_enabled' => 0
        ],
        [
            'id' => 5,
            'platform_name' => 'Microsoft',
            'pixel_id' => '',
            'access_token' => '',
            'platform_enabled' => 0,
            'additional_id' => '',
            'pixel_setting' => 'include',
            'pageview_send_serverside' => 1,
            'pageview_send_clientside' => 1,
            'serverside_global_enabled' => 0
        ]
    ];

    foreach ($default_platforms as $platform) {
        $query = $wpdb->prepare(
            "SELECT COUNT(*) FROM %i WHERE platform_name = %s",
            $platform_table,
            $platform['platform_name']
        );
        $count = $wpdb->get_var($query);

        if ($count == 0) {
            $wpdb->insert(
                $platform_table,
                $platform,
                ['%d', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%d', '%d']
            );
        }
    }

    if ($wpdb->last_error) {
        error_log('WordPress Database Error (platform_settings): ' . $wpdb->last_error);
    }
}

function unipixel_ensure_logging_settings_exist()
{
    $loggingDefaults = array(
        'enableLogging_Admin'           => false,
        'enableLogging_InitiateEvents'  => false,
        'enableLogging_SendEvents'      => false,
    );
    // Only set the option if it doesn't exist yet
    if (false === get_option('unipixel_logging_options')) {
        update_option('unipixel_logging_options', $loggingDefaults);
    }
}

/**
 * Schema delta + one-time back-fill for separate transport settings.
 * - Adds columns if missing (defensive for upgrades).
 * - Back-fills according to agreed policy, then marks as migrated.
 */
function unipixel_setup_separate_transport_settings()
{
    global $wpdb;

    // Avoid re-running back-fill if we've already migrated.
    $migrated = get_option('unipixel_transport_settings_migrated');

    $platform_table = $wpdb->prefix . 'unipixel_platform_settings';
    $woo_table      = $wpdb->prefix . 'unipixel_woocomm_event_settings';
    $custom_table   = $wpdb->prefix . 'unipixel_events_settings';

    // 1) Add transport columns if missing (safe even if dbDelta ran)
    $col = $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM %i LIKE %s", $platform_table, 'pageview_send_clientside'));
    if (!$col) {
        $wpdb->query($wpdb->prepare("ALTER TABLE %i ADD COLUMN pageview_send_clientside TINYINT(1) NOT NULL DEFAULT 1", $platform_table));
    }

    foreach (['send_client', 'send_server'] as $c) {
        $col = $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM %i LIKE %s", $woo_table, $c));
        if (!$col) {
            $ddl = $c === 'send_client'
                ? "ALTER TABLE %i ADD COLUMN send_client TINYINT(1) NOT NULL DEFAULT 1"
                : "ALTER TABLE %i ADD COLUMN send_server TINYINT(1) NOT NULL DEFAULT 0";
            $wpdb->query($wpdb->prepare($ddl, $woo_table));
        }
    }

    foreach (['send_client', 'send_server'] as $c) {
        $col = $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM %i LIKE %s", $custom_table, $c));
        if (!$col) {
            $ddl = $c === 'send_client'
                ? "ALTER TABLE %i ADD COLUMN send_client TINYINT(1) NOT NULL DEFAULT 1"
                : "ALTER TABLE %i ADD COLUMN send_server TINYINT(1) NOT NULL DEFAULT 0";
            $wpdb->query($wpdb->prepare($ddl, $custom_table));
        }
    }

    // 2) Rename WooCommerce column: event_enableresponselogging ➝ send_server_log_response
    $hasOld = $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM %i LIKE %s", $woo_table, 'event_enableresponselogging'));
    $hasNew = $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM %i LIKE %s", $woo_table, 'send_server_log_response'));
    if ($hasOld && !$hasNew) {
        $wpdb->query($wpdb->prepare(
            "ALTER TABLE %i CHANGE COLUMN event_enableresponselogging send_server_log_response TINYINT(1) NOT NULL DEFAULT 0",
            $woo_table
        ));
    }

    // 3) Add send_server_log_response to custom events table
    $col = $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM %i LIKE %s", $custom_table, 'send_server_log_response'));
    if (!$col) {
        $wpdb->query($wpdb->prepare(
            "ALTER TABLE %i ADD COLUMN send_server_log_response TINYINT(1) NOT NULL DEFAULT 0",
            $custom_table
        ));
    }

    // 4) Add send_server_log_response to platform settings table
    $col = $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM %i LIKE %s", $platform_table, 'send_server_log_response'));
    if (!$col) {
        $wpdb->query($wpdb->prepare(
            "ALTER TABLE %i ADD COLUMN send_server_log_response TINYINT(1) NOT NULL DEFAULT 0",
            $platform_table
        ));
    }

    // 5) One-time back-fill (if not yet migrated)
    if (!$migrated) {
        // 5a) PageView: client = 1 everywhere; preserve existing server-side as-is
        $wpdb->query($wpdb->prepare("UPDATE %i SET pageview_send_clientside = 1", $platform_table));

        // 5b) WooCommerce events
        // Meta (id=1): both on where event_enabled=1; else both 0
        $wpdb->query($wpdb->prepare(
            "UPDATE %i
             SET send_client = CASE WHEN event_enabled=1 THEN 1 ELSE 0 END,
                 send_server = CASE WHEN event_enabled=1 THEN 1 ELSE 0 END
             WHERE platform_id = 1",
            $woo_table
        ));

        // Google (id=4): purchase → both on if enabled; others → client-only if enabled
        $wpdb->query($wpdb->prepare(
            "UPDATE %i
             SET send_client = CASE WHEN event_enabled=1 THEN 1 ELSE 0 END,
                 send_server = CASE
                                 WHEN event_enabled=1 AND event_platform_ref = 'purchase' THEN 1
                                 ELSE 0
                               END
             WHERE platform_id = 4",
            $woo_table
        ));

        // 5c) Custom events
        // Meta (1): both on
        $wpdb->query($wpdb->prepare(
            "UPDATE %i SET send_client = 1, send_server = 1 WHERE platform_id = 1",
            $custom_table
        ));
        // Google (4): client-only
        $wpdb->query($wpdb->prepare(
            "UPDATE %i SET send_client = 1, send_server = 0 WHERE platform_id = 4",
            $custom_table
        ));

        // Mark migrated
        update_option('unipixel_transport_settings_migrated', 1);
    }

    if ($wpdb->last_error) {
        error_log('UniPixel transport settings migration DB error: ' . $wpdb->last_error);
    }
}

function unipixel_patch_event_log_columns()
{
    global $wpdb;

    $log_table = $wpdb->prefix . 'unipixel_event_log';

    $columns = [
        'method' => "ALTER TABLE %i ADD COLUMN method VARCHAR(10) NULL",
        'party' => "ALTER TABLE %i ADD COLUMN party VARCHAR(10) NULL",
        'event_order' => "ALTER TABLE %i ADD COLUMN event_order VARCHAR(20) NULL"
    ];

    foreach ($columns as $col => $ddl) {
        $exists = $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM %i LIKE %s", $log_table, $col));
        if (!$exists) {
            $wpdb->query($wpdb->prepare($ddl, $log_table));
        }
    }

    if ($wpdb->last_error) {
        error_log('UniPixel event log column patching DB error: ' . $wpdb->last_error);
    }
}


/**
 * One-time migration: add serverside_global_enabled column and back-fill.
 * Existing installs with a non-empty access_token get serverside_global_enabled = 1
 * (preserving current behaviour). New installs default to 0 (client-side only).
 */
function unipixel_setup_serverside_global_enabled()
{
    if (get_option('unipixel_serverside_global_enabled_migrated')) {
        return;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'unipixel_platform_settings';

    // Add column if missing (defensive for upgrades where dbDelta hasn't run yet)
    $col = $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM %i LIKE %s", $table, 'serverside_global_enabled'));
    if (!$col) {
        $wpdb->query($wpdb->prepare(
            "ALTER TABLE %i ADD COLUMN serverside_global_enabled TINYINT(1) NOT NULL DEFAULT 0",
            $table
        ));
    }

    // Back-fill: set to 1 where access_token is non-empty (existing users already had tokens)
    $wpdb->query($wpdb->prepare(
        "UPDATE %i SET serverside_global_enabled = 1 WHERE access_token IS NOT NULL AND access_token != ''",
        $table
    ));

    update_option('unipixel_serverside_global_enabled_migrated', 1);

    if ($wpdb->last_error) {
        error_log('UniPixel serverside_global_enabled migration DB error: ' . $wpdb->last_error);
    }
}


function unipixel_get_dbstore_events_schema()
{
    return [
        'dbstore_pageview_events' => false,
        'dbstore_woocommerce_events' => [
            'AddToCart'        => true,
            'add_to_cart'      => true,
            'InitiateCheckout' => true,
            'begin_checkout'   => true,
            'Purchase'         => true,
            'purchase'         => true,
            'ViewContent'      => true,
            'view_item'        => true,
        ],
        'dbstore_custom_events' => true,
    ];
}
