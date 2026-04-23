# Order Messaging Kenya Plugin

🚀 **Complete order messaging and M-Pesa automation plugin specifically built for Kenya**

This plugin integrates WhatsApp messaging, M-Pesa payments, and store order workflows to create a seamless commerce experience.

## Features

✅ **Order Notifications** - Send order confirmations, payment updates, and shipping notifications via WhatsApp
✅ **"Order via WhatsApp" Button** - Add clickable WhatsApp button on product pages
✅ **Auto Order Creation** - Automatically create orders from WhatsApp messages
✅ **M-Pesa Integration** - Daraja API integration for Safaricom M-Pesa payments
✅ **STK Push** - Send payment prompts directly to customer phones
✅ **Cart Recovery** - Automated WhatsApp reminders for abandoned carts
✅ **Conversation Logs** - Track all customer conversations and transactions
✅ **Admin Dashboard** - Complete settings panel in WordPress admin

## Requirements

- WordPress 5.0+
- PHP 7.4+
- WooCommerce 3.0+
- WhatsApp Business Account (Twilio or Meta)
- M-Pesa/Daraja API Credentials

## Installation

1. **Upload plugin files** to `/wp-content/plugins/whatsapp-woocommerce/`
2. **Activate** the plugin from WordPress admin
3. **Configure** in WordPress Admin → WhatsApp Settings

## Initial Setup Guide

### Step 1: WhatsApp Configuration

Choose between **Twilio** or **Meta** (recommended for scalability):

#### Option A: Twilio Setup
```
1. Sign up at https://www.twilio.com/
2. Create WhatsApp messaging service
3. Copy:
   - Account SID
   - Auth Token
   - Twilio WhatsApp Phone Number
4. Add to plugin settings
```

#### Option B: Meta (WhatsApp Business API)
```
1. Create Meta Business Account
2. Apply for WhatsApp Business API
3. Get:
   - Phone Number ID
   - Access Token (30-day validity)
4. Add to plugin settings
```

### Step 2: M-Pesa/Daraja Configuration

```
1. Visit https://developer.safaricom.co.ke/
2. Create app in Daraja
3. Copy:
   - Consumer Key
   - Consumer Secret
4. Get from M-Pesa Portal:
   - Business Till Number (600XXX)
   - Online Passkey
5. Add all to plugin settings
6. Test connection from admin panel
```

### Step 3: Enable Features

In plugin settings:
- ✅ Enable Order Notifications
- ✅ Enable Order Creation from WhatsApp
- ✅ Enable Cart Recovery
- ✅ Enable M-Pesa STK Push

## API Endpoints

The plugin registers REST API endpoints for webhook handling:

```
POST /wp-json/wwcc/v1/mpesa-callback
POST /wp-json/wwcc/v1/incoming-messages
GET  /wp-json/wwcc/v1/incoming-messages (webhook verification)
```

### Configure Webhooks

**For Daraja (M-Pesa Confirmation):**
```
Callback URL: https://yoursite.com/wp-json/wwcc/v1/mpesa-callback
```

**For WhatsApp Messages (Meta/Twilio):**
```
Webhook URL: https://yoursite.com/wp-json/wwcc/v1/incoming-messages
Verify Token: (Set in plugin settings)
```

## Database Tables Created

| Table | Purpose |
|-------|---------|
| `wwcc_whatsapp_logs` | WhatsApp message history |
| `wwcc_mpesa_logs` | M-Pesa transaction logs |
| `wwcc_conversations` | WhatsApp conversations |
| `wwcc_webhook_logs` | Webhook debug logs |
| `wwcc_carts` | Abandoned cart tracking |

## Usage Guide

### For Store Owners

1. **Product Pages** - "Order via WhatsApp" button appears automatically
2. **Order Notifications** - Customers receive WhatsApp updates automatically
3. **Manual Charging** - Use "Send M-Pesa Payment Prompt" in order admin
4. **View Logs** - Check WhatsApp Settings → Message Logs & Conversations

### For Customers

1. **Click "Order via WhatsApp"** on product page
2. **Message store** to create order
3. **Receive order confirmation** via WhatsApp
4. **Pay via M-Pesa** when prompted
5. **Get shipping updates** via WhatsApp

## Order Flow

