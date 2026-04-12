<?php
/**
 * Webhook Handler
 *
 * Handles incoming webhooks from various providers
 *
 * @package WhatsApp_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Webhook Handler class
 */
class Webhook_Handler {

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
		// Register REST routes for webhooks
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );

		// Hook to process M-Pesa callbacks
		add_action( 'wwcc_process_mpesa_callback', [ MPesa_Handler::class, 'process_mpesa_callback' ] );

		// Hook to process incoming messages
		add_action( 'wwcc_process_incoming_message', [ Order_Sync::class, 'handle_incoming_message' ] );
	}

	/**
	 * Register REST API routes
	 */
	public function register_routes() {
		// Already registered in individual classes
		// This is just a central place to reference them
	}

	/**
	 * Log webhook for debugging
	 *
	 * @param string $source Webhook source (e.g., 'twilio', 'meta', 'daraja')
	 * @param array  $data Webhook data
	 * @param string $status Status (success, error, etc.)
	 */
	public static function log_webhook( $source, $data, $status = 'received' ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Inserts into the plugin's custom webhook log table.
		$wpdb->insert(
			$wpdb->prefix . 'wwcc_webhook_logs',
			[
				'source'     => $source,
				'data'       => wp_json_encode( $data ),
				'status'     => $status,
				'created_at' => current_time( 'mysql' ),
			],
			[ '%s', '%s', '%s', '%s' ]
		);
	}
}
