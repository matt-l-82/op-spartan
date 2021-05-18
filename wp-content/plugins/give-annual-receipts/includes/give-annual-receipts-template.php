<?php
/**
 * This template is used to generate pdf content for preview default pdf and download pdf.
 *
 * @since 1.0.1
 *
 */
// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


$donor_id             = isset( $_GET['donor'] ) ? give_clean( $_GET['donor'] ) : '';
$is_default_template  = isset( $_GET['give_annual_receipts_action'] ) ? give_clean( $_GET['give_annual_receipts_action'] ) : '';
$give_options         = give_get_settings();
$donor_address        = array();
$donation_address     = array();
$is_address_available = false;

if ( ! is_admin() && ( empty( $donor_id ) || $id !== $donor_id ) && 'preview_annual_receipts' !== $is_default_template ) {
	return false;
}
if ( ! empty ( $is_default_template ) && 'preview_annual_receipts' === $is_default_template ) {
	$is_address_available = true;
	$address_1            = '123';
	$address_2            = 'Example St';
	$address_city         = 'City';
	$address_state        = 'State';
	$address_zip          = '12345';
	$donor_name           = 'DonorFirst DonorLast';
	$default_donations    = '';
	$default_donations    .= '<tr>
								<td width="24%">October 30, 2018</td>
								<td width="23%" align="right">$10.00</td>
								<td width="6%"> &nbsp;</td>
								<td width="24%">Example-001</td>
								<td width="23%">Credit Card</td>
							</tr>
							<tr>
								<td width="24%">November 10, 2018</td>
								<td width="23%" align="right">$40.00</td>
								<td width="6%"> &nbsp;</td>
								<td width="24%">Example-002</td>
								<td width="23%">PayPal</td>
							</tr>
							<tr>
								<td width="24%">November 20, 2018</td>
								<td width="23%" align="right">$20.00</td>
								<td width="6%"> &nbsp;</td>
								<td width="24%">Example-003</td>
								<td width="23%">Offline Donation</td>
							</tr>
							<tr>
								<td></td>
								<td align="right">Total: $70.00</td>
								<td></td>
								<td></td>
							</tr>';

}

$donor_address = __give_annual_receipt_get_donor_address( $donor_id );

if ( '' !== $donor_address['line1'] && empty( $address_1 ) ) {
	$address_1            = $donor_address['line1'];
	$address_2            = $donor_address['line2'];
	$address_city         = $donor_address['city'];
	$address_state        = $donor_address['state'];
	$address_zip          = $donor_address['zip'];
	$is_address_available = true;
}

