<?php

use Give\Receipt\DonationReceipt;
use GivePdfReceipt\Helpers\Admin\Settings;
use GivePdfReceipt\Receipt\UpdateDonationReceipt;

/**
 * Class Give_PDF_Receipts_Engine
 *
 * @since 2.0.4
 */
class Give_PDF_Receipts_Engine {

	/**
	 * Give_PDF_Receipts_Engine constructor.
	 */
	public function __construct() {

		add_action( 'give_generate_pdf_receipt', array( $this, 'generate_pdf_receipt' ) );
		add_action( 'give_donation_history_header_after', array( $this, 'donation_history_header' ) );
		add_action( 'give_donation_history_row_end', array( $this, 'donation_history_link' ), 10, 2 );
		add_action( 'give_payment_receipt_after', array( $this, 'receipt_shortcode_link' ), 10 );
		add_action( 'give_new_receipt', array(
			$this,
			'updateDonationReceipt'
		), 99, 1 ); // Add actual Donation total and Recovery Fee on Payment receipt.
		add_filter( 'give_payment_row_actions', array( $this, 'receipt_link' ), 10, 2 );

		// Register PDF Receipts Section and Settings on Give Donation form.
		add_action( 'give_metabox_form_data_settings', array( $this, 'give_pdf_receipts_settings' ), 10, 2 );
		add_filter( 'give_form_pdf_receipts_metabox_fields', array(
			$this,
			'give_pdf_receipts_per_form_callback'
		), 10, 2 );
		add_action( 'give_save_give_pdf_receipt_template', array( $this, 'save_custom_pdf_template_per_form' ), 10, 3 );

		// Show PDF Receipt Download button under the Update Donation.
		add_action( 'give_view_donation_details_update_after', array(
			$this,
			'donation_detail_download_button'
		), 10, 1 );

	}

	/**
	 * Register 'PDF Receipts' section on edit donation form page.
	 *
	 * @param array $setting section array.
	 * @param int   $post_id donation form id.
	 *
	 * @return array $settings return the pdf receipts sections array.
	 * @since  2.1.0
	 * @access public
	 *
	 */
	public function give_pdf_receipts_settings( $setting, $post_id ) {

		// Appending the form PDF Receipts option section.
		$setting['form_pdf_receipts'] = apply_filters( 'give_form_pdf_receipts_options', array(
			'id'        => 'form_pdf_receipts_options',
			'title'     => __( 'PDF Receipts', 'give-pdf-receipts' ),
			'icon-html' => '<span class="pdf-receipts-icon"></span>',
			'fields'    => apply_filters( 'give_form_pdf_receipts_metabox_fields', array(), $post_id ),
		) );

		return $setting;
	}

	/**
	 * Register Setting fields for 'PDF Receipts' section in donation form edit page.
	 *
	 * @param array $settings setting fields array.
	 * @param int   $form_id  Form ID.
	 *
	 * @return array
	 * @since  2.1.0
	 * @access public
	 *
	 */
	public function give_pdf_receipts_per_form_callback( $settings, $form_id ) {
		return give_pdf_receipts_settings( false, $form_id );
	}

	/**
	 * Save custom template for form.
	 *
	 * @param string $form_meta_key
	 * @param string $form_meta_value
	 * @param int    $form_id
	 */
	public function save_custom_pdf_template_per_form( $form_meta_key, $form_meta_value, $form_id ) {
		Settings::SaveCustomTemplate( $form_id );
	}

