<?php
/**
 * Configuration Example
 *
 * This file shows how to configure the WooCommerce Order Messaging Kenya plugin.
 * Copy settings from your API providers and add them to WordPress admin.
 *
 * @package Order_Messaging_For_WooCommerce_Kenya
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * IMPORTANT: Do NOT add credentials directly to this file!
 * Add them via WordPress Admin Panel instead:
 * 
 * WordPress Admin → WhatsApp Settings → [Configure]
 */

// ==========================================
// STEP 1: SET UP TWILIO (WhatsApp)
// ==========================================

/*
Details you'll need from Twilio:
- Account SID (starts with AC)
- Auth Token (secret key)
- WhatsApp Phone Number (assigned by Twilio)

Get them from: https://console.twilio.com/

Example values (DO NOT USE - get your own):
Account SID: ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
Auth Token: your_auth_token_here
Phone: +1234567890 (Your Twilio WhatsApp number)

WHERE TO ADD IN WORDPRESS:
Admin → WhatsApp Settings → WhatsApp Configuration
  → Provider: Select "Twilio"
  → Business WhatsApp Number: +254... (your business phone)
  → Twilio Account SID: ACxxxxxxxx
  → Twilio Auth Token: [Copy from console]
  → Twilio WhatsApp Number: +1234567890
*/

// ==========================================
// STEP 2: SET UP META (Alternative WhatsApp)
// ==========================================

/*
Details you'll need from Meta:
- Phone Number ID (from Business Manager)
- Access Token (from Business Manager)

Get them from:
https://www.facebook.com/business/tools/meta-business-manager

Example values:
Phone Number ID: 1234567890123
Access Token: EAAbsBExxxx... (long alphanumeric)

WHERE TO ADD IN WORDPRESS:
Admin → WhatsApp Settings → Meta Configuration
  → Phone Number ID: 1234567890123
  → Access Token: [Long token from Meta]
*/

// ==========================================
// STEP 3: SET UP M-PESa (DARAJA API)
// ==========================================

/*
Details you'll need:
- Consumer Key
- Consumer Secret
- Till Number (Business Shortcode) - 6-digit number like 600123
- Passkey (Different from till password)

Get them from:
https://developer.safaricom.co.ke/

AND get Till Number & Passkey from:
M-Pesa Portal: https://www.safaricom.co.ke/business/m-pesa-for-business

Step-by-step:
1. Visit https://developer.safaricom.co.ke/
2. Sign up for account
3. Create new App
4. Copy Consumer Key and Consumer Secret
5. Log into M-Pesa Portal (https://www.safaricom.co.ke/)
6. Get your Till Number (Business Shortcode) - looks like 600123
7. Get Online Passkey from Portal
8. Add all to WordPress

Example values (DO NOT USE THESE):
Consumer Key: h1f3hf8hfw8fhwfhwf8...
Consumer Secret: fhwf8hfw8fhw8fhwwf8...
Till Number: 600123
Passkey: MySecurePasskey123

WHERE TO ADD IN WORDPRESS:
Admin → WhatsApp Settings → M-Pesa Configuration
  → Consumer Key: [From Daraja]
  → Consumer Secret: [From Daraja]
  → Till Number: 600123
  → Online Passkey: [From M-Pesa Portal]
*/

// ==========================================
// STEP 4: WEBHOOK CONFIGURATION
// ==========================================

/*
After setting up, you need to tell Twilio & Daraja where to send callbacks.

DARAJA (M-Pesa Confirmation):
Location: https://developer.safaricom.co.ke/
Go to your app settings
Add Callback URL: https://yourdomain.com/wp-json/wwcc/v1/mpesa-callback
Replace yourdomain.com with your actual domain

TWILIO (WhatsApp Messages):
Location: https://console.twilio.com/ → Messaging → WhatsApp
Add Webhook URL: https://yourdomain.com/wp-json/wwcc/v1/incoming-messages
Check "Messages" event

META (WhatsApp Business):
Location: Meta Business Manager → WhatsApp → Configuration
Add Webhook URL: https://yourdomain.com/wp-json/wwcc/v1/incoming-messages
Verify Token: (Set in WordPress admin panel)
*/

// ==========================================
// STEP 5: FEATURE CONFIGURATION
// ==========================================

/*
After API setup, configure features in WordPress:

Admin → WhatsApp Settings → Feature Settings

Enable/Disable:
☑ Enable Order Notifications (recommended: ON)
  → Sends confirmations, payment updates, shipping
  
☑ Enable Order Creation from WhatsApp (recommended: ON)
  → Customers can order via WhatsApp
  
☑ Enable Cart Recovery (recommended: ON)
  → Auto-messages for abandoned carts
  → Set recovery hours (default: 2)
  
☑ Enable M-Pesa STK Push (recommended: ON)
  → Sends payment prompt to customer phone

☐ Enable Debug Mode (recommended: OFF)
  → Only use for troubleshooting
  → Logs extra info to wp-content/debug.log
*/

// ==========================================
// STEP 6: TEST CONFIGURATION
// ==========================================

/*
After everything is set up:

1. Test M-Pesa:
   Admin → WhatsApp Settings → M-Pesa section
   Click "Test Connection" button
   Should see: "M-Pesa API connection successful!"

2. Test WhatsApp:
   Admin → WhatsApp Settings → WhatsApp section
   Click "Test Connection" button
   Enter your phone number: +2547XXXXXXXX
   Check you receive test message

3. Create test order:
   Shop → Product page
   Click "Order via WhatsApp"
   In WhatsApp, send order message
   Check order is created in WooCommerce

4. Test M-Pesa Payment:
   Admin → Orders
   Open any order
   Click "Send M-Pesa Payment Prompt"
   Payment prompt should appear on test phone
*/

