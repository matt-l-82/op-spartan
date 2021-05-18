<?php

defined( 'ABSPATH' ) || exit;

/**
 * Function is used to generate pdf content.
 *
 * @since    1.0.1
 *
 * @param $id int  Donor id to download pdf.
 *
 * @return string
 */
function give_annual_receipts_pdf_content( $id ) {
	ob_start();
	include_once GIVE_ANNUAL_RECEIPTS_DIR . '/includes/give-annual-receipts-template.php';

	return $content = ob_get_clean();
}

/**
 * Download Annual Receipts Form.
 *
 * @since 1.0.0
 */
function give_annual_receipts_preview() {

	/**
	 * Load TCPDF
	 */
	if ( file_exists( GIVE_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
		require_once GIVE_PLUGIN_DIR . 'vendor/autoload.php';
	}

	include_once GIVE_ANNUAL_RECEIPTS_DIR . '/includes/give-annual-receipts-tcpdf.php';

	$page_unit   = apply_filters( 'give_annual_receipts_page_unit', 'mm' );
	$page_format = apply_filters( 'give_annual_receipts_page_format', 'a4' );
	$form_layout = apply_filters( 'give_annual_receipts_layout', 'p' );
	$font_size   = apply_filters( 'give_annual_receipts_font_size', 14 );
	$form_font   = apply_filters( 'give_annual_receipts_font', 'dejavusans' );
	$form_font   = ( in_array(
		give_get_currency(), array(
			'RIAL',
			'RUB',
			'IRR',
		)
	) ) ? 'CODE2000' : $form_font; // Set 'CODE2000' font if Currency is Iranian rial.
	// create new PDF document
	$pdf = new Give_Annual_Receipts_PDF( $form_layout, $page_unit, $page_format, true, 'UTF-8', false );
	$pdf->SetAuthor( apply_filters( 'give_annual_receipts_author', get_option( 'blogname' ) ) );
	$pdf->setImageScale( 1.5 );
	$pdf->SetLeftMargin( 5 );
	$pdf->SetRightMargin( 5 );
	$pdf->SetHeaderMargin( 10 );
	$pdf->SetFooterMargin( PDF_MARGIN_FOOTER );
	$pdf->SetAutoPageBreak( true, 0 );
	$pdf->SetFont( $form_font, '', $font_size, 'false' );
	$pdf->AddPage();
	$default_html = give_annual_receipts_pdf_content( 0 );
	$pdf->writeHTMLCell( '', '', '', '40', $default_html, 0, 0, false, false, 'l', true );
	$pdf->Output( 'give_annual_receipts_default.pdf', 'I' );
	exit;
}

/**
 * Function is used to generate pdf for admin.
 *
 * @since 1.0.0
 */
function give_annual_receipts_admin_download() {

	/**
	 * Load TCPDF
	 */
	if ( ! class_exists( 'TCPDF' ) ) {
		if ( file_exists( GIVE_PLUGIN_DIR . 'vendor/tecnickcom/tcpdf/tcpdf.php' ) ) {
			require_once GIVE_PLUGIN_DIR . 'vendor/tecnickcom/tcpdf/tcpdf.php';
		} else {
			// Load autoloader.
			require_once GIVE_PLUGIN_DIR . 'includes/libraries/tcpdf/tcpdf.php';
		}
	}
	include_once GIVE_ANNUAL_RECEIPTS_DIR . '/includes/give-annual-receipts-tcpdf.php';
	$dynamic_html = give_annual_receipts_pdf_content( give_clean( $_GET['donor'] ) );
	if ( false === $dynamic_html || '' === $dynamic_html ) {
		return;
	}

	$page_unit   = apply_filters( 'give_annual_receipts_page_unit', 'mm' );
	$page_format = apply_filters( 'give_annual_receipts_page_format', 'a4' );
	$form_layout = apply_filters( 'give_annual_receipts_layout', 'p' );
	$font_size   = apply_filters( 'give_annual_receipts_font_size', 14 );
	$form_font   = apply_filters( 'give_annual_receipts_font', 'dejavusans' );
	$filename    = give_annual_receipts_generate_file_name();
	$form_font   = ( in_array(
		give_get_currency(), array(
			'RIAL',
			'RUB',
			'IRR',
		)
	) ) ? 'CODE2000' : $form_font; // Set 'CODE2000' font if Currency is Iranian rial.
	// create new PDF document
	$pdf = new Give_Annual_Receipts_PDF( $form_layout, $page_unit, $page_format, true, 'UTF-8', false );
	$pdf->SetAuthor( apply_filters( 'give_annual_receipts_author', get_option( 'blogname' ) ) );
	$pdf->setImageScale( 1.5 );
	$pdf->SetLeftMargin( 5 );
	$pdf->SetRightMargin( 5 );
	$pdf->SetHeaderMargin( 10 );
	$pdf->SetFooterMargin( PDF_MARGIN_FOOTER );
	$pdf->SetAutoPageBreak( true, 30 );
	$pdf->SetFont( $form_font, '', $font_size, 'false' );
	$pdf->AddPage();
	$pdf->writeHTMLCell( '', '', '', '40', $dynamic_html, 0, 0, false, false, 'l', true );
	$pdf->Output( apply_filters( 'give_annual_receipts_filename', $filename ) . '.pdf', 'I' );
	exit;
}

/**
 * Returns the saved address for a donor
 * Note: only for internal purpose
 *
 * @access public
 * @since  1.0
 *
 * @param       int   /null $donor_id Donor ID.
 * @param array $args donor args.
 *
 * @return array The donor's address, if any
 */
function __give_annual_receipt_get_donor_address( $donor_id = null, $args = array() ) {
	$address         = array();
	$default_address = array(
		'line1'   => '',
		'line2'   => '',
		'city'    => '',
		'state'   => '',
		'country' => '',
		'zip'     => '',
	);

	$args = wp_parse_args(
		$args,
		array(
			'address_type' => 'billing',
		)
	);

	$donor = new Give_Donor( $donor_id );

	if (
		! $donor->id ||
		empty( $donor->address ) ||
		! array_key_exists( $args['address_type'], $donor->address )
	) {
		return $default_address;
	}

	switch ( true ) {
		case is_string( end( $donor->address[ $args['address_type'] ] ) ):
			$address = wp_parse_args( $donor->address[ $args['address_type'] ], $default_address );
			break;

		case is_array( end( $donor->address[ $args['address_type'] ] ) ):
			$address = wp_parse_args( array_shift( $donor->address[ $args['address_type'] ] ), $default_address );
			break;
	}

	return $address;
}

/**
 * Function is used to generate pdf for donor.
 *
 * @since 1.0.0
 */
function give_annual_receipts_generate_pdf() {

	$donor = give_annual_receipts_get_donor_object();
	if ( empty ( $donor ) ) {
		return;
	}

	/**
	 * Load TCPDF
	 */
	if ( file_exists( GIVE_PLUGIN_DIR . 'vendor/tecnickcom/tcpdf/tcpdf.php' ) ) {
		require_once GIVE_PLUGIN_DIR . 'vendor/tecnickcom/tcpdf/tcpdf.php';
	}

	include_once GIVE_ANNUAL_RECEIPTS_DIR . '/includes/give-annual-receipts-tcpdf.php';

	$dynamic_html = give_annual_receipts_pdf_content( $donor->id );
	if ( "" === $dynamic_html || false === $dynamic_html ) {
		return;
	}
	$page_unit   = apply_filters( 'give_annual_receipts_page_unit', 'mm' );
	$page_format = apply_filters( 'give_annual_receipts_page_format', 'a4' );
	$form_layout = apply_filters( 'give_annual_receipts_layout', 'p' );
	$font_size   = apply_filters( 'give_annual_receipts_font_size', 14 );
	$form_font   = apply_filters( 'give_annual_receipts_font', 'dejavusans' );
	$filename    = give_annual_receipts_generate_file_name();
	$form_font   = ( in_array(
		give_get_currency(), array(
			'RIAL',
			'RUB',
			'IRR',
		)
	) ) ? 'CODE2000' : $form_font; // Set 'CODE2000' font if Currency is Iranian rial.
	// create new PDF document
	$pdf = new Give_Annual_Receipts_PDF( $form_layout, $page_unit, $page_format, true, 'UTF-8', false );
	$pdf->SetAuthor( apply_filters( 'give_annual_receipts_author', get_option( 'blogname' ) ) );
	$pdf->setImageScale( 1.5 );
	$pdf->SetLeftMargin( 5 );
	$pdf->SetRightMargin( 5 );
	$pdf->SetHeaderMargin( 10 );
	$pdf->SetFooterMargin( PDF_MARGIN_FOOTER );
	$pdf->SetAutoPageBreak( true, 30 );
	$pdf->SetFont( $form_font, '', $font_size, 'false' );
	$pdf->AddPage();
	$pdf->writeHTMLCell( '', '', '', '40', $dynamic_html, 0, 0, false, false, 'l', true );
	$pdf->Output( apply_filters( 'give_annual_receipts_filename', $filename ) . '.pdf', 'I' );
	exit;
}