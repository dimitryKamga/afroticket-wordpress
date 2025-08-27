# AfroTicket SMS System Documentation

## 🎯 Overview

The AfroTicket SMS system automatically sends ticket download links via SMS when customers book events. This provides a secure, convenient way for customers to receive their tickets directly on their mobile devices.

## 📱 Key Features

### ✅ Secure SMS Delivery
- **Time-limited download links** (72 hours default, configurable)
- **SHA256 hash protection** against link enumeration attacks
- **Customer email verification** for hash generation
- **Rate limiting** (5 downloads/IP/hour) to prevent abuse
- **Automatic cleanup** of expired download hashes

### ✅ Phone Input Enhancement
- **10-digit validation** with user-friendly placeholder (4388319155)
- **Mobile-optimized input** with `type="tel"`
- **Automatic +1 prefix** added for SMS delivery
- **Pattern validation** ensures correct format

### ✅ Admin Management
- **WordPress admin panel** at `Settings → SMS Ticket Security`
- **Secure credential storage** in WordPress options (not hardcoded)
- **Download attempt logging** for security monitoring
- **Configurable settings** for expiration and rate limiting

## 🏗️ System Architecture

### SMS Flow
```
Customer Books Ticket → Email Sent → SMS Hook Triggered → 
Secure Hash Generated → SMS Sent with Download Link → 
Customer Clicks Link → PDF Downloaded
```

### Security Layers
1. **Hash Generation**: `booking_id:ticket_id:customer_email:expiration_timestamp`
2. **WordPress Salt**: Uses WordPress salt for HMAC-SHA256 signing
3. **Time Expiration**: Links automatically expire (default 72 hours)
4. **Rate Limiting**: Max 5 downloads per IP per hour
5. **Customer Verification**: Email must match booking

## 🔧 Technical Implementation

### Core Files Modified
- **`wp-content/themes/meup-child/functions.php`**: Main SMS system
- **`wp-content/themes/meup-child/eventlist/cart/customer_input.php`**: Phone input enhancement

### Key Functions

#### SMS Sending
```php
function send_ticket_sms($tickets, $phoneCustomer, $facebooklink)
```
- Formats SMS message with ticket links
- Uses Twilio API for delivery
- Returns boolean success status

#### Secure Hash Generation
```php
function afroticket_generate_secure_hash($booking_id, $ticket_id, $customer_email)
```
- Creates unique hash for each ticket download
- Stores hash data in WordPress transients
- Automatically expires after configured time

#### Download Handler
```php
function afroticket_handle_secure_download()
```
- Processes `/download-ticket/{hash}` URLs
- Validates hash and checks expiration
- Serves PDF file securely

### WordPress Integration
- **Rewrite Rules**: Custom URL structure for `/download-ticket/{hash}`
- **Query Variables**: Registered `afroticket_download_hash` query var
- **Admin Menu**: SMS settings panel in WordPress admin
- **wp_mail Hook**: Triggers SMS when booking emails are sent

## 📞 Twilio Configuration

### Required Credentials
- **Account SID**: Your Twilio account identifier
- **Auth Token**: Authentication token for API access
- **Messaging Service SID**: Twilio messaging service for SMS delivery

### Setup Methods

#### Method 1: WordPress Admin (Recommended)
1. Go to `WP Admin → Settings → SMS Ticket Security`
2. Enter your Twilio credentials
3. Configure link expiration and rate limiting
4. Save settings

#### Method 2: WP-CLI
```bash
wp option update twilio_account_sid "YOUR_ACCOUNT_SID"
wp option update twilio_auth_token "YOUR_AUTH_TOKEN" 
wp option update twilio_messaging_service_sid "YOUR_MESSAGING_SID"
```

#### Method 3: Database Direct
Add to `wp_options` table:
```sql
INSERT INTO wp_options (option_name, option_value) VALUES 
('twilio_account_sid', 'YOUR_ACCOUNT_SID'),
('twilio_auth_token', 'YOUR_AUTH_TOKEN'),
('twilio_messaging_service_sid', 'YOUR_MESSAGING_SID');
```

## 🔒 Security Features

### Download Link Protection
- **Unique hashes**: Each ticket gets a unique download hash
- **Time-limited access**: Links expire after 72 hours (configurable)
- **Customer verification**: Hash includes customer email for validation
- **Rate limiting**: Maximum 5 downloads per IP address per hour
- **PDF protection**: Direct PDF access blocked via .htaccess

### Data Security
- **No hardcoded credentials**: All sensitive data in WordPress options
- **WordPress salt integration**: Uses WordPress security functions
- **Automatic cleanup**: Expired hashes automatically removed
- **Error logging**: Security events logged for monitoring