// ==========================================
// REQUIRED API CREDENTIALS SUMMARY
// ==========================================

/*
Minimum credentials needed:

FOR WHATSAPP:
Option A (Twilio):
□ Twilio Account SID
□ Twilio Auth Token
□ Twilio WhatsApp Phone Number

Option B (Meta):
□ Meta Phone Number ID
□ Meta Access Token

FOR M-PESA:
□ Daraja Consumer Key
□ Daraja Consumer Secret
□ Business Till Number
□ Online Passkey

FOR WEBHOOKS:
□ Webhook URLs configured in Daraja
□ Webhook URLs configured in Twilio/Meta
□ Webhook Verify Token (set in WordPress)

FOR YOUR STORE:
□ Your business WhatsApp number (+254...)
□ Your business phone number
*/

// ==========================================
// TROUBLESHOOTING CHECKLIST
// ==========================================

/*
If something doesn't work:

WhatsApp Messages Not Sending:
☐ Check API credentials are correct
☐ Phone number format: +2547XXXXXXXX (must include +254)
☐ Check WordPress debug log: wp-content/debug.log
☐ Enable Debug Mode and check logs again

M-Pesa STK Not Appearing:
☐ Check Till Number is correct
☐ Check Passkey is correct (different from till password!)
☐ Check your IP is whitelisted in Daraja
☐ Check order amount is greater than 0
☐ Check customer phone has M-Pesa enabled

Orders Not Creating from WhatsApp:
☐ Enable "Order Creation" feature in settings
☐ Check product names match exactly
☐ Test with exact product name in message
☐ Check customer phone for any errors

No Messages Delivered:
☐ Check API credentials again
☐ Run "Test Connection" from WordPress
☐ Check internet connection on server
☐ Check firewall not blocking API calls
☐ Try with test phone numbers from providers

Database Errors:
☐ Check MySQL error logs
☐ Reactivate plugin to recreate tables
☐ Check file permissions on wp-content
☐ Check database user has CREATE TABLE permission
*/

// ==========================================
// GETTING API CREDENTIALS - QUICK GUIDE
// ==========================================

/*
TWILIO (Recommended for beginners):
1. Go to https://www.twilio.com/try-twilio
2. Create free account (gets $15 credit)
3. Go to Console: https://console.twilio.com/
4. Create WhatsApp Messaging Service
5. Test on sandbox numbers (or upgrade account)
6. Copy: Account SID, Auth Token, Phone Number

DARAJA / M-PESA:
1. Go to https://developer.safaricom.co.ke/
2. Create developer account
3. Create new app
4. Copy Consumer Key & Secret
5. Get Till Number from M-Pesa Portal:
   https://www.safaricom.co.ke/business
6. Get Passkey from same portal
7. (Passkey ≠ Password - ask support if unsure)

META WHATSAPP:
1. Create Meta Business Account
2. Go to Business Manager: https://business.facebook.com/
3. Create WhatsApp Business Account
4. Apply for API access
5. Get Phone Number ID & Access Token
6. Wait for approval from Meta (24-48 hours)
7. Add credentials to WordPress
*/

// ==========================================
// EXAMPLE CONFIGURATION (Fake Values)
// ==========================================

$wwcc_example_config = [
    // WhatsApp (Twilio Example)
    'whatsapp_provider'       => 'twilio',
    'business_phone'          => '+254712345678', // Your store's WhatsApp
    'twilio_sid'              => 'ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
    'twilio_token'            => 'auth_token_xxxxx',
    'twilio_phone'            => '+1234567890',

    // OR WhatsApp (Meta Example)
    // 'whatsapp_provider'    => 'meta',
    // 'meta_phone_number_id' => '1234567890123456',
    // 'meta_access_token'    => 'EAAbsBExxxxxxxxxxxxxxxxxxxxxxxxxxxx',

    // M-Pesa Configuration
    'daraja_consumer_key'     => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
    'daraja_consumer_secret'  => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
    'mpesa_till_number'       => '600123',          // 6-digit shortcode
    'mpesa_passkey'           => 'YourPasskeyXXXX', // From M-Pesa Portal

    // Features (all can be toggled in WordPress)
    'enable_notifications'    => 1,  // Send WhatsApp messages
    'enable_order_creation'   => 1,  // Auto-create orders
    'enable_cart_recovery'    => 1,  // Abandoned cart reminders
    'enable_mpesa_stk'        => 1,  // Payment prompts
    'cart_recovery_hours'     => 2,  // Wait 2 hours before reminder
    'enable_debug'            => 0,  // Don't log debug info (set to 1 to debug)

    // Webhook
    'webhook_verify_token'    => 'test_token_12345',
];

/**
 * DO NOT ADD CREDENTIALS TO THIS FILE!
 * Instead:
 * 1. Activate the plugin
 * 2. Go to WordPress Admin
 * 3. Navigate to: WhatsApp Settings
 * 4. Paste the credentials there
 * 5. Click "Save Settings"
 * 6. Test connections
 *
 * This auto-encrypts credentials in the database.
 */

// ==========================================
// SUPPORT & NEXT STEPS
// ==========================================

/*
Documentation Files:
- README.md - Full feature documentation
- QUICK_START.md - 15-minute setup guide
- DATABASE_SCHEMA.md - Database reference
- DEVELOPER_GUIDE.md - For developers
- BUILD_SUMMARY.md - Complete project overview

If you need help:
1. Read QUICK_START.md
2. Check troubleshooting section above
3. Enable debug mode and check wp-content/debug.log
4. Test each API separately
5. Check that webhooks are configured

Good luck! 🚀
*/
