<?php

class Qform_Public {
	public function __construct() {
		add_action( 'wp_enqueue_scripts',
			array( $this, 'enqueue_scripts_styles' ),
			20 );
		add_shortcode( 'qform_short_code',
			array( $this, 'qform_short_code_html' ) );
	}


	public function enqueue_scripts_styles() {
		if ( get_option( 'qform_main_token' ) ) {
			wp_enqueue_script( 'qform-public',
				QFORM_PLUGIN_URL . 'assets/js/qform-main-script.js' );
		}
	}

	public function qform_short_code_html( $attributes ) {
		$attributes = shortcode_atts( array(
			'id' => 0,
		), $attributes );
		$id         = (int) $attributes['id'];
		$short_code = Qform_Admin::get_short_code_one( $id );

		if ( ! $short_code ) {
			return '<p>' . esc_html( __( 'Shortcode not found!', 'qform' ) )
			       . '</p>';
		}

		if ( ! QFORM::checkToken() ) {
			return '<p style="color: red;">'
			       . esc_html( __( 'Check qform token!',
					'qform' ) ) . '</p>';
		}

		return '<div><div data-formid="' . esc_attr( $short_code[0]['form_id'] )
		       . '"></div></div>';
	}
}