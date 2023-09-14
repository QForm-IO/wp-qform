<?php

class Qform_Admin {
	public function __construct() {
		add_action(
			'admin_enqueue_scripts',
			array( $this, 'enqueue_scripts_styles' )
		);
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'init', array( $this, 'qform_gutenberg_block' ) );
		add_action(
			'admin_post_qform_delete_token',
			array( $this, 'qform_delete_token' )
		);
		add_action(
			'admin_post_qform_short_code_add',
			array( $this, 'add_short_code' )
		);
		add_action(
			'admin_post_qform_short_code_delete',
			array( $this, 'delete_short_code' )
		);
		add_action(
			'wp_ajax_qform_gutenberg',
			array( $this, 'get_form_json' )
		);
	}

	function qform_gutenberg_block() {
		if ( ! get_option( 'qform_main_token' ) ) {
			return;
		}
		wp_register_script(
			'qform-block',
			plugins_url(
				'/../gutenberg/block/assets/dist/blocks.js',
				__FILE__
			),
			array(
				'wp-blocks',
				'wp-element',
				'wp-editor',
				'wp-i18n',
				'wp-components',
				'wp-data',
				'wp-core-data',
				'wp-block-editor',
				'wp-block-editor',
			)
		);

		wp_register_style(
			'qform-block-editor',
			plugins_url(
				'/../gutenberg/block/assets/css/editor.css',
				__FILE__
			)
		);

		if ( function_exists( 'register_block_type' ) ) {
			register_block_type( 'qform/block', array(
				'editor_script'   => 'qform-block',
				'editor_style'    => 'qform-block-editor',
				'render_callback' => array( $this, 'block_render_callback' )
			) );
		}
	}

	function block_render_callback( $block_attributes, $content ) {
		if ( ! QFORM::checkToken() ) {
			return '<p style="color:red;">'
			       . esc_html( __( 'Check qform token!',
					'qform' ) ) . '</p>';
		}

		return '<div><div data-formid="'
		       . esc_attr( $block_attributes['formId'] )
		       . '"></div></div>';
	}


	public function qform_delete_token() {
		if ( ! isset( $_POST['qform_delete_token'] )
		     || ! wp_verify_nonce(
				sanitize_text_field( $_POST['qform_delete_token'] ),
				'qform_delete_token_action'
			)
		) {
			wp_die( esc_html( __( 'Error validation!', 'qform' ) ) );
		}

		delete_option( 'qform_main_token' );

		wp_redirect( sanitize_url( $_POST['_wp_http_referer'] ) );
		exit();
	}

	public function admin_init() {
		$this->qform_admin_settings();
	}

	public function enqueue_scripts_styles() {
		wp_enqueue_style(
			'qform-admin',
			QFORM_PLUGIN_URL . 'admin/css/qform-admin.css'
		);
		wp_enqueue_script(
			'qform-admin',
			QFORM_PLUGIN_URL . 'assets/js/qform-main-script.js'
		);

		$lang = array(
			'formChoose' => esc_html( __( 'Choose form', 'qform' ) ),
			'add'        => esc_html( __( 'Add', 'qform' ) ),
			'createForm' => esc_html( __( 'How to create a form?', 'qform' ) )
		);
		wp_localize_script( 'qform-admin', 'QFORM_LANG', $lang );
		wp_localize_script(
			'qform-admin',
			'QFORM_SECURITY',
			array( 'nonce' => wp_create_nonce( 'qform_gutenberg_action' ) )
		);
	}

	public function admin_menu() {
		add_menu_page(
			esc_html( __( 'Qform main', 'qform' ) ),
			esc_html( __( 'Qform', 'qform' ) ),
			'manage_options',
			'qform-main',
			array( $this, 'render_main_page' ),
			QFORM_PLUGIN_URL . 'assets/img/menu-logo.svg',
			66.123
		);

		add_submenu_page(
			'qform-main',
			esc_html( __( 'Qform settings', 'qform' ) ),
			esc_html( __( 'Settings', 'qform' ) ),
			'manage_options',
			'qform-main'
		);

		add_submenu_page(
			'qform-main',
			esc_html( __( 'Shortcode menu', 'qform' ) ),
			esc_html( __( 'Shortcode', 'qform' ) ),
			'manage_options',
			'qform-short-page',
			array( $this, 'render_short_page' )
		);
	}

	public function render_main_page() {
		require_once QFORM_PLUGIN_DIR
		             . 'admin/templates/main-page-template.php';
	}

	public function render_short_page() {
		require_once QFORM_PLUGIN_DIR
		             . 'admin/templates/short-page-template.php';
	}

	function qform_admin_settings() {
		register_setting( 'qform_main_group', 'qform_main_token' );

		add_settings_section(
			'qform_main_section',
			esc_html( __( 'Settings', 'qform' ) ),
			function () {
			},
			'qform-main'
		);

		add_settings_field(
			'qform_main_token',
			esc_html( __( 'Authorization token', 'qform' ) ),
			function () {
				echo '<input name="qform_main_token" id="qform_main_token" type="text" 
			placeholder="' . esc_attr( __( 'Enter token',
						'qform' ) )
				     . '" value="'
				     . esc_attr( get_option( 'qform_main_token' ) ) . '"
 			 class="regular-text code">';
			},
			'qform-main',
			'qform_main_section',
			array( 'label_for' => 'qform_main_token' )
		);
	}

	static function getForms() {
		$token = get_option( 'qform_main_token' ) !== null
			? get_option( 'qform_main_token' ) : '';

		if ( ! $token ) {
			return false;
		}

		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $token
			)
		);

		$response = wp_remote_get( QFORM_API . "/api/forms/get", $args );

		if ( isset( $response["body"] ) ) {
			$dataResponse = json_decode( $response["body"], true );
			if ( ! $dataResponse ) {
				return [];
			}

			if ( isset( $dataResponse['error'] ) ) {
				if ( $dataResponse['error'] === "Forbidden" ) {
					return [];
				}
			}

			if ( count( $dataResponse ) > 0 ) {
				if ( isset( $dataResponse['_embedded']['forms'] ) ) {
					return $dataResponse['_embedded']['forms'];
				}

				return [];
			}
		}

		return [];
	}

	public function add_short_code() {
		if ( ! isset( $_POST['qform_short_code_add'] )
		     || ! wp_verify_nonce(
				sanitize_text_field( $_POST['qform_short_code_add'] ),
				'qform_short_code_action_add'
			)
		) {
			wp_die( esc_html( __( 'Error!', 'qform' ) ) );
		}

		$data_str = isset( $_POST['qform_id'] )
			? sanitize_text_field( $_POST['qform_id'] ) : '';
		$data     = explode( '||', $data_str );

		if ( is_array( $data ) && ! ( count( $data ) == 2 ) ) {
			wp_die( esc_html( __( 'Error!', 'qform' ) ) );
		}
		$form_id   = $data[0];
		$form_name = $data[1];


		if ( empty( $form_id ) || empty( $form_name ) ) {
			set_transient(
				'qform_form_errors',
				esc_html( __( 'Form fields are required', 'qform' ) ),
				30
			);
		} else {
			$form_id   = wp_unslash( $form_id );
			$form_name = wp_unslash( $form_name );
			global $wpdb;

			$query
				= "INSERT INTO {$wpdb->prefix}qform_short_code (name, form_id) VALUES (%s, %s)";


			if ( false !== $wpdb->query(
					$wpdb->prepare(
						$query,
						$form_name,
						$form_id
					)
				)
			) {
				set_transient(
					'qform_form_success',
					esc_html( __( 'Shortcode added', 'qform' ) ),
					30
				);
			} else {
				set_transient(
					'qform_form_errors',
					esc_html( __( 'Error add shortcode', 'qform' ) ),
					30
				);
			}
		}

		wp_redirect( sanitize_url( $_POST['_wp_http_referer'] ) );
		exit;
	}

	public function delete_short_code() {
		if ( ! isset( $_POST['qform_short_code_delete'] )
		     || ! wp_verify_nonce(
				sanitize_text_field( $_POST['qform_short_code_delete'] ),
				'qform_short_code_action'
			)
		) {
			wp_die( esc_html( __( 'Error!', 'qform' ) ) );
		}
		$short_code_id = isset( $_POST['short_code_id'] )
			? (int) sanitize_text_field( $_POST['short_code_id'] ) : 0;

		global $wpdb;
		if ( $wpdb->delete(
			$wpdb->prefix . 'qform_short_code',
			array( 'id' => $short_code_id )
		)
		) {
			set_transient(
				'qform_form_success',
				esc_html( __( 'Shortcode deleted successfully', 'qform' ) ),
				30
			);
		} else {
			set_transient(
				'qform_form_errors',
				esc_html( __( 'Shortcode deletion error', 'qform' ) ),
				30
			);
		}

		wp_redirect( sanitize_url( $_POST['_wp_http_referer'] ) );
		exit;
	}

	public static function get_count_short_codes() {
		global $wpdb;

		return $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}qform_short_code"
		);
	}

	public static function get_short_code_one( $id ) {
		global $wpdb;

		return $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}qform_short_code WHERE id = "
			. (int) $id,
			ARRAY_A
		);
	}

	public static function get_short_codes( $per_page, $start ) {
		global $wpdb;

		return $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}qform_short_code ORDER BY id LIMIT {$start}, {$per_page}",
			ARRAY_A
		);
	}

	public static function get_pagination_meta( $per_page, $rows ) {
		$total_pages = ceil( $rows / $per_page ) ?: 1;

		$paged = isset( $_GET['paged'] ) ? sanitize_text_field( $_GET['paged'] )
			: 1;

		$paged = (int) $paged;
		if ( $paged < 1 ) {
			$paged = 1;
		}
		if ( $paged > $total_pages ) {
			$paged = $total_pages;
		}

		$start = ( $paged - 1 ) * $per_page;

		return array(
			'rows'        => $rows,
			'total_pages' => $total_pages,
			'paged'       => $paged,
			'start'       => $start,
		);
	}


	public function get_form_json() {
		if ( ! isset( $_POST['qform_nonce'] )
		     || ! wp_verify_nonce(
				sanitize_text_field( $_POST['qform_nonce'] ),
				'qform_gutenberg_action'
			)
		) {
			wp_die();
		}
		$rowData = Qform_Admin::getForms();

		$data = [];
		foreach ( $rowData as $key => $item ) {
			$data[ $key ]['id']     = (int) $item["id"];
			$data[ $key ]['name']   = esc_html( $item["name"] );
			$data[ $key ]['formId'] = esc_html( $item["formId"] );
			$data[ $key ]['status'] = (int) $item["status"];
		}
		wp_send_json( $data );
	}
}