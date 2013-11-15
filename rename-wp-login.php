<?php

/*
Plugin Name: Rename wp-login.php
Plugin URI: http://wordpress.org/plugins/rename-wp-login/
Description: Change wp-login.php to whatever you want. It can also prevent a lot of brute force attacks.
Author: avryl
Author URI: http://profiles.wordpress.org/avryl/
Version: 1.8
Text Domain: rename-wp-login
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! class_exists( 'Rename_WP_Login' ) ) {
	
	class Rename_WP_Login {
				
		private static $instance;
		
		private function basename() {
			
			return plugin_basename( __FILE__ );
			
		}
		
		private function url() {
			
			return plugin_dir_url( __FILE__ );
			
		}
		
		private function path() {
			
			return trailingslashit( dirname( __FILE__ ) );
			
		}
		
		private function set_404() {
			
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
		
		private function new_login_url() {
			
			return home_url() . '/' . get_option( 'rwl_page' ) . '/';
			
		}
		
		public static function instance() {
			
			if ( ! self::$instance )
				self::$instance = new self;
			
			return self::$instance;
			
		}
		
		private function __construct() {
			
			global $wp_version;
			
			if ( version_compare( $wp_version, '3.7', '<' ) ) {
			
				add_action( 'admin_init', array( $this, 'admin_init_incompatible' ) );
				add_action( 'admin_notices', array( $this, 'admin_notices_incompatible' ) );
				
				return;
			
			}
			
			register_activation_hook( $this->basename(), array( $this, 'activate' ) );
			register_uninstall_hook( $this->basename(), array( 'Rename_WP_Login', 'uninstall' ) );
			
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			
			add_filter( 'plugin_action_links_' . $this->basename(), array( $this, 'plugin_action_links' ) );
			
			if ( ! get_option('permalink_structure') )
				return;
			
			add_action( 'init', array( $this, 'init' ), 11 );
			add_action( 'login_init', array( $this, 'login_init' ) );
			
			add_filter( 'site_url', array( $this, 'site_url' ), 10, 4 );
			add_filter( 'login_url', array( $this, 'login_url' ), 10, 2 );
			add_filter( 'logout_url', array( $this, 'logout_url' ), 10, 2 );
			add_filter( 'register_url', array( $this, 'register_url' ), 10, 1 );
			add_filter( 'lostpassword_url', array( $this, 'lostpassword_url' ), 10, 2 );
			
		}
		
		public function admin_init_incompatible() {
						
			deactivate_plugins( $this->basename() );
			
		}
		
		public function admin_notices_incompatible() {
			
			echo '<div class="error"><p>Please upgrade to the latest version of WordPress before activating <strong>Rename wp-login.php</strong>.</p></div>';
			
			if ( isset( $_GET['activate'] ) )
				unset( $_GET['activate'] );
			
		}
		
		public function activate() {
			
			add_option( 'rwl_redirect', '1' );
			add_option( 'rwl_page', 'login' );
			
		}
		
		public static function uninstall() {
			
			delete_option( 'rwl_page' );
			delete_option( 'rwl_admin' );
			
		}
		
		public function admin_init() {
			
			add_settings_section( 'rename-wp-login-section', 'Login', '__return_false', 'permalink' );
			
			add_settings_field( 'rwl-page', '<label for="rwl-page-input">Rename wp-login.php</label>', array( $this, 'rwl_page_input' ), 'permalink', 'rename-wp-login-section' );
			add_settings_field( 'rwl-admin', '<label for="rwl-admin-input">Redirect wp-admin</label>', array( $this, 'rwl_admin_input' ), 'permalink', 'rename-wp-login-section' );
			
			if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
				
				if ( ! empty( $_POST['rwl_page'] ) ) {
					
					update_option( 'rwl_page', sanitize_title_with_dashes( $_POST['rwl_page'] ) );
					
				}
				
				if ( isset( $_POST['rwl_admin'] ) ) {
					
					update_option( 'rwl_admin', '1' );
					
				} else {
					
					delete_option( 'rwl_admin' );
					
				}
				
				
				
			}
			
			if ( get_option( 'rwl_redirect' ) ) {
				
				delete_option( 'rwl_redirect' );
				
				wp_redirect( admin_url( 'options-permalink.php#rwl-page-input' ) );
				
				exit;
				
			}
			
		}
		
		public function rwl_page_input() {
			
			echo '<code>' . home_url() . '/</code> <input id="rwl-page-input" type="text" name="rwl_page" value="' . get_option( 'rwl_page' ) . '"> <code>/</code>';
			
		}
		
		public function rwl_admin_input() {
			
			echo '<input id="rwl-admin-input" type="checkbox" name="rwl_admin" value="1" ' . checked( get_option( 'rwl_admin' ), true, false ) . '> Enabling this option will redirect any admin requests to the new login page if not logged in, but beware that this will reveal the location of it.';
			
		}
		
		public function admin_notices() {
			
			global $pagenow;
			
			if ( ! get_option( 'permalink_structure' ) ) {
				
				echo '<div class="error"><p><strong>Rename wp-login.php</strong> doesn’t work if you’re using the default permalink structure.<br>You must <a href="' . admin_url( 'options-permalink.php' ) . '">choose</a> another permalink structure for it to work.</p></div>';
				
			} elseif ( isset( $_GET['settings-updated'] ) && $pagenow == 'options-permalink.php' ) {
						
				echo '<div class="updated"><p>Your login page is now here: <a href="' . $this->new_login_url() . '">' . home_url() . '/<strong>' . get_option( 'rwl_page' ) . '</strong>/</a>. Bookmark this page!</p></div>';
				
			}
			
		}
		
		public function plugin_action_links( $links ) {
			
			array_unshift( $links, '<a href="options-permalink.php#rwl-page-input">Settings</a>' );
			
			return $links;
			
		}
		
		public function init() {
			
			if ( is_admin() && ! is_user_logged_in() && ! defined( 'DOING_AJAX' ) && ! get_option( 'rwl_admin' ) ) {
				
				remove_action( 'wp_head', 'mp6_override_toolbar_margin', 11 );
				
				$this->set_404();
				
			}
			
			if ( ! get_option( 'rwl_page' ) ) {
				
				update_option( 'rwl_page', 'login' );
				
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
					
					require_once( dirname( __FILE__ ) . '/rwl-login.php' );
					
					exit;
					
				}
				
			}
			
		}
		
		public function login_init() {
			
			if ( strpos( $_SERVER['REQUEST_URI'], 'wp-login' ) ) {
				
				$this->set_404();
				
			}
			
		}
		
		public function site_url( $url, $path, $scheme, $blog_id ) {
			
			return ( strpos( $path, 'wp-login.php' ) !== false && $scheme == 'login_post' ) ? $this->new_login_url() . str_replace( 'wp-login.php', '', $path ) : $url;
			
		}
		
		public function login_url( $login_url, $redirect = '' ) {
			
			$login_url = $this->new_login_url();
			
			if ( ! empty( $redirect ) )
				$login_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $login_url );
			
			return $login_url;
			
		}
		
		public function logout_url( $login_url, $redirect = '' ) {
			
			$args = array();
			$args['action'] = 'logout';
			if ( ! empty( $redirect ) )
				$args['redirect_to'] = urlencode( $redirect );
			
			$logout_url = add_query_arg( $args, $this->new_login_url() );
			$logout_url = wp_nonce_url( $logout_url, 'log-out' );
			
			return $logout_url;
			
		}
		
		public function register_url( $register_url ) {
			
			return $this->new_login_url() . '?action=register';
			
		}
		
		public function lostpassword_url( $lostpassword_url, $redirect = '' ) {
			
			$args = array();
			$args['action'] = 'lostpassword';
			if ( ! empty( $redirect) )
				$args['redirect_to'] = urlencode( $redirect );
			
			$lostpassword_url = add_query_arg( $args, $this->new_login_url() );
			
			return $lostpassword_url;
			
		}
		
		
		
	}
	
	Rename_WP_Login::instance();
		
}