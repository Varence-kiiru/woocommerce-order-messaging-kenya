<?php
/**
 * Frontend Integration
 *
 * Handles frontend scripts and styles
 *
 * @package WhatsApp_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue frontend scripts and styles
 */
function wwcc_enqueue_frontend_assets() {
	if ( ! is_product() && ! is_cart() && ! is_checkout() ) {
		return;
	}

	// Enqueue stylesheet
	wp_enqueue_style(
		'wwcc-frontend',
		WWCC_PLUGIN_URL . 'assets/css/frontend.css',
		[],
		WWCC_PLUGIN_VERSION
	);

	// Enqueue script
	wp_enqueue_script(
		'wwcc-frontend',
		WWCC_PLUGIN_URL . 'assets/js/frontend.js',
		[ 'jquery' ],
		WWCC_PLUGIN_VERSION,
		true
	);

	// Localize script
	wp_localize_script(
		'wwcc-frontend',
		'wwcc_frontend',
		[
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'wwcc_nonce' ),
			'site_url' => site_url(),
		]
	);
}

add_action( 'wp_enqueue_scripts', 'wwcc_enqueue_frontend_assets' );

/**
 * Add WhatsApp button to product page
 */
function wwcc_add_product_whatsapp_button() {
	if ( ! is_product() ) {
		return;
	}

	global $product;

	if ( ! $product ) {
		return;
	}

	// Check if feature is enabled
	if ( ! WWCC_Settings::get( 'enable_order_creation' ) ) {
		return;
	}

	$product_id = $product->get_id();
	$phone_number = WWCC_Settings::get( 'business_phone' );

	if ( ! $phone_number ) {
		return;
	}

	// Build message
	$message = sprintf(
		/* translators: 1: Product name, 2: Product price in KES */
		__( 'Hi, I want to order:\nProduct: %1$s\nPrice: KES %2$s', 'order-messaging-for-woocommerce-kenya' ),
		$product->get_name(),
		$product->get_price()
	);

	// Clean phone number
	$clean_phone = preg_replace( '/[^0-9]/', '', $phone_number );

	$whatsapp_url = 'https://wa.me/' . $clean_phone . '?text=' . urlencode( $message );

	$icon_url = WWCC_PLUGIN_URL . 'assets/images/whatsapp_icon.jpg';
	echo sprintf(
		'<a href="%s" class="wp-element-button wwcc-whatsapp-button" target="_blank" rel="noopener noreferrer">
			<img src="%s" alt="WhatsApp" class="wwcc-whatsapp-button-icon">
			%s
		</a>',
		esc_url( $whatsapp_url ),
		esc_url( $icon_url ),
		esc_html__( 'Order via WhatsApp', 'order-messaging-for-woocommerce-kenya' )
	);
}

add_action( 'woocommerce_after_add_to_cart_button', 'wwcc_add_product_whatsapp_button', 15 );

/**
 * Add WhatsApp button to shop loop (product listing)
 */
function wwcc_add_shop_loop_whatsapp_button() {
	global $product;

	if ( ! $product ) {
		return;
	}

	// Check if feature is enabled
	if ( ! WWCC_Settings::get( 'enable_order_creation' ) ) {
		return;
	}

	$phone_number = WWCC_Settings::get( 'business_phone' );

	if ( ! $phone_number ) {
		return;
	}

	// Build message
	$message = sprintf(
		/* translators: 1: Product name, 2: Product price in KES */
		__( 'Hi, I want to order:\nProduct: %1$s\nPrice: KES %2$s', 'order-messaging-for-woocommerce-kenya' ),
		$product->get_name(),
		$product->get_price()
	);

	// Clean phone number
	$clean_phone = preg_replace( '/[^0-9]/', '', $phone_number );

	$whatsapp_url = 'https://wa.me/' . $clean_phone . '?text=' . urlencode( $message );

	$icon_url = WWCC_PLUGIN_URL . 'assets/images/whatsapp_icon.jpg';
	echo sprintf(
		'<a href="%s" class="wp-element-button wwcc-whatsapp-button wwcc-whatsapp-button--small" target="_blank" rel="noopener noreferrer">
			<img src="%s" alt="WhatsApp" class="wwcc-whatsapp-button-icon">
			%s
		</a>',
		esc_url( $whatsapp_url ),
		esc_url( $icon_url ),
		esc_html__( 'Order via WhatsApp', 'order-messaging-for-woocommerce-kenya' )
	);
}

add_action( 'woocommerce_after_shop_loop_item', 'wwcc_add_shop_loop_whatsapp_button', 15 );

/**
 * Mark cart as abandoned when checkout is initiated
 */
function wwcc_check_cart_abandonment() {
	if ( ! is_checkout() || ! WC()->cart ) {
		return;
	}

	// This would be called when cart is about to be processed
	// You could save cart status to custom table here
}

add_action( 'wp_footer', 'wwcc_check_cart_abandonment' );
