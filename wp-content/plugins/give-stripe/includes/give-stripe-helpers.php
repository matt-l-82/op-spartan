<?php
/**
 * Stripe Helper Functions
 *
 * @package     Give
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Endpoint URL by Token Type.
 *
 * @param string $token_type Get endpoint URL based on token type provided.
 *
 * @return string
 * @since 1.6
 *
 */
function give_stripe_ach_get_endpoint_url( $token_type = 'exchange' ) {

	$endpoint_url = esc_url( 'https://%1$s.plaid.com/item/public_token/exchange' );

	if ( 'bank_account' === $token_type ) {
		$endpoint_url = esc_url( 'https://%1$s.plaid.com/processor/stripe/bank_account_token/create' );
	}

	return sprintf(
		$endpoint_url,
		give_stripe_ach_get_api_endpoint()
	);
}

/**
 * Get Stripe ACH (Plaid) API Version.
 *
 * @return string
 * @since 1.6
 *
 */
function give_stripe_ach_get_current_api_version() {

	// Current API Version: v2.
	return 'v2';
}

/**
 * Get Plaid Checkout URL.
 *
 * @return string
 * @since 1.6
 *
 */
function give_stripe_ach_get_plaid_checkout_url() {
	return sprintf(
		esc_url( 'https://cdn.plaid.com/link/%1$s/stable/link-initialize.js' ),
		give_stripe_ach_get_current_api_version()
	);
}

/**
 * Get Plaid Link token.
 *
 * @since 2.3.0
 * @deprecated
 */
function give_stripe_get_ach_link_token() {

	$args = [
			'client_id'     => trim( give_get_option( 'plaid_client_id' ) ),
			'secret'        => trim( give_get_option( 'plaid_secret_key' ) ),
			'client_name'   => get_bloginfo( 'sitename' ),
			'user'          => [ 'client_user_id' => get_bloginfo( 'admin_email' ) ],
			'products'      => [ 'transactions' ],
			'country_codes' => [ 'US' ],
			'language'      => 'en',
	];

	$mode = give_get_option( 'plaid_api_mode', 'sandbox' );

	$response = wp_remote_post(
			sprintf( 'https://%s.plaid.com/link/token/create', $mode ),
			[
					'headers' => [ 'Content-Type' => 'application/json; charset=utf-8' ],
					'body'    => json_encode( $args ),
			]
	);


	$response = json_decode( wp_remote_retrieve_body( $response ) );

	if ( isset( $response->error_code ) ) {
		give_record_gateway_error(
				esc_html__( 'Plaid API Error', 'give-stripe' ),
				sprintf(
				/* translators: %s Error Message */
						__( 'An error occurred when processing a donation via Plaid\'s API. Details: %s', 'give-stripe' ),
						"{$response->error_code} (error code) - {$response->error_type} (error type) - {$response->error_message}"
				)
		);
		give_set_error( 'stripe_ach_request_error', esc_html__( 'There was an API error received from the payment gateway. Please try again.', 'give-stripe' ) );
		give_send_back_to_checkout( '?payment-mode=stripe_ach' );

		wp_send_json_error( $response );
	}

	wp_send_json_success( $response );

}


/**
 * This function is used to display payment request donate button.
 *
 * @param int   $form_id    Donation Form ID.
 * @param array $args       List of essential arguments.
 * @param bool  $showFields Whether to show fields or not.
 *
 * @return mixed
 * @since 2.2.0
 *
 */
function give_stripe_payment_request_donate_button( $form_id, $args, $showFields ) {

	// Disable showing default donate button.
	remove_action( 'give_donation_form_after_cc_form', 'give_checkout_submit', 9999 );

	$id_prefix       = ! empty( $args['id_prefix'] ) ? $args['id_prefix'] : 0;
	$user_agent      = give_get_user_agent();
	$selectedGateway = give_get_chosen_gateway( $form_id );
	ob_start();
	?>
	<fieldset id="give_purchase_submit" class="give-donation-submit">
		<?php
		/**
		 * Fire before donation form submit.
		 *
		 * @since 2.2.0
		 */
		do_action( 'give_donation_form_before_submit', $form_id, $args );

		give_checkout_hidden_fields( $form_id );

		if (
			'stripe_google_pay' === $selectedGateway ||
			'stripe_apple_pay' === $selectedGateway
		) {
			if ( $showFields ) {
				echo give_stripe_payment_request_button_markup( $form_id, $args );
			}
		} else {
			// Default to Give Core method.
			echo give_get_donation_form_submit_button( $form_id, $args );
		}

		/**
		 * Fire after donation form submit.
		 *
		 * @since 2.2.0
		 */
		do_action( 'give_donation_form_after_submit', $form_id, $args );
		?>
	</fieldset>
	<?php

	return ob_get_clean();
}

/**
 * Load Payment Request Button Markup.
 *
 * @param int   $formId Donation Form ID.
 * @param array $args   List of additional arguments.
 *
 * @since 2.2.12
 *
 * @return void|mixed
 */
function give_stripe_payment_request_button_markup( $formId, $args ) {
	ob_start();
	$id_prefix  = ! empty( $args['id_prefix'] ) ? $args['id_prefix'] : 0;
	$user_agent = give_get_user_agent();
	?>
	<div id="give-stripe-payment-request-button-<?php echo esc_html( $id_prefix ); ?>" class="give-stripe-payment-request-button give-hidden">
		<div class="give_error">
			<p>
				<strong><?php esc_attr_e( 'ERROR:', 'give-stripe' ); ?></strong>
				<?php
				if ( ! is_ssl() ) {
					esc_attr_e( 'In order to donate using Apple or Google Pay the connection needs to be secure. Please visit the secure donation URL (https) to give using this payment method.', 'give-stripe' );
				} elseif ( preg_match( '/Chrome[\/\s](\d+\.\d+)/', $user_agent ) ) {
					esc_attr_e( 'Either you do not have a saved card to donate with Google Pay or you\'re using an older version of Chrome without Google Pay support.', 'give-stripe' );
				} elseif ( preg_match( '/Safari[\/\s](\d+\.\d+)/', $user_agent ) ) {
					esc_attr_e( 'Either your browser does not support Apple Pay or you do not have a saved payment method.', 'give-stripe' );
				}
				?>
			</p>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
