<?php
/**
 * Cart Recovery Handler
 *
 * Handles cart abandonment detection and WhatsApp reminders
 *
 * @package WhatsApp_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cart Recovery class
 */
class WWCC_Cart_Recovery {

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
		// Enqueue cart tracking script on frontend
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_cart_tracking_script' ] );

		// Cron for sending reminders
		add_action( 'wwcc_cart_recovery_cron', [ $this, 'send_recovery_messages' ] );
		add_action( 'woocommerce_checkout_order_processed', [ $this, 'maybe_track_pending_order' ], 10, 1 );
		add_action( 'woocommerce_payment_complete', [ $this, 'clear_recovery_for_order' ], 10, 1 );
		add_action( 'woocommerce_order_status_completed', [ $this, 'clear_recovery_for_order' ], 10, 1 );
		add_action( 'woocommerce_order_status_processing', [ $this, 'clear_recovery_for_order' ], 10, 1 );

		// Schedule cron on init
		if ( ! wp_next_scheduled( 'wwcc_cart_recovery_cron' ) ) {
			wp_schedule_event( time(), 'hourly', 'wwcc_cart_recovery_cron' );
		}
	}

	/**
	 * Mark newly created unpaid orders for recovery follow-up.
	 *
	 * @param int $order_id WooCommerce order ID.
	 */
	public function maybe_track_pending_order( $order_id ) {
		if ( ! WWCC_Settings::get( 'enable_cart_recovery', 1 ) ) {
			return;
		}

		self::mark_cart_abandoned( $order_id );
	}

	/**
	 * Clear recovery queue once an order is paid or completed.
	 *
	 * @param int $order_id WooCommerce order ID.
	 */
	public function clear_recovery_for_order( $order_id ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Updates the plugin's custom cart recovery table for a completed order.
		$wpdb->update(
			$wpdb->prefix . 'wwcc_carts',
			[
				'abandoned'     => 0,
				'recovery_sent' => 1,
			],
			[ 'order_id' => $order_id ],
			[ '%d', '%d' ],
			[ '%d' ]
		);
	}

	/**
	 * Enqueue cart tracking script
	 */
	public function enqueue_cart_tracking_script() {
		// Only load on shop and product pages
		if ( ! ( is_shop() || is_product() || is_product_category() ) ) {
			return;
		}

		wp_enqueue_script(
			'wwcc-cart-recovery',
			WWCC_PLUGIN_URL . 'assets/js/cart-recovery.js',
			[ 'jquery' ],
			WWCC_PLUGIN_VERSION,
			true
		);
	}

	/**
	 * Track cart activity on frontend
	 *
	 * @deprecated Use enqueue_cart_tracking_script instead
	 */
	public function track_cart_activity() {
		// This method is kept for backward compatibility but is no longer used
		return;
	}

	/**
	 * Send recovery messages to customers with abandoned carts
	 */
	public function send_recovery_messages() {
		if ( ! WWCC_Settings::get( 'enable_cart_recovery' ) ) {
			return;
		}

		$hours_threshold = intval( WWCC_Settings::get( 'cart_recovery_hours', 2 ) );

		// Find abandoned carts that haven't been contacted yet
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Reads from the plugin's custom cart recovery table.
		$abandoned_carts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}wwcc_carts 
				WHERE abandoned = 1 
				AND recovery_sent = 0 
				AND DATE_ADD(last_activity, INTERVAL %d HOUR) <= NOW()",
				$hours_threshold
			)
		);

		foreach ( $abandoned_carts as $cart ) {
			$this->send_recovery_message( $cart );
		}
	}

	/**
	 * Send recovery message for a specific cart
	 */
	private function send_recovery_message( $cart ) {
		// Get order if it exists
		$order = wc_get_order( $cart->order_id );

		if ( ! $order || $order->get_total() <= 0 ) {
			return;
		}

		$phone = $order->get_billing_phone();

		$message = sprintf(
			/* translators: 1: Customer first name, 2: Order total, 3: Checkout payment URL */
			__( '👀 Hi %1$s!\n\nYou left items in your cart worth KES %2$s\n\n👉 Complete your order here: %3$s\n\nOffer expires soon! 🎁', 'pesaflow-payments-for-woocommerce' ),
			$order->get_billing_first_name(),
			$order->get_formatted_order_total(),
			$order->get_checkout_payment_url()
		);

		$result = WWCC_WhatsApp_API::get_instance()->send_message( $phone, $message );

		if ( ! is_wp_error( $result ) ) {
			// Mark as sent
			global $wpdb;

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Updates the plugin's custom cart recovery table.
			$wpdb->update(
				$wpdb->prefix . 'wwcc_carts',
				[ 'recovery_sent' => 1, 'recovery_sent_at' => current_time( 'mysql' ) ],
				[ 'id' => $cart->id ],
				[ '%d', '%s' ],
				[ '%d' ]
			);
		}
	}

	/**
	 * Detect and save abandoned cart
	 *
	 * This should be called when a customer adds to cart but doesn't complete checkout
	 *
	 * @param int $order_id Order ID (pending order from cart)
	 */
	public static function mark_cart_abandoned( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order || 'pending' !== $order->get_status() ) {
			return;
		}

		global $wpdb;

		// Check if cart already exists
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Reads from the plugin's custom cart recovery table.
		$existing = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id FROM {$wpdb->prefix}wwcc_carts WHERE order_id = %d",
				$order_id
			)
		);

		if ( $existing ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Updates the plugin's custom cart recovery table.
			$wpdb->update(
				$wpdb->prefix . 'wwcc_carts',
				[
					'abandoned'     => 1,
					'last_activity' => current_time( 'mysql' ),
				],
				[ 'id' => $existing->id ],
				[ '%d', '%s' ],
				[ '%d' ]
			);
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Inserts into the plugin's custom cart recovery table.
			$wpdb->insert(
				$wpdb->prefix . 'wwcc_carts',
				[
					'order_id'       => $order_id,
					'phone_number'   => $order->get_billing_phone(),
					'cart_total'     => $order->get_total(),
					'abandoned'      => 1,
					'last_activity'  => current_time( 'mysql' ),
					'recovery_sent'  => 0,
				],
				[ '%d', '%s', '%f', '%d', '%s', '%d' ]
			);
		}
	}
}
