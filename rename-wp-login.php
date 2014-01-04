<?php

/*
Plugin Name: Rename wp-login.php
Plugin URI: http://wordpress.org/plugins/rename-wp-login/
Description: Change wp-login.php to whatever you want. It can also prevent a lot of brute force attacks.
Author: avryl
Author URI: http://profiles.wordpress.org/avryl/
Version: 2.1
Text Domain: rename-wp-login
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! class_exists( 'Rename_WP_Login' )
	&& defined( 'ABSPATH' ) ) {

	class Rename_WP_Login {

		private function basename() {

			return plugin_basename( __FILE__ );

		}

		private function path() {

			return trailingslashit( dirname( __FILE__ ) );

		}

		private function set_404() {

			global $wp_query;

			status_header( 404 );

			$wp_query->set_404();

			if ( ( ( $template = get_404_template() )
					|| ( $template = get_index_template() ) )
				&& ( $template = apply_filters( 'template_include', $template ) ) ) {

				include( $template );

			}

			die;

		}

		private function new_login_slug() {

			if ( ( $slug = get_option( 'rwl_page' ) )
				|| ( is_multisite()
					&& is_plugin_active_for_network( $this->basename() )
					&& ( $slug = get_site_option( 'rwl_page', 'login' ) ) )
				|| ( $slug = 'login' ) ) {

				return $slug;

			}

		}

		public function new_login_url() {

			if ( get_option( 'permalink_structure' ) ) {

				return trailingslashit( trailingslashit( home_url() ) . $this->new_login_slug() );

			}

			else {

				return trailingslashit( home_url() ) . '?' . $this->new_login_slug();

			}

		}

		public function __construct() {

			global $wp_version;

			if ( version_compare( $wp_version, '3.8', '<' ) ) {

				add_action( 'admin_notices', array( $this, 'admin_notices_incompatible' ) );
				add_action( 'network_admin_notices', array( $this, 'admin_notices_incompatible' ) );

				return;

			}

			register_activation_hook( $this->basename(), array( $this, 'activate' ) );
			register_uninstall_hook( $this->basename(), array( 'Rename_WP_Login', 'uninstall' ) );

			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			add_action( 'network_admin_notices', array( $this, 'admin_notices' ) );

			if ( is_multisite()
				&& ! function_exists( 'is_plugin_active_for_network' ) ) {

			    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

			}

			add_filter( 'plugin_action_links_' . $this->basename(), array( $this, 'plugin_action_links' ) );

			if ( is_multisite()
				&& is_plugin_active_for_network( $this->basename() ) ) {

				add_filter( 'network_admin_plugin_action_links_' . $this->basename(), array( $this, 'plugin_action_links' ) );

				add_action( 'wpmu_options', array( $this, 'wpmu_options' ) );
				add_action( 'update_wpmu_options', array( $this, 'update_wpmu_options' ) );

			}

			add_action( 'wp_loaded', array( $this, 'init' ) );
			add_action( 'login_init', array( $this, 'login_init' ) );

			add_filter( 'site_url', array( $this, 'site_url' ), 10, 2 );
			add_filter( 'network_site_url', array( $this, 'site_url' ), 10, 2 );
			add_filter( 'wp_redirect', array( $this, 'wp_redirect' ), 10, 2 );

			add_filter( 'site_option_welcome_email', array( $this, 'welcome_email' ) );

			remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );

		}

		public function admin_notices_incompatible() {

			echo '<div class="update-nag"><p>Please upgrade to the latest version of WordPress to activate <strong>Rename wp-login.php</strong>.</p></div>';

		}

		public function activate() {

			add_option( 'rwl_redirect', '1' );

			delete_option( 'rwl_admin' );

		}

		public static function uninstall() {

			global $wpdb;

			if ( is_multisite() ) {

				$blogs = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

					if ( $blogs ) {

						foreach( $blogs as $blog ) {

							switch_to_blog( $blog );

							delete_option( 'rwl_page' );

						}

						restore_current_blog();

					}

				delete_site_option( 'rwl_page' );

			}

			else {

				delete_option( 'rwl_page' );

			}

		}

		public function wpmu_options() {

			$out = '';

			$out .= '<h3>Rename wp-login.php</h3>';
			$out .= '<p>This option allows you to set a networkwide default, which can be overridden by individual sites. Simply go to to the site’s permalink settings to change the url.</p>';
			$out .= '<p>Need help? Try the <a href="http://wordpress.org/support/plugin/rename-wp-login#postform" target="_blank">support forum</a>.</p>';
			$out .= '<table class="form-table">';
				$out .= '<tr valign="top">';
					$out .= '<th scope="row">Networkwide default</th>';
					$out .= '<td><input id="rwl-page-input" type="text" name="rwl_page" value="' . get_site_option( 'rwl_page', 'login' )  . '"></td>';
				$out .= '</tr>';
			$out .= '</table>';

			echo $out;

		}

		public function update_wpmu_options() {

			if ( ( $rwl_page = sanitize_title_with_dashes( $_POST['rwl_page'] ) )
				&& strpos( $rwl_page, 'wp-login' ) === false
				&& ! in_array( $rwl_page, $this->forbidden_slugs() ) ) {

				update_site_option( 'rwl_page', $rwl_page );

			}

		}

		public function admin_init() {

			global $pagenow;

			add_settings_section(
				'rename-wp-login-section',
				'Rename wp-login.php',
				array( $this, 'rwl_section_desc' ),
				'permalink'
			);

			add_settings_field(
				'rwl-page',
				'<label for="rwl-page">Login url</label>',
				array( $this, 'rwl_page_input' ),
				'permalink',
				'rename-wp-login-section'
			);

			if ( isset( $_POST['rwl_page'] )
				&& $pagenow === 'options-permalink.php' ) {

				if ( ( $rwl_page = sanitize_title_with_dashes( $_POST['rwl_page'] ) )
					&& strpos( $rwl_page, 'wp-login' ) === false
					&& ! in_array( $rwl_page, $this->forbidden_slugs() ) ) {

					if ( $rwl_page === get_site_option( 'rwl_page', 'login' ) ) {

						delete_option( 'rwl_page' );

					}

					else {

						update_option( 'rwl_page', $rwl_page );

					}

				}

			}

			if ( get_option( 'rwl_redirect' ) ) {

				delete_option( 'rwl_redirect' );

				if ( is_multisite()
					&& is_super_admin()
					&& is_plugin_active_for_network( $this->basename() ) ) {

					$redirect = network_admin_url( 'settings.php#rwl-page-input' );

				}

				else {

					$redirect = admin_url( 'options-permalink.php#rwl-page-input' );

				}

				wp_safe_redirect( $redirect );

				die;

			}

		}

		public function rwl_section_desc() {

			$out = '';

			if ( ! is_multisite()
				|| is_super_admin() ) {

				$out .= '<p>Need help? Try the <a href="http://wordpress.org/support/plugin/rename-wp-login#postform" target="_blank">support forum</a>.</p>';

			}

			if ( is_multisite()
				&& is_super_admin()
				&& is_plugin_active_for_network( $this->basename() ) ) {

				$out .= '<p>To set a networkwide default, go to <a href="' . network_admin_url( 'settings.php#rwl-page-input' ) . '">Network Settings</a>.</p>';

			}

			echo $out;

		}

		public function rwl_page_input() {

			if ( get_option( 'permalink_structure' ) ) {

				echo '<code>' . trailingslashit( home_url() ) . '</code> <input id="rwl-page-input" type="text" name="rwl_page" value="' . $this->new_login_slug()  . '"> <code>/</code>';

			}

			else {

				echo '<code>' . trailingslashit( home_url() ) . '?</code> <input id="rwl-page-input" type="text" name="rwl_page" value="' . $this->new_login_slug()  . '">';

			}

		}

		public function admin_notices() {

			global $pagenow, $cache_rejected_uri;

			$out = '';

			if ( ! is_network_admin()
				&& $pagenow === 'options-permalink.php'
				&& isset( $_GET['settings-updated'] ) ) {

				$out .= '<div class="updated"><p>Your login page is now here: <strong><a href="' . $this->new_login_url() . '">' . $this->new_login_url() . '</a></strong>. Bookmark this page!</p></div>';

			}

			if ( current_user_can( 'manage_options' )
				&& function_exists( 'w3_instance' )
				&& ( $w3tc = w3_instance( 'W3_Config' ) )
				&& ( $w3tc = $w3tc->get_array( 'pgcache.reject.uri' ) )
				&& ! in_array( $this->new_login_slug(), $w3tc ) ) {

				$admin_url = admin_url( 'admin.php?page=w3tc_pgcache#pgcache_reject_uri' );

				$out .= '<div class="update-nag"><strong>W3 Total Cache</strong> is enabled on your website. To make sure <strong>Rename wp-login.php</strong> works correctly, you should add <strong>' . $this->new_login_slug() . '</strong> to <a href="' . $admin_url . '">Never cache the following pages</a>. This notice will disappear once you’ve done that correctly.</div>';

			}

			if ( current_user_can( 'manage_options' )
				&& is_array( $cache_rejected_uri )
				&& ! in_array( $this->new_login_slug(), $cache_rejected_uri ) ) {

				$admin_url = is_network_admin() ? network_admin_url( 'settings.php?page=wpsupercache&tab=settings#rejecturi' ) : admin_url( 'options-general.php?page=wpsupercache&tab=settings#rejecturi' );

				$out .= '<div class="update-nag"><strong>WP Super Cache</strong> is enabled on your website. To make sure <strong>Rename wp-login.php</strong> works correctly, you should add <strong>' . $this->new_login_slug() . '</strong> to <a href="' . $admin_url . '">Rejected URIs</a>. This notice will disappear once you’ve done that correctly.</div>';

			}

			echo $out;

		}

		public function plugin_action_links( $links ) {

			if ( is_network_admin()
				&& is_plugin_active_for_network( $this->basename() ) ) {

				array_unshift( $links, '<a href="' . network_admin_url( 'settings.php#rwl-page-input' ) . '">Settings</a>' );

			}

			elseif ( ! is_network_admin() ) {

				array_unshift( $links, '<a href="' . admin_url( 'options-permalink.php#rwl-page-input' ) . '">Settings</a>' );

			}

			return $links;

		}

		public function init() {

			if ( is_admin()
				&& ! is_user_logged_in()
				&& ! defined( 'DOING_AJAX' ) ) {

				wp_die( __( 'You must log in to access the admin area.' ) );

			}

			if ( ! is_multisite()
				&& ( strpos( $_SERVER['REQUEST_URI'], 'wp-signup' )  !== false
					|| strpos( $_SERVER['REQUEST_URI'], 'wp-activate' ) )  !== false ) {

				wp_die( __( 'This feature is not enabled.' ) );

			}

			$request = parse_url( $_SERVER['REQUEST_URI'] );

			if ( $request['path'] === home_url( $this->new_login_slug(), 'relative' ) ) {

				wp_safe_redirect( $this->new_login_url() . ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . $_SERVER['QUERY_STRING'] : '' ) );

				die;

			}

			if ( untrailingslashit( $request['path'] ) === home_url( $this->new_login_slug(), 'relative' )
				|| ( ! get_option( 'permalink_structure' )
					&& isset( $_GET[$this->new_login_slug()] ) ) ) {

				status_header( 200 );

				require_once( $this->path() . 'rwl-login.php' );

				die;

			}

		}

		public function login_init() {

			if ( strpos( $_SERVER['REQUEST_URI'], 'wp-login' ) !== false ) {

				if ( ( $referer = wp_get_referer() )
					&& strpos( $referer, 'wp-activate.php' ) !== false
					&& ( $referer = parse_url( $referer ) )
					&& ! empty( $referer['query'] ) ) {

					parse_str( $referer['query'], $referer );

					if ( ! empty( $referer['key'] )
						&& ( $result = wpmu_activate_signup( $referer['key'] ) )
						&& is_wp_error( $result )
						&& ( $result->get_error_code() === 'already_active'
							|| $result->get_error_code() === 'blog_taken' ) ) {

						wp_safe_redirect( $this->new_login_url() . ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . $_SERVER['QUERY_STRING'] : '' ) );

						die;

					}

				}

				$this->set_404();

			}

		}

		public function site_url( $url, $path ) {

			return $this->filter_wp_login_php( $url );

		}

		public function wp_redirect( $location, $status ) {

			return $this->filter_wp_login_php( $location );

		}

		public function filter_wp_login_php( $url ) {

			if ( strpos( $url, 'wp-login.php' ) !== false ) {

				$args = explode( '?', $url );

				if ( isset( $args[1] ) ) {

					parse_str( $args[1], $args );

					$url = add_query_arg( $args, $this->new_login_url() );

				}

				else {

					$url = $this->new_login_url();

				}

			}

			return $url;

		}

		public function welcome_email( $value ) {

			return $value = preg_replace( '/(BLOG_URL)([\w\-]+)(\.php)/', 'BLOG_URL' . trailingslashit( get_site_option( 'rwl_page', 'login' ) ), $value );

		}

		public function forbidden_slugs() {

			$wp = new WP;

			return array_merge( $wp->public_query_vars, $wp->private_query_vars );

		}

	}

	new Rename_WP_Login;

}
