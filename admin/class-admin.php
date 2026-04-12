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
class Admin_WhatsApp_WooCommerce {

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
			__( 'WhatsApp WooCommerce', 'woocommerce-order-messaging-kenya' ),
			__( 'WhatsApp Settings', 'woocommerce-order-messaging-kenya' ),
			'manage_options',
			'wwcc-settings',
			[ $this, 'render_settings_page' ],
			'dashicons-whatsapp'
		);

		add_submenu_page(
			'wwcc-settings',
			__( 'Message Logs', 'woocommerce-order-messaging-kenya' ),
			__( 'Message Logs', 'woocommerce-order-messaging-kenya' ),
			'manage_options',
			'wwcc-logs',
			[ $this, 'render_logs_page' ]
		);

		add_submenu_page(
			'wwcc-settings',
			__( 'Conversations', 'woocommerce-order-messaging-kenya' ),
			__( 'Conversations', 'woocommerce-order-messaging-kenya' ),
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

		wp_enqueue_script(
			'wwcc-admin',
			WWCC_PLUGIN_URL . 'assets/js/admin.js',
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
					'testing'         => __( 'Testing...', 'woocommerce-order-messaging-kenya' ),
					'success'         => __( 'Success!', 'woocommerce-order-messaging-kenya' ),
					'error'           => __( 'Error', 'woocommerce-order-messaging-kenya' ),
					'message_sent'    => __( 'Message sent successfully!', 'woocommerce-order-messaging-kenya' ),
					'confirm_delete'  => __( 'Are you sure?', 'woocommerce-order-messaging-kenya' ),
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
			return $settings;
		}

		// Sanitize sensitive fields
		if ( isset( $settings['twilio_token'] ) ) {
			$settings['twilio_token'] = sanitize_text_field( $settings['twilio_token'] );
		}

		if ( isset( $settings['twilio_sid'] ) ) {
			$settings['twilio_sid'] = sanitize_text_field( $settings['twilio_sid'] );
		}

		if ( isset( $settings['meta_access_token'] ) ) {
			$settings['meta_access_token'] = sanitize_text_field( $settings['meta_access_token'] );
		}

		if ( isset( $settings['daraja_consumer_key'] ) ) {
			$settings['daraja_consumer_key'] = sanitize_text_field( $settings['daraja_consumer_key'] );
		}

		if ( isset( $settings['daraja_consumer_secret'] ) ) {
			$settings['daraja_consumer_secret'] = sanitize_text_field( $settings['daraja_consumer_secret'] );
		}

		if ( isset( $settings['business_phone'] ) ) {
			$settings['business_phone'] = WWCC_Settings::sanitize_phone( $settings['business_phone'] );
		}

		if ( isset( $settings['mpesa_till_number'] ) ) {
			$settings['mpesa_till_number'] = sanitize_text_field( $settings['mpesa_till_number'] );
		}

		if ( isset( $settings['mpesa_passkey'] ) ) {
			$settings['mpesa_passkey'] = sanitize_text_field( $settings['mpesa_passkey'] );
		}

		return $settings;
	}

	/**
	 * Render settings page
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page', 'woocommerce-order-messaging-kenya' ) );
		}

		require_once WWCC_PLUGIN_DIR . 'admin/views/settings.php';
	}

	/**
	 * Render logs page
	 */
	public function render_logs_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page', 'woocommerce-order-messaging-kenya' ) );
		}

		require_once WWCC_PLUGIN_DIR . 'admin/views/logs.php';
	}

	/**
	 * Render conversations page
	 */
	public function render_conversations_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page', 'woocommerce-order-messaging-kenya' ) );
		}

		require_once WWCC_PLUGIN_DIR . 'admin/views/conversations.php';
	}

	/**
	 * Test M-Pesa connection (AJAX)
	 */
	public function test_mpesa_connection() {
		check_ajax_referer( 'wwcc_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Permission denied', 'woocommerce-order-messaging-kenya' ) );
		}

		$mpesa = MPesa_Handler::get_instance();
		$token = $mpesa->get_access_token();

		if ( is_wp_error( $token ) ) {
			wp_send_json_error( $token->get_error_message() );
		}

		wp_send_json_success( [
			'message' => __( 'M-Pesa API connection successful!', 'woocommerce-order-messaging-kenya' ),
			'token'   => substr( $token, 0, 20 ) . '...',
		] );
	}

	/**
	 * Test WhatsApp connection (AJAX)
	 */
	public function test_whatsapp_connection() {
		check_ajax_referer( 'wwcc_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Permission denied', 'woocommerce-order-messaging-kenya' ) );
		}

		$phone = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';

		if ( ! $phone ) {
			wp_send_json_error( __( 'Phone number required', 'woocommerce-order-messaging-kenya' ) );
		}

		$result = WhatsApp_API::get_instance()->send_message(
			$phone,
			__( '✅ WhatsApp API Test - Connection Successful!', 'woocommerce-order-messaging-kenya' )
		);

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		wp_send_json_success( [
			'message' => __( 'Test message sent successfully!', 'woocommerce-order-messaging-kenya' ),
		] );
	}

	/**
	 * Charge order via M-Pesa (AJAX)
	 */
	public function ajax_charge_order() {
		check_ajax_referer( 'wwcc_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_orders' ) ) {
			wp_send_json_error( __( 'Permission denied', 'woocommerce-order-messaging-kenya' ) );
		}

		$order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;

		if ( ! $order_id ) {
			wp_send_json_error( __( 'Invalid order', 'woocommerce-order-messaging-kenya' ) );
		}

		$mpesa = MPesa_Handler::get_instance();
		$result = $mpesa->charge_order( $order_id );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		wp_send_json_success( [
			'message'  => __( 'Payment prompt sent to customer!', 'woocommerce-order-messaging-kenya' ),
			'checkout' => $result,
		] );
	}
}
