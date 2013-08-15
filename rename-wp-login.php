<?php
/*
Plugin Name: Rename wp-login
Plugin URI: http://wordpress.org/plugins/rename-wp-login/
Description: Rename wp-login, and block it to prevent brute force attacks.
Author: avryl
Author URI: http://profiles.wordpress.org/avryl/
Version: 1.0
Text Domain: rename-wp-login
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

register_uninstall_hook(__FILE__, 'rwl_uninstall');
function rwl_uninstall() {
	delete_option('rwl_page');
}

register_activation_hook(__FILE__, 'rwl_activation');
function rwl_activation() {
	add_option('rwl_redirect', '1');
}

add_action('init', 'rwl_init');
function rwl_init() {
	if (!get_option('rwl_page') || get_option('rwl_page') == '') {
		update_option('rwl_page', wp_unique_post_slug('login', 0, 'publish', 'page', 0));
	}
}

add_action('login_init', 'rwl_login_init');
function rwl_login_init() {
	global $wp_query, $post;
	if (!$post) {
		status_header(404);
		$wp_query->set_404();
		if (file_exists(TEMPLATEPATH . '/404.php')) {
			require_once(TEMPLATEPATH . '/404.php');
		} else {
			require_once(TEMPLATEPATH . '/index.php');
		}
		exit;
	}
}

add_action('wp', 'rwl_wp');
function rwl_wp() {
	global $wp_query, $post, $wp;
  	if ($wp_query->is_404 && $wp->request == get_option('rwl_page')) {
		$post = new stdClass();
		$post->ID = 0;
		$wp_query->queried_object = $post;
		$wp_query->queried_object_id = 0;
		$wp_query->post = $post;
		$wp_query->found_posts = 1;
		$wp_query->post_count = 1;
		$wp_query->is_singular = true;
		$wp_query->is_404 = false;
		$wp_query->posts = array($post);
		$wp_query->is_page = true;
		require_once(dirname(__FILE__) . '/wp-login.php');
		exit;
	}
}

add_action('admin_init', 'rwl_admin_init');
function rwl_admin_init() {
	add_settings_section('rename-wp-login-section', 'Login', '__return_false', 'permalink');
	add_settings_field('rwl-page', '<label for="rwl-page-input">Rename wp-login.php</label>', 'rwl_page', 'permalink', 'rename-wp-login-section');
	if (!empty($_POST['rwl_page'])) {
		update_option('rwl_page', wp_unique_post_slug($_POST['rwl_page'], 0, 'publish', 'page', 0));
	}
	if (get_option('rwl_redirect') == '1') {
		delete_option('rwl_redirect');
		wp_redirect(admin_url('options-permalink.php#rwl-page-input'));
	}
}

function rwl_page() {
	echo '<code>' . site_url() . '/</code> <input id="rwl-page-input" type="text" name="rwl_page" value="' . get_option('rwl_page') . '" /> <code>/</code>';
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'rwl_plugin_action_links');
function rwl_plugin_action_links($links) {
	array_unshift($links, '<a href="options-permalink.php#rwl-page-input">Settings</a>');
	return $links;
}

add_filter('site_url', 'rwl_filter_site_url', 10, 4);
function rwl_filter_site_url($url, $path, $scheme, $blog_id) {
	return (strpos($path, 'wp-login.php') !== false && $scheme == 'login_post') ? site_url() . '/' . get_option('rwl_page') . '/' . str_replace('wp-login.php', '', $path) : $url;
}

add_filter('login_url', 'rwl_filter_login_url', 10, 2);
function rwl_filter_login_url($login_url, $redirect = '') {
	$login_url = site_url() . '/' . get_option('rwl_page') . '/';
	if (!empty($redirect))
		$login_url = add_query_arg('redirect_to', urlencode($redirect), $login_url);
	return $login_url;
}

add_filter('logout_url', 'rwl_filter_logout_url', 10, 2);
function rwl_filter_logout_url($login_url, $redirect = '') {
	$args = array('action' => 'logout');
	if (!empty($redirect)) {
		$args['redirect_to'] = urlencode($redirect);
	}
	$logout_url = add_query_arg($args, site_url() . '/' . get_option('rwl_page') . '/');
	$logout_url = wp_nonce_url($logout_url, 'log-out');
	return $logout_url;
}

add_filter('lostpassword_url', 'rwl_filter_lostpassword_url', 10, 2);
function rwl_filter_lostpassword_url($lostpassword_url, $redirect = '') {
	$args = array( 'action' => 'lostpassword' );
	if (!empty($redirect)) {
		$args['redirect_to'] = $redirect;
	}
	$lostpassword_url = add_query_arg($args, site_url() . '/' . get_option('rwl_page') . '/');
	return $lostpassword_url;
}

add_filter('register_url', 'rwl_filter_register_url');
function rwl_filter_register_url($register_url) {
	return site_url() . '/' . get_option('rwl_page') . '/?action=register';
}