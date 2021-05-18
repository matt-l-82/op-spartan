<?php
/**
 * Template Functions
 *
 * @description: All the template functions for the PDF receipt when they are being built or generated.
 *
 * @package    Give PDF Receipts
 * @since      1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the list of pre-built PDF Receipts for the DOM2PDF Builder in TinyMCE within Settings.
 *
 * @return mixed|void
 * @since 2.4.0
 *
 */
function give_get_pdf_builder_default_templates() {

	return apply_filters( 'give_get_pdf_builder_default_templates', [
		[
			'name'     => 'Fresh Blue',
			'filepath' => GIVE_PDF_PLUGIN_DIR . 'templates/receipt-1.php',
		],
		[
			'name'     => 'Night White',
			'filepath' => GIVE_PDF_PLUGIN_DIR . 'templates/receipt-2.php',
		],
		[
			'name'     => 'Professional Serif',
			'filepath' => GIVE_PDF_PLUGIN_DIR . 'templates/receipt-3.php',
		],
		[
			'name'     => 'Light Gray',
			'filepath' => GIVE_PDF_PLUGIN_DIR . 'templates/receipt-4.php',
		],
	] );

}


/**
 * Get Settings
 *
 * Gets the settings for PDF Receipts plugin if they exist.
 *
 * @param object $give_pdf PDF receipt object.
 * @param string $setting  Setting name.
 *
 * @return string Returns option if it exists.
 * @since 1.0
 *
 */
function give_pdf_get_settings( $give_pdf, $setting ) {

	// Get donation id from transaction key, if the id provided is not integer.
	$payment_id       = ! empty( $_GET['_give_hash'] ) ? give_get_donation_id_by_key( $_GET['_give_hash'] ) : 0;
	$give_pdf_payment = get_post( $payment_id );
	$form_id          = give_get_payment_form_id( $payment_id );
	$form_id          = ! empty( $form_id ) ? $form_id : 0;

	// Get Form ID if Preview  Per-Form.
	if ( ! empty( $_GET['form_id'] ) ) {
		$form_id = absint( $_GET['form_id'] );
	}

	// Get PDF Receipts Settings based on Global or Per-Form.
	$give_options = give_get_pdf_receipts_all_settings( $form_id );

	if ( 'name' === $setting ) {
		if ( isset( $give_options['give_pdf_name'] ) ) {
			return $give_options['give_pdf_name'];
		}
	}

	if ( 'addr_line1' === $setting ) {
		if ( isset( $give_options['give_pdf_address_line1'] ) ) {
			return $give_options['give_pdf_address_line1'];
		}
	}

	if ( 'addr_line2' === $setting ) {
		if ( isset( $give_options['give_pdf_address_line2'] ) ) {
			return $give_options['give_pdf_address_line2'];
		}
	}

	if ( 'city_state_zip' === $setting ) {
		if ( isset( $give_options['give_pdf_address_city_state_zip'] ) ) {
			return $give_options['give_pdf_address_city_state_zip'];
		}
	}

	if ( 'email' === $setting ) {
		if ( isset( $give_options['give_pdf_email_address'] ) ) {
			return $give_options['give_pdf_email_address'];
		}
	}

	if ( 'notes' === $setting ) {
		if ( isset( $give_options['give_pdf_additional_notes'] ) && ! empty( $give_options['give_pdf_additional_notes'] ) ) {
			$give_pdf_additional_notes = $give_options['give_pdf_additional_notes'];
			$give_pdf_payment_date     = ! empty( $give_pdf_payment->post_date ) ? strtotime( $give_pdf_payment->post_date ) : current_time( 'timestamp', 1 );
			$receipt_id                = ! empty( $give_pdf_payment->ID ) ? give_pdf_get_payment_number( $give_pdf_payment->ID ) : '123456789';

			$give_pdf_additional_notes = str_replace( '{page}', 'Page' . $give_pdf->getPage(), $give_pdf_additional_notes );
			$give_pdf_additional_notes = str_replace( '{sitename}', get_bloginfo( 'name' ), $give_pdf_additional_notes );
			$give_pdf_additional_notes = str_replace( '{today}', date_i18n( get_option( 'date_format' ), time() ), $give_pdf_additional_notes );
			$give_pdf_additional_notes = str_replace( '{date}', date_i18n( get_option( 'date_format' ), strtotime( $give_pdf_payment_date ) ), $give_pdf_additional_notes );
			$give_pdf_additional_notes = str_replace( '{receipt_id}', $receipt_id, $give_pdf_additional_notes );
			$give_pdf_additional_notes = strip_tags( $give_pdf_additional_notes );
			$give_pdf_additional_notes = stripslashes_deep( html_entity_decode( $give_pdf_additional_notes, ENT_COMPAT, 'UTF-8' ) );

			return $give_pdf_additional_notes;
		}
	}

	return '';
}


