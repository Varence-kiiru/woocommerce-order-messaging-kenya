=== WooCommerce Order Messaging Kenya ===
Contributors: Varence Kiiru
Tags: woocommerce, e-commerce, messaging, mpesa, kenya
Requires at least: 5.0
Requires PHP: 7.4
Tested up to: 6.9
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Complete order messaging and M-Pesa payment automation for WooCommerce Kenya (via Twilio, with order notifications, and cart recovery).

== Description ==

WooCommerce Order Messaging Kenya is a comprehensive plugin that seamlessly integrates order messaging (via Twilio) and M-Pesa payment processing with your WooCommerce store.

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
* **M-Pesa Daraja API**: For payment processing (https://developer.safaricom.co.ke/)

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to WhatsApp WooCommerce settings
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
https://github.com/Varence-kiiru/whatsapp-woocommerce-kenya

== License ==

This plugin is licensed under the GPLv2 or later license.

== Credits ==

Developed by Varence Kiiru
https://github.com/Varence-kiiru
