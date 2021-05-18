<?php
namespace GiveFormFieldManager\Helpers;

use Give_FFM_Render_Form;
use function give_get_payment_form_id as getDonationFormId;
use function give_get_meta as getDonationMeta;

/**
 * Class Form
 *
 * @package GiveFormFieldManager\Helpers
 * @since 1.4.5
 */
class Form{
	/**
	 * Get custom fields from donation meta.
	 *
	 * @param int $donationId
	 *
	 * @return array
	 * @since 1.4.5
	 */
	public static function getSavedCustomFields( $donationId ) {
		$fields = [];

		// List of field types that we can ignore when returning the receipt detail.
		$skipFieldList = [ 'section', 'html', 'action_hook' ];

		$formId         = getDonationFormId( $donationId );
		$customFormFields = Give_FFM_Render_Form::get_input_fields( $formId );

		// Loop through form fields and match.
		foreach ( $customFormFields as $key => $value ) {

			if ( empty( $value ) ) {
				continue;
			}

			foreach ( $value as $field ) {

				// Double check this input type is set.
				if ( ! isset( $field['input_type'] ) ) {
					continue;
				}

				// Ignore section break and HTML input type.
				if ( in_array( $field['input_type'], $skipFieldList, true ) ) {
					continue;
				}

				// Whether to return a single value (complex repeaters return array).
				if ( isset( $field['columns'] ) && ! empty( $field['columns'][0] ) ) {
					$field_data = getDonationMeta( $donationId, $field['name'], false );
				} else {
					$field_data = getDonationMeta( $donationId, $field['name'], true );
				}

				// Only show fields with data.
				if ( empty( $field_data ) ) {
					continue;
				}

				// Handle various input types' output.
				switch ( $field['input_type'] ) {
					case 'textarea' :
						$field['value'] = nl2br( $field_data );
						break;

					case 'checkbox' :
					case 'radio' :
						$field['value'] = html_entity_decode( $field_data );
						break;

					default :
						$field['value'] = $field_data;
				}

				$fields[] = $field;
			}
		}

		return $fields;
	}
}