/**
 * Calculate Line Heights
 *
 * Calculates the line heights for the 'To' block
 *
 * @param string $setting Setting name.
 *
 * @return string Returns line height.
 * @since 1.0
 *
 */
function give_pdf_calculate_line_height( $setting ) {
	if ( empty( $setting ) ) {
		return 0;
	} else {
		return 6;
	}
}

/**
 * Retrieve the payment number
 *
 * If sequential order numbers are enabled, this returns the order numbered
 *
 * @param int $payment_id Donation ID.
 *
 * @return int|string
 * @since       1.0
 *
 */
function give_pdf_get_payment_number( $payment_id = 0 ) {
	if ( function_exists( 'give_get_payment_number' ) ) {
		return give_get_payment_number( $payment_id );
	} else {
		return $payment_id;
	}
}

/**
 * Create html content by template.
 *
 * @param array  $args             {
 *
 * @type string  $template_content Template content (Required).
 * @type int     $donation_id      The donation ID save to payment meta (optional).
 * @type WP_Post $payment_data     Payment information related to the Donation (optional if $payment_id set).
 * @type string  $payment_method   Payment method (optional).
 * @type string  $payment_status   Payment status (optional).
 * @type array   $payment_meta     Payment meta (optional).
 * @type array   $buyer_info       Donor address information (optional).
 * @type string  $payment_date     Donation date (optional).
 * @type string  $transaction_id   The gateway payment ID save to payment meta (optional).
 * @type string  $receipt_link     Receipt link (optional).
 * @type bool    $pdf_preview      true = For preview PDF in browser |  false = For download PDF in browser (optional).
 * }
 *
 * @return string Returns html content
 * @since   1.0
 * @updated 2.2.0
 *
 */
