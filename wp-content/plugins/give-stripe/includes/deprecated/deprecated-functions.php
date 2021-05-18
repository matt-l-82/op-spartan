<?php
/**
 * Deprecated Functions
 *
 * All functions that have been deprecated.
 *
 * @package     Give-Stripe
 * @subpackage  Deprecated
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This function is used to check whether the manual api keys are enabled or not.
 *
 * @since 2.2.0
 * @deprecated 2.2.6
 *
 * @return bool
 */
function give_stripe_is_manual_api_keys_enabled() {
	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '2.2.6', 'give_stripe_is_manual_api_keys_enabled', $backtrace );

	return give_is_setting_enabled( give_get_option( 'stripe_user_api_keys', 'disabled' ) );
}

/**
 * Check if the user has manually added keys.
 *
 * @since 2.2.0
 * @deprecated 2.2.6
 *
 * @return bool
 */
function give_stripe_connect_has_user_added_keys() {
	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '2.2.6', 'give_stripe_connect_has_user_added_keys', $backtrace );

	$live_secret          = give_get_option( 'live_secret_key' );
	$test_secret          = give_get_option( 'test_secret_key' );
	$live_publishable_key = give_get_option( 'live_publishable_key' );
	$test_publishable_key = give_get_option( 'test_publishable_key' );

	if (
		! empty( $live_secret )
		|| ! empty( $test_secret )
		|| ! empty( $live_publishable_key )
		|| ! empty( $test_publishable_key )
	) {
		return true;
	}

	return false;
}

/**
 * Check whether Apple Pay or Google pay settings is enabled or not.
 *
 * @since 1.6
 * @deprecated 2.2.6
 *
 * @return bool
 */
function give_stripe_is_apple_google_pay_enabled() {
	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '2.2.6', 'give_stripe_is_apple_google_pay_enabled', $backtrace );

	return give_is_setting_enabled( give_get_option( 'stripe_enable_apple_google_pay' ) );
}
