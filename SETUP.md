# AfroTicket WordPress Setup Guide

## 🚀 Quick Setup

### 1. Twilio SMS Configuration

After deploying this code, you need to configure your Twilio credentials in WordPress:

**Method 1: WordPress Admin (Recommended)**
1. Go to `WP Admin → Settings → SMS Ticket Security`
2. Enter your Twilio credentials:
   - **Account SID**: Your Twilio Account SID
   - **Auth Token**: Your Twilio Auth Token  
   - **Messaging Service SID**: Your Twilio Messaging Service SID

**Method 2: WP-CLI (Advanced)**
```bash
wp option update twilio_account_sid "YOUR_ACCOUNT_SID"
wp option update twilio_auth_token "YOUR_AUTH_TOKEN" 
wp option update twilio_messaging_service_sid "YOUR_MESSAGING_SID"
```

**Method 3: Database Direct**
Add to `wp_options` table:
- `option_name`: `twilio_account_sid`, `option_value`: Your Account SID
- `option_name`: `twilio_auth_token`, `option_value`: Your Auth Token
- `option_name`: `twilio_messaging_service_sid`, `option_value`: Your Messaging SID

### 2. Security Configuration

**Enable Secure Downloads**
1. Ensure `.htaccess` in `/wp-content/uploads/` contains:
```apache
<Files "event__ticket*.pdf">
    Order deny,allow
    Deny from all
</Files>
```

**Flush Rewrite Rules**
```bash
wp rewrite flush
```

### 3. Test SMS System

**Send Test Email to Trigger SMS:**
```bash
wp eval "wp_mail('customer@email.com', 'Booking Confirmation #11044', 'Test message');"
```

## 📋 Features Included

### ✅ Secure SMS Delivery
- Time-limited download links (72 hours default)
- SHA256 hash protection against enumeration
- Customer email verification
- Rate limiting (5 downloads/IP/hour)

### ✅ Phone Input Enhancement
- 10-digit validation with placeholder
- Mobile-optimized `type="tel"`
- Automatic +1 prefix for SMS

### ✅ Admin Management
- SMS settings at `Settings → SMS Ticket Security`
- Download attempt logging
- Configurable link expiration
- Security monitoring

## 🔧 Troubleshooting

### SMS Not Sending
1. Check Twilio credentials in admin panel
2. Verify phone number format (+1XXXXXXXXXX)
3. Check error logs for API responses
4. Test Twilio credentials directly

### Download Links Not Working
1. Flush rewrite rules: `wp rewrite flush`
2. Check `.htaccess` permissions
3. Verify hash generation in admin logs

### Phone Input Issues
1. Clear browser cache
2. Check EventList template override is active
3. Verify child theme is activated

## 🔒 Security Notes

- **Never commit credentials** to version control
- **Use WordPress options** for sensitive data
- **Regular security updates** recommended
- **Monitor download logs** for suspicious activity

## 📞 Support

For technical support with this implementation:
1. Check WordPress error logs
2. Review SMS delivery logs in admin
3. Test individual components separately
4. Verify all dependencies are active