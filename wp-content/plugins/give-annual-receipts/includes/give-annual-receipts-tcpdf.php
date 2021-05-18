<?php
/**
 * Extend the TCPDF class to create custom Header and Footer.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Give_Annual_Receipts_PDF
 */
class Give_Annual_Receipts_PDF extends TCPDF {

	public $isLastPage = false;

	/**
	 * Function is used to print header in pdf.
	 *
	 * @since 1.0.0
	 */
	public function Header() {
		// Return if page is not first page.
		if ( $this->page > 1 ) {
			return;
		}
		$font_size       = apply_filters( 'give_annual_receipts_font_size', 14 );
		$form_font       = apply_filters( 'give_annual_receipts_font', 'dejavusans' );
		$form_font       = ( in_array( give_get_currency(), array( 'RIAL', 'RUB', 'IRR' ) ) ) ? 'CODE2000' : $form_font;
		$give_small_logo = GIVE_PLUGIN_DIR . 'assets/dist/images/give-logo-large.png';
		$this->SetFont( $form_font, '', $font_size, 'false' );
		$give_options = give_get_settings();
		$logo_URL     = ! empty( $give_options['give_annual_receipts_logo_upload'] ) ? $give_options['give_annual_receipts_logo_upload'] : $give_small_logo;

		$headerHtml   = '<table style="color: #333; width: 100%;" border="0">
					<tbody>
					<tr>
						<td align="center">
							<img src="' . $logo_URL . '" height="90" width="auto" />
						</td>
					</tr>
					</tbody>
					</table>';
		$this->SetY( 20 );
		$this->writeHTML( $headerHtml, true, false, true, false, 'C' );
	}

	/**
	 * Function is used to print footer in pdf.
	 *
	 * @since 1.0.0
	 */
	public function Footer() {
		// Return if not last page
		if ( ! $this->isLastPage ) {
			return;
		}
		$give_options = give_get_settings();
		$this->SetY( - 30 );
		$font_size = apply_filters( 'give_annual_receipts_font_size', 14 );
		$form_font = apply_filters( 'give_annual_receipts_font', 'dejavusans' );
		$form_font = ( in_array( give_get_currency(), array( 'RIAL', 'RUB', 'IRR' ) ) ) ? 'CODE2000' : $form_font;
		$this->SetFont( $form_font, '', $font_size, 'false' );
		$footerHtml = '<table style="font-family: sans-serif; font-size: 15px; line-height: 1.5; width: 100%; margin: 0 auto; color: #333;" border="0">
					<tbody>
					<tr>
						<td>' . nl2br( $give_options['give_annual_receipts_footer'] ) . '</td>
					</tr>
					<tr>
						<td style="font-size: 12px;">' . $give_options['give_annual_receipts_sub_footer'] . '</td>
					</tr>
					</tbody>
					</table>';
		$this->writeHTML( $footerHtml, true, false, true, false, 'C' );
	}

	/**
	 * Function is used to check if page is last or not
	 *
	 * @since 1.0.0
	 *
	 * @param bool $resetmargins
	 */
	public function lastPage( $resetmargins = false ) {
		$this->setPage( $this->getNumPages() );
		$this->isLastPage = true;
	}
}