	/**
	 * Generate PDF Receipt
	 *
	 * Loads and stores all of the data for the payment.  The HTML2PDF class is
	 * instantiated and do_action() is used to call the receipt template which goes
	 * ahead and renders the receipt.
	 *
	 * @since 1.0
	 * @uses  HTML2PDF
	 * @uses  wp_is_mobile()
	 */
	public function generate_pdf_receipt() {

		// Sanitize & Fetch $_GET Parameters.
		$get_data = give_clean( filter_input_array( INPUT_GET ) );

		// Define required variables.
		$donation_id = ! empty( $get_data['donation_id'] ) ? absint( $get_data['donation_id'] ) : false;

		if ( ! $donation_id ) {
			// Get donation id from transaction key, if the id provided is not integer.
			$donation_id = give_get_donation_id_by_key( $get_data['_give_hash'] );

			// Check if current donation is renewal
			// Set renewal id as donation id.
			$renewal_id = ! empty( $_GET['recurring_donation_id'] ) ? absint( $_GET['recurring_donation_id'] ) : 0;

			if (
				$renewal_id
				&& ! empty( $get_data['_give_hash'] )
				&& give_pdf_receipts_is_recurring_active()
				&& ( $get_data['_give_hash'] === give_get_payment_key( $donation_id ) )
				&& ( (int) give_get_meta( $donation_id, 'subscription_id', true ) || give_recurring_is_parent_donation( $donation_id ) )
			) {
				$donation_id = $renewal_id;
			}

			// To avoid unauthorised access with backward compatibility support.
		} elseif ( ! wp_verify_nonce( $get_data['_give_hash'], "give-download-pdf-receipt-{$donation_id}" ) ) {
			return;
		}

		// Sanity Check.
		if ( ! $this->handle_pdf_generation_error( $donation_id ) ) {
			return;
		}

		/**
		 * This action hook will be used to process additional steps before generating PDF receipts.
		 *
		 * @param int $donation_id Donation ID.
		 */
		do_action( 'give_pdf_generate_receipt', $donation_id );

		$payment_data      = get_post( $donation_id );
		$payment_meta      = give_get_payment_meta( $donation_id );
		$form_id           = give_get_payment_form_id( $donation_id );
		$is_per_form       = give_pdf_receipts_is_per_form_customized( $form_id );

		// Check if enabled Global/Per-Form.
		if ( $is_per_form ) {
			$template_id  = give_get_meta( $form_id, 'give_pdf_receipt_template', true );
			$give_options = give_pdf_receipts_per_form_settings( $form_id ); // Build Per-Form based settings array.
		} else {
			$give_options = give_get_settings();
			$template_id  = $give_options['give_pdf_receipt_template'];
		}

		$buyer_info           = give_get_payment_meta_user_info( $donation_id );
		$payment_gateway_used = give_get_payment_gateway( $donation_id );
		$company_name         = isset( $give_options['give_pdf_company_name'] ) ? $give_options['give_pdf_company_name'] : '';
		$payment_method       = give_get_gateway_checkout_label( $payment_gateway_used );
		$payment_date         = date_i18n( get_option( 'date_format' ), strtotime( $payment_data->post_date ) );
		$payment_status       = give_get_payment_status( $donation_id, true );

		// Add WPML Support.
		$this->add_wpml_support( $donation_id );

		if ( isset( $give_options['give_pdf_generation_method'] ) && 'custom_pdf_builder' === $give_options['give_pdf_generation_method'] ) {

			$template = get_post( $template_id );

			$template_content = '';
			if ( isset( $template ) ) {
				$template_content = ( $is_per_form ) ? give_get_meta( $form_id, 'give_pdf_builder', true ) : $template->post_content;
			}

			// Set PDF Preview. true for preview it in browser else download it.
			$pdf_preview = false;

			give_pdf_get_compile_html(
				array(
					'content'        => $template_content,
					'donation_id'    => $donation_id,
					'payment'        => $payment_data,
					'payment_method' => $payment_method,
					'payment_status' => $payment_status,
					'payment_meta'   => $payment_meta,
					'buyer_info'     => $buyer_info,
					'payment_date'   => $payment_date,
					'transaction_id' => give_get_payment_transaction_id( $donation_id ),
					'receipt_link'   => $this->get_pdf_receipt_url( $donation_id ),
					'pdf_preview'    => $pdf_preview,
				)
			);
		}

		/**
		 * TCPDF Generation Method.
		 *
		 * Generate pdf using legacy TCPDF default template.
		 */
		require_once GIVE_PDF_PLUGIN_DIR . 'includes/class-give-tcpdf.php';

		$give_pdf = new Give_PDF_Receipt( 'P', 'mm', 'A4', true, 'UTF-8', false );

		$give_pdf->SetDisplayMode( 'real' );
		$give_pdf->setJPEGQuality( 100 );

		$give_pdf->SetTitle( sprintf( ( __( 'Donation Receipt %s', 'give-pdf-receipts' ) ), give_pdf_get_payment_number( $donation_id ) ) );
		$give_pdf->SetCreator( __( 'GiveWP', 'give-pdf-receipts' ) );
		$give_pdf->SetAuthor( get_option( 'blogname' ) );

		$address_line_2_line_height = isset( $give_options['give_pdf_address_line2'] ) ? 6 : 0;

		if ( ! isset( $give_options['give_pdf_templates'] ) ) {
			$give_options['give_pdf_templates'] = 'default';
		}

		// Set Backward compatibility for the color templates.
		$color_templates = array(
			'blue',
			'green',
			'orange',
			'pink',
			'purple',
			'red',
			'yellow',
		);

		if ( in_array( $give_options['give_pdf_templates'], $color_templates, true ) ) {
			$give_options['give_pdf_templates'] = 'default';
		}

		do_action( 'give_pdf_template_' . $give_options['give_pdf_templates'], $give_pdf, $payment_data, $payment_meta, $buyer_info, $payment_gateway_used, $payment_method, $address_line_2_line_height, $company_name, $payment_date, $payment_status );

		if ( ob_get_length() ) {
			ob_end_clean();
		}

		$pdf_receipt_filename = sprintf(
		/* translators: 1. Filename, 2. Donation Number. */
			'%1$s-%2$s.pdf',
			apply_filters( 'give_pdf_receipt_filename_prefix', __( 'Receipt', 'give-pdf-receipts' ) ),
			give_pdf_get_payment_number( $donation_id )
		);

		$give_pdf->Output( $pdf_receipt_filename, 'D' );

		die(); // Stop the rest of the page from processing and being sent to the browser.
	}

