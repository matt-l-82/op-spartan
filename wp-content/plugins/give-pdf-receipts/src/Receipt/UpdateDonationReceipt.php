<?php
namespace GivePdfReceipt\Receipt;


use Give\Receipt\DonationReceipt;
use Give\Receipt\UpdateReceipt;
use Give_PDF_Receipts;
use Give_PDF_Receipts_Engine;

/**
 * Class PdfReceiptDetailsGroup
 * @package GivePdfReceipt\Receipt
 *
 * @since 2.3.7
 */
class UpdateDonationReceipt extends UpdateReceipt {
	/**
	 * @var Give_PDF_Receipts_Engine
	 */
	private $pdfEngine;

	/**
	 * Apply changes to donation receipt.
	 *
	 * @since 2.3.7
	 */
	public function apply() {
		/* @var Give_PDF_Receipts_Engine $pdfEngine */
		$this->pdfEngine = Give_PDF_Receipts::get_instance()->engine;

		// Exit if we can not register receipt link line item.
		if( ! $this->canRegisterReceiptLinkLineItem() ) {
			return;
		}

		$section = $this->receipt->addSection([
			'id' => 'PDFReceipt',
		]);

		$section->addLineItem([
			'id' => 'receiptLink',
			'label' => esc_html__( 'Receipt', 'give-pdf-receipts' ),
			'value' => $this->getReceiptLink()
		]);
	}

	/**
	 * Return receipt link.
	 *
	 * @return string
	 */
	private function getReceiptLink() {
		$inlineScript = 'window.setTimeout( function(){ window.parentIFrame.sendMessage( { action: \'giveEmbedFormContentLoaded\' } ) }, 3000 )';

		return sprintf(
			'<a id="give-pdf-receipt-link" title="%3$s" href="%1$s" onclick="%4$s">%2$s</a>',
			esc_url( $this->pdfEngine->get_pdf_receipt_url( $this->receipt->donationId ) ),
			give_pdf_receipts_download_pdf_text( false ),
			give_pdf_receipts_download_pdf_text( false ),
			$inlineScript
		);
	}

	/**
	 * Flag to check whether or not we can register receipt link line item.
	 *
	 * @return bool
	 * @since 2.3.7
	 */
	private function canRegisterReceiptLinkLineItem(){
		// Sanity check.
		if ( ! $this->pdfEngine->is_receipt_link_allowed( $this->receipt->donationId ) ) {
			return false;
		}

		// Bail out, if PDF Receipt disable from Per-Form or disable globally (if globally enabled).
		if ( is_give_pdf_receipts_disabled( $this->receipt->donationId ) ) {
			return false;
		}

		return true;
	}
}
