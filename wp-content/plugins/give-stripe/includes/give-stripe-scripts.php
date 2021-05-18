<?php
/**
 * Give Stripe Scripts
 *
 * @package    Give
 * @subpackage Stripe Premium
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 * @since      1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load Admin javascript
 *
 * @since  1.0
 *
 * @return void
 */
function give_stripe_admin_js() {

	Give_Scripts::register_script( 'give-stripe-admin-js', GIVE_STRIPE_PLUGIN_URL . 'assets/dist/js/give-stripe-admin.js', 'jquery', GIVE_STRIPE_VERSION );
	wp_enqueue_script( 'give-stripe-admin-js' );

	wp_register_style( 'give-stripe-admin-css', GIVE_STRIPE_PLUGIN_URL . 'assets/dist/css/give-stripe-admin.css', false, GIVE_STRIPE_VERSION );
	wp_enqueue_style( 'give-stripe-admin-css' );

}

add_action( 'admin_enqueue_scripts', 'give_stripe_admin_js', 100 );

/**
 * Load Admin javascript
 *
 * @since  1.0
 *
 * @return void
 */
function give_stripe_frontend_assets() {

	/**
	 * Bailout, if Stripe account is not configured.
	 *
	 * We are not loading any scripts if Stripe account is not configured to avoid an intentional console error
	 * for Stripe integration.
	 */
	if ( ! Give\Helpers\Gateways\Stripe::isAccountConfigured() ) {
		return;
	}

	// Apple Pay, Google Pay, and Checkout uses payment request API.
	if (
		give_is_gateway_active( 'stripe_google_pay' ) ||
		give_is_gateway_active( 'stripe_apple_pay' ) ||
		give_is_gateway_active( 'stripe_checkout' )
	) {
		Give_Scripts::register_script( 'give-stripe-payment-request-js', GIVE_STRIPE_PLUGIN_URL . 'assets/dist/js/give-stripe-payment-request.js', 'jquery', GIVE_STRIPE_VERSION );
		wp_enqueue_script( 'give-stripe-payment-request-js' );
	}
}

add_action( 'wp_enqueue_scripts', 'give_stripe_frontend_assets' );

/**
 * Load Stripe vars with external JS.
 *
 * @since 2.2.0
 *
 * @return bool
 */
function give_stripe_premium_load_external_js( $is_gateway_active ) {
	return (
		$is_gateway_active ||
		give_is_gateway_active( 'stripe_google_pay' ) ||
		give_is_gateway_active( 'stripe_apple_pay' )
	);
}

add_filter( 'give_stripe_js_loading_conditions', 'give_stripe_premium_load_external_js' );
