<?php
/**
 * Main WhatsApp WooCommerce Plugin Class
 *
 * @package WhatsApp_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class
 */
class WWCC_Plugin {

	/**
	 * Instance variable
	 *
	 * @var WWCC_Plugin
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
		// Dependencies are loaded in init() to avoid early loading
	}

	/**
	 * Load plugin dependencies
	 */
	private function load_dependencies() {
		// Core classes
		require_once WWCC_PLUGIN_DIR . 'includes/class-whatsapp-api.php';
		require_once WWCC_PLUGIN_DIR . 'includes/class-mpesa-handler.php';
		require_once WWCC_PLUGIN_DIR . 'includes/class-order-sync.php';
		require_once WWCC_PLUGIN_DIR . 'includes/class-cart-recovery.php';
		require_once WWCC_PLUGIN_DIR . 'includes/class-webhook-handler.php';
		require_once WWCC_PLUGIN_DIR . 'includes/class-settings.php';
		require_once WWCC_PLUGIN_DIR . 'includes/class-db.php';
		require_once WWCC_PLUGIN_DIR . 'includes/class-frontend.php';

		// Admin classes
		if ( is_admin() ) {
			require_once WWCC_PLUGIN_DIR . 'admin/class-admin.php';
		}
	}

	/**
	 * Initialize plugin hooks
	 */
	public function init() {
		// Load dependencies first
		$this->load_dependencies();

		$this->maybe_upgrade();

		// Check WooCommerce dependency
		if ( ! $this->is_woocommerce_active() ) {
			add_action( 'admin_notices', [ $this, 'woocommerce_missing_notice' ] );
			return;
		}

		// Initialize core components
		WWCC_WhatsApp_API::get_instance();
		WWCC_MPesa_Handler::get_instance();
		WWCC_Order_Sync::get_instance();
		WWCC_Cart_Recovery::get_instance();
		WWCC_Webhook_Handler::get_instance();

		// Initialize admin (if admin)
		if ( is_admin() ) {
			WWCC_Admin::get_instance();
		}

		// Add settings link in plugins list
		add_filter( 'plugin_action_links_' . plugin_basename( WWCC_PLUGIN_FILE ), [ $this, 'add_settings_link' ] );

		// Register AJAX endpoints for frontend
		$this->register_ajax_handlers();
	}

	/**
	 * Check if WooCommerce is active
	 */
	private function is_woocommerce_active() {
		return class_exists( 'WooCommerce' ) || class_exists( 'woocommerce' );
	}

	/**
	 * Ensure schema and defaults stay in sync across updates.
	 */
	private function maybe_upgrade() {
		$stored_version = get_option( 'wwcc_plugin_version', '' );

		if ( WWCC_PLUGIN_VERSION !== $stored_version ) {
			WWCC_DB::create_tables();
			update_option( 'wwcc_plugin_version', WWCC_PLUGIN_VERSION );
		}
	}

	/**
	 * WooCommerce missing notice
	 */
	public function woocommerce_missing_notice() {
		echo '<div class="notice notice-error"><p>';
		echo wp_kses_post(
			__( 'WhatsApp WooCommerce requires WooCommerce to be installed and active.', 'pesaflow-payments-for-woocommerce' )
		);
		echo '</p></div>';
	}

	/**
	 * Add settings link to plugins page
	 */
	public function add_settings_link( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			admin_url( 'admin.php?page=wwcc-settings' ),
			__( 'Settings', 'pesaflow-payments-for-woocommerce' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Register AJAX handlers
	 */
	private function register_ajax_handlers() {
		add_action( 'wp_ajax_nopriv_wwcc_send_whatsapp_order', [ $this, 'ajax_send_whatsapp_order' ] );
		add_action( 'wp_ajax_wwcc_send_whatsapp_order', [ $this, 'ajax_send_whatsapp_order' ] );
	}

	/**
	 * AJAX handler to send WhatsApp order message
	 */
	public function ajax_send_whatsapp_order() {
		check_ajax_referer( 'wwcc_nonce', 'nonce' );

		$product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;

		if ( ! $product_id ) {
			wp_send_json_error( __( 'Invalid product', 'pesaflow-payments-for-woocommerce' ) );
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			wp_send_json_error( __( 'Product not found', 'pesaflow-payments-for-woocommerce' ) );
		}

		$phone_number = WWCC_Settings::get( 'business_phone' );
		if ( ! $phone_number ) {
			wp_send_json_error( __( 'Store WhatsApp not configured', 'pesaflow-payments-for-woocommerce' ) );
		}

		// Build WhatsApp message
		$message = sprintf(
			/* translators: 1: Product name, 2: Product price in KES */
			__( 'Hi, I want to order:\nProduct: %1$s\nPrice: KES %2$s', 'pesaflow-payments-for-woocommerce' ),
			$product->get_name(),
			$product->get_price()
		);

		$whatsapp_url = 'https://wa.me/' . preg_replace( '/[^0-9]/', '', $phone_number ) . '?text=' . urlencode( $message );

		wp_send_json_success( [ 'whatsapp_url' => $whatsapp_url ] );
	}

	/**
	 * Plugin activation
	 */
	public static function activate() {
		// Load all dependencies for activation
		require_once WWCC_PLUGIN_DIR . 'includes/class-settings.php';
		require_once WWCC_PLUGIN_DIR . 'includes/class-db.php';

		// Create database tables
		WWCC_DB::create_tables();

		if ( ! wp_next_scheduled( 'wwcc_cart_recovery_cron' ) ) {
			wp_schedule_event( time(), 'hourly', 'wwcc_cart_recovery_cron' );
		}

		// Set plugin version
		update_option( 'wwcc_plugin_version', WWCC_PLUGIN_VERSION );
	}

	/**
	 * Plugin deactivation
	 */
	public static function deactivate() {
		// Clean up any scheduled events
		wp_clear_scheduled_hook( 'wwcc_cart_recovery_cron' );
	}
}