## 🎯 Customer Experience

### Booking Process
1. Customer visits AfroTicket website
2. Selects event and fills booking form
3. Enters **10-digit phone number** (e.g., 4388319155)
4. Completes booking with payment/registration

### SMS Delivery
1. **Email sent** with booking confirmation
2. **SMS sent automatically** to +1{phone} format
3. SMS contains: "🎫 Your AfroTicket is ready! Download your ticket: [secure link]"
4. Customer clicks link on mobile device
5. **PDF ticket downloads** directly to device

### SMS Message Format
```
🎫 Your AfroTicket is ready!

Download your ticket:
https://afroticket.ca/download-ticket/{secure_hash}

📱 Follow us: https://facebook.com/afroticket

🔒 Secure link expires in 72 hours
```

## 🛠️ Troubleshooting

### SMS Not Sending
1. **Check Twilio credentials** in admin panel
2. **Verify phone number format** (must be 10 digits)
3. **Check error logs** for API responses:
   ```bash
   tail -f wp-content/debug.log | grep "SMS"
   ```
4. **Test Twilio credentials** directly via Twilio console

### Download Links Not Working
1. **Flush rewrite rules**: `wp rewrite flush`
2. **Check .htaccess permissions** in wp-content/uploads/
3. **Verify hash generation** in admin logs
4. **Test URL manually** with known valid hash

### Phone Input Issues
1. **Clear browser cache** after template changes
2. **Check child theme activation** (meup-child must be active)
3. **Verify template override** in child theme directory
4. **Test with different browsers**

## 📊 Monitoring & Analytics

### Admin Panel Statistics
- View download attempts and success rates
- Monitor security events (rate limiting, invalid hashes)
- Track SMS delivery status and errors
- Review hash expiration and cleanup logs

### Log Files to Monitor
- **WordPress Debug Log**: General PHP errors and SMS status
- **Server Error Log**: Twilio API responses and cURL errors
- **Webhook Deploy Log**: Automated deployment status

### Success Metrics
- **SMS delivery rate**: Track successful Twilio API responses
- **Download completion**: Monitor PDF download attempts
- **Security events**: Watch for suspicious activity
- **Customer satisfaction**: Reduced support tickets for ticket access

## 🔄 Automated Deployment

The SMS system is integrated with automated deployment:

### GitHub Integration
- **Repository**: `dimitryKamga/afroticket-wordpress`
- **Webhook**: Automatically deploys on push to main branch
- **Dual Environment**: Updates both live and test sites simultaneously

### Deployment Process
```
Local Changes → git push → GitHub → Webhook → 
Live Site Updated + Test Site Updated
```

### Backup System
- **Automatic backups** before each deployment
- **10-backup retention** for both environments
- **Rollback capability** if issues occur

## 🚀 Future Enhancements

### Potential Improvements
1. **Multiple SMS providers** (backup for Twilio)
2. **SMS templates** (customizable message formats)
3. **Delivery confirmations** (read receipts)
4. **SMS scheduling** (send at optimal times)
5. **Multi-language support** (SMS in customer's language)
6. **Analytics dashboard** (detailed SMS metrics)
7. **A/B testing** (different SMS formats)

### Integration Opportunities
- **WhatsApp Business API** for richer messaging
- **Push notifications** for mobile app users
- **Email + SMS sequences** for better engagement
- **Social media integration** (share tickets)

## 📝 Version History

### Current Version: 2.0
- ✅ Secure hash-based download system
- ✅ WordPress options-based credential storage
- ✅ Enhanced phone input validation
- ✅ Rate limiting and security monitoring
- ✅ Automated deployment integration
- ✅ Dual environment support (live + test)

### Previous Issues Fixed
- **Broken API calls**: Fixed literal string concatenation in Twilio calls
- **Hardcoded secrets**: Moved to WordPress options for security
- **PHP warnings**: Fixed undefined array key errors
- **Hash generation**: Removed broken POST code from function
- **Template overrides**: Proper child theme implementation

## 🆘 Support & Contact

### Technical Support
1. **Check WordPress error logs** first
2. **Review SMS delivery logs** in admin panel
3. **Test individual components** separately
4. **Verify dependencies** are active and updated

### Emergency Procedures
If SMS system fails:
1. **Check Twilio service status**
2. **Verify webhook deployment succeeded**
3. **Test manual SMS sending** via admin
4. **Roll back to previous backup** if needed

---

**🤖 Generated with Claude Code**  
**📅 Last Updated**: August 27, 2025  
**🔧 Maintained by**: AfroTicket Development Team