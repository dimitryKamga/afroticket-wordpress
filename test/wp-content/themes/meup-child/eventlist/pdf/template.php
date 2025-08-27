<?php if ( ! defined( 'ABSPATH' ) ) exit; 

// Get Info ticket
$ticket = $args['ticket']; 
$ticket_id = $ticket['ticket_id'];
$event_id = get_post_meta( $ticket_id, OVA_METABOX_EVENT . 'event_id', true );
$thumbnail_url = get_the_post_thumbnail_url( $event_id, 'full' );

// Get PDF mode for styling
$pdf_mode = isset( $GLOBALS['meup_pdf_mode'] ) ? $GLOBALS['meup_pdf_mode'] : meup_get_pdf_mode();
if ( empty( $pdf_mode ) ) { $pdf_mode = 'clean'; } // Default to clean mode

// Mode-specific font sizes
$font_sizes = array(
    'compact' => array('label' => '10pt', 'content' => '9pt', 'padding' => '10px'),
    'enhanced' => array('label' => '12pt', 'content' => '11pt', 'padding' => '15px'), 
    'clean' => array('label' => '11pt', 'content' => '10pt', 'padding' => '12px')
);
$current_size = isset($font_sizes[$pdf_mode]) ? $font_sizes[$pdf_mode] : $font_sizes['clean'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?php echo sprintf( esc_html__( 'Ticket #%s', 'eventlist' ), $ticket['ticket_id'] ); ?></title>
</head>
<body>

<!-- Template Mode Indicator -->
<div style="text-align: right; font-size: 8pt; color: #999; margin-bottom: 10px;">
    <?php echo ucfirst($pdf_mode); ?>
</div>

<table class="pdf_content">
    <tbody>
      <tr style="border: 5px solid <?php echo $ticket['color_border_ticket']; ?>;">

          <td class="left">
              <table style="width: 100%; border-collapse: collapse;" >
                
                <tr class="name_event">
                    <!-- Event Name -->
                    <td colspan="2">
                        <span style="color: <?php echo $ticket['color_label_ticket']; ?>; font-size: <?php echo $current_size['label']; ?>">
                            <b><?php esc_html_e( 'Event', 'eventlist' ); ?>:</b>
                        </span>
                        <br>
                        <span style="color: <?php echo $ticket['color_content_ticket']; ?>; font-size: <?php echo $current_size['content']; ?>">
                            <?php echo $ticket['event_name']; ?>
                        </span>
                    </td>
                </tr>

                <tr class="time">
                    <td class="time_content" style="border-right: 5px solid <?php echo $ticket['color_border_ticket']; ?>;">

                        <div style="color: <?php echo $ticket['color_label_ticket']; ?>; font-size: <?php echo $current_size['label']; ?>">
                            <b><?php esc_html_e( 'Time', 'eventlist' ); ?>:</b>
                        </div>

                        <div style="color: <?php echo $ticket['color_content_ticket']; ?>; font-size: <?php echo $current_size['content']; ?>">
                            <?php echo $ticket['date']; ?>
                            <br>
                            <?php echo $ticket['time']; ?>
                        </div>

                    </td>
                    <td class="venue_content" align="right">
                        <div style="color: <?php echo $ticket['color_label_ticket']; ?>; font-size: <?php echo $current_size['label']; ?>">
                            <b><?php esc_html_e( 'Venue', 'eventlist' ); ?>:</b>
                        </div>
                        <div style="color: <?php echo $ticket['color_content_ticket']; ?>; font-size: <?php echo $current_size['content']; ?>">
                            <?php echo $ticket['venue']; ?>
                            <br>
                            <?php echo $ticket['address']; ?>
                        </div>
                </td>
                </tr>
                
                <tr class="order_info">
                    <td colspan="2">
                        <div style="color: <?php echo $ticket['color_label_ticket']; ?>; font-size: <?php echo $current_size['label']; ?>">
                            <b><?php esc_html_e( 'Order Info', 'eventlist' ); ?>:</b>
                        </div>
                        <div style="color: <?php echo $ticket['color_content_ticket']; ?>; font-size: <?php echo $current_size['content']; ?>">
                            <?php echo $ticket['order_info']; ?>
                        </div>
                    </td>
                </tr>

                <tr class="ticket_type">
                    <td colspan="2">
                        <div style="color: <?php echo $ticket['color_label_ticket']; ?>; font-size: <?php echo $current_size['label']; ?>">
                            <b><?php esc_html_e( 'Ticket', 'eventlist' ); ?>:</b>
                        </div>
                        <div style="color: <?php echo $ticket['color_content_ticket']; ?>; font-size: <?php echo $current_size['content']; ?>">
                            <!-- Ticket Number -->
                            #<?php echo $ticket['ticket_id']; ?> - <?php echo $ticket['type_ticket']; ?>
                        </div>
                    </td>
                </tr>

            </table>
          </td>

          <td class="right">
              <table style="border: none;" vertical-align="top">
                  
                <tr>
                    <td>
                        <img src="https://afroticket.ca/wp-content/uploads/2019/06/logo-preview-ticket.png" width="150" />
                    </td>
                </tr>
            <br><br>
                <tr>
                    <td>
                        <barcode code="<?php echo $ticket['qrcode_str']; ?>" type="QR" disableborder="1" />
                    </td>
                </tr>

            </table>
          </td>

      </tr>

    </tbody>

</table>

<!-- Description Ticket -->
<p style="color: <?php echo apply_filters( 'el_desc_ticket_pdf', '#333333' ); ?>; font-size: <?php echo $current_size['content']; ?>">
<?php echo $ticket['desc_ticket']; ?>
</p>

<!-- Private Ticket -->
<p style="color: <?php echo apply_filters( 'el_private_desc_ticket_pdf', '#333333' ); ?>; font-size: <?php echo $current_size['content']; ?>">
<?php echo $ticket['private_desc_ticket']; ?>
</p>

<!-- Image of Ticket -->
<?php if ( ! empty( $thumbnail_url ) ): ?>
<p class="aligncenter">
<img src="<?php echo $thumbnail_url; ?>" height="<?php echo $pdf_mode === 'compact' ? '300' : ($pdf_mode === 'enhanced' ? '600' : '500'); ?>" />
</p>
<?php endif; ?>

<style>

    table.pdf_content{
        border-collapse: collapse;    
    }

    .left{
        width: 500px;
        border-right: 5px solid <?php echo $ticket['color_border_ticket']; ?>;    
        padding: 0px;
    }

    .right{
        width: 150px;
        padding: <?php echo $current_size['padding']; ?>;
    }

    .aligncenter {
        text-align: center;
    }
    
    .name_event td,
    .time td,
    .order_info td
    {
        border: none;
        border-bottom: 5px solid <?php echo $ticket['color_border_ticket']; ?>c;
        padding: <?php echo $current_size['padding']; ?>;
    }

    .ticket_type td{
        padding: <?php echo $current_size['padding']; ?>;    
    }

</style>

</body>
</html>
