<?php
/**
 * Rename wp-login.php - Unit tests
 */

class Rename_WP_Login_UnitTest extends WP_UnitTestCase {

	/**
	 * Check that activation doesn't break.
	 */
	function test_vaa_activated() {
		$this->assertTrue( is_plugin_active( TEST_RWL_PLUGIN_PATH ) );
	}
}
