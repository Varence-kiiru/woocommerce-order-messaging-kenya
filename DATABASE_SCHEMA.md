# Database Schema

All database tables are automatically created when the plugin is activated.

## Tables

### 1. `wp_wwcc_whatsapp_logs`

Stores all WhatsApp messages sent by the plugin.

```sql
CREATE TABLE IF NOT EXISTS wp_wwcc_whatsapp_logs (
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
);
```

**Columns:**
- `id` - Unique message log ID
- `order_id` - Associated WooCommerce order ID
- `phone_number` - Customer phone number (e.g., +2547XXXXXXXX)
- `message` - Full message text sent
- `message_type` - Type (order_confirmation, payment_received, order_shipped, etc.)
- `sent_at` - When message was sent
- `created_at` - When log entry was created

**Message Types:**
- `order_confirmation` - Order created notification
- `payment_received` - Payment confirmed
- `order_shipped` - Order shipped
- `cart_recovery` - Abandoned cart reminder
- `custom` - Custom message

---

### 2. `wp_wwcc_mpesa_logs`

Tracks all M-Pesa related transactions.

```sql
CREATE TABLE IF NOT EXISTS wp_wwcc_mpesa_logs (
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
);
```

**Columns:**
- `id` - Unique transaction log ID
- `order_id` - Associated WooCommerce order
- `phone_number` - Customer phone number
- `amount` - Transaction amount (KES)
- `transaction_type` - Type (stk_push, callback, check_status)
- `status` - Status (initiated, completed, failed)
- `transaction_id` - M-Pesa transaction reference
- `logged_at` - When transaction occurred
- `created_at` - When log was created

**Transaction Types:**
- `stk_push` - STK push initiated to customer
- `callback` - Payment confirmation received
- `check_status` - Manual status check

**Statuses:**
- `initiated` - STK push sent to customer
- `completed` - Payment successful
- `failed` - Payment failed or cancelled

---

### 3. `wp_wwcc_conversations`

Stores WhatsApp conversation history from customers.

```sql
CREATE TABLE IF NOT EXISTS wp_wwcc_conversations (
    id BIGINT(20) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    phone_number VARCHAR(20) NOT NULL,
    message LONGTEXT,
    order_id BIGINT(20) UNSIGNED,
    action VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY phone_number (phone_number),
    KEY order_id (order_id),
    KEY created_at (created_at)
);
```

**Columns:**
- `id` - Unique conversation log ID
- `phone_number` - Customer phone
- `message` - Message content
- `order_id` - Associated order (if any)
- `action` - Action taken (order_created, order_updated, contact_requested, etc.)
- `created_at` - When received

**Actions:**
- `order_created` - Order was created from WhatsApp
- `order_updated` - Order status changed
- `message_received` - Customer message received
- `contact_requested` - Customer requested contact

---

### 4. `wp_wwcc_webhook_logs`

Debug log for all webhook calls from external providers.

```sql
CREATE TABLE IF NOT EXISTS wp_wwcc_webhook_logs (
    id BIGINT(20) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    source VARCHAR(50) NOT NULL,
    data LONGTEXT,
    status VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY source (source),
    KEY created_at (created_at)
);
```

**Columns:**
- `id` - Log entry ID
- `source` - Webhook source (twilio, meta, daraja, etc.)
- `data` - Full JSON payload received
- `status` - Processing status (received, success, error)
- `created_at` - When received

**Sources:**
- `twilio` - Twilio WhatsApp webhook
- `meta` - Meta (Instagram/Facebook) WhatsApp webhook
- `daraja` - Safaricom Daraja M-Pesa callback
- `manual` - Manual test/debug

---

### 5. `wp_wwcc_carts`

Abandoned cart tracking for recovery.

```sql
CREATE TABLE IF NOT EXISTS wp_wwcc_carts (
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
);
```

**Columns:**
- `id` - Cart record ID
- `order_id` - Associated pending order
- `phone_number` - Customer phone
- `cart_total` - Cart value in KES
- `abandoned` - Whether cart is abandoned (1 = yes)
- `last_activity` - Last time customer interacted
- `recovery_sent` - Whether recovery message sent (1 = yes)
- `recovery_sent_at` - When recovery message sent
- `created_at` - When cart was created

---

## Query Examples

### Get all messages for an order
```sql
SELECT * FROM wp_wwcc_whatsapp_logs 
WHERE order_id = 123 
ORDER BY created_at DESC;
```

### Get pending M-Pesa transactions
```sql
SELECT * FROM wp_wwcc_mpesa_logs 
WHERE status = 'initiated' 
AND DATE_ADD(logged_at, INTERVAL 10 MINUTE) > NOW()
ORDER BY logged_at DESC;
```

### Get abandoned carts not yet recovered
```sql
SELECT * FROM wp_wwcc_carts 
WHERE abandoned = 1 
AND recovery_sent = 0 
AND DATE_ADD(last_activity, INTERVAL 2 HOUR) <= NOW();
```

### Get customer conversation history
```sql
SELECT * FROM wp_wwcc_conversations 
WHERE phone_number = '+2547XXXXXXXX' 
ORDER BY created_at DESC;
```

### Get failed webhook callbacks
```sql
SELECT * FROM wp_wwcc_webhook_logs 
WHERE status = 'error' 
ORDER BY created_at DESC 
LIMIT 50;
```

---

## Data Retention

The plugin stores data indefinitely. To clean up old logs:

```php
// Delete logs older than 90 days
global $wpdb;
$wpdb->query( 
    "DELETE FROM {$wpdb->prefix}wwcc_whatsapp_logs 
    WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)" 
);
```

## Backup Considerations

Ensure your WordPress database backups include these custom tables when making backups.

## Performance Notes

- Add indexes on `order_id`, `phone_number`, and timestamp columns for faster queries
- Consider archiving old logs to a separate table for better performance
- Monitor table sizes, especially `wwcc_whatsapp_logs` and `wwcc_webhook_logs`

---

## WordPress Post Meta

The plugin also stores order-related data in WordPress post meta:

```
Order ID (WooCommerce Post Type)
├── _order_source (whatsapp, woocommerce, etc)
├── _wwcc_whatsapp_phone (customer phone for WhatsApp orders)
├── _wwcc_checkout_request_id (M-Pesa STK checkpoint ID)
├── _wwcc_mpesa_transaction_id (M-Pesa transaction ref)
└── _wwcc_recovery_sent (cart recovery tracking)
```
