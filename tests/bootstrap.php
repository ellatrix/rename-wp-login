<?php

if ( function_exists( 'xdebug_disable' ) ) {
	xdebug_disable();
}
// PHP < 5.3
if ( ! defined( '__DIR__' ) ) {
	define( '__DIR__', dirname( __FILE__ ) );
}

// Error reporting
error_reporting( E_ALL & ~E_DEPRECATED & ~E_STRICT );

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

define( 'TEST_RWL_PLUGIN_NAME'   , 'rename-wp-login.php' );
define( 'TEST_RWL_PLUGIN_FOLDER' , basename( dirname( __DIR__ ) ) );
define( 'TEST_RWL_PLUGIN_PATH'   , TEST_RWL_PLUGIN_FOLDER . '/' . TEST_RWL_PLUGIN_NAME );


// Activates this plugin in WordPress so it can be tested.
$GLOBALS['wp_tests_options'] = array(
  'active_plugins' => array( TEST_RWL_PLUGIN_PATH ),
);

require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
	require dirname( __DIR__ ) . '/' . TEST_RWL_PLUGIN_NAME;
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';

echo 'Installing Rename wp-login.php' . PHP_EOL;

activate_plugin( TEST_RWL_PLUGIN_PATH );
