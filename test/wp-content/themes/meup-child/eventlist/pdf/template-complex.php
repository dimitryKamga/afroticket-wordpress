<?php if ( ! defined( 'ABSPATH' ) ) exit; 

// Get event featured image
$event_image = isset( $ticket['event_featured_image'] ) ? $ticket['event_featured_image'] : ( isset( $GLOBALS['meup_event_featured_image'] ) ? $GLOBALS['meup_event_featured_image'] : '' );

// Check which PDF library is being used
$pdf_library = get_option( 'meup_pdf_library', 'mpdf' );
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?php echo sprintf( esc_html__( 'Ticket #%s', 'eventlist' ), $ticket['ticket_id'] ); ?></title>
    
    <?php if ( $pdf_library === 'dompdf' ): ?>
    <!-- Dompdf Optimized CSS -->
    <style>
        @page { margin: 2cm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #202124;
        }
        
        .ticket-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e8eaed;
        }
        
        .ticket-title {
            font-size: 18px;
            font-weight: normal;
            color: <?php echo ! empty( $ticket['color_content_ticket'] ) ? $ticket['color_content_ticket'] : '#202124'; ?>;
            margin-bottom: 5px;
        }
        
        .ticket-number {
            font-size: 12px;
            color: <?php echo ! empty( $ticket['color_label_ticket'] ) ? $ticket['color_label_ticket'] : '#5f6368'; ?>;
        }
        
        .logo-section img { max-height: 40px; max-width: 150px; }
        
        .main-content {
            display: flex;
            gap: 40px;
            margin-bottom: 30px;
        }
        
        .event-details { flex: 2; }
        .qr-section { flex: 1; text-align: center; }
        
        .section {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e8eaed;
        }
        
        .section:last-child { border-bottom: none; }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: <?php echo ! empty( $ticket['color_label_ticket'] ) ? $ticket['color_label_ticket'] : '#202124'; ?>;
            margin-bottom: 10px;
        }
        
        .event-name {
            font-size: 16px;
            color: <?php echo ! empty( $ticket['color_content_ticket'] ) ? $ticket['color_content_ticket'] : '#202124'; ?>;
            margin-bottom: 8px;
        }
        
        .datetime-venue {
            display: flex;
            gap: 30px;
            margin-top: 15px;
        }
        
        .info-group { flex: 1; }
        
        .info-group h4 {
            font-size: 12px;
            font-weight: bold;
            color: <?php echo ! empty( $ticket['color_label_ticket'] ) ? $ticket['color_label_ticket'] : '#202124'; ?>;
            margin-bottom: 5px;
        }
        
        .info-group p {
            font-size: 11px;
            color: <?php echo ! empty( $ticket['color_content_ticket'] ) ? $ticket['color_content_ticket'] : '#5f6368'; ?>;
        }
        
        .qr-code-container {
            width: 120px;
            height: 120px;
            border: 1px solid #e8eaed;
            background: #f8f9fa;
            margin: 0 auto 15px auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .qr-label { font-size: 10px; color: #5f6368; }
        
        .info-cards {
            display: flex;
            gap: 20px;
            margin: 25px 0;
        }
        
        .info-card {
            flex: 1;
            padding: 15px;
            background: #f8f9fa;
            border: 1px solid #e8eaed;
            border-radius: 5px;
        }
        
        .info-card h4 {
            font-size: 12px;
            font-weight: bold;
            color: <?php echo ! empty( $ticket['color_label_ticket'] ) ? $ticket['color_label_ticket'] : '#202124'; ?>;
            margin-bottom: 5px;
        }
        
        .description-section {
            margin: 25px 0;
            padding: 20px;
            background: #f8f9fa;
            border-left: 4px solid <?php echo ! empty( $ticket['color_border_ticket'] ) ? $ticket['color_border_ticket'] : '#4285f4'; ?>;
            border-radius: 0 5px 5px 0;
        }
        
        .event-image {
            text-align: center;
            margin: 25px 0;
        }
        
        .event-image img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 5px;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e8eaed;
            text-align: center;
            font-size: 10px;
            color: #5f6368;
        }
    </style>
    
    <?php else: ?>
    <!-- mPDF Optimized CSS -->
    <style>
        @page { margin: 2cm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #202124;
        }
        
        .ticket-container { width: 100%; max-width: 800px; margin: 0 auto; }
        
        .header {
            width: 100%;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e8eaed;
        }
        
        .header-left { width: 70%; float: left; }
        .header-right { width: 30%; float: right; text-align: right; }
        
        .ticket-title {
            font-size: 16pt;
            color: <?php echo ! empty( $ticket['color_content_ticket'] ) ? $ticket['color_content_ticket'] : '#202124'; ?>;
            margin-bottom: 5px;
        }
        
        .ticket-number {
            font-size: 10pt;
            color: <?php echo ! empty( $ticket['color_label_ticket'] ) ? $ticket['color_label_ticket'] : '#5f6368'; ?>;
        }
        
        .logo-section img { max-height: 30px; max-width: 150px; }
        
        .clearfix::after { content: ""; display: table; clear: both; }
        
        .main-content { width: 100%; margin-bottom: 25px; }
        .content-left { width: 65%; float: left; padding-right: 30px; }
        .content-right { width: 35%; float: right; text-align: center; }
        
        .section {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e8eaed;
        }
        
        .section:last-child { border-bottom: none; }
        
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: <?php echo ! empty( $ticket['color_label_ticket'] ) ? $ticket['color_label_ticket'] : '#202124'; ?>;
            margin-bottom: 8px;
        }
        
        .event-name {
            font-size: 13pt;
            color: <?php echo ! empty( $ticket['color_content_ticket'] ) ? $ticket['color_content_ticket'] : '#202124'; ?>;
            margin-bottom: 5px;
        }
        
        .datetime-venue { width: 100%; }
        .info-group { width: 48%; float: left; margin-bottom: 10px; }
        .info-group:nth-child(even) { float: right; }
        
        .info-group h4 {
            font-size: 9pt;
            font-weight: bold;
            color: <?php echo ! empty( $ticket['color_label_ticket'] ) ? $ticket['color_label_ticket'] : '#202124'; ?>;
            margin-bottom: 3px;
        }
        
        .info-group p {
            font-size: 9pt;
            color: <?php echo ! empty( $ticket['color_content_ticket'] ) ? $ticket['color_content_ticket'] : '#5f6368'; ?>;
        }
        
        .qr-code-container {
            width: 120px;
            height: 120px;
            border: 1px solid #e8eaed;
            background: #f8f9fa;
            margin: 0 auto 15px auto;
            text-align: center;
            padding: 10px;
        }
        
        .qr-label { font-size: 8pt; color: #5f6368; }
        
        .info-cards { width: 100%; margin: 20px 0; }
        .info-card { width: 48%; float: left; padding: 15px; background: #f8f9fa; border: 1px solid #e8eaed; }
        .info-card:nth-child(even) { float: right; }
        
        .info-card h4 {
            font-size: 9pt;
            font-weight: bold;
            color: <?php echo ! empty( $ticket['color_label_ticket'] ) ? $ticket['color_label_ticket'] : '#202124'; ?>;
            margin-bottom: 5px;
        }
        
        .description-section {
            width: 100%;
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid <?php echo ! empty( $ticket['color_border_ticket'] ) ? $ticket['color_border_ticket'] : '#4285f4'; ?>;
        }
        
        .event-image { width: 100%; text-align: center; margin: 20px 0; }
        .event-image img { max-width: 100%; max-height: 200px; }
        
        .footer {
            width: 100%;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e8eaed;
            text-align: center;
            font-size: 8pt;
            color: #5f6368;
        }
    </style>
    <?php endif; ?>
</head>
<body>
    <div class="ticket-container">
        <!-- Header -->
        <div class="header <?php echo $pdf_library === 'mpdf' ? 'clearfix' : ''; ?>">
            <?php if ( $pdf_library === 'mpdf' ): ?>
                <div class="header-left">
                    <h1 class="ticket-title"><?php esc_html_e( 'Event Ticket', 'eventlist' ); ?></h1>
                    <div class="ticket-number">#<?php echo $ticket['ticket_id']; ?> - <?php echo $ticket['type_ticket']; ?></div>
                </div>
                <div class="header-right">
                    <?php if( ! empty( $ticket['logo_url'] ) ): ?>
                        <div class="logo-section">
                            <img src="<?php echo esc_url($ticket['logo_url']); ?>" alt="Logo">
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div>
                    <h1 class="ticket-title"><?php esc_html_e( 'Event Ticket', 'eventlist' ); ?></h1>
                    <div class="ticket-number">#<?php echo $ticket['ticket_id']; ?> - <?php echo $ticket['type_ticket']; ?></div>
                </div>
                <?php if( ! empty( $ticket['logo_url'] ) ): ?>
                    <div class="logo-section">
                        <img src="<?php echo esc_url($ticket['logo_url']); ?>" alt="Logo">
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Main Content -->
        <div class="main-content <?php echo $pdf_library === 'mpdf' ? 'clearfix' : ''; ?>">
            <div class="<?php echo $pdf_library === 'mpdf' ? 'content-left' : 'event-details'; ?>">
                <!-- Event Section -->
                <div class="section">
                    <div class="section-title"><?php esc_html_e( 'Event', 'eventlist' ); ?></div>
                    <?php if ( ! empty( $ticket['event_name'] ) ): ?>
                        <div class="event-name"><?php echo $ticket['event_name']; ?></div>
                    <?php endif; ?>
                </div>

                <!-- Date/Time and Venue -->
                <div class="section">
                    <div class="datetime-venue <?php echo $pdf_library === 'mpdf' ? 'clearfix' : ''; ?>">
                        <div class="info-group">
                            <h4><?php esc_html_e( 'Date and Time', 'eventlist' ); ?></h4>
                            <?php if ( ! empty( $ticket['date'] ) ): ?>
                                <p><?php echo $ticket['date']; ?></p>
                            <?php endif; ?>
                            <?php if ( ! empty( $ticket['time'] ) ): ?>
                                <p><?php echo $ticket['time']; ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="info-group">
                            <h4><?php esc_html_e( 'Venue', 'eventlist' ); ?></h4>
                            <?php if ( ! empty( $ticket['venue'] ) ): ?>
                                <p><?php echo $ticket['venue']; ?></p>
                            <?php endif; ?>
                            <?php if ( ! empty( $ticket['address'] ) ): ?>
                                <p><?php echo $ticket['address']; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Order Information -->
                <div class="section">
                    <div class="section-title"><?php esc_html_e( 'Order Information', 'eventlist' ); ?></div>
                    <?php if ( ! empty( $ticket['order_info'] ) ): ?>
                        <div class="section-content"><?php echo $ticket['order_info']; ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- QR Code Section -->
            <div class="<?php echo $pdf_library === 'mpdf' ? 'content-right' : 'qr-section'; ?>">
                <div class="qr-code-container">
                    <barcode code="<?php echo $ticket['qrcode']; ?>" type="QR" disableborder="1" size="1" />
                </div>
                <div class="qr-label"><?php esc_html_e( 'Scan this code at entry', 'eventlist' ); ?></div>
            </div>
        </div>

        <!-- Info Cards -->
        <div class="info-cards <?php echo $pdf_library === 'mpdf' ? 'clearfix' : ''; ?>">
            <div class="info-card">
                <h4><?php esc_html_e( 'Ticket Type', 'eventlist' ); ?></h4>
                <p><?php echo $ticket['type_ticket']; ?></p>
            </div>
            <div class="info-card">
                <h4><?php esc_html_e( 'QR Code', 'eventlist' ); ?></h4>
                <p><?php echo substr($ticket['qrcode_str'], 0, 20) . '...'; ?></p>
            </div>
        </div>

        <?php if ( ! empty( $ticket['extra_service'] ) ): ?>
            <div class="description-section">
                <h4><?php esc_html_e( 'Extra Services', 'eventlist' ); ?></h4>
                <p><?php echo $ticket['extra_service']; ?></p>
            </div>
        <?php endif; ?>

        <?php if ( ! empty( $ticket['desc_ticket'] ) ): ?>
            <div class="description-section">
                <h4><?php esc_html_e( 'Event Description', 'eventlist' ); ?></h4>
                <p><?php echo $ticket['desc_ticket']; ?></p>
            </div>
        <?php endif; ?>

        <?php if ( ! empty( $ticket['private_desc_ticket'] ) ): ?>
            <div class="description-section">
                <h4><?php esc_html_e( 'Important Information', 'eventlist' ); ?></h4>
                <p><?php echo $ticket['private_desc_ticket']; ?></p>
            </div>
        <?php endif; ?>

        <!-- Event Image -->
        <?php if ( ! empty( $event_image ) ): ?>
            <div class="event-image">
                <img src="<?php echo esc_url( $event_image ); ?>" alt="<?php echo esc_attr( $ticket['event_name'] ); ?>">
            </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="footer">
            <p><?php esc_html_e( 'This ticket is personal and non-transferable. It cannot be refunded, exchanged, or resold.', 'eventlist' ); ?></p>
        </div>
    </div>
</body>
</html>
