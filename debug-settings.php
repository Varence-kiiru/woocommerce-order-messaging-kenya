<?php
/**
 * Debug script to check plugin settings storage
 * Place this in your WordPress root and access via http://localhost/debug-settings.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load WordPress
require_once( dirname( __FILE__ ) . '/wp-load.php' );

// Check if plugin is active
$active_plugins = get_option( 'active_plugins' );
echo "<h2>Plugin Status</h2>";
echo "Active: " . ( in_array( 'woocommerce-order-messaging-kenya/woocommerce-order-messaging-kenya.php', $active_plugins ) ? 'YES ✓' : 'NO ✗' ) . "<br>";

// Check settings in database
echo "<h2>Settings Stored in Database</h2>";
echo "<pre>";
// All WWCC settings stored with option_name like "wwcc_*"
$settings = get_option( 'wwcc_settings' );
if ( $settings ) {
    var_dump( $settings );
} else {
    echo "No settings found. Checking individual options...\n";
    
    $keys = [
        'whatsapp_provider',
        'twilio_sid',
        'twilio_token',
        'twilio_phone',
        'meta_phone_number_id',
        'meta_access_token'
    ];
    
    foreach ( $keys as $key ) {
        $value = get_option( 'wwcc_' . $key );
        echo esc_html( $key ) . ": " . ( $value ? esc_html( $value ) : '[EMPTY]' ) . "\n";
    }
}
echo "</pre>";

// Check database tables
echo "<h2>Plugin Database Tables</h2>";
global $wpdb;
$tables = [
    $wpdb->prefix . 'wwcc_whatsapp_logs',
    $wpdb->prefix . 'wwcc_mpesa_logs',
    $wpdb->prefix . 'wwcc_conversations',
    $wpdb->prefix . 'wwcc_webhook_logs',
    $wpdb->prefix . 'wwcc_carts'
];

foreach ( $tables as $table ) {
    $exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) === $table;
    echo esc_html( $table ) . ": " . ( $exists ? 'EXISTS ✓' : 'MISSING ✗' ) . "<br>";
}

echo "<hr><p><strong>If provider and credentials show [EMPTY], your settings didn't save. Go back to Settings page and try saving again.</strong></p>";
?>
