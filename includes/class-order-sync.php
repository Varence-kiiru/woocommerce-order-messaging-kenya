<?php
/**
 * Order Sync Handler
 *
 * Handles automatic order creation from WhatsApp messages
 *
 * @package WhatsApp_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Order Sync class
 */
class Order_Sync {

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
		// Register REST API endpoint for incoming WhatsApp messages
		add_action( 'rest_api_init', [ $this, 'register_webhook_routes' ] );

		// Process incoming messages
		add_action( 'wp_ajax_nopriv_wwcc_process_incoming_message', [ $this, 'handle_incoming_message' ] );
		add_action( 'wp_ajax_wwcc_process_incoming_message', [ $this, 'handle_incoming_message' ] );
	}

	/**
	 * Register REST API webhook routes
	 */
	public function register_webhook_routes() {
		register_rest_route(
			'wwcc/v1',
			'/incoming-messages',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'receive_incoming_message' ],
				'permission_callback' => '__return_true',
			]
		);

		// Webhook verification (GET /incoming-messages)
		register_rest_route(
			'wwcc/v1',
			'/incoming-messages',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'verify_webhook' ],
				'permission_callback' => '__return_true',
			]
		);
	}

	/**
	 * Verify webhook from WhatsApp provider
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|string Response
	 */
	public function verify_webhook( $request ) {
		$verify_token = WWCC_Settings::get( 'webhook_verify_token', 'test_token_12345' );
		$token        = $request->get_param( 'hub_verify_token' );
		$challenge    = $request->get_param( 'hub_challenge' );

		if ( $verify_token === $token ) {
			return $challenge;
		}

		return new WP_REST_Response( [ 'error' => 'Invalid token' ], 403 );
	}

	/**
	 * Receive incoming WhatsApp message
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response
	 */
	public function receive_incoming_message( $request ) {
		$json = $request->get_body();
		$data = json_decode( $json, true );

		// Process asynchronously
		wp_schedule_single_event( time(), 'wwcc_process_incoming_message', [ $data ] );

		return new WP_REST_Response( [ 'success' => true ], 200 );
	}

	/**
	 * Handle incoming WhatsApp message
	 *
	 * @param array $data Message data from WhatsApp provider
	 */
	public function handle_incoming_message( $data = null ) {
		// Get from $_REQUEST if no parameter passed
		if ( null === $data ) {
			// Get the raw body
			$json = file_get_contents( 'php://input' );
			$data = json_decode( $json, true );
		}

		if ( empty( $data ) ) {
			return;
		}

		// Extract message details based on provider
		$provider = WWCC_Settings::get( 'whatsapp_provider', 'twilio' );

		if ( 'meta' === $provider ) {
			$message_data = $this->extract_meta_message_data( $data );
		} else {
			$message_data = $this->extract_twilio_message_data( $data );
		}

		if ( ! $message_data ) {
			return;
		}

		// Extract customer info
		$customer_data = $this->extract_customer_data( $message_data );
		if ( ! $customer_data ) {
			return;
		}

		// Extract order items
		$items = $this->extract_order_items( $message_data );
		if ( empty( $items ) ) {
			// Send message asking for order details
			WhatsApp_API::get_instance()->send_message(
				$customer_data['phone'],
				__( 'Hi! Please share the product name and quantity you want to order. Example: Nike Shoes, 2', 'woocommerce-order-messaging-kenya' )
			);
			return;
		}

		// Create order
		$order = $this->create_order_from_message( $customer_data, $items );

		if ( is_wp_error( $order ) ) {
			WhatsApp_API::get_instance()->send_message(
				$customer_data['phone'],
				/* translators: Error message from order creation */
				__( '❌ Sorry, we couldn\'t create your order. Please try again or contact support.', 'woocommerce-order-messaging-kenya' )
			);
			return;
		}

		// Send confirmation
		$message = sprintf(
			/* translators: 1: Order ID, 2: Order total in KES */
			__( '✅ Great! Your order #%1$d has been created.\n\nTotal: KES %2$s\n\nReply to confirm or adjust', 'woocommerce-order-messaging-kenya' ),
			$order->get_id(),
			$order->get_formatted_order_total()
		);

		WhatsApp_API::get_instance()->send_message( $customer_data['phone'], $message );

		// Log conversation
		$this->log_conversation( $customer_data['phone'], $message_data['text'], $order->get_id(), 'order_created' );
	}

	/**
	 * Extract message data from Meta (WhatsApp Business) API
	 */
	private function extract_meta_message_data( $data ) {
		if ( ! isset( $data['entry'] ) || empty( $data['entry'] ) ) {
			return false;
		}

		$entry = $data['entry'][0];

		if ( ! isset( $entry['changes'] ) || empty( $entry['changes'] ) ) {
			return false;
		}

		$changes = $entry['changes'][0];
		$message = $changes['value']['messages'] ?? false;

		if ( ! $message || empty( $message ) ) {
			return false;
		}

		$msg = $message[0];

		return [
			'phone'      => $msg['from'],
			'text'       => $msg['text']['body'] ?? '',
			'message_id' => $msg['id'],
			'timestamp'  => $msg['timestamp'],
		];
	}

	/**
	 * Extract message data from Twilio API
	 */
	private function extract_twilio_message_data( $data ) {
		if ( ! isset( $data['Messages'] ) || empty( $data['Messages'] ) ) {
			return false;
		}

		$message = $data['Messages'][0];

		// Extract phone from Twilio format (whatsapp:+2547XXXXXXX)
		$phone = str_replace( 'whatsapp:', '', $message['From'] );

		return [
			'phone'      => $phone,
			'text'       => $message['Body'] ?? '',
			'message_id' => $message['MessageSid'],
			'timestamp'  => $message['DateSent'] ?? time(),
		];
	}

	/**
	 * Extract customer info from message
	 *
	 * @param array $message_data Message data
	 * @return array|false Customer data or false
	 */
	private function extract_customer_data( $message_data ) {
		$text  = trim( $message_data['text'] );
		$phone = $message_data['phone'];

		// Look for existing customer by phone
		$customer = get_user_by( 'meta', '_billing_phone', $phone );

		if ( $customer ) {
			return [
				'user_id'  => $customer->ID,
				'name'     => $customer->first_name . ' ' . $customer->last_name,
				'email'    => $customer->user_email,
				'phone'    => $phone,
			];
		}

		// Try to extract name from message (first words before product name)
		// Simple heuristic: first 1-2 words before "order", "buy", "want", "please"
		$name = $this->extract_name_from_message( $text, $phone );

		if ( ! $name ) {
			$name = 'Customer';
		}

		return [
			'user_id'  => 0,
			'name'     => $name,
			'email'    => 'customer_' . md5( $phone ) . '@whatsapp.local',
			'phone'    => $phone,
		];
	}

	/**
	 * Extract customer name from message
	 */
	private function extract_name_from_message( $text, $phone ) {
		// Very basic: look for comma separation or keywords
		$parts = explode( ',', $text );
		$first_part = trim( $parts[0] );

		// If it looks like a name (short, mostly letters), use it
		if ( strlen( $first_part ) < 25 && preg_match( '/^[a-zA-Z\s]+$/', $first_part ) ) {
			return $first_part;
		}

		// Fallback: use last digits of phone
		return 'Customer-' . substr( $phone, -4 );
	}

	/**
	 * Extract order items from message
	 *
	 * Simple parsing to find product names and quantities
	 *
	 * @param array $message_data Message data
	 * @return array Order items or empty array
	 */
	private function extract_order_items( $message_data ) {
		$text = strtolower( trim( $message_data['text'] ) );
		$items = [];

		// Try to find product mentions
		// Look for patterns like:
		// - "Nike Shoes"
		// - "x2" or "2x" or "qty: 2"
		// - "price: 3000"

		// Split by comma
		$parts = explode( ',', $text );

		foreach ( $parts as $part ) {
			$part = trim( $part );

			// Skip common words
			if ( in_array( $part, [ 'order', 'please', 'want', 'buy', 'i want to', 'hi', 'hello' ] ) ) {
				continue;
			}

			// Check if this part looks like a product name (has letters)
			if ( preg_match( '/[a-z]/i', $part ) ) {
				// Extract quantity if mentioned
				$quantity = 1;

				if ( preg_match( '/(\d+)\s*x/', $part ) ) {
					preg_match( '/(\d+)\s*x/', $part, $matches );
					$quantity = intval( $matches[1] );
					$part     = preg_replace( '/\d+\s*x\s*/i', '', $part );
				} elseif ( preg_match( '/x\s*(\d+)/', $part ) ) {
					preg_match( '/x\s*(\d+)/', $part, $matches );
					$quantity = intval( $matches[1] );
					$part     = preg_replace( '/x\s*\d+/i', '', $part );
				}

				$part = trim( $part );

				// Try to find matching product
				$product = $this->find_product_by_name( $part );

				if ( $product ) {
					$items[] = [
						'product_id' => $product->get_id(),
						'quantity'   => $quantity,
						'price'      => $product->get_price(),
					];
				}
			}
		}

		return $items;
	}

	/**
	 * Find WooCommerce product by name (with fuzzy matching)
	 *
	 * @param string $name Product name to search for
	 * @return WC_Product|false Product object or false
	 */
	private function find_product_by_name( $name ) {
		$name = trim( $name );

		// Query products
		$args = [
			'post_type'  => 'product',
			'numberposts' => 5,
			's'          => $name,
		];

		$products = get_posts( $args );

		if ( empty( $products ) ) {
			return false;
		}

		// Return first match
		return wc_get_product( $products[0]->ID );
	}

	/**
	 * Create WooCommerce order from WhatsApp message
	 *
	 * @param array $customer_data Customer information
	 * @param array $items Order items
	 * @return WC_Order|WP_Error Order object or error
	 */
	private function create_order_from_message( $customer_data, $items ) {
		// Create or get customer
		$customer_id = 0;

		if ( $customer_data['user_id'] ) {
			$customer_id = $customer_data['user_id'];
		}

		// Create order
		$order = wc_create_order( [ 'customer_id' => $customer_id ] );

		if ( is_wp_error( $order ) ) {
			return $order;
		}

		// Add billing info
		$order->set_billing_first_name( $customer_data['name'] );
		$order->set_billing_phone( $customer_data['phone'] );
		$order->set_billing_email( $customer_data['email'] );

		if ( $customer_data['user_id'] ) {
			// Get address from user
			$user = get_userdata( $customer_data['user_id'] );
			$order->set_billing_address_1( get_user_meta( $user->ID, '_billing_address_1', true ) );
			$order->set_billing_city( get_user_meta( $user->ID, '_billing_city', true ) );
			$order->set_billing_postcode( get_user_meta( $user->ID, '_billing_postcode', true ) );
			$order->set_billing_country( get_user_meta( $user->ID, '_billing_country', true ) );
		}

		// Add items to order
		foreach ( $items as $item_data ) {
			$product = wc_get_product( $item_data['product_id'] );

			if ( ! $product ) {
				continue;
			}

			$order->add_product( $product, $item_data['quantity'] );
		}

		// Calculate totals
		$order->calculate_totals();

		// Set source
		$order->update_meta_data( '_order_source', 'whatsapp' );
		$order->update_meta_data( '_wwcc_whatsapp_phone', $customer_data['phone'] );

		// Save order
		$order->save();

		return $order;
	}

	/**
	 * Log WhatsApp conversation
	 */
	private function log_conversation( $phone, $message, $order_id, $action ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Inserts into the plugin's custom conversation log table.
		$wpdb->insert(
			$wpdb->prefix . 'wwcc_conversations',
			[
				'phone_number' => $phone,
				'message'      => $message,
				'order_id'     => $order_id,
				'action'       => $action,
				'created_at'   => current_time( 'mysql' ),
			],
			[ '%s', '%s', '%d', '%s', '%s' ]
		);
	}
}
