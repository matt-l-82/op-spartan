<?php
/**
 * Give - Stripe Premium | Frontend Actions.
 *
 * @since 2.2.0
 *
 * @package    Give
 * @subpackage Stripe Premium
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

// Bailout, if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This function is used to update application name as per the Stripe Premium Add-on.
 *
 * @param string $name Name of the application.
 *
 * @since 2.2.0
 *
 * @return string
 */
function give_stripe_premium_set_application_name( $name ) {
	return 'Give Stripe';
}
add_filter( 'give_stripe_get_application_name', 'give_stripe_premium_set_application_name' );

/**
 * This function is used to update application version as per the Stripe Premium Add-on.
 *
 * @param string $version Version of the application.
 *
 * @since 2.2.0
 *
 * @return string
 */
function give_stripe_premium_set_application_version( $version ) {
	return GIVE_STRIPE_VERSION;
}
add_filter( 'give_stripe_get_application_version', 'give_stripe_premium_set_application_version' );

/**
 * This function is used to add card details check based on the token received.
 *
 * @param \Stripe\Card        $new_card          New Card details.
 * @param string              $payment_method_id Source ID.
 * @param Give_Stripe_Gateway $stripe_gateway    Complete details about the Give_Stripe_Gateway class.
 *
 * @since 2.2.0
 *
 * @return \Stripe\Card
 */
function give_stripe_premium_add_card_details( $new_card, $payment_method_id, $stripe_gateway ) {

	if ( give_stripe_is_source_type( $payment_method_id, 'ba' ) || give_stripe_is_source_type( $payment_method_id, 'btok' ) ) {
		$token_details = $stripe_gateway->get_token_details( $payment_method_id );
		$new_card      = $token_details->bank_account;

		return $new_card;
	}

	return $new_card;

}
add_filter( 'give_stripe_get_new_card_details', 'give_stripe_premium_add_card_details', 10, 3 );

/**
 * This function will add support for Google/Apple Pay to set their respective browser based gateways as default.
 *
 * @param string $selected_gateway Selected Payment Gateway.
 *
 * @since 2.5.5
 *
 * @return string
 */
function give_stripe_add_payment_request_support( $selected_gateway ) {
	global $is_safari, $is_chrome;

	if ( $is_safari && 'stripe_google_pay' === $selected_gateway ) {
		return 'stripe_apple_pay';
	} elseif ( $is_chrome && 'stripe_apple_pay' === $selected_gateway ) {
		return 'stripe_google_pay';
	} else {
		return $selected_gateway;
	}
}
add_filter( 'give_chosen_gateway', 'give_stripe_add_payment_request_support' );
