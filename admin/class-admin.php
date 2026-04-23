<?php
/**
 * Admin Interface
 *
 * Handles admin pages and settings UI
 *
 * @package WhatsApp_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin class
 */
class WWCC_Admin {

	/**
	 * Instance variable
	 */
	private static $instance = null;

	/**
	 * Get instance
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->register_hooks();
	}

	/**
	 * Register hooks
	 */
	private function register_hooks() {
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );

		// AJAX handlers for admin
		add_action( 'wp_ajax_wwcc_test_mpesa_connection', [ $this, 'test_mpesa_connection' ] );
		add_action( 'wp_ajax_wwcc_test_whatsapp_connection', [ $this, 'test_whatsapp_connection' ] );
		add_action( 'wp_ajax_wwcc_charge_order', [ $this, 'ajax_charge_order' ] );
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'WhatsApp WooCommerce', 'pesaflow-payments-for-woocommerce' ),
			__( 'WhatsApp Settings', 'pesaflow-payments-for-woocommerce' ),
			'manage_options',
			'wwcc-settings',
			[ $this, 'render_settings_page' ],
			'dashicons-whatsapp'
		);

		add_submenu_page(
			'wwcc-settings',
			__( 'Message Logs', 'pesaflow-payments-for-woocommerce' ),
			__( 'Message Logs', 'pesaflow-payments-for-woocommerce' ),
			'manage_options',
			'wwcc-logs',
			[ $this, 'render_logs_page' ]
		);

		add_submenu_page(
			'wwcc-settings',
			__( 'Conversations', 'pesaflow-payments-for-woocommerce' ),
			__( 'Conversations', 'pesaflow-payments-for-woocommerce' ),
			'manage_options',
			'wwcc-conversations',
			[ $this, 'render_conversations_page' ]
		);
	}

	/**
	 * Enqueue admin assets
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( strpos( $hook, 'wwcc-' ) === false ) {
			return;
		}

		wp_enqueue_style(
			'wwcc-admin',
			WWCC_PLUGIN_URL . 'assets/css/admin.css',
			[],
			WWCC_PLUGIN_VERSION
		);

		// Enqueue settings page styles and scripts
		wp_enqueue_style(
			'wwcc-settings',
			WWCC_PLUGIN_URL . 'assets/css/settings.css',
			[],
			WWCC_PLUGIN_VERSION
		);

		wp_enqueue_script(
			'wwcc-admin',
			WWCC_PLUGIN_URL . 'assets/js/admin.js',
			[ 'jquery' ],
			WWCC_PLUGIN_VERSION,
			true
		);

		// Enqueue settings page scripts
		wp_enqueue_script(
			'wwcc-settings',
			WWCC_PLUGIN_URL . 'assets/js/settings.js',
			[ 'jquery' ],
			WWCC_PLUGIN_VERSION,
			true
		);

		wp_localize_script(
			'wwcc-admin',
			'wwccAdmin',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'wwcc_nonce' ),
				'strings' => [
					'testing'         => __( 'Testing...', 'pesaflow-payments-for-woocommerce' ),
					'success'         => __( 'Success!', 'pesaflow-payments-for-woocommerce' ),
					'error'           => __( 'Error', 'pesaflow-payments-for-woocommerce' ),
					'message_sent'    => __( 'Message sent successfully!', 'pesaflow-payments-for-woocommerce' ),
					'confirm_delete'  => __( 'Are you sure?', 'pesaflow-payments-for-woocommerce' ),
					'test_mpesa'      => __( 'Test M-Pesa connection?', 'pesaflow-payments-for-woocommerce' ),
					'enter_phone'     => __( 'Enter your phone number (with country code):', 'pesaflow-payments-for-woocommerce' ),
				],
			]
		);
	}

	/**
	 * Register settings
	 */
	public function register_settings() {
		register_setting( 'wwcc_settings_group', 'wwcc_settings', [
			'type'              => 'array',
			'sanitize_callback' => [ $this, 'sanitize_settings' ],
		] );
	}

	/**
	 * Sanitize settings
	 */
	public function sanitize_settings( $settings ) {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return WWCC_Settings::get_all();
		}

		if ( ! is_array( $settings ) ) {
			$settings = [];
		}

		$sanitized = WWCC_Settings::get_all();

		// Define sanitization rules for each setting
		$sanitization_rules = [
			// Text fields
			'whatsapp_provider'      => 'sanitize_key',
			'business_phone'         => [ $this, 'sanitize_phone_number' ],
			'twilio_sid'             => 'sanitize_text_field',
			'twilio_token'           => 'sanitize_text_field',
			'twilio_phone'           => [ $this, 'sanitize_phone_number' ],
			'meta_phone_number_id'   => 'sanitize_text_field',
			'meta_access_token'      => 'sanitize_text_field',
			'webhook_verify_token'   => 'sanitize_text_field',
			
			// M-Pesa fields
			'daraja_consumer_key'    => 'sanitize_text_field',
			'daraja_consumer_secret' => 'sanitize_text_field',
			'mpesa_till_number'      => 'sanitize_text_field',
			'mpesa_passkey'          => 'sanitize_text_field',
			
			// Checkboxes (boolean values)
			'enable_notifications'   => 'absint',
			'enable_order_creation'  => 'absint',
			'enable_cart_recovery'   => 'absint',
			'enable_mpesa_stk'       => 'absint',
			'enable_debug'           => 'absint',
			
			// Numbers
			'cart_recovery_hours'    => 'absint',
		];

		$boolean_keys = [
			'enable_notifications',
			'enable_order_creation',
			'enable_cart_recovery',
			'enable_mpesa_stk',
			'enable_debug',
		];

		foreach ( $boolean_keys as $boolean_key ) {
			$sanitized[ $boolean_key ] = empty( $settings[ $boolean_key ] ) ? 0 : 1;
		}

		// Process each setting
		foreach ( $settings as $key => $value ) {
			if ( ! isset( $sanitization_rules[ $key ] ) ) {
				// For unknown keys, use a safe default sanitization
				if ( is_array( $value ) ) {
					$sanitized[ $key ] = array_map( 'sanitize_text_field', $value );
				} else {
					$sanitized[ $key ] = sanitize_text_field( $value );
				}
				continue;
			}

			$sanitize_func = $sanitization_rules[ $key ];
			
			if ( is_callable( $sanitize_func ) ) {
				if ( is_array( $sanitize_func ) ) {
					// Method callback
					$sanitized[ $key ] = call_user_func( $sanitize_func, $value );
				} else {
					// Function name
					$sanitized[ $key ] = call_user_func( $sanitize_func, $value );
				}
			}
		}

		return $sanitized;
	}

	/**
	 * Sanitize phone number
	 *
	 * @param string $phone Phone number to sanitize
	 * @return string Sanitized phone number
	 */
	private function sanitize_phone_number( $phone ) {
		if ( ! is_string( $phone ) ) {
			return '';
		}

		return preg_replace( '/(?!^\+)[^\d]/', '', trim( $phone ) );
	}

	/**
	 * Render settings page
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page', 'pesaflow-payments-for-woocommerce' ) );
		}

		require_once WWCC_PLUGIN_DIR . 'admin/views/settings.php';
	}

	/**
	 * Render logs page
	 */
	public function render_logs_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page', 'pesaflow-payments-for-woocommerce' ) );
		}

		require_once WWCC_PLUGIN_DIR . 'admin/views/logs.php';
	}

	/**
	 * Render conversations page
	 */
	public function render_conversations_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page', 'pesaflow-payments-for-woocommerce' ) );
		}

		require_once WWCC_PLUGIN_DIR . 'admin/views/conversations.php';
	}

	/**
	 * Test M-Pesa connection (AJAX)
	 */
	public function test_mpesa_connection() {
		check_ajax_referer( 'wwcc_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Permission denied', 'pesaflow-payments-for-woocommerce' ) );
		}

		$mpesa = WWCC_MPesa_Handler::get_instance();
		$token = $mpesa->get_access_token();

		if ( is_wp_error( $token ) ) {
			wp_send_json_error( $token->get_error_message() );
		}

		wp_send_json_success( [
			'message' => __( 'M-Pesa API connection successful!', 'pesaflow-payments-for-woocommerce' ),
			'token'   => substr( $token, 0, 20 ) . '...',
		] );
	}

	/**
	 * Test WhatsApp connection (AJAX)
	 */
	public function test_whatsapp_connection() {
		check_ajax_referer( 'wwcc_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Permission denied', 'pesaflow-payments-for-woocommerce' ) );
		}

		$phone = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';

		if ( ! $phone ) {
			wp_send_json_error( __( 'Phone number required', 'pesaflow-payments-for-woocommerce' ) );
		}

		$result = WWCC_WhatsApp_API::get_instance()->send_message(
			$phone,
			__( '✅ WhatsApp API Test - Connection Successful!', 'pesaflow-payments-for-woocommerce' )
		);

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		wp_send_json_success( [
			'message' => __( 'Test message sent successfully!', 'pesaflow-payments-for-woocommerce' ),
		] );
	}

	/**
	 * Charge order via M-Pesa (AJAX)
	 */
	public function ajax_charge_order() {
		check_ajax_referer( 'wwcc_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_orders' ) ) {
			wp_send_json_error( __( 'Permission denied', 'pesaflow-payments-for-woocommerce' ) );
		}

		$order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;

		if ( ! $order_id ) {
			wp_send_json_error( __( 'Invalid order', 'pesaflow-payments-for-woocommerce' ) );
		}

		$mpesa = WWCC_MPesa_Handler::get_instance();
		$result = $mpesa->charge_order( $order_id );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		wp_send_json_success( [
			'message'  => __( 'Payment prompt sent to customer!', 'pesaflow-payments-for-woocommerce' ),
			'checkout' => $result,
		] );
	}
}
