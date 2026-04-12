<?php
/**
 * Database Setup
 *
 * Create necessary database tables for the plugin
 *
 * @package WhatsApp_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database class
 */
class DB_WhatsApp_WooCommerce {

	/**
	 * Create plugin database tables
	 */
	public static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		// Table for WhatsApp message logs
		$whatsapp_logs_sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wwcc_whatsapp_logs (
			id BIGINT(20) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
			order_id BIGINT(20) UNSIGNED,
			phone_number VARCHAR(20) NOT NULL,
			message LONGTEXT,
			message_type VARCHAR(50),
			sent_at DATETIME,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			KEY order_id (order_id),
			KEY phone_number (phone_number),
			KEY sent_at (sent_at)
		) $charset_collate;";

		// Table for M-Pesa transaction logs
		$mpesa_logs_sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wwcc_mpesa_logs (
			id BIGINT(20) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
			order_id BIGINT(20) UNSIGNED,
			phone_number VARCHAR(20) NOT NULL,
			amount DECIMAL(10, 2),
			transaction_type VARCHAR(50),
			status VARCHAR(50),
			transaction_id VARCHAR(100),
			logged_at DATETIME,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			KEY order_id (order_id),
			KEY phone_number (phone_number),
			KEY transaction_id (transaction_id),
			KEY logged_at (logged_at)
		) $charset_collate;";

		// Table for conversations
		$conversations_sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wwcc_conversations (
			id BIGINT(20) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
			phone_number VARCHAR(20) NOT NULL,
			message LONGTEXT,
			order_id BIGINT(20) UNSIGNED,
			action VARCHAR(100),
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			KEY phone_number (phone_number),
			KEY order_id (order_id),
			KEY created_at (created_at)
		) $charset_collate;";

		// Table for webhook logs
		$webhook_logs_sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wwcc_webhook_logs (
			id BIGINT(20) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
			source VARCHAR(50) NOT NULL,
			data LONGTEXT,
			status VARCHAR(50),
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			KEY source (source),
			KEY created_at (created_at)
		) $charset_collate;";

		// Table for abandoned carts
		$carts_sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wwcc_carts (
			id BIGINT(20) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
			order_id BIGINT(20) UNSIGNED NOT NULL,
			phone_number VARCHAR(20) NOT NULL,
			cart_total DECIMAL(10, 2),
			abandoned TINYINT(1) DEFAULT 0,
			last_activity DATETIME,
			recovery_sent TINYINT(1) DEFAULT 0,
			recovery_sent_at DATETIME,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			KEY order_id (order_id),
			KEY phone_number (phone_number),
			KEY abandoned (abandoned),
			KEY recovery_sent (recovery_sent)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( $whatsapp_logs_sql );
		dbDelta( $mpesa_logs_sql );
		dbDelta( $conversations_sql );
		dbDelta( $webhook_logs_sql );
		dbDelta( $carts_sql );

		// Initialize default settings
		if ( ! get_option( WWCC_Settings::OPTION_KEY ) ) {
			add_option( WWCC_Settings::OPTION_KEY, WWCC_Settings::get_defaults() );
		}
	}

	/**
	 * Drop plugin database tables on uninstall
	 */
	public static function drop_tables() {
		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wwcc_whatsapp_logs" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wwcc_mpesa_logs" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wwcc_conversations" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wwcc_webhook_logs" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wwcc_carts" );
	}
}
