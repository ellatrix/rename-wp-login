<?php
/**
 * Rename wp-login.php - Unit tests
 */

class Rename_WP_Login_UnitTest extends WP_UnitTestCase {

	/**
	 * Check that activation doesn't break.
	 */
	function test_activated() {

		$this->assertTrue( is_plugin_active( TEST_RWL_PLUGIN_PATH ) );

		$plugin = Rename_WP_Login::get_instance();

		$plugin->activate();
		$this->assertEquals( '1', get_option( 'rwl_redirect' ) );
	}

	/**
	 * Check URL filtering
	 */
	function test_slug() {

		$plugin = Rename_WP_Login::get_instance();

		// No settings made.
		$slug = $plugin->new_login_slug();
		$this->assertEquals( 'login', $slug );

		// Check URL filtering.
		update_option( 'rwl_page', 'rwl-test-slug' );
		if ( is_multisite() ) {
			update_site_option( 'rwl_page', 'rwl-test-slug' );
		}

		$slug = $plugin->new_login_slug();
		$this->assertEquals( 'rwl-test-slug', $slug );

		$url = 'http://www.test.org/wp-login.php';
		$new_url = $plugin->filter_wp_login_php( $url );
		$this->assertNotFalse( strpos( $new_url, $slug ) );
	}
}
