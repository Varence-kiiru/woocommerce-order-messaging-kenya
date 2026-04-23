<?php
/**
 * WhatsApp API Handler
 *
 * Handles communication with WhatsApp via Twilio/Meta APIs
 *
 * @package WhatsApp_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WhatsApp API class
 */
class WWCC_WhatsApp_API {

	/**
	 * Instance variable
	 */
	private static $instance = null;

	/**
	 * API provider (twilio or meta)
	 */
	private $provider = 'twilio';

	/**
	 * Twilio SID
	 */
	private $twilio_sid;

	/**
	 * Twilio Auth Token
	 */
	private $twilio_token;

	/**
	 * Twilio Phone Number
	 */
	private $twilio_phone;

	/**
	 * Meta (WhatsApp Business) Phone Number ID
	 */
	private $meta_phone_number_id;

	/**
	 * Meta (WhatsApp Business) Access Token
	 */
	private $meta_access_token;

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
	 * Load API credentials from settings
	 */
	private function load_credentials() {
		$this->provider          = WWCC_Settings::get( 'whatsapp_provider', 'twilio' );
		$this->twilio_sid        = WWCC_Settings::get( 'twilio_sid' );
		$this->twilio_token      = WWCC_Settings::get( 'twilio_token' );
		$this->twilio_phone      = WWCC_Settings::get( 'twilio_phone' );
		$this->meta_phone_number_id = WWCC_Settings::get( 'meta_phone_number_id' );
		$this->meta_access_token = WWCC_Settings::get( 'meta_access_token' );
	}

	/**
	 * Register WordPress hooks
	 */
	private function register_hooks() {
		// Send notification on new order
		add_action( 'woocommerce_order_status_changed', [ $this, 'on_order_created' ], 10, 1 );

		// Send notification on order shipped
		add_action( 'woocommerce_order_status_completed', [ $this, 'on_order_completed' ], 10, 1 );

		// Send payment received notification
		add_action( 'woocommerce_payment_complete', [ $this, 'on_payment_complete' ], 10, 1 );
	}

	/**
	 * Send message via WhatsApp
	 *
	 * @param string $phone_number Customer phone number (with country code, e.g., +2547XXXXXXXX)
	 * @param string $message Message text
	 * @param array  $args Additional arguments
	 * @return array|WP_Error Response from API
	 */
	public function send_message( $phone_number, $message, $args = [] ) {
		$this->load_credentials();

		// Sanitize phone number
		$phone_number = $this->sanitize_phone( $phone_number );

		if ( ! $phone_number ) {
			return new WP_Error( 'invalid_phone', __( 'Invalid phone number', 'pesaflow-payments-for-woocommerce' ) );
		}

		// Check if API is configured
		if ( ! $this->is_configured() ) {
			return new WP_Error( 'not_configured', __( 'WhatsApp API not configured', 'pesaflow-payments-for-woocommerce' ) );
		}

		if ( 'twilio' === $this->provider ) {
			return $this->send_via_twilio( $phone_number, $message, $args );
		} elseif ( 'meta' === $this->provider ) {
			return $this->send_via_meta( $phone_number, $message, $args );
		}

		return new WP_Error( 'unknown_provider', __( 'Unknown WhatsApp provider', 'pesaflow-payments-for-woocommerce' ) );
	}

	/**
	 * Send message via Twilio
	 *
	 * @param string $phone_number Phone number with country code
	 * @param string $message Message text
	 * @param array  $args Additional arguments
	 * @return array|WP_Error Response
	 */
	private function send_via_twilio( $phone_number, $message, $args = [] ) {
		$url = "https://api.twilio.com/2010-04-01/Accounts/{$this->twilio_sid}/Messages.json";

		$body = [
			'From' => 'whatsapp:' . $this->twilio_phone,
			'To'   => 'whatsapp:' . $phone_number,
			'Body' => $message,
		];

		$response = wp_remote_post(
			$url,
			[
				'headers'   => [
					'Authorization' => 'Basic ' . base64_encode( "{$this->twilio_sid}:{$this->twilio_token}" ),
					'Content-Type'  => 'application/x-www-form-urlencoded',
				],
				'body'      => $body,
				'timeout'   => 30,
				'sslverify' => true,
			]
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 201 === $status_code && isset( $body['sid'] ) ) {
			return [
				'success'  => true,
				'message_id' => $body['sid'],
				'provider' => 'twilio',
			];
		}

		return new WP_Error(
			'twilio_error',
			isset( $body['message'] ) ? $body['message'] : __( 'Failed to send WhatsApp message', 'pesaflow-payments-for-woocommerce' )
		);
	}

