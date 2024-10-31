<?php
/**
 * Plugin Name:       Ninja Spam Protection
 * Plugin URI:        https://wordpress.org/plugins/ninja-spam-protection/
 * Description:       The ultimate solution to prevent spam comments on the default commenting system for WordPress in WordPress.
 * Author:            Random Outputs
 * Author URI:        https://randomoutputs.com/
 * Version:           1.0.0
 * Text Domain:       ninja-spam-protection
 */

defined('ABSPATH') OR die();
register_activation_hook( __FILE__, 'ninja_spam_protection_activation_hook' );

function ninja_spam_protection_activation_hook() {
    set_transient( 'ninja-spam-protection-activation-notice', TRUE, 5 );
}

add_action( 'admin_notices', 'ninja_spam_protection_notice' );
function ninja_spam_protection_notice() {

    if ( get_transient( 'ninja-spam-protection-activation-notice' ) ) {
        ?><style>div#message.updated{ display: none; }</style>
        <div class="updated notice is-dismissible">
          <p><?php _e( 'Thank you for using Ninja Spam Protection. Please clear Page Cache', 'ninja-spam-protection' ); ?></p>
        </div>
        <?php
        delete_transient( 'ninja-spam-protection-activation-notice' );
    }
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'ninja_spam_protection_add_action_links');
function ninja_spam_protection_add_action_links( $links ) {
    $plugin_shortcuts = array(
        '<a rel="noopener" href="https://www.buymeacoffee.com/randomoutputs" target="_blank" style="color:blue;">' . __('Buy developer a coffee', 'ninja-spam-protection') . '</a>'
    );
    return array_merge( $links, $plugin_shortcuts );
}

add_filter( 'comment_form_defaults', 'ninja_spam_protection_remove_comment_action_url' );
function ninja_spam_protection_remove_comment_action_url( $defaults ) {
	$defaults['action'] = '';
	return $defaults;
}

function ninja_spam_protection_block_response() {
	header('HTTP/1.1 400 Bad Request');
	header('Status: 400 Bad Request');
	header('Connection: Close');
	die('<h1>Error 400</h1><span style="padding:10px;background-color:#FFFF00">If you are an administrator of this site, please clear once the Page Cache.</span>');
}

function ninja_spam_protection_modify_action_url() {
	if (is_singular() && comments_open()) {
		echo "\n<script>";
		echo "let ninja-spam-protection-comment-form = document.querySelector(\"#ninja-spam-protection-comment-form, #ast-ninja-spam-protection-comment-form, #fl-comment-form, #ht-ninja-spam-protection-comment-form\");";
		echo "document.onscroll = function () {";
		$ninja_spam_protection_unique_key  = sha1($_SERVER['DOCUMENT_ROOT']);
		echo "ninja-spam-protection-comment-form.action = \"" . wp_make_link_relative(get_site_url()) . "/wp-comments-post.php?$ninja_spam_protection_unique_key\";";
		echo "};";
		echo "</script>\n";
	}
}
add_action('wp_footer', 'ninja_spam_protection_modify_action_url', 99);

$ninja_spam_protection_request = "wp-comments-post.php";
$ninja_spam_protection_query_list = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : FALSE;
$ninja_spam_protection_post_request_received = isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "POST" ? TRUE : FALSE;
$ninja_spam_protection_request_recieved = (strpos($ninja_spam_protection_requested_uri, $ninja_spam_protection_request) !== FALSE) ? TRUE : FALSE;
$ninja_spam_protection_requested_uri = isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : "";
$ninja_spam_protection_request_contain_key = $ninja_spam_protection_query_list == $ninja_spam_protection_requested_key ? TRUE : FALSE;
$ninja_spam_protection_requested_key = sha1($_SERVER['DOCUMENT_ROOT']);

if ( $ninja_spam_protection_post_request_received == TRUE ) {
	if ( $ninja_spam_protection_request_recieved == TRUE && $ninja_spam_protection_request_contain_key == FALSE ) {
		ninja_spam_protection_block_response();
	}
}