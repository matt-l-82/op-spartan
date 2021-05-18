<?php
/**
 * Give Annual Receipts settings.
 *
 * @since 1.0.0
 *
 * @return array $settings Annual Receipts Settings array.
 */
function give_annual_receipts_settings() {
	$prefix = 'give_annual_receipts_';

	// Define default content after receipt.
	$content_after_receipt  = __( 'No goods or services were provided in exchange for these contributions.','give-annual-receipts' ) . "\r\n\r\n";
	$content_after_receipt .= __( 'Sincerely,','give-annual-receipts' ) . "\r\n\r\n";
	$content_after_receipt .= __( 'FirstName LastName', 'give-annual-receipts'  ) . "\r\n";
	$content_after_receipt .= __( 'Position, Organization','give-annual-receipts' ) . "\r\n";

	// Define default footer.
	$footer  = __( '123 Organization St, City, State 12345', 'give-annual-receipts' ) . "\r\n";
	$footer .= __( 'Phone (555) 555-5555', 'give-annual-receipts' );

	// Check if pdf receipt plugin active. Copy common settings value to annual receipt settings
	$is_pdf_receipt_active = defined( 'GIVE_PDF_PLUGIN_BASENAME' ) ? is_plugin_active( GIVE_PDF_PLUGIN_BASENAME ) : false;

	if ( $is_pdf_receipt_active ) {
		$give_options          = give_get_settings();
		$give_pdf_logo_upload  = isset( $give_options['give_pdf_logo_upload'] ) ? $give_options['give_pdf_logo_upload'] : '';
	}

	// Settings array for the donation form 'PDF Receipts' section.
	$annual_receipts_fields = array(
		array(
			'id'   => 'give_annual_receipts_settings',
			'type' => 'title',
		),
		array(
			'id'            => $prefix . 'enable_disable',
			'type'          => 'radio_inline',
			'name'          => __( 'Annual Receipts', 'give-annual-receipts' ),
			'desc'          => __( 'This enables annual receipts on the Donation History page for donors and within the Donor profile for administrators.', 'give-annual-receipts' ),
			'wrapper_class' => 'give_annual_receipts_enable_disable',
			'default'       => 'disabled',
			'options'       => array(
				'enabled'  => __( 'Enabled', 'give-annual-receipts' ),
				'disabled' => __( 'Disabled', 'give-annual-receipts' ),
			),
		),
		array(
			'id'            => $prefix . 'tax_year',
			'type'          => 'multi_dropdown',
			'name'          => __( 'Tax Year End Date', 'give-annual-receipts' ),
			'description'   => __( 'Enter the last month and day of the tax year upon which the annual receipt is based.', 'give-annual-receipts' ),
			'wrapper_class' => 'set-annual-receipts-builder-option annual-receipts-fields give-hidden',
		),
		array(
			'id'            => $prefix . 'logo_upload',
			'type'          => 'file',
			'name'          => __( 'Logo', 'give-annual-receipts' ),
			'description'   => __( 'Upload the logo that you would like to display on the receipt.', 'give-annual-receipts' ),
			'default'       => ! empty( $give_pdf_logo_upload ) ? $give_pdf_logo_upload : '',
			'wrapper_class' => 'set-annual-receipts-builder-option annual-receipts-fields give-hidden',
		),
		array(
			'id'            => $prefix . 'content_before_receipt',
			'type'          => 'textarea',
			'name'          => __( 'Content Before Receipt Table', 'give-annual-receipts' ),
			'description'   => __( 'Enter content to appear before the receipt table.', 'give-annual-receipts' ),
			'default'       => __( 'On behalf of our organization, thank you so much for your generous contributions:', 'give-annual-receipts' ),
			'wrapper_class' => 'set-annual-receipts-builder-option annual-receipts-fields give-hidden',
		),
		array(
			'id'            => $prefix . 'content_after_receipt',
			'type'          => 'textarea',
			'name'          => __( 'Content After Receipt Table', 'give-annual-receipts' ),
			'description'   => __( 'Enter content to appear after the receipt table.', 'give-annual-receipts' ),
			'wrapper_class' => 'set-annual-receipts-builder-option annual-receipts-fields give-hidden',
			'default'       => $content_after_receipt,
		),
		array(
			'id'            => $prefix . 'footer',
			'type'          => 'textarea',
			'name'          => __( 'Footer', 'give-annual-receipts' ),
			'description'   => __( 'Enter the message you would like to be shown on the footer of the receipt.', 'give-annual-receipts' ),
			'wrapper_class' => 'set-annual-receipts-builder-option annual-receipts-fields give-hidden',
			'default'       => $footer,
		),
		array(
			'id'            => $prefix . 'sub_footer',
			'type'          => 'text',
			'name'          => __( 'Subfooter', 'give-annual-receipts' ),
			'description'   => __( 'Enter the message you would like to be shown in smaller text below the footer.', 'give-annual-receipts' ),
			'wrapper_class' => 'set-annual-receipts-builder-option annual-receipts-fields give-hidden',
			'default'       => __( 'Subfooter text such as tax ID number goes here.', 'give-annual-receipts' ),
		),
		array(
			'id'            => 'give_annual_receipts_preview_button',
			'type'          => 'annual_receipts_preview_button',
			'name'          => __( 'Preview Template', 'give-annual-receipts' ),
			'description'   => __( 'Click the button above to preview how the PDF will appear to donors.', 'give-annual-receipts' ),
			'wrapper_class' => 'set-annual-receipts-builder-option annual-receipts-fields give-hidden',
		),
		array(
			'name'  => esc_html__( 'Annual Receipts Docs', 'give-annual-receipts' ),
			'id'    => 'annual_receipts_docs_link',
			'url'   => esc_url( 'https://docs.givewp.com/addon-annual-receipts' ),
			'title' => __( 'Annual Receipts Docs', 'give-annual-receipts' ),
			'type'  => 'give_docs_link',
		),
		array(
			'id'   => 'give_annual_receipts_settings',
			'type' => 'sectionend',
		),
	);

	return $annual_receipts_fields;
}
