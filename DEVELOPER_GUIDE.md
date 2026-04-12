# Developer Guide

Documentation for developers extending or modifying the WhatsApp WooCommerce Kenya plugin.

## Architecture Overview

The plugin follows a modular architecture:

```
Main Plugin (whatsapp-woocommerce.php)
├── Core Components
│   ├── WhatsApp_API           → Sends WhatsApp messages
│   ├── MPesa_Handler          → Handles M-Pesa payments
│   ├── Order_Sync             → Creates orders from WhatsApp
│   ├── Cart_Recovery          → Abandoned cart recovery
│   └── Webhook_Handler        → Processes webhooks
├── Frontend
│   └── class-frontend.php     → Product button, scripts
├── Admin
│   └── Admin_WhatsApp_WooCommerce → WordPress admin UI
└── Database
    └── DB_WhatsApp_WooCommerce    → Table management
```

## Core Classes

### WhatsApp_API

**File:** `includes/class-whatsapp-api.php`

Handles all WhatsApp communications.

```php
// Get instance
$whatsapp = WhatsApp_API::get_instance();

// Send message
$result = $whatsapp->send_message(
    '+2547XXXXXXXX',  // Phone with country code
    'Hello from WhatsApp!',
    ['order_id' => 123]
);

if ( is_wp_error( $result ) ) {
    echo $result->get_error_message();
} else {
    echo $result['message_id']; // Provider's message ID
}
```

**Key Methods:**

```php
// Send WhatsApp message
send_message( $phone_number, $message, $args = [] )

// Support both Twilio and Meta APIs
send_via_twilio( $phone, $message, $args )
send_via_meta( $phone, $message, $args )

// Hooks for automatic notifications
on_order_created( $order_id )
on_order_completed( $order_id )
on_payment_complete( $order_id )
```

---

### MPesa_Handler

**File:** `includes/class-mpesa-handler.php`

Manages M-Pesa Daraja API integration.

```php
// Get instance
$mpesa = MPesa_Handler::get_instance();

// Get access token
$token = $mpesa->get_access_token();

// Send STK Push (payment prompt)
$result = $mpesa->initiate_stk_push(
    order_id: 123,
    phone_number: '+2547XXXXXXXX',
    amount: 2500
);

if ( is_wp_error( $result ) ) {
    echo 'STK Push failed: ' . $result->get_error_message();
} else {
    echo 'Payment prompt sent!';
    echo $result['checkout_id']; // For webhook matching
}

// Process M-Pesa callback
// (Called automatically via webhook)
$mpesa->process_mpesa_callback( $data );

// Charge order (admin action)
$mpesa->charge_order( $order_id );
```

**Key Methods:**

```php
get_access_token()                          // Get Daraja token
initiate_stk_push( $order_id, $phone, $amount )  // Send payment prompt
handle_mpesa_callback( $request )           // Webhook handler
process_mpesa_callback( $data )             // Process payment
charge_order( $order_id )                   // Admin manual charge
```

---

### Order_Sync

**File:** `includes/class-order-sync.php`

Creates WooCommerce orders from WhatsApp messages.

```php
// Get instance
$order_sync = Order_Sync::get_instance();

// Manual conversation processing
$message_data = [
    'phone'      => '+2547XXXXXXXX',
    'text'       => 'Hi, I want Nike Shoes x2',
    'message_id' => 'msg_123',
    'timestamp'  => time()
];

$order_sync->handle_incoming_message( $message_data );

// Or use webhook automatically
// POST /wp-json/wwcc/v1/incoming-messages
```

**Workflow:**

```
Message Received
├── Extract customer info
├── Find matching products
├── Create WooCommerce order
├── Send confirmation via WhatsApp
└── Log conversation
```

**Key Methods:**

```php
receive_incoming_message( $request )       // REST webhook
handle_incoming_message( $data )           // Process message
extract_order_items( $message_data )       // Parse products
create_order_from_message( $customer, $items )  // Create order
```

---

### Cart_Recovery

**File:** `includes/class-cart-recovery.php`

Handles abandoned cart detection and reminders.

```php
// Get instance
$cart_recovery = Cart_Recovery::get_instance();

// Manually mark cart as abandoned
Cart_Recovery::mark_cart_abandoned( $order_id );

// Send recovery messages (runs on cron)
// Called hourly via wp_schedule_event('wwcc_cart_recovery_cron')
$cart_recovery->send_recovery_messages();
```

**How It Works:**

```
1. Customer adds items but doesn't checkout
2. Order created in 'pending' status
3. After configured hours (default: 2), cart marked abandoned
4. Hourly cron sends WhatsApp reminder
5. Customer clicks recovery link
6. Checkout page with saved items
```

---

### WWCC_Settings

**File:** `includes/class-settings.php`

Centralized settings management.

