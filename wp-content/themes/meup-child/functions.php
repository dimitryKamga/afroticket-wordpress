<?php
/**
 * Setup meup Child Theme's textdomain.
 *
 * Declare textdomain for this child theme.
 * Translations can be filed in the /languages/ directory.
 */
function meup_child_theme_setup() {
	load_child_theme_textdomain( 'meup-child', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'meup_child_theme_setup' );


// Add Code is here.
function quadlayers_remove_checkout_fields( $fields ) {

unset($fields['billing']['billing_state']);
unset($fields['billing']['billing_country']);
unset($fields['billing']['billing_city']);
unset($fields['billing']['billing_postcode']);

return $fields; 

}
add_filter( 'woocommerce_checkout_fields' , 'quadlayers_remove_checkout_fields' ); 


// Add Parent Style
add_action( 'wp_enqueue_scripts', 'meup_child_scripts', 100 );
function meup_child_scripts() {
    wp_enqueue_style( 'meup-parent-style', get_template_directory_uri(). '/style.css' );
}


add_filter( 'register_taxonomy_el_1', function ($params){ return array( 'slug' => 'elprice', 'name' => esc_html__( 'Price', 'meup-child' ) ); } );
add_filter( 'register_taxonomy_el_2', function ($params){ return array( 'slug' => 'eljob', 'name' => esc_html__( 'Job', 'meup-child' ) ); } );

add_filter( 'register_taxonomy_el_3', function ($params){ return array( 'slug' => 'taxonomy_default3', 'name' => esc_html__( 'Time', 'meup-child' ) ); } );
add_filter( 'register_taxonomy_el_4', function ($params){ return array( 'slug' => 'taxonomy_default4', 'name' => esc_html__( 'Space', 'meup-child' ) ); } );

// Remove jQuery Migrate log
add_action( 'wp_default_scripts', 'remove_jquery_migrate_console_log' );
function remove_jquery_migrate_console_log( $scripts ) {
    if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
        $script = $scripts->registered['jquery'];
        if ( $script->deps ) {
            $script->deps = array_diff( $script->deps, array( 'jquery-migrate' ) );
        }
    }
}

/**
 * PDF LIBRARY CONFIGURATION
 * Simple code snippet to control PDF generation
 */

// Default PDF library setting
if ( ! get_option( 'meup_pdf_library' ) ) {
    update_option( 'meup_pdf_library', 'mpdf' ); // Default to mPDF
}

/**
 * Simple code snippet to switch PDF library mode
 * Usage: meup_set_pdf_mode('enhanced'); or meup_set_pdf_mode('default');
 */
function meup_set_pdf_mode( $mode = 'default' ) {
    if ( in_array( $mode, array( 'clean', 'compact', 'enhanced' ) ) ) {
        update_option( 'meup_pdf_mode', $mode );
        return true;
    }
    return false;
}

/**
 * Get current PDF mode
 */
function meup_get_pdf_mode() {
    return get_option( 'meup_pdf_mode', 'enhanced' );
}

/**
 * Override PDF template to use child theme template
 */
function meup_child_override_pdf_template_path( $template_file, $template_name, $args, $template_path, $default_path ) {
    if ( $template_name === 'pdf/template.php' ) {
        $child_template = get_stylesheet_directory() . '/eventlist/pdf/template.php';
        if ( file_exists( $child_template ) ) {
            return $child_template;
        }
    }
    return $template_file;
}
add_filter( 'el_get_template', 'meup_child_override_pdf_template_path', 10, 5 );

/**
 * Add event featured image data before PDF template loads
 */
function meup_child_add_featured_image_before_pdf( $template_name, $template_path, $template_file, $args ) {
    if ( $template_name !== 'pdf/template.php' || ! isset( $args['ticket']['ticket_id'] ) ) {
        return;
    }
    
    $ticket_id = $args['ticket']['ticket_id'];
    
    // Get event ID
    $event_id = get_post_meta( $ticket_id, OVA_METABOX_EVENT . 'event_id', true );
    if ( ! $event_id ) {
        $event_id = get_post_meta( $ticket_id, OVA_METABOX_EVENT . 'id_event', true );
    }
    if ( ! $event_id ) {
        $booking_id = get_post_meta( $ticket_id, OVA_METABOX_EVENT . 'booking_id', true );
        if ( $booking_id ) {
            $event_id = get_post_meta( $booking_id, OVA_METABOX_EVENT . 'id_event', true );
        }
    }
    
    // Store featured image in global for template
    if ( $event_id && has_post_thumbnail( $event_id ) ) {
        $featured_image_url = get_the_post_thumbnail_url( $event_id, 'large' );
        if ( $featured_image_url ) {
            $GLOBALS['meup_event_featured_image'] = $featured_image_url;
        }
    }
    
    // Store PDF mode for template
    $GLOBALS['meup_pdf_mode'] = meup_get_pdf_mode();
}
add_action( 'el_before_template', 'meup_child_add_featured_image_before_pdf', 10, 4 );

