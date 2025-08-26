# AfroTicket WordPress Customizations

This repository contains the custom WordPress theme and modifications for AfroTicket.ca.

## 🎫 What's Included

### Custom Child Theme (`wp-content/themes/meup-child/`)
- **Custom Functions**: SMS integration, secure ticket downloads, PDF enhancements
- **Template Overrides**: EventList plugin customizations
- **Phone Input Enhancement**: 10-digit formatting with +1 auto-prefix
- **PDF Templates**: Clean table-based ticket templates
- **Custom Fonts**: DM Sans font family for modern typography

## 🔐 Security Features

### Secure SMS Ticket Delivery
- **Twilio Integration**: Automated SMS with ticket download links  
- **Secure URLs**: Time-limited download links (72 hours configurable)
- **Hash Protection**: SHA256 hashes prevent ticket enumeration attacks
- **Rate Limiting**: Prevents brute force download attempts
- **Admin Panel**: Settings at `WP Admin → Settings → SMS Ticket Security`

### Enhanced Security
- **Direct PDF Blocking**: Old predictable URLs return 403 Forbidden
- **Customer Verification**: Hash tied to customer email for validation
- **Download Logging**: Complete audit trail of all access attempts

## 📱 SMS System

### Message Format
```
🎫 Your AfroTicket is ready!

Download your ticket:
https://afroticket.ca/download-ticket/[secure-hash]

📱 Follow us: https://facebook.com/afroticket

🔒 Secure link expires in 72 hours
```

### Phone Input Enhancement
- **User-Friendly**: Placeholder shows `4388319155` format
- **Mobile Optimized**: `type="tel"` for numeric keyboards
- **Validation**: 10-digit pattern with length limits
- **Auto-Format**: Converts `4388319155` → `+14388319155` for SMS

## 🎨 PDF Templates

### Clean Table-Based Design
- **Modern Look**: Professional invoice-style layout
- **Brand Consistent**: AfroTicket logo and colors
- **Multiple Modes**: Clean (default), Compact, Enhanced
- **Mobile Compatible**: Responsive design for mPDF

## 🚀 Deployment

### What's Versioned
- ✅ Child theme customizations
- ✅ Template overrides  
- ✅ Custom functions and SMS system
- ✅ Configuration files
- ✅ Documentation

### What's Excluded
- ❌ WordPress core files
- ❌ Third-party plugins
- ❌ Upload directories
- ❌ Cache files
- ❌ Configuration secrets (`wp-config.php`)

## 🛠️ Key Features

### 1. Secure Ticket Downloads
```php
// Generate secure hash for ticket download
$hash = afroticket_generate_secure_hash($booking_id, $ticket_id, $customer_email);
$secure_url = get_site_url() . "/download-ticket/" . $hash;
```

### 2. SMS Integration  
```php
// Automatic SMS on ticket email
add_action("wp_mail", function($atts) {
    // Detect booking emails and send SMS with secure links
});
```

### 3. Phone Formatting
```html
<input type="tel" name="ticket_receiver_phone" 
       placeholder="4388319155" maxlength="10" 
       pattern="[0-9]{10}" />
```

## ⚙️ Admin Settings

### SMS Configuration
- **Link Expiration**: 1-168 hours (default: 72)
- **Rate Limiting**: Enable/disable IP-based limits
- **Download Logs**: Monitor all download attempts
- **Statistics**: View recent activity

## 📞 Technical Support

### Custom Code Location
- **Main Functions**: `wp-content/themes/meup-child/functions.php`
- **Template Overrides**: `wp-content/themes/meup-child/eventlist/`
- **Admin Settings**: WordPress Admin → Settings → SMS Ticket Security

### Key Functions
- `afroticket_generate_secure_hash()` - Creates time-limited download URLs
- `afroticket_handle_secure_download()` - Processes secure downloads
- `send_ticket_sms()` - Sends SMS notifications via Twilio
- `afroticket_phone_formatting_script()` - Client-side phone formatting

## 🔗 GitHub Integration

This repository uses automated deployment via GitHub webhooks for seamless updates to the live site.

---

**🚀 Built for AfroTicket.ca - Secure, Professional, User-Friendly**