```php
// Get setting
$phone = WWCC_Settings::get( 'business_phone' );
$phone = WWCC_Settings::get( 'business_phone', '+254700000000' ); // with default

// Get all settings
$all = WWCC_Settings::get_all();

// Update setting
WWCC_Settings::update( 'business_phone', '+2547XXXXXXXX' );

// Update multiple
WWCC_Settings::update_multiple( [
    'daraja_consumer_key'    => 'XYZ',
    'daraja_consumer_secret' => 'ABC',
] );

// Delete setting
WWCC_Settings::delete( 'business_phone' );

// Sanitize
$phone = WWCC_Settings::sanitize_phone( $phone );
$key = WWCC_Settings::sanitize_api_key( $key );
```

**Available Settings:**

```
WhatsApp:
- whatsapp_provider (twilio|meta)
- business_phone
- twilio_sid, twilio_token, twilio_phone
- meta_phone_number_id, meta_access_token
- webhook_verify_token

M-Pesa:
- daraja_consumer_key, daraja_consumer_secret
- mpesa_till_number, mpesa_passkey

Features:
- enable_notifications
- enable_order_creation
- enable_cart_recovery
- enable_mpesa_stk
- cart_recovery_hours
- enable_debug
```

---

## Hooks & Filters

### Actions

All major actions the plugin runs:

```php
// WhatsApp API
do_action( 'wwcc_message_sent', $order_id, $phone, $message );
do_action( 'wwcc_message_failed', $order_id, $error );

// M-Pesa
do_action( 'wwcc_mpesa_payment_received', $order_id, $amount );
do_action( 'wwcc_mpesa_payment_failed', $order_id );

// Orders
do_action( 'wwcc_order_created_from_whatsapp', $order_id, $phone );

// Cart Recovery
do_action( 'wwcc_recovery_message_sent', $order_id, $phone );

// Webhooks
do_action( 'wwcc_webhook_received', $source, $data );
```

### Filters

```php
// Message content
$message = apply_filters( 'wwcc_order_confirmation_message', $message, $order );
$message = apply_filters( 'wwcc_recovery_message', $message, $order );

// Phone number format
$phone = apply_filters( 'wwcc_phone_number', $phone, $context );

// Product matching
$product = apply_filters( 'wwcc_find_product', $product, $search_term );

// Order creation
$order_args = apply_filters( 'wwcc_create_order_args', $order_args, $message );
```

---

## Extending the Plugin

### Add Custom WhatsApp Message Type

```php
// In your plugin/theme functions.php

// Add filter to customize message
add_filter( 'wwcc_order_confirmation_message', function( $message, $order ) {
    // Add custom content
    $message .= "\n\nThank you for your purchase!";
    return $message;
}, 10, 2 );

// Send on custom hook
add_action( 'woocommerce_order_status_processing', function( $order_id ) {
    $order = wc_get_order( $order_id );
    $message = "Your order #" . $order_id . " is being prepared!";
    WhatsApp_API::get_instance()->send_message(
        $order->get_billing_phone(),
        $message,
        [ 'order_id' => $order_id ]
    );
});
```

### Add Custom Admin Action

```php
// In your plugin code

// Register new admin menu item
add_submenu_page(
    'wwcc-settings',
    'Analytics',
    'Analytics',
    'manage_options',
    'wwcc-analytics',
    'render_analytics_page'
);

function render_analytics_page() {
    // Your analytics code
    global $wpdb;
    
    $stats = $wpdb->get_results(
        "SELECT COUNT(*) as total_messages 
         FROM {$wpdb->prefix}wwcc_whatsapp_logs"
    );
    
    echo "Total messages sent: " . $stats[0]->total_messages;
}
```

### Override Message Building

```php
// Build custom messages for all notifications

add_filter( 'wwcc_order_confirmation_message', function( $message, $order ) {
    return sprintf(
        "🎉 Order #%d Confirmed!\n\nItems:\n%s\n\nTotal: KES %s",
        $order->get_id(),
        implode( "\n", array_map( function( $item ) {
            return "• " . $item->get_name() . " x" . $item->get_quantity();
        }, $order->get_items() ) ),
        $order->get_formatted_order_total()
    );
}, 10, 2 );
```

---

## REST API Endpoints

### Webhook Endpoints

```
POST /wp-json/wwcc/v1/mpesa-callback
    For M-Pesa payment confirmations
    No authentication required
    Payload: M-Pesa callback JSON

POST /wp-json/wwcc/v1/incoming-messages
    For incoming WhatsApp messages
    No authentication required
    Payload: Message from Twilio/Meta

GET /wp-json/wwcc/v1/incoming-messages
    Webhook verification endpoint
    Params: ?hub_verify_token=XXX&hub_challenge=YYY
```

### Custom Endpoints (to add)