/**
 * Enhanced mPDF configuration based on selected mode
 */
function meup_child_enhance_mpdf_config( $config ) {
    $upload_dir = wp_upload_dir();
    $pdf_mode = meup_get_pdf_mode();
    
    // Base configuration
    $config['mode'] = 'utf-8';
    $config['format'] = 'A4';
    $config['tempDir'] = $upload_dir['basedir'];
    $config['default_font'] = 'DejaVuSans';
    
    // Mode-specific configurations
    switch ( $pdf_mode ) {
        case 'compact':
            // Compact mode - smaller margins and fonts
            $config['margin_left'] = 10;
            $config['margin_right'] = 10;
            $config['margin_top'] = 10;
            $config['margin_bottom'] = 10;
            $config['default_font_size'] = 10;
            break;
            
        default: // clean mode
            // Clean mode - balanced, default settings
            $config['margin_left'] = 15;
            $config['margin_right'] = 15;
            $config['margin_top'] = 16;
            $config['margin_bottom'] = 16;
            $config['default_font_size'] = 10;
            break;
            
        case 'enhanced':
            // Enhanced mode - larger fonts and spacing
            $config['margin_left'] = 20;
            $config['margin_right'] = 20;
            $config['margin_top'] = 20;
            $config['margin_bottom'] = 20;
            $config['default_font_size'] = 12;
            break;
            
    }
    
    return $config;
}
add_filter( 'el_config_mpdf', 'meup_child_enhance_mpdf_config', 10, 1 );

/**
 * Admin notice to show current PDF mode
 */

/**
 * SIMPLE CODE SNIPPETS FOR PDF CONTROL
 * 
 * To use different PDF modes, add one of these lines to your functions.php:
 * 
 * meup_set_pdf_mode('clean');     // Clean, balanced design
 * meup_set_pdf_mode('enhanced');   // Large fonts, spacious layout  
 * meup_set_pdf_mode('compact');    // Small fonts, efficient layout
 * meup_set_pdf_mode('invoice');   // Professional invoice style
 * 
 * To check current mode:
 * $current_mode = meup_get_pdf_mode();
 */

// Set PDF mode to enhanced
add_action("init", function() { meup_set_pdf_mode("compact"); });

// SMS TEST - Direct constants for troubleshooting
define("BABYSOFT_EVENT_TWILLIO_SENDER_ID", "get_option('twilio_messaging_service_sid', 'YOUR_TWILIO_MESSAGING_SID')");
define("BABYSOFT_EVENT_TWILLIO_USER_TOKEN", "get_option('twilio_account_sid', 'YOUR_TWILIO_ACCOUNT_SID')");
define("BABYSOFT_EVENT_TWILLIO_AUTH_TOKEN", "get_option('twilio_auth_token', 'YOUR_TWILIO_AUTH_TOKEN')");
define("BABYSOFT_EVENT_TWILLIO_API_URL", "https://api.twilio.com/2010-04-01/Accounts/");
define("BABYSOFT_EVENT_TWILLIO_SENDER_NUMBER", "+14155238886");

// AfroTicket Secure SMS Ticket Download System
// Implementation in child theme for upgrade safety

// Admin Settings for Ticket Security
add_action('admin_menu', 'afroticket_security_admin_menu');
function afroticket_security_admin_menu() {
    add_options_page(
        'AfroTicket SMS Security',
        'SMS Ticket Security',
        'manage_options',
        'afroticket-sms-security',
        'afroticket_security_settings_page'
    );
}

