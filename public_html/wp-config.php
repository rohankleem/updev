<?php

// BEGIN iThemes Security - Do not modify or remove this line
// iThemes Security Config Details: 2
define( 'DISALLOW_FILE_EDIT', true ); // Disable File Editor - Security > Settings > WordPress Tweaks > File Editor
// END iThemes Security - Do not modify or remove this line

define( 'ITSEC_ENCRYPTION_KEY', 'emF4Pj1KVUxMa3NuKGBmQVlSazUqM3FwPUQ5eWNVa1hqQU9hL1kjOzRrdGlFK085XkRJSl1vX307NltAey00aA==' );

require_once(__DIR__ . '/../vendor/autoload.php');
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();

//turn off errors and warnings////
// ini_set('display_errors','On');
// ini_set('error_reporting', E_ALL );
//////////////////////////////////





define('DB_NAME', 			$_ENV['DB_NAME']);
define('DB_USER', 			$_ENV['DB_USER']);
define('DB_PASSWORD', 		$_ENV['DB_PASSWORD']);
define('DB_HOST', 			$_ENV['DB_HOST']);
define('DB_CHARSET', 		$_ENV['DB_CHARSET']);
define('DB_COLLATE', 		'');

define('AUTH_KEY',         	$_ENV['AUTH_KEY']);
define('SECURE_AUTH_KEY',  	$_ENV['SECURE_AUTH_KEY']);
define('LOGGED_IN_KEY',    	$_ENV['LOGGED_IN_KEY']);
define('NONCE_KEY',        	$_ENV['NONCE_KEY']);
define('AUTH_SALT',        	$_ENV['AUTH_SALT']);
define('SECURE_AUTH_SALT', 	$_ENV['SECURE_AUTH_SALT']);
define('LOGGED_IN_SALT',   	$_ENV['LOGGED_IN_SALT']);
define('NONCE_SALT',       	$_ENV['NONCE_SALT']);

// Convert environment variable string to boolean
$wp_debug = strtolower($_ENV['WP_DEBUG']) === 'true' ? true : false;
if (strtolower($_ENV['WP_DEBUG_LOG']) === 'true') {
    $wp_debug_log = true;
} elseif (strtolower($_ENV['WP_DEBUG_LOG']) === 'false') {
    $wp_debug_log = false;
} else {
    $wp_debug_log = $_ENV['WP_DEBUG_LOG']; // Use as a file path
}
$wp_debug_display = strtolower($_ENV['WP_DEBUG_DISPLAY']) === 'true' ? true : false;

define('WP_DEBUG',			$wp_debug);
define('WP_DEBUG_LOG',		$wp_debug_log);
define('WP_DEBUG_DISPLAY', 	$wp_debug_display);

define('WP_HOME', 			$_ENV['WP_HOME']);
define('WP_SITEURL', 		$_ENV['WP_SITEURL']);

$table_prefix = $_ENV['TABLE_PREFIX'];

/** Absolute path to the WordPress directory. */
if (!defined('ABSPATH')) {
	define('ABSPATH', __DIR__ . '/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

date_default_timezone_set("Australia/Melbourne");
