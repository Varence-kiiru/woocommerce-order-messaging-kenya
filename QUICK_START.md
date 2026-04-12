# Quick Start Guide

Get the WhatsApp WooCommerce Kenya plugin up and running in 15 minutes.

## Prerequisites

✅ WordPress site with WooCommerce installed
✅ WhatsApp Business Account (Twilio or Meta)
✅ Safaricom M-Pesa account
✅ Access to WordPress admin panel

---

## 5-Minute Setup

### 1. Install Plugin (2 min)

```bash
# Upload to WordPress
1. Go to WordPress Admin → Plugins → Add New
2. Upload whatsapp-woocommerce folder
3. Click Activate
```

### 2. Get API Credentials (2 min)

**For Twilio (recommended for quick start):**
- Sign up at https://twilio.com (Free trial includes $15 credit)
- Create WhatsApp messaging service
- Copy: SID, Auth Token, Phone Number

**For M-Pesa:**
- Visit https://developer.safaricom.co.ke
- Create Daraja app
- Copy: Consumer Key, Consumer Secret
- Get from M-Pesa Portal: Till Number, Passkey

### 3. Configure Plugin (1 min)

```
WordPress Admin → WhatsApp Settings
├── Paste Twilio credentials
├── Paste M-Pesa credentials
└── Click Save
```

---

## Feature-by-Feature Activation

### Enable WhatsApp Notifications (2 min)

1. **Admin:** WhatsApp Settings → Features
2. **Check:** "Enable Order Notifications"
3. **Test:** Create test order, customer should receive WhatsApp

### Enable "Order via WhatsApp" Button (1 min)

1. **Admin:** WhatsApp Settings → Features
2. **Check:** "Enable Order Creation from WhatsApp"
3. **Shop:** Visit product page, see WhatsApp button
4. **Test:** Click button, send test message

### Enable M-Pesa Payments (3 min)

1. **Admin:** WhatsApp Settings → M-Pesa Configuration
2. **Enter:** Till Number, Passkey
3. **Test:** Click "Test Connection"
4. **Admin Orders:** See "Send M-Pesa Payment Prompt" button

### Enable Cart Recovery (1 min)

1. **Admin:** WhatsApp Settings → Features
2. **Check:** "Enable Cart Recovery"
3. **Set:** Recovery delay (default: 2 hours)
4. **Auto:** Plugin will message customers who abandon carts

---

## Testing Checklist

Before going live:

```
☐ Test WhatsApp notification
  → Create order in WooCommerce
  → Receive WhatsApp message within 1 minute
  
☐ Test "Order via WhatsApp" button
  → Click button on product page
  → Opens WhatsApp with pre-filled message
  → Message arrives in WhatsApp conversation
  
☐ Test M-Pesa integration
  → Create order
  → Click "Send M-Pesa Payment Prompt"
  → Payment STK appears on test phone
  
☐ Test cart recovery
  → Add items to cart, don't checkout
  → Wait 2 hours (or test manually)
  → Receive whatsapp reminder
  
☐ Check logs
  → Admin → WhatsApp Settings → Message Logs
  → Verify messages are being logged
```

---

## Common Issues & Quick Fixes

### Problem: Messages not sending

**Solution 1:** Check phone number format
```
Must be: +2547XXXXXXXX (not 0712345678)
```

**Solution 2:** Verify API credentials
```
Admin → WhatsApp Settings → Test Connection
```

**Solution 3:** Check customer phone in order
```
Edit order → Billing → Phone must have country code
```

### Problem: STK Push not appearing

**Solution 1:** Check till and passkey
```
Admin → WhatsApp Settings → M-Pesa config
Re-enter till number and passkey exactly
```

**Solution 2:** Verify IP is whitelisted
```
Daraja dashboard → IP whitelist settings
Add your server IP: Check phpinfo()
```

**Solution 3:** Check Daraja app status
```
Daraja portal → Check app is approved
```

### Problem: Orders not creating from WhatsApp

**Solution 1:** Enable feature
```
Admin → WhatsApp Settings → Enable Order Creation
```

**Solution 2:** Product naming
```
Message must contain exact product name
Example: "Nike Shoes 2" creates order if product named "Nike Shoes"
```

---

## File Locations

```
WordPress Root
└── wp-content
    └── plugins
        └── whatsapp-woocommerce
            ├── whatsapp-woocommerce.php    ← Main file
            ├── README.md                   ← Full docs
            ├── DATABASE_SCHEMA.md          ← Database info
            ├── includes/                   ← Plugin code
            ├── admin/                      ← Admin panel
            └── assets/                     ← CSS & JS
```

---

## Next Steps

### Phase 1 (This Week)
✅ Install & configure
✅ Test all features
✅ Train team

### Phase 2 (Next Week)
✅ Launch to customers
✅ Monitor logs daily
✅ Gather feedback

### Phase 3 (Month 1)
✅ Optimize messaging
✅ A/B test recovery messages
✅ Track conversion metrics

---

## Monetization Quick Start

### Setup SaaS (Recommended)

```
1. Keep plugin on your server
2. Charge customers: KES 1,000-3,000/month
3. You handle API costs
4. You provide support
5. Massive profit margins!
```

### Pricing Tiers

```
Starter: KES 1,000/month
├─ Up to 100 WhatsApp messages/month
├─ Basic analytics
└─ Email support

Pro: KES 2,500/month
├─ Unlimited WhatsApp messages
├─ M-Pesa integration
├─ Cart recovery
└─ Priority support

Enterprise: KES 5,000/month
├─ Everything above
├─ Custom integrations
├─ Dedicated support
└─ SLA guarantee
```

### Target Market

- Small online shops (Jumia, Shopify sellers)
- Traditional retail going online
- Service providers needing booking system
- WhatsApp status sellers upgrading

---

## Support Resources

If you get stuck:

1. **Check logs:** Admin → WhatsApp Settings → Logs
2. **Read docs:** README.md & DATABASE_SCHEMA.md
3. **Enable debug:** Settings → Enable Debug Mode
4. **Check wp-content/debug.log** for errors

---

## License & Sales

```
GPL-2.0
✓ Sell as SaaS
✓ Charge monthly
✓ Modify as needed
✓ Keep source on server
```

---

**You're ready to launch!** 🚀

Questions? Check README.md for detailed documentation.