function afroticket_security_settings_page() {
    if (isset($_POST['submit'])) {
        update_option('afroticket_link_expiration_hours', sanitize_text_field($_POST['expiration_hours']));
        update_option("twilio_account_sid", sanitize_text_field($_POST["twilio_account_sid"]));
        update_option("twilio_auth_token", sanitize_text_field($_POST["twilio_auth_token"]));
        update_option("twilio_messaging_service_sid", sanitize_text_field($_POST["twilio_messaging_service_sid"]));
        update_option('afroticket_rate_limiting', isset($_POST['rate_limiting']) ? 1 : 0);
        echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
    }
    
    // Get current option values for form display
    $expiration_hours = get_option('afroticket_link_expiration_hours', 72);
    $twilio_account_sid = get_option('twilio_account_sid', '');
    $twilio_auth_token = get_option('twilio_auth_token', '');
    $twilio_messaging_service_sid = get_option('twilio_messaging_service_sid', '');
    $rate_limiting = get_option('afroticket_rate_limiting', 1);
    ?>
    <div class="wrap">
        <h1>AfroTicket SMS Ticket Security</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row">Twilio Account SID</th>
                    <td>
                        <input type="text" name="twilio_account_sid" value="<?php echo esc_attr($twilio_account_sid); ?>" 
                               class="regular-text" />
                        <p class="description">Your Twilio Account SID for SMS delivery</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Twilio Auth Token</th>
                    <td>
                        <input type="password" name="twilio_auth_token" value="<?php echo esc_attr($twilio_auth_token); ?>" 
                               class="regular-text" />
                        <p class="description">Your Twilio Auth Token</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Twilio Messaging Service SID</th>
                    <td>
                        <input type="text" name="twilio_messaging_service_sid" value="<?php echo esc_attr($twilio_messaging_service_sid); ?>" 
                               class="regular-text" />
                        <p class="description">Your Twilio Messaging Service SID</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Link Expiration (Hours)</th>
                    <td>
                        <input type="number" name="expiration_hours" value="<?php echo esc_attr($expiration_hours); ?>" 
                               min="1" max="168" />
                        <p class="description">How long secure download links remain valid (1-168 hours, default: 72)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Rate Limiting</th>
                    <td>
                        <label>
                            <input type="checkbox" name="rate_limiting" value="1" <?php checked($rate_limiting, 1); ?> />
                            Enable rate limiting (max 5 downloads per IP per hour)
                        </label>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        
        <h2>Download Statistics</h2>
        <?php afroticket_display_download_stats(); ?>
    </div>
    <?php
}

function afroticket_display_download_stats() {
    global $wpdb;
    $logs = get_option('afroticket_download_logs', []);
    $recent_logs = array_slice(array_reverse($logs), 0, 20);
    
    if (empty($recent_logs)) {
        echo '<p>No download attempts recorded yet.</p>';
        return;
    }
    
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Date</th><th>IP Address</th><th>Status</th><th>Hash</th></tr></thead><tbody>';
    
    foreach ($recent_logs as $log) {
        $status_color = $log['success'] ? 'green' : 'red';
        echo '<tr>';
        echo '<td>' . esc_html($log['timestamp']) . '</td>';
        echo '<td>' . esc_html($log['ip']) . '</td>';
        echo '<td style="color: ' . $status_color . '">' . ($log['success'] ? 'Success' : 'Failed') . '</td>';
        echo '<td>' . esc_html(substr($log['hash'], 0, 12)) . '...</td>';
        echo '</tr>';
    }
    
    echo '</tbody></table>';
}

// Secure Hash Generation System
function afroticket_generate_secure_hash($booking_id, $ticket_id, $customer_email) {
    $timestamp = time();
    $expiration_hours = get_option('afroticket_link_expiration_hours', 72);
        update_option("twilio_account_sid", sanitize_text_field($_POST["twilio_account_sid"]));
        update_option("twilio_auth_token", sanitize_text_field($_POST["twilio_auth_token"]));
        update_option("twilio_messaging_service_sid", sanitize_text_field($_POST["twilio_messaging_service_sid"]));
    $expiration_timestamp = $timestamp + ($expiration_hours * 3600);
    
    // Create unique hash using booking, ticket, customer, timestamp and WordPress salt
    $hash_data = $booking_id . ':' . $ticket_id . ':' . $customer_email . ':' . $expiration_timestamp;
    $hash = hash_hmac('sha256', $hash_data, wp_salt());
    
    // Store hash data in transient (auto-expires)
    $hash_info = [
        'booking_id' => $booking_id,
        'ticket_id' => $ticket_id,
        'customer_email' => $customer_email,
        'created' => $timestamp,
        'expires' => $expiration_timestamp,
        'used' => false
    ];
    
    set_transient('afroticket_hash_' . $hash, $hash_info, $expiration_hours * 3600);
    
    return $hash;
}