```
Customer clicks "Order via WhatsApp"
↓
Opens WhatsApp with pre-filled message
↓
Sends order request
↓
Plugin creates order automatically
↓
Order confirmation sent via WhatsApp
↓
Admin sends M-Pesa STK prompt
↓ 
Customer completes payment
↓
Order marked as paid
↓
Order progresses through WooCommerce workflow
```

## Settings Reference

### WhatsApp Settings

| Setting | Options | Default |
|---------|---------|---------|
| Provider | twilio, meta | twilio |
| Business Phone | Phone with country code | - |
| Webhook Verify Token | Any string | test_token_12345 |

### M-Pesa Settings

| Setting | Required | Example |
|---------|----------|---------|
| Consumer Key | Yes | From Daraja |
| Consumer Secret | Yes | From Daraja |
| Till Number | Yes | 600123 |
| Passkey | Yes | From M-Pesa Portal |

### Feature Toggles

| Feature | Default | Purpose |
|---------|---------|---------|
| Enable Notifications | ✅ | Send WhatsApp messages |
| Enable Order Creation | ✅ | Auto-create from WhatsApp |
| Enable Cart Recovery | ✅ | Abandoned cart reminders |
| Enable M-Pesa STK | ✅ | Payment prompts |
| Enable Debug | ❌ | Log debug info |

## Testing

### Test M-Pesa Connection
From admin: WhatsApp Settings → Test Connection (M-Pesa section)

### Test WhatsApp API
From admin: WhatsApp Settings → Test Connection (WhatsApp section)

### Test Message Sending
Send test order via WhatsApp to verify setup

## Troubleshooting

### Messages Not Sending

1. **Check API credentials** - Verify all keys are correct
2. **Check phone numbers** - Ensure format is +2547XXXXXXXX
3. **Enable debug mode** - WhatsApp Settings → Enable Debug Mode
4. **Check logs** - WhatsApp Settings → Message Logs

### M-Pesa Issues

1. **STK not appearing** - Check till number and passkey
2. **Check Daraja status** - Verify IP whitelisting
3. **Check callback URL** - Ensure webhook is configured

### Order Creation Failures

1. **Enable order creation** - Check feature toggle
2. **Check product names** - Ensure products exist in WooCommerce
3. **Check phone format** - Must be valid Kenya number

### Database Errors

1. **Ensure tables created** - Check database via phpMyAdmin
2. **Check logs** - Check error logs in `/wp-content/debug.log`
3. **Reactivate plugin** - Forces table recreation

## File Structure

```
whatsapp-woocommerce/
├── whatsapp-woocommerce.php         # Main plugin file
├── includes/
│   ├── class-whatsapp-woocommerce.php  # Main class
│   ├── class-whatsapp-api.php          # WhatsApp API handler
│   ├── class-mpesa-handler.php         # M-Pesa/Daraja handler
│   ├── class-order-sync.php            # Order creation from WhatsApp
│   ├── class-cart-recovery.php         # Cart abandonment handling
│   ├── class-webhook-handler.php       # Webhook processor
│   ├── class-settings.php              # Settings management
│   ├── class-db.php                    # Database setup
│   └── class-frontend.php              # Frontend assets & buttons
├── admin/
│   ├── class-admin.php                 # Admin interface
│   └── views/
│       ├── settings.php                # Settings page template
│       ├── logs.php                    # Message logs page
│       └── conversations.php           # Conversations page
├── assets/
│   ├── css/
│   │   ├── frontend.css               # Frontend styles
│   │   └── admin.css                  # Admin styles
│   └── js/
│       ├── frontend.js                # Frontend interactions
│       └── admin.js                   # Admin interactions
├── languages/                          # Translation files
└── README.md                           # This file
```

## Monetization Options

### 1. SaaS Model (Recommended)
- Monthly subscription: KES 1,000 - 3,000
- Includes hosted infrastructure
- Payment processing handled
- Customer support included

### 2. License Model
- One-time purchase: $29 - $79
- Sell on CodeCanyon or Gumroad
- Self-hosted, customers install locally

### 3. Hybrid Model
- Free plugin + paid API usage
- Plugin is free
- Charge per SMS/WhatsApp message sent

## Support & Contributions

For issues and feature requests, create issues on the GitHub repository.

## License

GPL-2.0-or-later

## Credits

Built for Kenyan e-commerce businesses. Integrates:
- Safaricom M-Pesa via Daraja API
- WhatsApp via Twilio & Meta APIs
- WooCommerce

---

**Made with ❤️ for Kenya's thriving e-commerce ecosystem**