	/**
	 * This function will handle the data and check for errors before generating PDF receipt.
	 *
	 * @param int $donation_id Donation ID.
	 *
	 * @return bool
	 * @since 2.3.2
	 *
	 */
	public function handle_pdf_generation_error( $donation_id ) {

		// Sanity check: Need Donation ID.
		if ( false === $donation_id ) {
			return false;
		}

		// Sanity check: Make sure the the receipt link is allowed.
		if ( ! $this->is_receipt_link_allowed( $donation_id ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Donation History Page Table Heading
	 *
	 * Appends to the table header (<thead>) on the Purchase History page for the
	 * Receipt column to be displayed
	 *
	 * @since 1.0
	 */
	function donation_history_header() {
		echo '<th class="give_receipt">' . __( 'Receipt', 'give-pdf-receipts' ) . '</th>';
	}

	/**
	 * Outputs the Receipt link.
	 *
	 * Adds the receipt link to the [donation_history] shortcode underneath the previously created "Receipt" header.
	 *
	 * @param int   $post_id       Payment post ID.
	 * @param array $donation_data All the donation data.
	 *
	 * @since 1.0
	 *
	 */
	function donation_history_link( $post_id, $donation_data ) {

		if ( ! $this->is_receipt_link_allowed( $post_id ) || is_give_pdf_receipts_disabled( $post_id ) ) {
			echo apply_filters( 'give_pdf_receipts_receipt_not_allowed_td', '<td>-</td>' );

			return;
		}

		printf(
			'<td class="give_receipt"><a class="give_receipt_link" title="%1$s" href="%2$s">%3$s</a></td>',
			give_pdf_receipts_download_pdf_text( false ),
			esc_url( $this->get_pdf_receipt_url( $post_id ) ),
			give_pdf_receipts_download_pdf_text()
		);
	}

	/**
	 * Receipt Shortcode Receipt Link.
	 *
	 * Adds the receipt link to the [give_receipt] shortcode.
	 *
	 * @param object $payment All the payment data
	 *
	 * @since 1.0.4
	 *
	 */
	public function receipt_shortcode_link( $payment ) {

		// Sanity check.
		if ( ! $this->is_receipt_link_allowed( $payment->ID ) ) {
			return;
		}

		// Bail out, if PDF Receipt disable from Per-Form or disable globally (if globally enabled).
		if ( is_give_pdf_receipts_disabled( $payment->ID ) ) {
			return;
		}
		?>
		<tr>
			<td><strong><?php _e( 'Receipt', 'give-pdf-receipts' ); ?>:</strong></td>
			<td>
				<a class="give_receipt_link" title="<?php echo give_pdf_receipts_download_pdf_text( false ); ?>"
				   href="<?php echo esc_url( $this->get_pdf_receipt_url( $payment->ID ) ); ?>"><?php echo give_pdf_receipts_download_pdf_text(); ?>
				</a>
			</td>
		</tr>
		<?php
	}

	/**
	 * Register download pdf receipt link to receipt.
	 *
	 * @param DonationReceipt $receipt
	 *
	 * @since 2.3.7
	 */
	public function updateDonationReceipt( $receipt ) {
		$updateReceipt = new UpdateDonationReceipt( $receipt );

		$updateReceipt->apply();
	}

	/**
	 * Check is receipt link is allowed.
	 *
	 * @param int $id Payment ID to verify total
	 *
	 * @return bool
	 * @since  1.0
	 * @access private
	 * @global    $give_options
	 *
	 */
	public function is_receipt_link_allowed( $id = null ) {

		$ret = true;

		if ( ! give_is_payment_complete( $id ) ) {
			$ret = false;
		}

		return apply_filters( 'give_pdf_is_receipt_link_allowed', $ret, $id );
	}


	/**
	 * Gets the Receipt URL.
	 *
	 * Generates an receipt URL and adds the necessary query arguments.
	 *
	 * @param int $donation_id Donation ID.
	 *
	 * @return string $receipt Receipt URL
	 * @since 1.0
	 *
	 */
	public function get_pdf_receipt_url( $donation_id ) {

		$give_pdf_params = array(
			'give-action' => 'generate_pdf_receipt',
			'_give_hash'  => give_get_payment_key( $donation_id ),
			// This parameter is not nonce, instead transaction key.
		);

		// Add donation id to query arguments only if renewal.
		if (
			give_pdf_receipts_is_recurring_active()
			&& ( (int) give_get_meta( $donation_id, 'subscription_id', true ) || give_recurring_is_parent_donation( $donation_id ) )
		) {
			$give_pdf_params['recurring_donation_id'] = $donation_id;
		}

		$receipt = esc_url( add_query_arg( $give_pdf_params, give_get_history_page_uri() ) );

		return $receipt;
	}


	/**
	 * Creates Link to Download the Receipt.
	 *
	 * Creates a link on the Payment History admin page for each payment to
	 * allow the ability to download a receipt for that payment.
	 *
	 * @param array  $row_actions      All the row actions on the Payment History page.
	 * @param object $give_pdf_payment Payment object containing all the payment data.
	 *
	 * @return array Modified row actions with Download Receipt link
	 * @since 1.0
	 *
	 */
	public function receipt_link( $row_actions, $give_pdf_payment ) {

		// Return default row actions, if the conditional check fails.
		if (
			! is_give_pdf_receipts_disabled( $give_pdf_payment->ID ) &&
			! $this->is_receipt_link_allowed( $give_pdf_payment->ID )
		) {
			return $row_actions;
		}

		$row_actions['receipt'] = sprintf(
			'<a href="%1$s">%2$s</a>',
			$this->get_pdf_receipt_url( $give_pdf_payment->ID ),
			esc_html( give_pdf_receipts_download_pdf_text( false ) )
		);

		return $row_actions;
	}

	/**
	 * Per-form set pdf preview button.
	 *
	 * @param array $field Field array.
	 *
	 * @since 2.1.0
	 *
	 */
	public function per_form_set_pdf_preview_button( $field ) {
		global $thepostid, $post;

		// Get the Donation form id.
		$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;

		// Get the styles if passed with the field array.
		$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
		$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';

		// Get the option value by field and donation form id.
		$field['value'] = give_get_field_value( $field, $thepostid );

		// Generate name for option field.
		$field['name'] = isset( $field['name'] ) ? $field['name'] : $field['id'];

		?>
		<p class="give-field-wrap <?php echo esc_attr( $field['id'] ); ?>_field <?php echo esc_attr( $field['wrapper_class'] ); ?>">
			<label for="<?php echo give_get_field_name( $field ); ?>"><?php echo wp_kses_post( $field['name'] ); ?></label>
			<a href="<?php echo esc_url( add_query_arg( array(
				'give_pdf_receipts_action' => 'preview_set_pdf_template',
				'form_id'                  => $thepostid,
			), admin_url() ) ); ?>"
			   class="button-secondary" target="_blank"
			   title="<?php _e( 'Preview Set PDF Template', 'give-pdf-receipts' ); ?> "><?php _e( 'Preview Set PDF Template', 'give-pdf-receipts' ); ?></a>
			<?php
			echo give_get_field_description( $field );
			?>
		</p>
		<?php
	}

