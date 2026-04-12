<?php
/**
 * M-Pesa/Daraja API Handler
 *
 * Handles M-Pesa payments and payment confirmation webhooks
 *
 * @package WhatsApp_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * M-Pesa Handler class
 */
class MPesa_Handler {

	/**
	 * Instance variable
	 */
	private static $instance = null;

	/**
	 * Daraja consumer key
	 */
	private $consumer_key;

	/**
	 * Daraja consumer secret
	 */
	private $consumer_secret;

	/**
	 * Business Till Number (Shortcode)
	 */
	private $till_number;

	/**
	 * Daraja access token (cached)
	 */
	private $access_token = null;

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
		$this->load_credentials();
		$this->register_hooks();
	}

	/**
	 * Load API credentials
	 */
	private function load_credentials() {
		$this->consumer_key    = WWCC_Settings::get( 'daraja_consumer_key' );
		$this->consumer_secret = WWCC_Settings::get( 'daraja_consumer_secret' );
		$this->till_number     = WWCC_Settings::get( 'mpesa_till_number' );
	}

	/**
	 * Register hooks
	 */
	private function register_hooks() {
		// Register webhook endpoint for M-Pesa confirmation
		add_action( 'rest_api_init', [ $this, 'register_webhook_routes' ] );
	}

	/**
	 * Register REST API webhook routes
	 */
	public function register_webhook_routes() {
		register_rest_route(
			'wwcc/v1',
			'/mpesa-callback',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'handle_mpesa_callback' ],
				'permission_callback' => '__return_true', // M-Pesa doesn't use WordPress auth
			]
		);
	}

	/**
	 * Get M-Pesa access token
	 *
	 * @return string|WP_Error Access token or error
	 */
	public function get_access_token() {
		// Return cached token if still valid
		if ( $this->access_token ) {
			return $this->access_token;
		}

		// Check if credentials are set
		if ( ! $this->consumer_key || ! $this->consumer_secret ) {
			return new WP_Error( 'missing_credentials', __( 'M-Pesa credentials not configured', 'woocommerce-order-messaging-kenya' ) );
		}

		$url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

		$response = wp_remote_get(
			$url,
			[
				'headers' => [
					'Authorization' => 'Basic ' . base64_encode( "{$this->consumer_key}:{$this->consumer_secret}" ),
				],
				'timeout'   => 30,
				'sslverify' => true,
			]
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['access_token'] ) ) {
			$this->access_token = $body['access_token'];
			return $body['access_token'];
		}

		return new WP_Error( 'token_error', __( 'Failed to get M-Pesa access token', 'woocommerce-order-messaging-kenya' ) );
	}

	/**
	 * Initiate STK Push (Payment Prompt) to customer phone
	 *
	 * @param int    $order_id WooCommerce order ID
	 * @param string $phone_number Customer phone number
	 * @param float  $amount Order amount
	 * @return array|WP_Error Result
	 */
	public function initiate_stk_push( $order_id, $phone_number, $amount ) {
		$token = $this->get_access_token();

		if ( is_wp_error( $token ) ) {
			return $token;
		}

		// Sanitize phone number
		$phone = preg_replace( '/[^0-9]/', '', $phone_number );

		// If starts with 0 (Kenya), replace with 254
		if ( strpos( $phone, '0' ) === 0 ) {
			$phone = '254' . substr( $phone, 1 );
		}

		if ( strlen( $phone ) !== 12 ) {
			return new WP_Error( 'invalid_phone', __( 'Invalid phone number for STK Push', 'woocommerce-order-messaging-kenya' ) );
		}

		$timestamp = date( 'YmdHis' );
		$password  = base64_encode( $this->till_number . WWCC_Settings::get( 'mpesa_passkey' ) . $timestamp );

		$url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

		$body = [
			'BusinessShortCode' => $this->till_number,
			'Password'          => $password,
			'Timestamp'         => $timestamp,
			'TransactionType'   => 'CustomerPayBillOnline',
			'Amount'            => ceil( $amount ),
			'PartyA'            => $phone,
			'PartyB'            => $this->till_number,
			'PhoneNumber'       => $phone,
			'CallBackURL'       => rest_url( 'wwcc/v1/mpesa-callback' ),
			'AccountReference'  => 'Order-' . $order_id,
			'TransactionDesc'   => 'Payment for Order #' . $order_id,
		];

		$response = wp_remote_post(
			$url,
			[
				'headers'   => [
					'Authorization' => 'Bearer ' . $token,
					'Content-Type'  => 'application/json',
				],
				'body'      => wp_json_encode( $body ),
				'timeout'   => 30,
				'sslverify' => true,
			]
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 0 === $body['ResponseCode'] ) {
			// Log the STK push attempt
			$this->log_transaction( $order_id, $phone, $amount, 'stk_push', 'initiated' );

			return [
				'success'     => true,
				'message'     => $body['ResponseDescription'],
				'checkout_id' => $body['CheckoutRequestID'],
			];
		}

		return new WP_Error(
			'stk_error',
			$body['ResponseDescription'] ?? __( 'STK Push failed', 'woocommerce-order-messaging-kenya' )
		);
	}

	/**
	 * Handle M-Pesa payment callback webhook
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response
	 */
	public function handle_mpesa_callback( $request ) {
		// Get the raw JSON to avoid WordPress processing issues
		$json = $request->get_body();
		$data = json_decode( $json, true );

		// Acknowledge receipt immediately to Daraja
		$response = new WP_REST_Response(
			[ 'ResultCode' => 0, 'ResultDesc' => 'Success' ],
			200
		);

		// Process asynchronously to prevent timeout
		wp_schedule_single_event( time(), 'wwcc_process_mpesa_callback', [ $data ] );

		return $response;
	}

	/**
	 * Process M-Pesa callback data
	 */
	public function process_mpesa_callback( $data ) {
		if ( ! isset( $data['Body']['stkCallback'] ) ) {
			return;
		}

		$callback = $data['Body']['stkCallback'];
		$order_id = $this->extract_order_id_from_reference( $callback['CheckoutRequestID'] );

		if ( ! $order_id ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		// Check if payment was successful
		if ( 0 === $callback['ResultCode'] ) {
			// Payment successful
			$phone = $order->get_billing_phone();

			// Mark order as paid
			$order->payment_complete();
			$order->update_status( 'processing', __( 'M-Pesa payment confirmed', 'woocommerce-order-messaging-kenya' ) );

			// Log transaction
			$this->log_transaction( $order_id, $phone, $order->get_total(), 'stk_push', 'completed' );

			// Send confirmation via WhatsApp
			WhatsApp_API::get_instance()->on_payment_complete( $order_id );
		} else {
			// Payment failed
			$phone = $order->get_billing_phone();
			$order->update_status( 'failed', __( 'M-Pesa payment was cancelled', 'woocommerce-order-messaging-kenya' ) );

			// Log transaction
			$this->log_transaction( $order_id, $phone, $order->get_total(), 'stk_push', 'failed' );

			// Send failure message via WhatsApp
			WhatsApp_API::get_instance()->send_message(
				$phone,
				sprintf(
					__( '❌ Payment failed for order #%d. Please try again or contact us.', 'woocommerce-order-messaging-kenya' ),
					$order_id
				)
			);
		}
	}

	/**
	 * Extract order ID from M-Pesa reference
	 *
	 * @param string $reference M-Pesa reference
	 * @return int|false Order ID or false
	 */
	private function extract_order_id_from_reference( $reference ) {
		// Try to find order by meta key
		global $wpdb;

		// Look up in postmeta for the CheckoutRequestID
		$order_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s",
				'_wwcc_checkout_request_id',
				$reference
			)
		);

		return $order_id ? intval( $order_id ) : false;
	}

	/**
	 * Log M-Pesa transaction
	 */
	private function log_transaction( $order_id, $phone, $amount, $transaction_type, $status ) {
		global $wpdb;

		$wpdb->insert(
			$wpdb->prefix . 'wwcc_mpesa_logs',
			[
				'order_id'         => $order_id,
				'phone_number'     => $phone,
				'amount'           => $amount,
				'transaction_type' => $transaction_type,
				'status'           => $status,
				'logged_at'        => current_time( 'mysql' ),
			],
			[ '%d', '%s', '%f', '%s', '%s', '%s' ]
		);
	}

	/**
	 * Charge order (initiate STK push) from admin
	 *
	 * @param int $order_id Order ID
	 */
	public function charge_order( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return false;
		}

		$phone_number = $order->get_billing_phone();
		$amount       = $order->get_total();

		$result = $this->initiate_stk_push( $order_id, $phone_number, $amount );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Store checkout request ID for later reference
		$order->update_meta_data( '_wwcc_checkout_request_id', $result['checkout_id'] );
		$order->save();

		// Send WhatsApp notification about payment
		WhatsApp_API::get_instance()->send_message(
			$phone_number,
			sprintf(
				__( '💰 Please complete payment for order #%d\nAmount: KES %s\nA prompt will appear on your phone shortly', 'woocommerce-order-messaging-kenya' ),
				$order_id,
				$amount
			)
		);

		return $result;
	}
}
