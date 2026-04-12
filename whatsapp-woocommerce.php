<?php
/**
 * Plugin Name: WhatsApp WooCommerce Kenya
 * Plugin URI: https://github.com/Varence-kiiru/whatsapp-woocommerce-kenya
 * Description: Complete WhatsApp + WooCommerce automation for Kenya (M-Pesa, order notifications, cart recovery)
 * Version: 1.0.0
 * Author: Varence Kiiru
 * Author URI: https://github.com/Varence-kiiru
 * License: GPL2
 * Text Domain: whatsapp-woocommerce
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 *
 * @package WhatsApp_WooCommerce
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
 * The main plugin class
 */
function wwcc_get_plugin() {
	static $plugin;

	if ( ! isset( $plugin ) ) {
		$plugin = new WhatsApp_WooCommerce();
	}

	return $plugin;
}

// Initialize plugin on plugins_loaded
add_action( 'plugins_loaded', [ wwcc_get_plugin(), 'init' ] );

// Activation hooks
register_activation_hook( __FILE__, [ 'WhatsApp_WooCommerce', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'WhatsApp_WooCommerce', 'deactivate' ] );