	/**
	 * PDF Receipt Download button under the Update Donation
	 *
	 * @param int $donation_id Donation ID.
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 */
	public function donation_detail_download_button( $donation_id ) {

		/**
		 * Fires in order details page, before the sidebar PDF Receipt Download button.
		 *
		 * @param int $donation_id Donation ID.
		 *
		 * @since 2.2.0
		 *
		 */
		do_action( 'give_view_donation_details_pdf_receipt_download_before', $donation_id );

		if (
			$this->is_receipt_link_allowed( $donation_id ) &&
			! is_give_pdf_receipts_disabled( $donation_id )
		) {
			?>
			<div id="major-publishing-actions">
				<div id="publishing-action">
					<?php
					echo sprintf(
						'<a href="%1$s" id="pdf-receipt-download" class="button-secondary right dashicons-download">%2$s</a>',
						$this->get_pdf_receipt_url( $donation_id ),
						esc_html( give_pdf_receipts_download_pdf_text( false ) )
					);
					?>
				</div>
				<div class="clear"></div>
			</div>
			<?php

			/**
			 * Fires in order details page, after the sidebar PDF Receipt Download button.
			 *
			 * @param int $donation_id Donation id.
			 *
			 * @since 2.2.0
			 *
			 */
			do_action( 'give_view_donation_details_pdf_receipt_download_after', $donation_id );
		} // End if().
	}

	/**
	 * This function will be used to add WPML support to PDF receipts.
	 *
	 * @param int $donation_id Donation ID.
	 *
	 * @return void
	 * @since 2.3.2
	 *
	 */
	public function add_wpml_support( $donation_id ) {
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			$lang = give_get_meta( $donation_id, 'wpml_language', true );
			if ( ! empty( $lang ) ) {
				global $sitepress;
				$sitepress->switch_lang( $lang );
			}
		}
	}

}