function afroticket_verify_secure_hash($hash) {
    $hash_info = get_transient('afroticket_hash_' . $hash);
    
    if (!$hash_info) {
        afroticket_log_download_attempt($hash, false, 'Hash not found or expired');
        return false;
    }
    
    if (time() > $hash_info['expires']) {
        delete_transient('afroticket_hash_' . $hash);
        afroticket_log_download_attempt($hash, false, 'Hash expired');
        return false;
    }
    
    return $hash_info;
}

function afroticket_rate_limit_check($ip) {
    if (!get_option('afroticket_rate_limiting', 1)) {
        return true; // Rate limiting disabled
    }
    
    $rate_limit_key = 'afroticket_rate_' . md5($ip);
    $attempts = get_transient($rate_limit_key);
    
    if ($attempts === false) {
        set_transient($rate_limit_key, 1, 3600); // 1 hour
        return true;
    }
    
    if ($attempts >= 5) {
        afroticket_log_download_attempt('', false, 'Rate limit exceeded for IP: ' . $ip);
        return false;
    }
    
    set_transient($rate_limit_key, $attempts + 1, 3600);
    return true;
}

function afroticket_log_download_attempt($hash, $success, $note = '') {
    $logs = get_option('afroticket_download_logs', []);
    
    $log_entry = [
        'timestamp' => current_time('mysql'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'hash' => substr($hash, 0, 16),
        'success' => $success,
        'note' => $note
    ];
    
    $logs[] = $log_entry;
    
    // Keep only last 100 entries
    if (count($logs) > 100) {
        $logs = array_slice($logs, -100);
    }
    
    update_option('afroticket_download_logs', $logs);
}

// Custom rewrite rule for secure downloads
add_action('init', 'afroticket_add_download_rewrite_rules');
function afroticket_add_download_rewrite_rules() {
    add_rewrite_rule(
        '^download-ticket/([a-f0-9]+)/?$',
        'index.php?afroticket_download_hash=$matches[1]',
        'top'
    );
}

add_filter('query_vars', 'afroticket_add_query_vars');
function afroticket_add_query_vars($vars) {
    $vars[] = 'afroticket_download_hash';
    return $vars;
}

// Handle secure download requests
add_action('template_redirect', 'afroticket_handle_secure_download');
function afroticket_handle_secure_download() {
    $hash = get_query_var('afroticket_download_hash');
    
    if (empty($hash)) {
        return;
    }
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // Rate limiting check
    if (!afroticket_rate_limit_check($ip)) {
        wp_die('Too many download attempts. Please try again later.', 'Rate Limited', ['response' => 429]);
    }
    
    // Verify hash
    $hash_info = afroticket_verify_secure_hash($hash);
    if (!$hash_info) {
        afroticket_log_download_attempt($hash, false, 'Invalid or expired hash');
        wp_die('Invalid or expired download link.', 'Access Denied', ['response' => 403]);
    }
    
    // Generate PDF and serve it
    $ticket_id = $hash_info['ticket_id'];
    $pdf = new EL_PDF();
    $pdf_file_path = $pdf->make_pdf_ticket($ticket_id);
    
    if (!file_exists($pdf_file_path)) {
        afroticket_log_download_attempt($hash, false, 'PDF file not found');
        wp_die('Ticket file not found.', 'File Error', ['response' => 404]);
    }
    
    // Mark hash as used (optional - you might want to allow multiple downloads)
    // delete_transient('afroticket_hash_' . $hash);
    
    afroticket_log_download_attempt($hash, true, 'Successful download');
    
    // Serve the PDF file
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="afroticket-' . $ticket_id . '.pdf"');
    header('Content-Length: ' . filesize($pdf_file_path));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: no-cache');
    
    readfile($pdf_file_path);
    exit;
}

// Flush rewrite rules on activation
add_action('after_switch_theme', 'afroticket_flush_rewrite_rules');
function afroticket_flush_rewrite_rules() {
    afroticket_add_download_rewrite_rules();
    flush_rewrite_rules();
}
// Updated SMS Integration with Secure Download Links

function createSmsBody($tickets, $phoneCustomer, $facebooklink, $serviceType = "twilio") {
    $ticketUrls = "";
    if (!empty($tickets) && is_array($tickets)) {
        foreach ($tickets as $ticket) {
            $ticketUrls .= $ticket . "\n";
        }
    }

    $expiration_hours = get_option('afroticket_link_expiration_hours', 72);
    $body = "🎫 Your AfroTicket is ready!\n\nDownload your ticket:\n{$ticketUrls}\n📱 Follow us: {$facebooklink}\n\n🔒 Secure link expires in {$expiration_hours} hours";
    
    if ($serviceType == "twilio") {
        $messaging_service_sid = get_option('twilio_messaging_service_sid', '');
        return "To=" . urlencode($phoneCustomer) . "&MessagingServiceSid=" . urlencode($messaging_service_sid) . "&Body=" . urlencode($body);
    }
    
    return "Service type not recognized";
}

function send_message_twillio($postvars) {
    // Get Twilio credentials from WordPress options
    $account_sid = get_option('twilio_account_sid');
    $auth_token = get_option('twilio_auth_token');
    
    // Check if credentials are set
    if (empty($account_sid) || empty($auth_token)) {
        error_log('AfroTicket SMS: Twilio credentials not configured');
        return false;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.twilio.com/2010-04-01/Accounts/" . $account_sid . "/Messages.json");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);
    curl_setopt($ch, CURLOPT_USERPWD, $account_sid . ":" . $auth_token);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        error_log('AfroTicket SMS: cURL error - ' . curl_error($ch));
        curl_close($ch);
        return false;
    }
    
    // Log response for debugging
    if ($httpCode != 201) {
        error_log('AfroTicket SMS: Twilio API error HTTP ' . $httpCode . ' - ' . $result);
    } else {
        error_log('AfroTicket SMS: Message sent successfully');
    }
    
    curl_close($ch);
    
    return ($httpCode == 201);
}

function send_ticket_sms($tickets, $phoneCustomer, $facebooklink = "") {
    $smsData = createSmsBody($tickets, $phoneCustomer, $facebooklink, "twilio");
    return send_message_twillio($smsData);
}

// Updated hook to generate secure URLs instead of direct PDF links
add_action("wp_mail", function($atts) {
    // Check if this is a ticket email
    if (strpos($atts["subject"], "Booking") !== false || strpos($atts["subject"], "Ticket") !== false) {
        
        // Extract booking ID from subject
        if (preg_match("/#(\d+)/", $atts["subject"], $matches)) {
            $booking_id = $matches[1];
            
            // Get and format phone number
            $phone = get_post_meta($booking_id, "ova_mb_event_phone", true);
            if (!empty($phone)) {
                $phone = preg_replace("/[^0-9]/", "", $phone);
                $phone = "+1" . $phone;
                
                // Get customer email for hash generation
                $customer_email = get_post_meta($booking_id, "ova_mb_event_email", true);
                
                if (empty($customer_email)) {
                    return $atts; // Can't create secure hash without customer email
                }
                
                // Generate secure download URLs
                $secure_tickets = [];
                $ticket_ids = get_post_meta($booking_id, "ova_mb_event_record_ticket_ids", true);
                if (!empty($ticket_ids) && is_array($ticket_ids)) {
                    foreach ($ticket_ids as $ticket_id) {
                        // Ensure PDF exists
                        $pdf = new EL_PDF();
                        $pdf_file_path = $pdf->make_pdf_ticket($ticket_id);
                        
                        if (file_exists($pdf_file_path)) {
                            // Generate secure hash for this ticket
                            $secure_hash = afroticket_generate_secure_hash($booking_id, $ticket_id, $customer_email);
                            $secure_url = get_site_url() . "/download-ticket/" . $secure_hash;
                            $secure_tickets[] = $secure_url;
                        }
                    }
                }
                
                // Send SMS with secure links
                if (!empty($secure_tickets)) {
                    send_ticket_sms($secure_tickets, $phone, "https://facebook.com/afroticket");
                }
            }
        }
    }
    
    return $atts;
}, 10, 1);