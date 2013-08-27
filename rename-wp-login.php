<?php
/*
Plugin Name: Rename wp-login.php
Plugin URI: http://wordpress.org/plugins/rename-wp-login/
Description: Change wp-login.php to whatever you want. It can also prevent a lot of brute force attacks.
Author: avryl
Author URI: http://profiles.wordpress.org/avryl/
Version: 1.3
Text Domain: rename-wp-login
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

register_uninstall_hook(__FILE__, 'rwl_uninstall');
function rwl_uninstall() {
	delete_option('rwl_page');
	delete_option('rwl_admin');
}

register_activation_hook(__FILE__, 'rwl_activation');
function rwl_activation() {
	add_option('rwl_redirect', '1');
	add_option('rwl_admin', '0');
	add_option('rwl_page', wp_unique_post_slug('login', 0, 'publish', 'page', 0));
}

add_action('admin_init', 'rwl_admin_init');
function rwl_admin_init() {
	add_settings_section('rename-wp-login-section', 'Login', '__return_false', 'permalink');
	add_settings_field('rwl-page', '<label for="rwl-page-input">Rename wp-login.php</label>', 'rwl_page', 'permalink', 'rename-wp-login-section');
	add_settings_field('rwl-admin', '<label for="rwl-admin-input">Redirect wp-admin/ to new login page (not recommended)</label>', 'rwl_admin', 'permalink', 'rename-wp-login-section');
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		if (!empty($_POST['rwl_page'])) {
			update_option('rwl_page', wp_unique_post_slug(sanitize_title_with_dashes($_POST['rwl_page']), 0, 'publish', 'page', 0));
		}
		update_option('rwl_admin', isset($_POST['rwl_admin']) ? $_POST['rwl_admin'] : '0');
	}
	if (get_option('rwl_redirect') == '1') {
		delete_option('rwl_redirect');
		wp_redirect(admin_url('options-permalink.php#rwl-page-input'));
	}
}

function rwl_page() {
	echo '<code>' . site_url() . '/</code> <input id="rwl-page-input" type="text" name="rwl_page" value="' . get_option('rwl_page') . '" /> <code>/</code>';
}

function rwl_admin() {
	echo '<input id="rwl-admin-input" type="checkbox" name="rwl_admin" value="1" ' . checked(get_option('rwl_admin'), true, false) . ' />';
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'rwl_plugin_action_links');
function rwl_plugin_action_links($links) {
	array_unshift($links, '<a href="options-permalink.php#rwl-page-input">Settings</a>');
	return $links;
}

add_action('admin_notices', 'rwl_admin_notices');
function rwl_admin_notices() {
	if (!get_option('permalink_structure')) {
		?>
		<div class="error">
		    <p><strong>Rename wp-login.php</strong> doesn't work if you’re using the default permalink structure.<br>You must choose another permalink structure for it to work.</p>
		</div>
		<?php
	} elseif ($_GET['settings-updated'] == true) {
		?>
		<div class="updated">
		    <p>Your login page is now here: <a href="<?php echo site_url(); ?>/<?php echo get_option('rwl_page'); ?>/"><?php echo site_url(); ?>/<strong><?php echo get_option('rwl_page'); ?></strong>/</a>. Bookmark this page!</p>
		</div>
		<?php
	}
}

if (!get_option('permalink_structure'))
	return;

add_action('wp_loaded', 'rwl_wp_loaded');
function rwl_wp_loaded() {
	if (is_admin() && !is_user_logged_in() && !defined('DOING_AJAX') && get_option('rwl_admin') != '1') {
		rwl_return_404();
	}
	if (!get_option('rwl_page') || get_option('rwl_page') == '') {
		update_option('rwl_page', wp_unique_post_slug('login', 0, 'publish', 'page', 0));
	}
}

add_action('login_init', 'rwl_login_init');
function rwl_login_init() {
	global $post;
	if (!$post) {
		rwl_return_404();
	}
}

function rwl_return_404() {
	global $wp_query;
	status_header(404);
	$wp_query->set_404();
	if (file_exists(TEMPLATEPATH . '/404.php')) {
		require_once(TEMPLATEPATH . '/404.php');
	} else {
		require_once(TEMPLATEPATH . '/index.php');
	}
	exit;
}

add_action('wp', 'rwl_wp');
function rwl_wp() {
	global $wp_query, $post, $wp;
  	if ($wp_query->is_404 && $wp->request == get_option('rwl_page')) {
  		status_header(200);
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