function give_pdf_get_compile_html( $args = array() ) {

	// for backward compatibility.
	if ( ! is_array( $args ) ) {

		$func_args = func_get_args();

		$args = wp_parse_args(
			$args,
			array(
				'content'        => isset( $func_args[0] ) ? $func_args[0] : '',
				'payment'        => isset( $func_args[1] ) ? $func_args[1] : array(),
				'payment_method' => isset( $func_args[2] ) ? $func_args[2] : '',
				'payment_status' => isset( $func_args[3] ) ? $func_args[3] : '',
				'payment_meta'   => isset( $func_args[4] ) ? $func_args[4] : array(),
				'buyer_info'     => isset( $func_args[5] ) ? $func_args[5] : array(),
				'payment_date'   => isset( $func_args[6] ) ? $func_args[6] : '',
				'transaction_id' => isset( $func_args[7] ) ? $func_args[7] : 0,
				'receipt_link'   => isset( $func_args[8] ) ? $func_args[8] : '',
				'pdf_preview'    => isset( $func_args[10] ) ? $func_args[10] : false,
			)
		);

		// Prepare essential arguments for filter to support backward compatibility.
		$args['donation_id'] = ! empty( $args['payment']->ID ) ? $args['payment']->ID : 0;
		$args['form_id']     = give_get_payment_form_id( $args['donation_id'] );
	}

	$donation_id = ! empty( $args['donation_id'] ) ? $args['donation_id'] : 0;
	$form_id     = give_get_payment_form_id( $donation_id );

	// Use donation form id for preview PDF receipts.
	if ( $form_id === 0 && isset( $_GET['form_id'] ) ) { // @codingStandardsIgnoreLine.
		$form_id = absint( $_GET['form_id'] );
	}

	$template_content = $args['content'];
	$payment          = ! empty( $args['payment'] ) ? $args['payment'] : get_post( $donation_id );
	$payment_method   = $args['payment_method'];
	$payment_status   = ! empty( $args['payment_status'] ) ? $args['payment_status'] : give_get_payment_status( $donation_id, true );
	$payment_meta     = ! empty( $args['payment_meta'] ) ? $args['payment_meta'] : give_get_payment_meta( $donation_id );
	$buyer_info       = ! empty( $args['buyer_info'] ) ? $args['buyer_info'] : give_get_payment_meta_user_info( $donation_id );
	$user_info        = ! empty( $buyer_info['id'] ) ? get_userdata( $buyer_info['id'] ) : '';
	$payment_date     = isset( $args['payment_date'] ) ? $args['payment_date'] : date_i18n( get_option( 'date_format' ), strtotime( $payment->post_date ) );
	$receipt_link     = ! empty( $args['receipt_link'] ) ? $args['receipt_link'] : give_pdf_receipts()->engine->get_pdf_receipt_url( $donation_id );
	$pdf_preview      = isset( $args['pdf_preview'] ) ? $args['pdf_preview'] : false;
	$currency_code    = give_get_currency( $donation_id );
	$currency_code    = ! empty( $currency_code ) ? $currency_code : give_get_currency( $form_id );

	// Actual donation amount.
	$donation_amount = give_currency_filter(
		give_format_amount( give_donation_amount( $donation_id ), array(
			'currency' => $currency_code,
		) ),
		array(
			'currency_code'   => $currency_code,
			'decode_currency' => true,
		)
	);

	// Set default amount.
	$default_amount = give_currency_filter(
		give_format_amount( 25, array(
			'currency' => $currency_code,
		) ),
		array(
			'currency_code'   => $currency_code,
			'decode_currency' => true,
		)
	);

	$give_pdf_total_price = ! empty( $donation_id ) ? $donation_amount : $default_amount;

	// Get Global/PerForm based settings.
	$give_options = give_get_pdf_receipts_all_settings( $form_id );
	$is_per_form  = give_pdf_receipts_is_per_form_customized( $form_id );

	if ( $is_per_form ) {
		$page_size         = give_get_meta( $form_id, 'give_pdf_builder_page_size', true );
		$page_size         = ! empty( $page_size ) ? $page_size : 'LETTER';
	} else {
		$page_size         = give_get_option( 'give_pdf_builder_page_size', array( 0, 0, 595.28, 841.89 ) );
	}

	$billing_address = isset( $buyer_info['address'] ) ? give_pdf_get_formatted_billing_address( $buyer_info['address'] ) : '';
	$receipt_id      = isset( $donation_id ) ? give_pdf_get_payment_number( $donation_id ) : '123456789';
	$transaction_key = isset( $payment_meta['key'] ) ? $payment_meta['key'] : '90120939030939239';
	$seq_donation_id = isset( $donation_id ) ? Give()->seq_donation_number->get_serial_code( $donation_id ) : '123456789';
	$full_name       = ( isset( $buyer_info['first_name'] ) && isset( $buyer_info['last_name'] ) ) ? $buyer_info['first_name'] . ' ' . $buyer_info['last_name'] : 'John Doe';
	$payment_date    = ! empty( $payment_date ) ? date_i18n( get_option( 'date_format' ), strtotime( $payment->post_date ) ) : date_i18n( get_option( 'date_format' ), current_time( 'timestamp', 1 ) );
	$user_email      = isset( $buyer_info['email'] ) ? $buyer_info['email'] : 'my.email@email.com';
	$username        = isset( $user_info->user_login ) ? $user_info->user_login : __( 'No Username Found', 'give-pdf-receipts' );
	$company_name    = give_get_meta( $donation_id, '_give_donation_company', true );

	// Check if user clicked on Preview PDF button or not.
	if ( give_pdf_is_preview_mode() ) {
		$company_name = 'Company Inc.';
	}

	// Transaction ID.
	$transaction_id = give_get_meta( $donation_id, '_give_payment_transaction_id', true );

	// Fee Recovery support.
	$give_fee_amount = give_get_meta( $donation_id, '_give_fee_amount', true );

	if ( ! empty( $give_fee_amount ) && method_exists( 'Give_Fee_Recovery_Admin', 'give_fee_email_tag_amount' ) ) {
		$fee_recovery     = new Give_Fee_Recovery_Admin();
		$new_amount       = $fee_recovery->give_fee_email_tag_amount( $donation_id );
		$new_amount       = ( '123456789' !== $donation_id ) ? $new_amount : $default_amount;
		$template_content = str_replace( '{price}', $new_amount, $template_content );
		$template_content = str_replace( '{amount}', $new_amount, $template_content );
	} else {
		$template_content = str_replace( '{price}', $give_pdf_total_price, $template_content );
		$template_content = str_replace( '{amount}', $give_pdf_total_price, $template_content );
	}

	// Replace tags.
	$template_content = str_replace( '{donation_name}', isset( $payment_meta['form_title'] ) ? $payment_meta['form_title'] : __( 'Example Donation Form Title', 'give-pdf-receipts' ), $template_content );
	$template_content = str_replace( '{first_name}', isset( $buyer_info['first_name'] ) ? $buyer_info['first_name'] : 'John', $template_content );
	$template_content = str_replace( '{full_name}', $full_name, $template_content );
	$template_content = str_replace( '{company_name}', $company_name, $template_content );
	$template_content = str_replace( '{username}', $username, $template_content );
	$template_content = str_replace( '{user_email}', $user_email, $template_content );
	$template_content = str_replace( '{billing_address}', $billing_address, $template_content );
	$template_content = str_replace( '{date}', $payment_date, $template_content );
	$template_content = str_replace( '{payment_id}', $seq_donation_id, $template_content );
	$template_content = str_replace( '{receipt_id}', $receipt_id, $template_content );
	$template_content = str_replace( '{payment_method}', $payment_method, $template_content );
	$template_content = str_replace( '{sitename}', get_bloginfo( 'name' ), $template_content );
	$template_content = str_replace( '{receipt_link}', $receipt_link, $template_content );
	$template_content = str_replace( '{transaction_id}', $transaction_id, $template_content );
	$template_content = str_replace( '{transaction_key}', $transaction_key, $template_content );
	$template_content = str_replace( '{payment_status}', $payment_status, $template_content );
	$template_content = str_replace( '{today}', date_i18n( get_option( 'date_format' ), time() ), $template_content );
	$template_content = str_replace( '{currency_code}', $currency_code, $template_content );

	// Check if recurring add-on is activate.
	if ( give_pdf_receipts_is_recurring_active() ) {
		$template_content = give_pdf_receipts_replace_recurring_tags( $template_content, $donation_id );
	}

	// Check if FFM add-on is activate.
	if ( give_pdf_receipts_is_ffm_active() ) {
		$template_content = give_pdf_receipts_replace_ffm_tags( $template_content, $donation_id );
	}

	/**
	 * Filter to complied PDF content.
	 *
	 * @param array  $args             {
	 *
	 * @type string  $template_content Template content (original).
	 * @type int     $donation_id      The gateway payment ID save to payment meta.
	 * @type WP_Post $payment_data     Payment information related to the Donation.
	 * @type string  $payment_method   Payment method.
	 * @type string  $payment_status   Payment status.
	 * @type array   $payment_meta     Payment meta.
	 * @type array   $buyer_info       Donor address information.
	 * @type string  $payment_data     Donation date.
	 * @type string  $transaction_id   The gateway payment ID save to payment meta.
	 * @type string  $receipt_link     Receipt link.
	 * @type bool    $pdf_preview      true = For preview PDF in browser |  false = For download PDF in browser.
	 * }
	 *
	 * @return string $template_content Template content.
	 * @since 2.3.1
	 *
	 * @type string  $template_content Template content.
	 *
	 */
	$template_content = apply_filters( 'give_pdf_compiled_template_content', $template_content, $args );

	/**
	 * TCPDF Generation Method.
	 *
	 * Generate pdf using TCPDF default template.
	 */
	require_once GIVE_PDF_PLUGIN_DIR . '/includes/class-give-tcpdf.php';

	$give_pdf = new Give_PDF_Receipt( 'P', 'mm', $page_size, true, 'UTF-8', false );
	$give_pdf->SetMargins( 0, 0, 0, false );
	$give_pdf->setPrintHeader( false ); // Remove default Header.
	$give_pdf->setPrintFooter( false ); // Remove default footer.
	$give_pdf->setCellPaddings( 0, 0, 0, 0 );
	$give_pdf->setImageScale( 1.6 );
	$give_pdf->setCellMargins( 0, 0, 0, 0 );
	$font = apply_filters( 'give_pdf_receipt_default_font', 'Helvetica' );

	if ( ! isset( $give_options['give_pdf_builder_special_chars'] ) ) {
		$give_options['give_pdf_builder_special_chars'] = 'disabled';
	}
	$currency_font = ! give_is_setting_enabled( $give_options['give_pdf_builder_special_chars'] ) ? $font : 'DejaVuSans';

	// Set 'CODE2000' font for Iranian Rial and Russian Rubble country for support currency sign.
	if (
		in_array( give_get_currency(), give_pdf_receipt_code2000_supported_countries(), true ) ||
		in_array( $currency_code, give_pdf_receipt_code2000_supported_countries(), true )
	) {
		$currency_font = apply_filters( 'give_pdf_receipt_currency_font', 'CODE2000' );
	}

	$give_pdf->AddPage( 'P', $page_size );
	$give_pdf->SetFont( $currency_font, '' );
	$give_pdf->SetAuthor( apply_filters( 'give_custom_pdf_receipt_author', get_option( 'blogname' ) ) );

	if ( empty( $donation_id ) ) {
		$give_pdf->SetTitle( apply_filters( 'give_custom_pdf_receipt_title', __( 'PDF Preview', 'give-pdf-receipts' ) ) );
	} else {
		$give_pdf->SetTitle( apply_filters( 'give_custom_pdf_receipt_title', __( 'Receipt ', 'give-pdf-receipts' ) ) . give_pdf_get_payment_number( $donation_id ) );
	}

	$pdf_receipt_filename_prefix = ( '123456789' === $donation_id ) ? __( 'Receipt-Preview', 'give-pdf-receipts' ) : __( 'Receipt', 'give-pdf-receipts' );
	$pdf_receipt_filename        = sprintf(
		'%1$s-%2$s.pdf',
		apply_filters( 'give_custom_pdf_receipt_filename_prefix', $pdf_receipt_filename_prefix ),
		give_pdf_get_payment_number( $donation_id )
	);

	$pdf_output = ( $pdf_preview ) ? 'I' : 'D';

	// Output Blank PDF if table of content blank.
	if ( empty( $template_content ) ) {
		if ( ob_get_length() ) {
			ob_end_clean();
		}

		$give_pdf->Output( $pdf_receipt_filename, $pdf_output );

		exit;
	}

	$give_pdf->writeHTMLCell( '', '', '', '', $template_content, 0, 0, false, false, '', false );
	$give_pdf->setCellMargins( 0, 0, 0, 0 );
	$give_pdf->SetMargins( 0, 0, 0, false );

	$last_cell_height     = $give_pdf->getLastH();
	$page_height          = $give_pdf->getPageHeight();
	$remain_height        = $page_height - $last_cell_height;
	$bottom_margin        = $give_pdf->getBreakMargin( 1 );
	$remain_margin_height = $page_height - $bottom_margin;

	$bg = array( 237, 237, 237 );
	if ( false !== strpos( $template_content, 'fresh_blue' ) || false !== strpos( $template_content, 'light_gray' ) ) {
		$bg = array( 237, 237, 237 );
	} elseif ( false !== strpos( $template_content, 'night_white' ) ) {
		$bg = array( 61, 61, 61 );
	} elseif ( false !== strpos( $template_content, 'professional_serif' ) ) {
		$bg = array( 82, 82, 82 );
	}

	/**
	 * Fill Bottom BG expand based on template.
	 *
	 * Calculate bottom Margin for each page and fill.
	 * Provided filter to change BG color.
	 *
	 * @since 2.2.0
	 */
	$give_pdf->Rect( 0, $remain_margin_height, $give_pdf->getPageWidth(), $bottom_margin, 'F', array(
		'L' => 0,
		'T' => 0,
		'R' => 0,
		'B' => 0,
	), apply_filters( 'give_pdf_receipt_remain_bottom_height_bg', $bg ) );

	$give_pdf->lastPage();

	/**
	 * Fill BG expand based on template.
	 *
	 * Calculate remaining height and fill background color based on template.
	 * Provided filter to change BG color.
	 *
	 * @since 2.2.0
	 */
	$give_pdf->Rect( 0, $last_cell_height, $give_pdf->getPageWidth(), $remain_height, 'F', array(
		'L' => 0,
		'T' => 0,
		'R' => 0,
		'B' => 0,
	), apply_filters( 'give_pdf_receipt_remain_height_bg', $bg ) );

	if ( ob_get_length() ) {
		ob_end_clean();
	}

	$give_pdf->Output( $pdf_receipt_filename, $pdf_output );

	exit;

}

