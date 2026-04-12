<?php
/**
 * Plugin Name: Order Messaging for WooCommerce Kenya
 * Plugin URI: https://github.com/Varence-kiiru/order-messaging-for-woocommerce-kenya
 * Description: Complete order messaging and M-Pesa payment automation for WooCommerce in Kenya (via Twilio, with order notifications, and cart recovery)
 * Version: 1.0.0
 * Author: Varence Kiiru
 * Author URI: https://github.com/Varence-kiiru
 * License: GPL2
 * Text Domain: order-messaging-for-woocommerce-kenya
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 *
 * @package Order_Messaging_For_WooCommerce_Kenya
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'WWCC_PLUGIN_FILE', __FILE__ );
define( 'WWCC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WWCC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WWCC_PLUGIN_VERSION', '1.0.0' );

// Load composer autoloader if it exists
if ( file_exists( WWCC_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
	require_once WWCC_PLUGIN_DIR . 'vendor/autoload.php';
}

// Load plugin classes
require_once WWCC_PLUGIN_DIR . 'includes/class-whatsapp-woocommerce.php';

/**
 * The main plugin class instance
 */
function wwcc_get_plugin() {
	static $plugin;

	if ( ! isset( $plugin ) ) {
		$plugin = new WhatsApp_WooCommerce();
	}

	return $plugin;
}

/**
 * Initialize plugin on init hook (recommended for translations)
 */
function wwcc_init_plugin() {
	wwcc_get_plugin()->init();
}
add_action( 'init', 'wwcc_init_plugin', 0 );

/**
 * Plugin activation hook
 */
function wwcc_activate_plugin() {
	if ( class_exists( 'WhatsApp_WooCommerce' ) ) {
		WhatsApp_WooCommerce::activate();
	}
}
register_activation_hook( __FILE__, 'wwcc_activate_plugin' );

/**
 * Plugin deactivation hook
 */
function wwcc_deactivate_plugin() {
	if ( class_exists( 'WhatsApp_WooCommerce' ) ) {
		WhatsApp_WooCommerce::deactivate();
	}
}
register_deactivation_hook( __FILE__, 'wwcc_deactivate_plugin' );
