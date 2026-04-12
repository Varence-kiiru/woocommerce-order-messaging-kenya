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
class Cart_Recovery {

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
		// Track abandoned carts
		add_action( 'wp_footer', [ $this, 'track_cart_activity' ] );

		// Cron for sending reminders
		add_action( 'wwcc_cart_recovery_cron', [ $this, 'send_recovery_messages' ] );

		// Schedule cron on init
		if ( ! wp_next_scheduled( 'wwcc_cart_recovery_cron' ) ) {
			wp_schedule_event( time(), 'hourly', 'wwcc_cart_recovery_cron' );
		}
	}

	/**
	 * Track cart activity on frontend
	 */
	public function track_cart_activity() {
		if ( is_admin() || ! is_shop() && ! is_product_category() && ! is_product() ) {
			return;
		}

		// Only on product pages and shop
		if ( ! is_product() && ! is_shop() && ! is_product_category() ) {
			return;
		}

		?>
		<script>
		(function() {
			if (typeof jQuery === 'undefined') return;
			
			var $ = jQuery;
			var cacheKey = 'wwcc_cart_tracked';
			var cartCount = $('span.cart-contents-count').text() || 0;
			
			if (cartCount > 0) {
				// Cart has items
				localStorage.setItem(cacheKey, JSON.stringify({
					cartCount: cartCount,
					timestamp: Date.now(),
					url: window.location.href
				}));
			}
		})();
		</script>
		<?php
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
			__( '👀 Hi %s!\n\nYou left items in your cart worth KES %s\n\n👉 Complete your order here: %s\n\nOffer expires soon! 🎁', 'woocommerce-order-messaging-kenya' ),
			$order->get_billing_first_name(),
			$order->get_formatted_order_total(),
			$order->get_checkout_payment_url()
		);

		$result = WhatsApp_API::get_instance()->send_message( $phone, $message );

		if ( ! is_wp_error( $result ) ) {
			// Mark as sent
			global $wpdb;

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
		$existing = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id FROM {$wpdb->prefix}wwcc_carts WHERE order_id = %d",
				$order_id
			)
		);

		if ( $existing ) {
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