```php
// In your extension code

register_rest_route( 'wwcc/v1', '/custom-endpoint', [
    'methods'             => 'POST',
    'callback'            => 'my_custom_handler',
    'permission_callback' => '__return_true',
] );

function my_custom_handler( $request ) {
    $params = $request->get_json_params();
    // Process request
    return new WP_REST_Response( [ 'success' => true ], 200 );
}
```

---

## Database Queries

### Get customer's order history (WhatsApp)

```php
global $wpdb;

$orders = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->posts} 
         WHERE post_type = 'shop_order'
         AND ID IN (
             SELECT post_id FROM {$wpdb->postmeta}
             WHERE meta_key = '_wwcc_whatsapp_phone'
             AND meta_value = %s
         )
         ORDER BY post_date DESC",
        $phone_number
    )
);

foreach ( $orders as $order ) {
    $order = wc_get_order( $order->ID );
    echo "Order #" . $order->get_id() . " - KES " . $order->get_total();
}
```

### Get message statistics

```php
global $wpdb;

$stats = $wpdb->get_results(
    "SELECT 
        message_type,
        COUNT(*) as count,
        DATE(sent_at) as date
    FROM {$wpdb->prefix}wwcc_whatsapp_logs
    WHERE sent_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY message_type, DATE(sent_at)
    ORDER BY date DESC"
);

foreach ( $stats as $stat ) {
    echo $stat->message_type . ": " . $stat->count . " messages on " . $stat->date;
}
```

---

## Testing

### Unit Testing Example

```php
// test-whatsapp-api.php

class Test_WhatsApp_API extends WP_UnitTestCase {
    
    public function test_sanitize_phone() {
        $phone = '0712345678';
        $sanitized = WhatsApp_API::sanitize_phone( $phone );
        $this->assertEquals( '+254712345678', $sanitized );
    }
    
    public function test_send_message() {
        // Mock Twilio response
        $result = WhatsApp_API::send_message(
            '+2547XXXXXXXX',
            'Test message'
        );
        $this->assertNotWPError( $result );
        $this->assertArrayHasKey( 'message_id', $result );
    }
}
```

---

## Performance Tips

1. **Cache access tokens**
   ```php
   set_transient( 'wwcc_mpesa_token', $token, 55 * MINUTE_IN_SECONDS );
   ```

2. **Batch message sending**
   ```php
   // Instead of sending individually in loops
   // Store in queue and process via cron or async job
   ```

3. **Optimize database queries**
   ```php
   // Use LIMIT for pagination
   // Add proper indexes on foreign keys
   // Archive old logs regularly
   ```

4. **Lazy load settings**
   ```php
   // Settings are cached in WordPress options
   // Avoid repeated get_option calls using WWCC_Settings class
   ```

---

## Security Considerations

1. **Validate webhooks**
   - All webhook endpoints should validate source
   - Implement signature verification if available

2. **Sanitize input**
   - All user input is sanitized via `sanitize_text_field()`
   - Phone numbers are validated

3. **Protect sensitive data**
   - API keys stored in WordPress options (encrypted in DB)
   - Never log full API responses
   - Use password fields in admin

4. **Rate limiting**
   - Consider rate limiting on webhook endpoints
   - Implement exponential backoff for retries

---

## Common Modifications

### Change notification message format

```php
add_filter( 'wwcc_order_confirmation_message', function( $message, $order ) {
    return "Order #" . $order->get_id() . " received!\nWill be ready soon.";
}, 10, 2 );
```

### Add custom order status

```php
add_action( 'woocommerce_order_status_ready', function( $order_id ) {
    $order = wc_get_order( $order_id );
    WhatsApp_API::get_instance()->send_message(
        $order->get_billing_phone(),
        "📦 Your order is ready for delivery!"
    );
});
```

### Disable auto-replies

```php
// Disable auto-notifications for specific orders
add_filter( 'wwcc_send_notification', function( $should_send, $order_id ) {
    $order = wc_get_order( $order_id );
    
    // Don't send if order is from specific customer
    if ( $order->get_customer_id() === get_current_user_id() ) {
        return false;
    }
    
    return $should_send;
}, 10, 2 );
```

---

## Troubleshooting for Developers

### Debug mode

```php
// Enable in WordPress
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );

// Enable in plugin
WWCC_Settings::update( 'enable_debug', 1 );

// Check logs
tail -f /wp-content/debug.log
```

### Test API connectivity

```php
$whatsapp = WhatsApp_API::get_instance();
$result = $whatsapp->send_message( '+2547XXXXXXXX', 'Test' );

if ( is_wp_error( $result ) ) {
    die( 'Error: ' . $result->get_error_message() );
}

print_r( $result );
```

---

## Version Compatibility

**Tested With:**
- WordPress 5.0+
- WooCommerce 3.0+
- PHP 7.4+

**Known Limitations:**
- MultiSite not fully tested
- WPML translations partial
- ACF compatibility: limited

---

Need help? Check README.md or raise an issue on GitHub.
