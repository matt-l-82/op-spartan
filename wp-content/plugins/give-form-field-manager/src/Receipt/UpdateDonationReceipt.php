<?php
namespace GiveFormFieldManager\Receipt;

use Give\Receipt\DonationReceipt;
use Give\Receipt\Section;
use Give\Receipt\UpdateReceipt;
use GiveFormFieldManager\Helpers\Form;

/**
 * Class UpdateReceiptGroup
 *
 * This class is responsible to register detail item and handle detail item object creation.
 *
 * @package GiveFormFieldManager\Receipt
 * @since 1.4.5
 */
class UpdateDonationReceipt extends UpdateReceipt {
	/**
	 * Apply changes
	 *
	 * @since 1.4.5
	 */
	public function apply(){
		/* @var Section $additionInformationSection */
		$additionInformationSection = $this->receipt[DonationReceipt::ADDITIONALINFORMATIONSECTIONID];
		$fields = Form::getSavedCustomFields( $this->receipt->donationId);

		$skipFieldList = [ 'file_upload', 'hidden' ];

		foreach ( $fields as $field ) {
			// Ignore section break and HTML input type.
			if ( in_array( $field['input_type'], $skipFieldList, true ) ) {
				continue;
			}

			$field['id'] = $field['name'];
			if( is_array( $field['value'] ) ) {
				$field['value'] = $this->convertArrayValueToHTMLTable( $field );
			}

			$additionInformationSection->addLineItem($field);
		}
	}

	/**
	 * Convert array dataset to HTML table.
	 *
	 * @param array $field
	 * @return string
	 * @since 1.4.5
	 */
	private function convertArrayValueToHTMLTable( $field ){
		$thead = '';
		if( $field['columns'] ) {
			$thead .= '<tr>';
			foreach ( $field['columns'] as $column ) {

				$thead .= '<th>';
				$thead .= $column;
				$thead .= '</th>';

			}
			$thead .= '</tr>';
		}

		$tbody = '';
		foreach ( $field['value'] as $data ) {
			$data = explode( '| ', $data );
			$tbody .= '<tr>';
			foreach ( $data as $th ) {
				$tbody .= '<td>';
				$tbody .= $th;
				$tbody .= '</td>';
			}
			$tbody .= '</tr>';
		}

		return sprintf(
			'<table class="give-table give-ffm-repeater-field"><thead>%1$s</thead><tbody>%2$s</tbody></table>',
			$thead,
			$tbody
		);
	}
}