	/**
	 * Send message via Meta (WhatsApp Business API)
	 *
	 * @param string $phone_number Phone number with country code
	 * @param string $message Message text
	 * @param array  $args Additional arguments
	 * @return array|WP_Error Response
	 */
	private function send_via_meta( $phone_number, $message, $args = [] ) {
		$url = "https://graph.facebook.com/v18.0/{$this->meta_phone_number_id}/messages";

		// Clean phone number for Meta API
		$clean_phone = preg_replace( '/[^0-9]/', '', $phone_number );

		$body = [
			'messaging_product' => 'whatsapp',
			'recipient_type'    => 'individual',
			'to'                => $clean_phone,
			'type'              => 'text',
			'text'              => [
				'body' => $message,
			],
		];

		$response = wp_remote_post(
			$url,
			[
				'headers'   => [
					'Authorization' => 'Bearer ' . $this->meta_access_token,
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

		if ( 200 === $status_code && isset( $body['messages'][0]['id'] ) ) {
			return [
				'success'    => true,
				'message_id' => $body['messages'][0]['id'],
				'provider'   => 'meta',
			];
		}

		return new WP_Error(
			'meta_error',
			isset( $body['error']['message'] ) ? $body['error']['message'] : __( 'Failed to send WhatsApp message', 'pesaflow-payments-for-woocommerce' )
		);
	}

	/**
	 * Sanitize phone number to format: +2547XXXXXXXX
	 *
	 * @param string $phone Phone number
	 * @return string|false Formatted phone or false if invalid
	 */
	private function sanitize_phone( $phone ) {
		// Remove all non-numeric characters
		$clean = preg_replace( '/[^0-9]/', '', $phone );

		// Ensure it's a valid length (at least 9 digits for Kenya)
		if ( strlen( $clean ) < 9 ) {
			return false;
		}

		// If longer than 12 digits, it's invalid
		if ( strlen( $clean ) > 15 ) {
			return false;
		}

		// Add +254 if starts with 0 (Kenya)
		if ( strpos( $clean, '7' ) === 0 && strlen( $clean ) === 9 ) {
			$clean = '254' . $clean;
		}

		// Ensure it starts with country code
		if ( strpos( $clean, '254' ) !== 0 ) {
			$clean = '254' . $clean;
		}

		return '+' . $clean;
	}

	/**
	 * Check if WhatsApp API is configured
	 */
	private function is_configured() {
		if ( 'twilio' === $this->provider ) {
			return ! empty( $this->twilio_sid ) && ! empty( $this->twilio_token ) && ! empty( $this->twilio_phone );
		} elseif ( 'meta' === $this->provider ) {
			return ! empty( $this->meta_phone_number_id ) && ! empty( $this->meta_access_token );
		}

		return false;
	}

	/**
	 * Hook: Send notification when order is created
	 */
	public function on_order_created( $order_id ) {
		if ( ! WWCC_Settings::get( 'enable_notifications', 1 ) ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order || 'pending' !== $order->get_status() ) {
			return;
		}

		$phone   = $order->get_billing_phone();
		$message = $this->build_order_confirmation_message( $order );

		$this->send_message( $phone, $message, [ 'order_id' => $order_id ] );

		// Log the message
		$this->log_message( $order_id, $phone, $message, 'order_confirmation' );
	}

	/**
	 * Hook: Send notification when order is completed
	 */
	public function on_order_completed( $order_id ) {
		if ( ! WWCC_Settings::get( 'enable_notifications', 1 ) ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		$phone   = $order->get_billing_phone();
		$message = sprintf(
			/* translators: 1: Customer first name, 2: Order ID, 3: Order tracking URL */
			__( '🎉 Hi %1$s!\n\nYour order #%2$d has been completed and is on its way 📦\n\nTrack your order here: %3$s', 'pesaflow-payments-for-woocommerce' ),
			$order->get_billing_first_name(),
			$order_id,
			$order->get_view_order_url()
		);

		$this->send_message( $phone, $message, [ 'order_id' => $order_id ] );

		// Log the message
		$this->log_message( $order_id, $phone, $message, 'order_shipped' );
	}

	/**
	 * Hook: Send notification when payment is completed
	 */
	public function on_payment_complete( $order_id ) {
		if ( ! WWCC_Settings::get( 'enable_notifications', 1 ) ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		$phone   = $order->get_billing_phone();
		$message = sprintf(
			/* translators: 1: Customer first name, 2: Order ID, 3: Order total in KES */
			__( '✅ Hi %1$s!\n\nPayment received for order #%2$d\nTotal: %3$s\n\nWe\'ll ship your order shortly!', 'pesaflow-payments-for-woocommerce' ),
			$order->get_billing_first_name(),
			$order_id,
			wp_strip_all_tags( $order->get_formatted_order_total() )
		);

		$this->send_message( $phone, $message, [ 'order_id' => $order_id ] );

		// Log the message
		$this->log_message( $order_id, $phone, $message, 'payment_received' );
	}

	/**
	 * Build order confirmation message
	 */
	private function build_order_confirmation_message( $order ) {
		$items = '';
		foreach ( $order->get_items() as $item ) {
			$items .= sprintf( "• %s x%d - KES %s\n", $item->get_name(), $item->get_quantity(), $item->get_total() );
		}

		/* translators: 1: Customer first name, 2: Order ID, 3: Order items list, 4: Order total in KES, 5: M-Pesa till number */
		return sprintf(
			__( "👋 Hi %1\$s!\n\nYour order #%2\$d has been received!\n\n%3\$sTotal: %4\$s\n\n💰 Pay via M-Pesa to: %5\$s\n\nThank you! 🙏", 'pesaflow-payments-for-woocommerce' ),
			$order->get_billing_first_name(),
			$order->get_id(),
			$items,
			wp_strip_all_tags( $order->get_formatted_order_total() ),
			WWCC_Settings::get( 'mpesa_till_number', 'YOUR_TILL_NUMBER' )
		);
	}

	/**
	 * Log WhatsApp message for record keeping
	 */
	private function log_message( $order_id, $phone, $message, $message_type ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Inserts into the plugin's custom WhatsApp log table.
		$wpdb->insert(
			$wpdb->prefix . 'wwcc_whatsapp_logs',
			[
				'order_id'     => $order_id,
				'phone_number' => $phone,
				'message'      => $message,
				'message_type' => $message_type,
				'sent_at'      => current_time( 'mysql' ),
			], 
			[ '%d', '%s', '%s', '%s', '%s' ]
		);
	}
}
