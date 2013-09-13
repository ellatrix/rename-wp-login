<?php

/*
Plugin Name: Rename wp-login.php
Plugin URI: http://wordpress.org/plugins/rename-wp-login/
Description: Change wp-login.php to whatever you want. It can also prevent a lot of brute force attacks.
Author: avryl
Author URI: http://profiles.wordpress.org/avryl/
Version: 1.5
Text Domain: rename-wp-login
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

register_activation_hook( __FILE__, 'rwl_activation' );
register_uninstall_hook( __FILE__, 'rwl_uninstall' );

add_action( 'admin_init', 'rwl_admin_init' );
add_action( 'admin_notices', 'rwl_admin_notices' );

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'rwl_plugin_action_links' );

function rwl_activation() {
	
	add_option( 'rwl_redirect', '1' );
	add_option( 'rwl_admin', '0' );
	add_option( 'rwl_page', wp_unique_post_slug( 'login', 0, 'publish', 'page', 0 ) );
	
}

function rwl_uninstall() {
	
	delete_option( 'rwl_page' );
	delete_option( 'rwl_admin' );
	
}

function rwl_admin_init() {
	
	add_settings_section( 'rename-wp-login-section', 'Login', '__return_false', 'permalink' );
	
	add_settings_field( 'rwl-page', '<label for="rwl-page-input">Rename wp-login.php</label>', 'rwl_page', 'permalink', 'rename-wp-login-section' );
	add_settings_field( 'rwl-admin', '<label for="rwl-admin-input">Redirect wp-admin to new login page (not recommended)</label>', 'rwl_admin', 'permalink', 'rename-wp-login-section' );
	
	if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
		
		if ( ! empty( $_POST['rwl_page'] ) ) {
			
			update_option( 'rwl_page', wp_unique_post_slug( sanitize_title_with_dashes( $_POST['rwl_page'] ), 0, 'publish', 'page', 0 ) );
			
		}
		
		update_option( 'rwl_admin', isset( $_POST['rwl_admin'] ) ? $_POST['rwl_admin'] : '0' );
		
	}
	
	if ( get_option( 'rwl_redirect' ) == '1' ) {
		
		delete_option( 'rwl_redirect' );
		
		wp_redirect( admin_url( 'options-permalink.php#rwl-page-input' ) );
		
	}
	
}

function rwl_page() {
	
	echo '<code>' . home_url() . '/</code> <input id="rwl-page-input" type="text" name="rwl_page" value="' . get_option( 'rwl_page' ) . '" /> <code>/</code>';
	
}

function rwl_admin() {
	
	echo '<input id="rwl-admin-input" type="checkbox" name="rwl_admin" value="1" ' . checked( get_option( 'rwl_admin' ), true, false ) . ' />';
	
}

function rwl_admin_notices() {
	
	if ( ! get_option( 'permalink_structure' ) ) {
		
		echo '<div class="error"><p><strong>Rename wp-login.php</strong> doesn’t work if you’re using the default permalink structure.<br>You must <a href="' . admin_url( 'options-permalink.php' ) . '">choose</a> another permalink structure for it to work.</p></div>';
		
	} elseif ( $_GET['settings-updated'] == true ) {
				
		echo '<div class="updated"><p>Your login page is now here: <a href="' . home_url() . '/' . get_option( 'rwl_page' ) . '/">' . home_url() . '/<strong>' . get_option( 'rwl_page' ) . '</strong>/</a>. Bookmark this page!</p></div>';
		
	}
	
}

function rwl_plugin_action_links( $links ) {
	
	array_unshift( $links, '<a href="options-permalink.php#rwl-page-input">Settings</a>' );
	
	return $links;
	
}

if ( ! get_option('permalink_structure') )
	return;

add_action( 'init', 'rwl_init', 11 );
add_action( 'login_init', 'rwl_login_init' );

add_filter( 'site_url', 'rwl_filter_site_url', 10, 4 );
add_filter( 'login_url', 'rwl_filter_login_url', 10, 2 );
add_filter( 'logout_url', 'rwl_filter_logout_url', 10, 2 );
add_filter( 'register_url', 'rwl_filter_register_url', 10, 1 );
add_filter( 'lostpassword_url', 'rwl_filter_lostpassword_url', 10, 2 );

function rwl_init() {
	
	if ( is_admin() && ! is_user_logged_in() && ! defined( 'DOING_AJAX' ) && get_option( 'rwl_admin' ) != '1' ) {
		
		remove_action( 'wp_head', 'mp6_override_toolbar_margin', 11 );
		
		rwl_404();
		
	}
	
	if ( ! get_option( 'rwl_page' ) || get_option( 'rwl_page' ) == '' ) {
		
		update_option( 'rwl_page', wp_unique_post_slug( 'login', 0, 'publish', 'page', 0 ) );
		
	}
	
	if ( strpos( $_SERVER['REQUEST_URI'], get_option( 'rwl_page' ) ) ) {
		
		$home_url = parse_url( home_url() );
		
		$home_path = '';
		if ( isset( $home_url['path'] ) )
			$home_path = $home_url['path'];
		$home_path = trim( $home_path, '/' );
		
		$req_uri = $_SERVER['REQUEST_URI'];
		$req_uri_array = explode( '?', $req_uri );
		$req_uri = $req_uri_array[0];
		$req_uri = trim( $req_uri, '/' );
		$req_uri = preg_replace( "|^$home_path|i", '', $req_uri );
		$req_uri = trim( $req_uri, '/' );
		
		if ( $req_uri == get_option('rwl_page') ) {
			
			status_header( 200 );
			
			require_once( dirname( __FILE__ ) . '/wp-login.php' );
			
			exit;
			
		}
		
	}
	
}

function rwl_login_init() {
	
	if ( strpos( $_SERVER['REQUEST_URI'], 'wp-login.php' ) ) {
		
		rwl_404();
		
	}
	
}

function rwl_filter_site_url( $url, $path, $scheme, $blog_id ) {
	
	return ( strpos( $path, 'wp-login.php' ) !== false && $scheme == 'login_post' ) ? rwl_login_url() . str_replace( 'wp-login.php', '', $path ) : $url;
	
}

function rwl_filter_login_url( $login_url, $redirect = '' ) {
	
	$login_url = rwl_login_url();
	
	if ( ! empty( $redirect ) )
		$login_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $login_url );
	
	return $login_url;
	
}

function rwl_filter_logout_url( $login_url, $redirect = '' ) {
	
	$args = array();
	$args['action'] = 'logout';
	if ( ! empty( $redirect ) )
		$args['redirect_to'] = urlencode( $redirect );
	
	$logout_url = add_query_arg( $args, rwl_login_url() );
	$logout_url = wp_nonce_url( $logout_url, 'log-out' );
	
	return $logout_url;
	
}

function rwl_filter_register_url( $register_url ) {
	
	return rwl_login_url() . '?action=register';
	
}

function rwl_filter_lostpassword_url( $lostpassword_url, $redirect = '' ) {
	
	$args = array();
	$args['action'] = 'lostpassword';
	if ( ! empty( $redirect) )
		$args['redirect_to'] = urlencode( $redirect );
	
	$lostpassword_url = add_query_arg( $args, rwl_login_url() );
	
	return $lostpassword_url;
	
}

function rwl_login_url() {
	
	return home_url() . '/' . get_option( 'rwl_page' ) . '/';
	
}

function rwl_404() {
	
	global $wp_query;
	
	status_header( 404 );
	
	$wp_query->set_404();
	
	$template = get_404_template();
	
	if ( ! $template )
		$template = get_index_template();
	
	if ( $template = apply_filters( 'template_include', $template ) )
		include( $template );
	
	exit;
	
}