if ( empty( $is_default_template ) ) {
	$receipt_year = isset( $_GET['receipt_year'] ) ? $_GET['receipt_year'] : '';
	$donor_name   = give_get_donor_name_by( $donor_id, 'donor' );
	$end_month    = (int) $give_options['give_annual_receipts_tax_month'];
	$end_day      = (int) $give_options['give_annual_receipts_tax_day'];
	$date_range   = give_annual_receipts_get_date_range( $receipt_year, $end_month, $end_day );
	$payments     = give_annual_receipts_get_donors_payments_by_year(
		$donor_id,
		$date_range['start_date'],
		$date_range['end_date']
	);
	$annual_total = 0;
	if ( empty( $payments ) ) {
		return false;
	}
	$donation_address = give_get_donation_address( $payments[0]->ID );
	if ( '' === $donor_address['line1'] && $donation_address['line1'] ) {
		$address_1            = $donation_address['line1'];
		$address_2            = $donation_address['line2'];
		$address_city         = $donation_address['city'];
		$address_state        = $donation_address['state'];
		$address_zip          = $donation_address['zip'];
		$is_address_available = true;
	}
}
$receipt_date = '<td align="right" style="font-weight: bold;">' . date( get_option( 'date_format' ) ) . '</td>';
?>
<table style="font-family: sans-serif; font-size: 15px; width: 100%; margin: 0 auto; color: #333;" border="0">
	<tbody>
	<tr>
		<td>
			<table width="100%" style="padding: 0; margin: 0; line-height: 1.5;">
				<tbody>
				<tr>
					<td style="height:35px;"></td>
				</tr>
				<?php if ( true === $is_address_available ) : ?>
					<tr>
						<td>
							<table>
								<tr>
									<td>
										<div style="font-weight: bold; font-family: helvetica; font-size: 16px;">
											<?php echo $donor_name; ?><br>
											<?php echo $address_1 . ' ' . $address_2; ?><br>
											<?php echo $address_city . ', ' . $address_state . ' ' . $address_zip; ?>
										</div>
									</td>
									<?php echo $receipt_date; ?>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td style="height: 15px;"></td>
					</tr>
				<?php endif; ?>
				<tr>
					<td>
						<table>
							<tr>
								<td style="font-size: 15px;" align="left">Dear <?php echo $donor_name; ?>,</td>
								<?php echo false === $is_address_available ? $receipt_date : ' '; ?>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td style="height: 15px;"></td>
				</tr>
				<tr>
					<td colspan="4">
						<?php
						echo isset( $give_options['give_annual_receipts_content_before_receipt'] ) ?
							nl2br( $give_options['give_annual_receipts_content_before_receipt'] ) : '';
						?>
					</td>
				</tr>
				<tr>
					<td style="height: 15px;"></td>
				</tr>
				<tr>
					<td>
						<table style="line-height: 1.75;">
							<thead>
							<tr style="font-weight:bold;">
								<th width="24%" scope="col">
									<?php _e( 'Date', 'give-annual-receipts' ); ?>
								</th>
								<th width="23%" scope="col" align="right">
									<?php _e( 'Amount', 'give-annual-receipts' ); ?>
								</th>
								<th width="6%">&nbsp;</th>
								<th width="24%" scope="col">
									<?php _e( 'Donation ID', 'give-annual-receipts' ); ?>
								</th>
								<th width="23%" scope="col" align="left">
									<?php _e( 'Payment Method', 'give-annual-receipts' ); ?>
								</th>
							</tr>
							</thead>
							<tbody>
							<?php if ( ! empty( $payments ) ) { ?>
								<?php foreach ( $payments as $payment ) : ?>
									<?php $annual_total += give_annual_receipts_get_donation_amount( $payment->ID ); ?>
									<tr>
										<td width="24%">
											<?php
											echo date_i18n( give_date_format(), strtotime( $payment->post_date ) );
											?>
										</td>
										<td width="23%"
										    align="right"><?php echo give_donation_amount( $payment->ID, true ); ?>
										</td>
										<td width="6%"> &nbsp;</td>
										<td width="24%"><?php echo Give()->seq_donation_number->get_serial_code( $payment->ID ); ?></td>
										<td width="23%">
											<?php
											$gateway = give_get_payment_gateway( $payment->ID );
											echo esc_html( give_get_gateway_checkout_label( $gateway ) );
											?>
										</td>
									</tr>
								<?php endforeach; ?>
								<tr>
									<td></td>
									<td align="right">
										<?php _e( 'Total:', 'give-annual-receipts' ); ?>
										<span><?php echo give_currency_filter( give_format_amount( $annual_total ) ); ?></span>
									</td>
									<td></td>
									<td></td>
								</tr>
							<?php } else if ( ! empty ( $is_default_template ) ) {
								echo $default_donations;
							} ?>
							</tbody>
						</table>
					</td>
				</tr>
				<tr>
					<td style="height: 20px;"></td>
				</tr>
				<tr>
					<td colspan="4">
						<?php
						echo isset( $give_options['give_annual_receipts_content_after_receipt'] ) ?
							nl2br( $give_options['give_annual_receipts_content_after_receipt'] ) : '';
						?>
					</td>
				</tr>
				<tr>
					<td style="height: 20px;"></td>
				</tr>
				</tbody>
			</table>
		</td>
	</tr>
	</tbody>
</table>