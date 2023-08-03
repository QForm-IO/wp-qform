<?php
/*
Plugin Name: Qform
Plugin URI: https://app.qform.io
Description: Creating complex forms
Version: 1.0.0
Requires at least: 5.2
Requires PHP:      5.6
Author: Qform Team
Author URI: https://github.com/QForm-IO/wp-qform
Text Domain: qform
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'QFORM_PLUGIN_DIR' ) ) {
	define( 'QFORM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'QFORM_PLUGIN_URL' ) ) {
	define( 'QFORM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'QFORM_PLUGIN_NAME' ) ) {
	define( 'QFORM_PLUGIN_NAME', dirname( plugin_basename( __FILE__ ) ) );
}

if ( ! defined( 'QFORM_MAIN_URL' ) ) {
	define( 'QFORM_MAIN_URL', 'https://qform.io' );
}

if ( ! defined( 'QFORM_APP_URL' ) ) {
	define( 'QFORM_APP_URL', 'https://app.qform.io' );
}

if ( ! defined( 'QFORM_API' ) ) {
	define( 'QFORM_API', 'https://uapi.qform.io' );
}


if ( ! function_exists( 'qform_activation' ) ) {
	function qform_activation() {
		require_once QFORM_PLUGIN_DIR . 'includes/class-qform-activate.php';
		Qform_Activate::active();
	}
}


register_activation_hook( __FILE__, 'qform_activation' );

if ( ! function_exists( 'qform_run' ) ) {
	function qform_run() {
		require_once QFORM_PLUGIN_DIR . 'includes/class-qform.php';
		( new Qform() );
	}

	qform_run();
}