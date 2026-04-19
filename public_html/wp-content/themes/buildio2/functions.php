<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Email Handling
add_action('phpmailer_init', 'send_smtp_email');
add_action('wp_mail_failed', 'log_mailer_errors', 10, 1);


function send_smtp_email($phpmailer)
{
    $phpmailer->isSMTP();
    $phpmailer->Host       = $_ENV['SMTP_HOST'];
    $phpmailer->Port       = $_ENV['SMTP_PORT'];
    $phpmailer->SMTPSecure = $_ENV['SMTP_SECURE'];
    $phpmailer->SMTPAuth   = $_ENV['SMTP_AUTH'];
    $phpmailer->Username   = $_ENV['SMTP_USERNAME'];
    $phpmailer->Password   = $_ENV['SMTP_PASSWORD'];
    $phpmailer->From       = $_ENV['SMTP_FROMEMAIL'];
    $phpmailer->FromName   = $_ENV['SMTP_FROMNAME'];
    //$phpmailer->addReplyTo('info@example.com', 'Information');
}


function log_mailer_errors($wp_error)
{
    $fn = __DIR__ . '/../' . 'email.log';
    $fp = fopen($fn, 'a');
    fputs($fp, "Mailer Error: " . $wp_error->get_error_message() . "\n");
    fclose($fp);

}

function do_enqueue()
{

	//echo "HELLOOOOOOxxxxxxxxxxxx";

	global $stylesversion;
    global $scriptsversion;
    $stylesversion = "1.3";
    $scriptsversion = "1.3";

	wp_enqueue_style('styles-dist-main', get_stylesheet_directory_uri() . '/dist/main.bundle.css?ver=' . $stylesversion, array(), 1);
	wp_enqueue_script('scripts-dist-main', get_stylesheet_directory_uri() . '/dist/main.bundle.js?ver=' . $scriptsversion, array(), 1, true);

}



if ( ! function_exists( 'buildiotheme_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various
	 * WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme
	 * hook, which runs before the init hook. The init hook is too late
	 * for some features, such as indicating support post thumbnails.
	 */
	function buildiotheme_setup() {

    /**
	 * Make theme available for translation.
	 * Translations can be placed in the /languages/ directory.
	 */
		load_theme_textdomain( 'buildiotheme', get_template_directory() . '/languages' );

		/**
		 * Add default posts and comments RSS feed links to <head>.
		 */
		add_theme_support( 'automatic-feed-links' );

        /**
		 * Add title tag
		 */
        add_theme_support('title-tag');

		/**
		 * Enable support for post thumbnails and featured images.
		 */
		add_theme_support( 'post-thumbnails' );

		/**
		 * Add support for two custom navigation menus.
		 */
		register_nav_menus( array(
			'primary'   => __( 'Primary Menu', 'buildiotheme' ),
			'secondary' => __( 'Secondary Menu', 'buildiotheme' ),
		) );

		/**
		 * Enable support for the following post formats:
		 * aside, gallery, quote, image, and video
		 */
		add_theme_support( 'post-formats', array( 'aside', 'gallery', 'quote', 'image', 'video' ) );

		//echo "HELLOOOOOO";

		//echo get_stylesheet_directory_uri() . '/dist/main.bundle.css' ;




	}
endif; // myfirsttheme_setup
add_action('after_setup_theme', 'buildiotheme_setup' );
add_action('wp_enqueue_scripts', 'do_enqueue');


function custom_excerpt_length($length) {
    return 8; // Adjust the number of words you want in the excerpt
}
add_filter('excerpt_length', 'custom_excerpt_length');

function custom_excerpt_more($more) {
	//return ' <a href="' . get_permalink() . '">more...</a>'; // Remove the default "[...]" at the end of the excerpt
    return '...';
}
add_filter('excerpt_more', 'custom_excerpt_more');



function sc_get_content_substr($content, $length = 50)
{

    $content = wp_strip_all_tags($content, true);

    $content =  substr($content, 0, $length);


    return $content;
}

function pagination_echo_default($echo = true) {
    // Default Pagination
    $default_pagination = get_the_posts_pagination(array(
        'mid_size'  => 2,
        'prev_text' => __('&larr; Previous', 'textdomain'),
        'next_text' => __('Next &rarr;', 'textdomain'),
    ));

    if ($echo) {
        echo $default_pagination;
    } else {
        return $default_pagination;
    }
}


function pagination_echo_bootstrap($custom_query = null, $echo = true) {
    if (!$custom_query) {
        global $wp_query;
        $custom_query = $wp_query;
    }

    $pages = paginate_links(array(
        'base'         => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
        'format'       => '?paged=%#%',
        'current'      => max(1, get_query_var('paged')),
        'total'        => $custom_query->max_num_pages,
        'type'         => 'array',
        'show_all'     => false,
        'end_size'     => 3,
        'mid_size'     => 3,
        'prev_next'    => true,
        'prev_text'    => __('&laquo;'),
        'next_text'    => __('&raquo;'),
    ));

    if (is_array($pages)) {
        $pagination = '<nav aria-label="Page navigation"><ul class="pagination">';
        foreach ($pages as $page) {
            $active = strpos($page, 'current') !== false ? ' active' : '';
            $pagination .= '<li class="page-item' . $active . '">' . str_replace('page-numbers', 'page-link', $page) . '</li>';
        }
        $pagination .= '</ul></nav>';

        if ($echo) {
            echo $pagination;
        } else {
            return $pagination;
        }
    }
}




// Default OG image fallback for pages/posts without a featured image (Yoast free)
add_filter('wpseo_opengraph_image', function ($image_url) {
	if (empty($image_url) || !has_post_thumbnail()) {
		return 'https://unipixelhq.com/wp-content/uploads/2026/04/unipixel-facebook-page-banner-4.png';
	}
	return $image_url;
});


// Register a custom REST API endpoint for the Monday.com webhook
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/monday-webhook', [
        'methods'  => 'POST',
        'callback' => 'handle_monday_webhook',
        'permission_callback' => '__return_true', // No authentication required
    ]);
});
// Validate Monday Webhook Request (Permission Callback)
// function validate_monday_webhook_request(WP_REST_Request $request) {
//     // Optional: Add custom token validation
//     $headers = $request->get_headers();
//     $auth_token = $headers['authorization'][0] ?? null; // Example: "Bearer your_custom_token"

//     // Validate the token (Replace with your actual token)
//     $expected_token = 'your_custom_token_here';
//     if ($auth_token !== "Bearer {$expected_token}") {
//         return new WP_Error(
//             'rest_forbidden',
//             __('Unauthorized request.'),
//             ['status' => 403]
//         );
//     }

//     return true; // Request is authorized
// }

// Handle Monday Webhook
function handle_monday_webhook(WP_REST_Request $request) {
    $data = $request->get_json_params();

    // Handle Challenge Verification
    if (isset($data['challenge'])) {
        return ['challenge' => $data['challenge']];
    }

    // Process the Webhook Payload
    // if (isset($data['event'])) {
    //     $itemId = $data['event']['itemId'] ?? null;
    //     $columnId = $data['event']['columnId'] ?? null;
    //     $columnValue = $data['event']['columnValue'] ?? null;

    //     // Your custom logic goes here
    //     error_log("Webhook Event: Item ID: $itemId, Column ID: $columnId, Value: $columnValue");

    //     // Example response to Monday.com
    //     return ['success' => true];
    // }

    return ['error' => 'Invalid payload.'];
}



