<?php

class Qform {

	public function __construct() {
		$this->load_dependecies();
		$this->init_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function load_dependecies() {
		require_once QFORM_PLUGIN_DIR . 'admin/class-qform-admin.php';
		require_once QFORM_PLUGIN_DIR . 'public/class-qform-public.php';
	}

	private function init_hooks() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'qform',
			false,
			QFORM_PLUGIN_NAME . '/languages/' );
	}

	private function define_admin_hooks() {
		( new Qform_Admin() );
	}

	private function define_public_hooks() {
		( new Qform_Public() );
	}

	public static function checkToken() {
		$token = get_option( 'qform_main_token' ) !== null
			? get_option( 'qform_main_token' ) : '';

		if ( ! $token ) {
			return false;
		}

		$args     = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $token
			)
		);
		$response = wp_remote_get( QFORM_API . "/api/users/me", $args );

		if ( isset( $response["body"] ) ) {
			$dataResponse = json_decode( $response["body"], true );
			if ( ! $dataResponse ) {
				return false;
			}

			if ( isset( $dataResponse['error'] ) ) {
				if ( $dataResponse['error'] === "Forbidden" ) {
					return false;
				}
			}

			if ( count( $dataResponse ) > 0 ) {
				return true;
			}
		}

		return false;
	}
}
