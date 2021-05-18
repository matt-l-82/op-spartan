<?php
/**
 * Front-end functions
 *
 * @package     Give_PDF_Receipts
 * @subpackage  Functions
 * @copyright   Copyright (c) 2018, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.3.1
 */
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if Give - Recurring add-on is activate or not
 *
 * @since 2.3.1
 *
 * @return bool True if recurring add-on is activate or else false.
 */
function give_pdf_receipts_is_recurring_active() {
	$active = false;
	if ( class_exists( 'Give_Recurring' ) ) {
		$active = true;
	}

	return $active;
}

/**
 * Check if Give - Form Fields Manager add-on is activate or not
 *
 * @since 2.3.1
 *
 * @return bool True if ffm add-on is activate or else false.
 */
function give_pdf_receipts_is_ffm_active() {
	$active = false;
	if ( class_exists( 'Give_Form_Fields_Manager' ) ) {
		$active = true;
	}

	return $active;
}

/**
 * Replace tags in PDF receipts for recurring
 *
 * Note: This function is for internal purposes only.
 *
 * @since 2.3.1
 *
 * @param string $template_content Template content.
 * @param int    $payment_id Payment ID.
 *
 * @return string $template_content Template content.
 */
function give_pdf_receipts_replace_recurring_tags( $template_content, $payment_id ) {

	$recurring_email = new Give_Recurring_Emails();
	$tag_args        = array( 'payment_id' => $payment_id );

	$renewal_link            = $recurring_email::filter_email_tags( $tag_args, 'renewal_link' );
	$template_content        = str_replace( '{renewal_link}', $renewal_link, $template_content );
	$completion_date         = $recurring_email::filter_email_tags( $tag_args, 'completion_date' );
	$template_content        = str_replace( '{completion_date}', $completion_date, $template_content );
	$subscription_frequency  = $recurring_email::filter_email_tags( $tag_args, 'subscription_frequency' );
	$template_content        = str_replace( '{subscription_frequency}', $subscription_frequency, $template_content );
	$subscriptions_completed = $recurring_email::filter_email_tags( $tag_args, 'subscriptions_completed' );
	$template_content        = str_replace( '{subscriptions_completed}', $subscriptions_completed, $template_content );
	$cancellation_date       = $recurring_email::filter_email_tags( $tag_args, 'cancellation_date' );
	$template_content        = str_replace( '{cancellation_date}', $cancellation_date, $template_content );

	return $template_content;
}

/**
 * Replace tags in PDF receipts for FFM
 *
 * Note: This function is for internal purposes only.
 *
 * @since 2.3.1
 *
 * @param string $template_content Template content.
 * @param int    $payment_id Payment ID.
 *
 * @return string $template_content Template content.
 */
function give_pdf_receipts_replace_ffm_tags( $template_content, $payment_id ) {

	$ffm_email        = new Give_FFM_Emails();
	$template_content = str_replace( '{all_custom_fields}', $ffm_email->all_custom_email_tag( $payment_id ), $template_content );

	// get form id from payment id.
	$form_id = give_get_payment_form_id( $payment_id );

	// Get input field data.
	$ffm       = new Give_FFM_Render_Form();
	$form_data = $ffm->get_input_fields( $form_id );

	// Loop through form fields and match.
	foreach ( $form_data as $key => $value ) {

		if ( ! empty( $value ) ) {
			foreach ( $value as $field ) {

				// ignore section break and HTML input type.
				$ignore_type = array( 'section', 'html', 'action_hook', 'file_upload' );
				if ( isset( $field['name'] ) && isset( $field['input_type'] ) && in_array( $field['input_type'], $ignore_type ) ) {
					continue;
				}

				$field_name = "{meta_donation_{$field['name']}}";

				if ( false !== strpos( $template_content, $field_name ) ) {
					if ( isset( $field['columns'] ) && ! empty( $field['columns'][0] ) ) {
						$field_data = give_get_meta( $payment_id, $field['name'], false );
					} else {
						$field_data = give_get_meta( $payment_id, $field['name'], true );
					}

					// Check if repeater field data is being pulled and break it apart.
					if ( in_array( $field['input_type'], array( 'repeat', 'multiselect' ) ) ) {
						$field_value = implode( ',', explode( ' |', $field_data ) );
					} else {
						$field_value = $field_data;
					}
					$template_content = str_replace( $field_name, $field_value, $template_content );
				}
			}
		}
	}

	return $template_content;
}
