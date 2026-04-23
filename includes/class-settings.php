<?php
/**
 * Settings Handler
 *
 * Manages plugin settings and options
 *
 * @package WhatsApp_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings class
 */
class WWCC_Settings {

	/**
	 * Option key
	 */
	const OPTION_KEY = 'wwcc_settings';

	/**
	 * Get setting value
	 *
	 * @param string $key Setting key
	 * @param mixed  $default Default value if not found
	 * @return mixed Setting value
	 */
	public static function get( $key, $default = '' ) {
		$settings = self::get_all();

		return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
	}

	/**
	 * Update setting value
	 *
	 * @param string $key Setting key
	 * @param mixed  $value Setting value
	 * @return bool True if updated/saved
	 */
	public static function update( $key, $value ) {
		$settings         = self::get_all();
		$settings[ $key ] = $value;

		return update_option( self::OPTION_KEY, $settings );
	}

	/**
	 * Get all settings
	 *
	 * @return array All settings
	 */
	public static function get_all() {
		$saved_settings = get_option( self::OPTION_KEY, [] );

		if ( ! is_array( $saved_settings ) ) {
			$saved_settings = [];
		}

		return wp_parse_args( $saved_settings, self::get_defaults() );
	}

	/**
	 * Update multiple settings
	 *
	 * @param array $values Array of key => value pairs
	 * @return bool True if updated
	 */
	public static function update_multiple( $values ) {
		$settings = self::get_all();

		foreach ( $values as $key => $value ) {
			$settings[ $key ] = $value;
		}

		return update_option( self::OPTION_KEY, $settings );
	}

	/**
	 * Delete setting
	 *
	 * @param string $key Setting key
	 * @return bool True if deleted
	 */
	public static function delete( $key ) {
		$settings = get_option( self::OPTION_KEY, [] );

		if ( isset( $settings[ $key ] ) ) {
			unset( $settings[ $key ] );
			return update_option( self::OPTION_KEY, $settings );
		}

		return false;
	}

	/**
	 * Sanitize phone number setting
	 */
	public static function sanitize_phone( $phone ) {
		// Remove all non-numeric and plus sign
		return preg_replace( '/[^0-9+]/', '', $phone );
	}

	/**
	 * Sanitize API key setting
	 */
	public static function sanitize_api_key( $key ) {
		return sanitize_text_field( $key );
	}

	/**
	 * Get default settings
	 *
	 * @return array Default settings
	 */
	public static function get_defaults() {
		return [
			// WhatsApp Configuration
			'whatsapp_provider'      => 'twilio',
			'business_phone'         => '',
			'twilio_sid'             => '',
			'twilio_token'           => '',
			'twilio_phone'           => '',
			'meta_phone_number_id'   => '',
			'meta_access_token'      => '',
			'webhook_verify_token'   => 'test_token_12345',

			// M-Pesa Configuration
			'daraja_consumer_key'    => '',
			'daraja_consumer_secret' => '',
			'mpesa_till_number'      => '',
			'mpesa_passkey'          => '',

			// Features
			'enable_notifications'   => 1,
			'enable_order_creation'  => 1,
			'enable_cart_recovery'   => 1,
			'enable_mpesa_stk'       => 1,

			// Cart Recovery Settings
			'cart_recovery_hours'    => 2,

			// Additional settings
			'enable_debug'           => 0,
		];
	}
}
