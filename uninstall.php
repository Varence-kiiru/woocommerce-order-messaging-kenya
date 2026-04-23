<?php
/**
 * Plugin uninstall cleanup.
 *
 * @package Order_Messaging_For_WooCommerce_Kenya
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once __DIR__ . '/includes/class-settings.php';
require_once __DIR__ . '/includes/class-db.php';

WWCC_DB::drop_tables();

delete_option( WWCC_Settings::OPTION_KEY );
delete_option( 'wwcc_plugin_version' );

wp_clear_scheduled_hook( 'wwcc_cart_recovery_cron' );
