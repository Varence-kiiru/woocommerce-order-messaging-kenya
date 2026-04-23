=== PesaFlow Payments for WooCommerce ===
Contributors: Varence Kiiru, ngangakiiru
Tags: mpesa, payments, woocommerce, messaging, twilio
Requires at least: 5.0
Requires PHP: 7.4
Tested up to: 6.9
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

M-Pesa payment integration, WhatsApp messaging, and order automation for WooCommerce. Streamline payments and customer communication.

== Description ==

PesaFlow Payments for WooCommerce is a comprehensive plugin that seamlessly integrates M-Pesa payment processing and WhatsApp messaging with your WooCommerce store.

**Key Features:**

* **Order Messaging Integration** - Customers can initiate orders directly via Twilio messaging platform
* **M-Pesa Payment Gateway** - Accept payments via M-Pesa using the Daraja API
* **Message Notifications** - Receive messaging notifications for new orders
* **Cart Recovery** - Automatic cart abandonment recovery campaigns
* **Admin Dashboard** - Monitor conversations and M-Pesa transactions
* **Settings Management** - Easy configuration for Twilio and Daraja credentials

**Requirements:**

* WooCommerce 10.6 or higher
* Twilio WhatsApp Business Account
* M-Pesa Business Account (Daraja API credentials)
* PHP 7.4 or higher
* WordPress 5.0 or higher

**Uses External Services:**

This plugin integrates with:
* **Twilio**: For WhatsApp messaging (https://www.twilio.com/)
* **Meta**: For WhatsApp Business API messaging (https://www.meta.com/)
* **M-Pesa Daraja API**: For payment processing (https://developer.safaricom.co.ke/)

== External Services ==

This plugin connects to external third-party services to provide messaging and payment functionality. Please review the following information about each service:

=== Twilio (WhatsApp Messaging) ===

**Purpose:** Sends and receives WhatsApp messages, enabling order notifications and customer communication.

**What Data is Sent:**
- Customer phone numbers (in E.164 format, e.g., +254XXXXXXXXX)
- Order information (order number, total, items)
- Business messages and notifications
- Customer incoming messages

**When Data is Sent:**
- When an order is placed (if order notifications are enabled)
- When customers message the business via WhatsApp
- When cart recovery messages are triggered
- When manual messages are sent from admin panel

**Service Details:**
- Service Provider: Twilio Inc.
- Terms of Service: https://www.twilio.com/legal/terms
- Privacy Policy: https://www.twilio.com/legal/privacy

=== Meta (WhatsApp Business API) ===

**Purpose:** Alternative WhatsApp messaging provider for sending and receiving WhatsApp messages (optional - requires explicit selection in settings).

**What Data is Sent:**
- Customer phone numbers (in E.164 format)
- Order information (order number, total, items)
- Business messages and notifications
- Customer incoming messages

**When Data is Sent:**
- When an order is placed (if order notifications enabled)
- When customers message the business via WhatsApp
- When cart recovery messages are triggered
- When manual messages are sent from admin panel

**Service Details:**
- Service Provider: Meta Platforms, Inc.
- Terms of Service: https://www.facebook.com/legal/terms
- Privacy Policy: https://www.facebook.com/privacy/explanation

=== Safaricom Daraja M-Pesa API ===

**Purpose:** Processes M-Pesa payment requests and receives payment confirmation callbacks. Enables STK push (payment prompt) functionality for customers.

**What Data is Sent:**
- Customer phone numbers (in E.164 format for Kenya: +254XXXXXXXXX)
- Payment amounts (in KES - Kenyan Shillings)
- Order reference numbers
- Till/Business Short Code
- Timestamps and transaction IDs
- Callback confirmations

**When Data is Sent:**
- When an M-Pesa payment prompt is initiated for an order
- When payment confirmation callbacks are received
- When payment status is queried

**Service Details:**
- Service Provider: Safaricom PLC
- Developer Portal: https://developer.safaricom.co.ke/
- Terms of Service: https://developer.safaricom.co.ke/start
- Privacy Policy: https://www.safaricom.co.ke/about/our-company/legal-and-compliance
- API Documentation: https://developer.safaricom.co.ke/APIs

=== Webhook Configuration ===

This plugin uses webhooks to receive incoming messages and payment confirmations. The webhook endpoints are:

- **Incoming Messages:** `https://yourdomain.com/wp-json/wwcc/v1/incoming-messages` (POST)
- **M-Pesa Callback:** `https://yourdomain.com/wp-json/wwcc/v1/mpesa-callback` (POST)

These endpoints must be configured in your Twilio, Meta, and Safaricom accounts respectively to receive real-time updates.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to PesaFlow Payments settings
4. Enter your Twilio API credentials and M-Pesa Daraja API keys
5. Configure your business phone number and M-Pesa settings
6. Enable desired features (Order Creation, Cart Recovery, etc.)

== Frequently Asked Questions ==

= Do I need a WhatsApp Business Account? =
Yes, you need a Twilio WhatsApp Business Account to use WhatsApp messaging features.

= What payment methods are supported? =
Currently, the plugin supports M-Pesa payment integration for Kenyan businesses. Additional payment methods can be added.

= Can customers create orders via WhatsApp? =
Yes. Customers can click the "Order via WhatsApp" button on product pages and shop listings, which opens WhatsApp with a pre-filled message.

= How are orders created from WhatsApp messages? =
Orders are created automatically when customers send messages via WhatsApp. The admin receives notifications and can confirm the order details.

= Is cart recovery automatic? =
Yes. The plugin has built-in cart abandonment recovery that sends WhatsApp reminders to customers who abandon their carts.

= Are my credentials secure? =
Yes. All API credentials are stored securely in the WordPress database with proper sanitization and escaping.

= Does this work with all WooCommerce themes? =
Yes. The plugin integrates seamlessly with all WooCommerce themes, including theme-native button styling.

== Changelog ==

= 1.0.0 =
* Initial release
* WhatsApp order integration via Twilio
* M-Pesa payment gateway via Daraja API
* Order notifications
* Cart recovery automation
* Admin dashboard for monitoring conversations and transactions
* Comprehensive settings management

== Support ==

For issues, questions, or feature requests, please visit:
https://github.com/Varence-kiiru/woocommerce-order-messaging-kenya

== License ==

This plugin is licensed under the GPLv2 or later license.

== Credits ==

Developed by Varence Kiiru
https://github.com/Varence-kiiru