/**
 * Generate a PDF preview.
 *
 * When the admin clicks the "preview" button to view the PDF rendered.
 */
function give_pdf_receipts_template_preview() {

	// Sanity Checks.
	if (
		! isset( $_GET['give_pdf_receipts_action'] )
		|| 'preview_pdf' !== $_GET['give_pdf_receipts_action']
	) {
		return;
	}

	// Admin's only.
	if ( ! is_admin() ) {
		return;
	}

	// Get Form ID if Preview  Per-Form.
	$form_id     = ! empty( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;
	$template_id = give_get_option('give_pdf_receipt_template');
	$per_form    = false;
	if ( ! empty( $form_id ) ) {
		$template_id = give_get_meta( $form_id, 'give_pdf_receipt_template', true );
		$per_form    = true;
	}

	// Sample PDF.
	$sample_give_pdf_buyer_info = array(
		'address' => array(
			'line1'   => '123 Sample Road',
			'line2'   => 'Apt. 201',
			'city'    => 'San Diego',
			'state'   => 'California',
			'country' => 'US',
			'zip'     => '92101',
		),
	);

	if ( $template_id ) {
		$template         = get_post( $template_id );
		$template_content = ( $per_form ) ? give_get_meta( $form_id, 'give_pdf_builder', true ) : $template->post_content;

		// Set PDF Preview. true for preview it in browser else download it.
		$pdf_preview = true;

		// Output w/ dummy data.
		$html = give_pdf_get_compile_html(
			array(
				'content'        => $template_content,
				'payment'        => '',
				'payment_method' => __( 'Test Donation', 'give-pdf-receipts' ),
				'payment_status' => __( 'Complete', 'give-pdf-receipts' ),
				'payment_meta'   => '',
				'buyer_info'     => $sample_give_pdf_buyer_info,
				'payment_date'   => '',
				'transaction_id' => 'ch_190aZ9x90a',
				'receipt_link'   => 'http://sample.com/receipt-url-example/',
				'pdf_preview'    => $pdf_preview,
			)
		);

	}

	exit;

}

add_action( 'admin_init', 'give_pdf_receipts_template_preview' );

/**
 * Generate a Set PDF preview.
 *
 * @since 2.1.0
 *
 * When the admin clicks the "Preview Set PDF Template" button to view the PDF rendered.
 */
function give_set_pdf_receipt_template_preview() {

	// Sanity Checks.
	if (
		! isset( $_GET['give_pdf_receipts_action'] )
		|| 'preview_set_pdf_template' !== $_GET['give_pdf_receipts_action']
	) {
		return;
	}

	// Admin's only.
	if ( ! is_admin() ) {
		return;
	}

	$give_options = give_get_settings();

	if ( ! empty( $_GET['form_id'] ) ) {
		$give_options = give_pdf_receipts_per_form_settings( $_GET['form_id'] );
	}

	// Sample PDF.
	$give_pdf_buyer_info = array(
		'first_name' => 'John',
		'last_name'  => 'Doe',
		'address'    => array(
			'line1'   => '123 Sample Road',
			'line2'   => 'Apt. 201',
			'city'    => 'San Diego',
			'state'   => 'California',
			'country' => 'US',
			'zip'     => '92101',
		),
	);

	$give_pdf_payment_meta = array(
		'email' => 'email@hotmail.com',
		'key'   => 'ab45dsedsf54542dfs458edsfs',
	);

	$company_name            = isset( $give_options['give_pdf_company_name'] ) ? $give_options['give_pdf_company_name'] : '';
	$give_pdf_payment_date   = date_i18n( get_option( 'date_format' ), current_time( 'timestamp', 1 ) );
	$give_pdf_payment_status = __( 'Complete', 'give-pdf-receipts' );

	/**
	 * TCPDF Generation Method.
	 *
	 * Generate pdf using legacy TCPDF default template.
	 */
	require_once GIVE_PDF_PLUGIN_DIR . '/includes/class-give-tcpdf.php';

	$give_pdf = new Give_PDF_Receipt( 'P', 'mm', 'A4', true, 'UTF-8', false );

	$give_pdf->SetDisplayMode( 'real' );
	$give_pdf->setJPEGQuality( 100 );

	$give_pdf->SetTitle( __( 'Example Donation Receipt', 'give-pdf-receipts' ) );
	$give_pdf->SetCreator( __( 'GiveWP', 'give-pdf-receipts' ) );
	$give_pdf->SetAuthor( get_option( 'blogname' ) );

	$address_line_2_line_height = isset( $give_options['give_pdf_address_line2'] ) ? 6 : 0;

	if ( ! isset( $give_options['give_pdf_templates'] ) ) {
		$give_options['give_pdf_templates'] = 'default';
	}

	do_action( 'give_pdf_template_' . $give_options['give_pdf_templates'], $give_pdf, array(), $give_pdf_payment_meta, $give_pdf_buyer_info, 'Test Donation', 'Test Donation', $address_line_2_line_height, $company_name, $give_pdf_payment_date, $give_pdf_payment_status );

	if ( ob_get_length() ) {
		ob_end_clean();
	}

	$give_pdf->Output( apply_filters( 'give_pdf_receipt_filename_prefix', __( 'Example Donation Receipt', 'give-pdf-receipts' ) ) . '.pdf', 'I' );

	exit();

}

add_action( 'admin_init', 'give_set_pdf_receipt_template_preview' );


/**
 * Function to output the "Download Receipt" text.
 *
 * @param bool $right_arrow Display right arrow or not? Default true.
 *
 * @return string $output
 * @since 2.1
 *
 */
function give_pdf_receipts_download_pdf_text( $right_arrow = true ) {

	$output = __( 'Download Receipt', 'give-pdf-receipts' );
	if ( $right_arrow ) {
		$output .= ' &raquo;';
	}

	return apply_filters( 'give_pdf_receipt_shortcode_link_text', $output );
}

/**
 * This function will change hex code to rgb color code.
 *
 * @param string $hex hexadecimal Color Code.
 *
 * @return array $rgb RGB Color code.
 * @since 2.2.0
 *
 */
function give_hex_to_rgb( $hex ) {
	$hex      = str_replace( '#', '', $hex );
	$length   = strlen( $hex );
	$rgb['r'] = hexdec( $length == 6 ? substr( $hex, 0, 2 ) : ( $length == 3 ? str_repeat( substr( $hex, 0, 1 ), 2 ) : 0 ) );
	$rgb['g'] = hexdec( $length == 6 ? substr( $hex, 2, 2 ) : ( $length == 3 ? str_repeat( substr( $hex, 1, 1 ), 2 ) : 0 ) );
	$rgb['b'] = hexdec( $length == 6 ? substr( $hex, 4, 2 ) : ( $length == 3 ? str_repeat( substr( $hex, 2, 1 ), 2 ) : 0 ) );

	return $rgb;
}

/**
 * Return RGB Color Code from setting Global or Per-Form.
 *
 * @param int $form_id Form ID.
 *
 * @return array $rgb_code_array Color Code.
 * @since 2.2.0
 *
 */
function give_get_chosen_color( $form_id ) {
	$is_per_form = give_pdf_receipts_is_per_form_customized( $form_id );
	if ( $is_per_form ) {
		$chosen_color_code = give_get_meta( $form_id, 'give_pdf_colorpicker', true );
		$chosen_color_code = ! empty( $chosen_color_code ) ? $chosen_color_code : '#333';
		$rgb_code_array    = give_hex_to_rgb( $chosen_color_code );
	} else {
		// Get Color Picker color.
		$rgb_code_array = give_hex_to_rgb( give_get_option( 'give_pdf_colorpicker', '#333' ) );
	}

	return $rgb_code_array;
}

/**
 * Get Code2000 supported currencies.
 *
 * @return array
 * @since 2.2.0
 *
 */
function give_pdf_receipt_code2000_supported_countries() {
	return apply_filters( 'give_pdf_receipt_code2000_supported_countries', array( 'RIAL', 'RUB', 'IRR' ) );
}

/**
 * Build PDF args.
 *
 * @param int   $payment_id            Donation ID.
 * @param int   $form_id               Donation Form ID.
 * @param array $give_pdf_payment_meta Payment meta.
 *
 * @return array $pdf_receipt_args
 * @since 2.2.0
 *
 */
function give_pdf_get_args( $payment_id, $form_id, $give_pdf_payment_meta ) {

	// Set Font and style.
	$font         = apply_filters( 'give_pdf_receipt_default_font', 'Helvetica' );
	$font_size_32 = apply_filters( 'give_pdf_receipt_font_size_32', 32 );
	$font_size_18 = apply_filters( 'give_pdf_receipt_font_size_18', 18 );
	$font_size_16 = apply_filters( 'give_pdf_receipt_font_size_16', 16 );
	$font_size_14 = apply_filters( 'give_pdf_receipt_font_size_14', 14 );
	$font_size_12 = apply_filters( 'give_pdf_receipt_font_size_12', 12 );
	$font_size_10 = apply_filters( 'give_pdf_receipt_font_size_10', 10 );

	// Get RGB Color Code from the Settings.
	$rgb_code_array = give_get_chosen_color( $form_id );

	// Get PDF Receipts Settings based on Global or Per-Form.
	$give_options = give_get_pdf_receipts_all_settings( $form_id );

	$currency_font = empty( $give_options['give_pdf_enable_char_support'] ) ? $font : 'DejaVuSans';
	$currency_code = give_get_payment_currency_code( $payment_id );

	// Set 'CODE2000' font for Iranian Rial and Russian Rubble country for support currency sign.
	$currency_font_style = '';
	if ( in_array( give_get_currency(), give_pdf_receipt_code2000_supported_countries() )
	     || in_array( $currency_code, give_pdf_receipt_code2000_supported_countries() )
	) {
		$currency_font       = 'CODE2000';
		$currency_font_style = 'B';
	}

	// Support for Hindi.
	if ( 'INR' === give_get_currency() ) {
		$font          = 'freesans';
		$currency_font = 'freesans';
	}

	// Set Donation Name and Donation Amount.
	$payment_amount = ( ! empty( $payment_id ) && 123456789 !== $payment_id ) ? give_donation_amount( $payment_id ) : 25.00;
	$total          = give_currency_filter(
		give_format_amount( $payment_amount, array(
			'currency' => $currency_code,
		) ),
		array( 'currency_code' => $currency_code, 'decode_currency' => true )
	);

	$give_pdf_title  = get_the_title( $form_id );
	$give_form_title = ! empty( $give_pdf_title ) ? $give_pdf_title : apply_filters( 'give_pdf_default_form_title', __( 'Example Donation Form Title', 'give-pdf-receipts' ) );
	$give_form_title = html_entity_decode( $give_form_title, ENT_COMPAT, 'UTF-8' );

	// If multi-level, Custom amount, append to the form title.
	if ( give_has_variable_prices( $form_id ) ) {

		$price_id = isset( $give_pdf_payment_meta['price_id'] ) ? $give_pdf_payment_meta['price_id'] : null;

		if ( 'custom' === $price_id ) {
			$custom_amount_text = give_get_meta( $form_id, '_give_custom_amount_text', true );
			$custom_amount_text = ! empty( $custom_amount_text ) ? $custom_amount_text : __( 'Custom Amount', 'give-pdf-receipts' );
			$give_form_title    .= ' - ' . $custom_amount_text;
		} else {
			$give_form_title .= ' - ' . give_get_price_option_name( $form_id, $price_id, $payment_id );
		}

	}

	// Fee Recovery support.
	$fee_amount = give_get_meta( $payment_id, '_give_fee_amount', true );
	$fee        = give_currency_filter(
		give_format_amount( $fee_amount, array(
			'currency' => $currency_code,
		) ),
		array( 'currency_code' => $currency_code, 'decode_currency' => true )
	);

	$amount = give_donation_amount( $payment_id );
	// Subtract Fee amount from donation amount.
	if ( ! empty( $fee_amount ) ) {
		$amount -= $fee_amount;
	}

	$donation_amount = give_currency_filter(
		give_format_amount( $amount, array(
			'currency' => $currency_code,
		) ),
		array( 'currency_code' => $currency_code, 'decode_currency' => true )
	);

	$fee_recovery_support = false;
	if ( ! empty( $fee_amount ) ) {
		$fee_recovery_support = true;
	}

	$pdf_receipt_args                         = array();
	$pdf_receipt_args['font']                 = $font;
	$pdf_receipt_args['font_size_32']         = $font_size_32;
	$pdf_receipt_args['font_size_18']         = $font_size_18;
	$pdf_receipt_args['font_size_16']         = $font_size_16;
	$pdf_receipt_args['font_size_14']         = $font_size_14;
	$pdf_receipt_args['font_size_12']         = $font_size_12;
	$pdf_receipt_args['font_size_10']         = $font_size_10;
	$pdf_receipt_args['rgb_code_array']       = $rgb_code_array;
	$pdf_receipt_args['currency_font']        = $currency_font;
	$pdf_receipt_args['currency_font_style']  = $currency_font_style;
	$pdf_receipt_args['total']                = $total;
	$pdf_receipt_args['give_form_title']      = $give_form_title;
	$pdf_receipt_args['fee']                  = $fee;
	$pdf_receipt_args['donation_amount']      = $donation_amount;
	$pdf_receipt_args['fee_recovery_support'] = $fee_recovery_support;
	$pdf_receipt_args['give_options']         = $give_options;

	return $pdf_receipt_args;

}

/**
 * Format the billing address for PDF receipts.
 *
 * @param array $address Billing Address.
 *
 * @return string
 * @since 2.2.1
 *
 */
function give_pdf_get_formatted_billing_address( $address ) {

	if ( empty( $address ) ) {
		return '';
	}

	$formatted_address = '';

	// Line 1.
	if ( isset( $address['line1'] ) && ! empty( $address['line1'] ) ) {
		$formatted_address .= $address['line1'];
	}

	// Line 2.
	if ( isset( $address['line2'] ) && ! empty( $address['line2'] ) ) {
		$formatted_address .= ' <br/>' . $address['line2'];
	}

	// City.
	if ( isset( $address['city'] ) && ! empty( $address['city'] ) ) {
		$formatted_address .= ' <br/>' . $address['city'];

		// State.
		if ( isset( $address['state'] ) && ! empty( $address['state'] ) ) {
			$formatted_address .= ', ' . $address['state'];
		}

		// Zip.
		if ( isset( $address['zip'] ) && ! empty( $address['zip'] ) ) {
			$formatted_address .= ', ' . $address['zip'];
		}

	}

	// Country.
	if ( isset( $address['country'] ) && ! empty( $address['country'] ) ) {
		$countries         = give_get_country_list();
		$country           = isset( $countries[ $address['country'] ] ) ? $countries[ $address['country'] ] : $address['country'];
		$formatted_address .= ' <br/>' . $country;
	}

	return apply_filters( 'give_pdf_get_formatted_billing_address', $formatted_address, $address );

}


/**
 * Get if previewing PDF or not
 *
 * @return bool
 * @since 2.3.2
 */
function give_pdf_is_preview_mode() {
	$out = false;

	// Check if user clicked on Preview PDF button or not.
	if (
		isset( $_GET['give_pdf_receipts_action'] )
		&& 'preview_pdf' === give_clean( $_GET['give_pdf_receipts_action'] )
	) {
		$out = true;
	}

	return $out;
}
