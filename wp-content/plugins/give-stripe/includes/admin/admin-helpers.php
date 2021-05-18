<?php
/**
 * Give - Stripe Premium | Admin Helpers.
 *
 * @since 2.2.1
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
 * Retrieve API endpoint.
 *
 * @since 2.0.0
 *
 * @return string
 */
function give_stripe_ach_get_api_endpoint() {

	/**
	 * This hook filter the result of api endpoint.
	 *
	 * @since 2.0
	 */
	return apply_filters(
		'give_stripe_ach_get_api_endpoint',
		give_get_option( 'plaid_api_mode', 'sandbox' )
	);

}
