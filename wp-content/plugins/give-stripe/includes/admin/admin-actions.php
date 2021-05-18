<?php
/**
 * Give - Stripe Premium | Admin Actions.
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
 * Outputs the Stripe Customer ID on the donor profile if found.
 *
 * @since 1.6
 *
 * @param Give_Donor $donor
 */
function give_stripe_output_customer_id_on_donor_profile( $donor ) {
	$customer_id = give_stripe_get_customer_id( $donor->email );
	$test_mode   = give_is_test_mode() ? 'test/' : '';
	$url         = "https://dashboard.stripe.com/{$test_mode}customers/{$customer_id}";
	?>
	<div id="give-stripe-customer-id-wrap" class="donor-section clear">
		<div class="give-stripe-customer-id-inner postbox" style="padding:20px;">
			<form class="give-stripe-update-customer-id" method="post"
					action="<?php echo esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $donor->id ) ); ?>">
				<span class="stripe-customer-id-label"><?php esc_html_e( 'Stripe Customer ID', 'give-stripe' ) ?>:</span>

				<?php if ( ! empty( $customer_id ) ) : ?>
					<a href="<?php echo esc_url( $url ); ?>"
					   target="_blank"
					   class="give-stripe-customer-link"><?php echo $customer_id; ?></a>
				<?php else : ?>
					<span class="give-stripe-customer-link"><?php _e( 'None found', 'give-stripe' ) ?>
						<span class="give-tooltip give-icon give-icon-question"
							  data-tooltip="<?php esc_attr_e( 'This donor does not have a Stripe Customer ID. They likely made their donation(s) using another gateway. You can attach this donor to an existing Stripe Customer by updating this field.', 'give-stripe' ); ?>"></span>
					</span>
				<?php endif; ?>
				<input type="text" class="give-stripe-customer-id-input" name="give_stripe_customer_id"
					   value="<?php echo $customer_id; ?>"/>

				<a href="#"
				   class="button button-small give-stripe-customer-id-update"><?php esc_html_e( 'Update', 'give-stripe' ); ?></a>

				<span class="give-stripe-customer-submit-wrap">
						<button type="submit"
								class="button button-small give-stripe-customer-id-submit"><?php esc_html_e( 'Submit', 'give-stripe' ); ?></button>
						<a href="#"
						   class="button button-small give-stripe-customer-id-cancel"><?php esc_html_e( 'Cancel', 'give-stripe' ); ?></a>
					</span>

				<input type="hidden" name="donor_id" value="<?php echo $donor->id; ?>"/>
				<?php wp_nonce_field( 'edit-donor-stripe-customer-id', '_wpnonce', false, true ); ?>
				<input type="hidden" name="give_action" value="edit_stripe_customer_id"/>
			</form>

		</div>
	</div>
	<?php
}
add_action( 'give_donor_before_address', 'give_stripe_output_customer_id_on_donor_profile', 10, 1 );


/**
 * Updates the Stripe customer ID within the Give DB.
 *
 * @since 1.6
 *
 * @param $args
 *
 * @return bool
 */
function give_stripe_process_customer_id_update( $args ) {

	$donor_edit_role = apply_filters( 'give_edit_donors_role', 'edit_give_payments' );


	if ( ! is_admin() || ! current_user_can( $donor_edit_role ) ) {
		wp_die( __( 'You do not have permission to edit this donor.', 'give-stripe' ), __( 'Error', 'give-stripe' ), array(
			'response' => 403,
		) );
	}

	if ( empty( $args ) ) {
		return false;
	}

	$nonce = $args['_wpnonce'];

	if ( ! wp_verify_nonce( $nonce, 'edit-donor-stripe-customer-id' ) ) {
		wp_die( __( 'WP nonce verification failed.', 'give-stripe' ), __( 'Error', 'give-stripe' ), array(
			'response' => 400,
		) );
	}

	// Sanitize $_POST.
	$posted = give_clean( $_POST ); // WPCS: input var ok.

	$donor_id           = isset( $posted['donor_id'] ) ? $posted['donor_id'] : '';
	$stripe_customer_id = isset( $posted['give_stripe_customer_id'] ) ? $posted['give_stripe_customer_id'] : '';

	// Get the Give donor.
	$donor = new Give_Donor( $donor_id );

	// Update donor meta.
	$donor->update_meta( give_stripe_get_customer_key(), $stripe_customer_id );


}

add_action( 'give_edit_stripe_customer_id', 'give_stripe_process_customer_id_update', 10, 1 );


/**
 * This function will check that the Give test mode and the plaid api endpoint are in sync or not.
 *
 * @since 2.0
 */
function give_stripe_ach_api_endpoint_sync_notice() {

	// Proceed if Stripe + Plaid ACH Settings.
	if ( give_is_gateway_active( 'stripe_ach' ) ) {

		$post_data = give_clean( $_POST ); // WPCS: input var ok, sanitization ok, CSRF ok.

		$is_test_mode       = ! empty( $post_data['test_mode'] ) ? give_is_setting_enabled( $post_data['test_mode'] ) : give_is_test_mode();
		$is_plaid_api_mode  = ! empty( $post_data['plaid_api_mode'] ) ? $post_data['plaid_api_mode'] : give_stripe_ach_get_api_endpoint();
		$plaid_settings_url = esc_url( admin_url() . 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings&group=plaid' );

		if (
			$is_test_mode
			&& ( 'production' === $is_plaid_api_mode || 'development' === $is_plaid_api_mode )
		) {

			Give()->notices->register_notice( array(
				'id'          => 'give-stripe-incorrect-sync-api-mode',
				'type'        => 'warning',
				'description' => sprintf(
					/* translators: %s Plaid Settings URL */
					__( '<strong>Notice:</strong> You currently are using GiveWP in test mode but have Plaid\'s API in development/live mode. <a href="%1$s">Click here</a> to change the Plaid API mode.' , 'give-stripe' ),
					$plaid_settings_url
				),
			) );

		} elseif (
			! $is_test_mode
			&& 'sandbox' === $is_plaid_api_mode
		) {

			Give()->notices->register_notice( array(
				'id'          => 'give-stripe-incorrect-sync-api-mode',
				'type'        => 'warning',
				'description' => sprintf(
					/* translators: %s Plaid Settings URL */
					__( '<strong>Notice:</strong> You currently are using GiveWP in live mode but have Plaid\'s API in test mode. <a href="%1$s">Click here</a> to change the Plaid API mode.' , 'give-stripe' ),
					$plaid_settings_url
				),
			) );

		}
	} // End if().

}

add_action( 'admin_notices', 'give_stripe_ach_api_endpoint_sync_notice' );

/**
 * Display Recurring Add-on Update Notice.
 *
 * @since 2.0.6
 */
function give_stripe_display_minimum_recurring_version_notice() {

	if (
		defined( 'GIVE_RECURRING_PLUGIN_BASENAME' ) &&
		is_plugin_active( GIVE_RECURRING_PLUGIN_BASENAME )
	) {

		if (
			version_compare( GIVE_STRIPE_VERSION, '2.0.6', '>=' ) &&
			version_compare( GIVE_STRIPE_VERSION, '2.1', '<' ) &&
			version_compare( GIVE_RECURRING_VERSION, '1.7', '<' )
		) {
			Give()->notices->register_notice( array(
				'id'          => 'give-stripe-require-minimum-recurring-version',
				'type'        => 'error',
				'dismissible' => false,
				'description' => __( 'Please update the <strong>GiveWP Recurring Donations</strong> add-on to version 1.7+ to be compatible with the latest version of the Stripe payment gateway.', 'give-stripe' ),
			) );
		} elseif (
			version_compare( GIVE_STRIPE_VERSION, '2.1', '>=' ) &&
			version_compare( GIVE_RECURRING_VERSION, '1.8', '<' )
		) {
			Give()->notices->register_notice( array(
				'id'          => 'give-stripe-require-minimum-recurring-version',
				'type'        => 'error',
				'dismissible' => false,
				'description' => __( 'Please update the <strong>GiveWP Recurring Donations</strong> add-on to version 1.8+ to be compatible with the latest version of the Stripe payment gateway.', 'give-stripe' ),
			) );
		}
	}
}
add_action( 'admin_notices', 'give_stripe_display_minimum_recurring_version_notice' );

/**
 * This function will be useful to register admin notices.
 *
 * @since 2.0.8
 */
function give_stripe_register_admin_notices() {

	// Bailout.
	if ( ! is_admin() ) {
		return;
	}

	$get_data = give_clean( $_GET ); // WPCS: input var ok, sanitization ok, CSRF ok.

	// Bulk action notices.
	if (
		! empty( $get_data['post_type'] ) && 'give_forms' === $get_data['post_type'] &&
		! empty( $get_data['page'] ) && 'give-settings' === $get_data['page'] &&
		! empty( $get_data['tab'] ) && 'gateways' === $get_data['tab'] &&
		! empty( $get_data['section'] ) && 'stripe-settings' === $get_data['section']
	) {

		$message_notices = give_get_admin_messages_key();
		if ( current_user_can( 'manage_options' ) && ! empty( $message_notices ) ) {
			foreach ( $message_notices as $message_notice ) {
				switch ( $message_notice ) {
					case 'apple-pay-registration-error':
						Give()->notices->register_notice( array(
							'id'          => 'give-stripe-apple-pay-error',
							'type'        => 'error',
							'description' => sprintf(
								/* translators: %1$s Stripe Logs URL */
								__( 'An error occurred while registering your site domain with Apple Pay. Please <a href="%1$s">review the error</a> under the Stripe logs.', 'give-stripe' ),
								esc_url_raw( admin_url( 'edit.php?post_type=give_forms&page=give-tools&tab=logs&section=stripe' ) )
							),
							'show'        => true,
						) );
						break;

					case 'apple-pay-registration-success':
						Give()->notices->register_notice( array(
							'id'          => 'give-stripe-apple-pay-success',
							'type'        => 'updated',
							'description' => __( 'You have successfully registered your site domain. You can now begin accepting donations using Apple Pay via Stripe.', 'give-stripe' ),
							'show'        => true,
						) );
						break;
				}
			}
		}
	}
}

add_action( 'admin_notices', 'give_stripe_register_admin_notices', - 1 );

/**
 * This function is used to add Stripe account using manual API keys.
 *
 * @since 2.2.6
 *
 * @return void
 */
function give_stripe_add_manual_account() {
	// Bailout, if current user cannot manage settings.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$postData    = give_clean( $_POST );
	$allAccounts = give_stripe_get_all_accounts();
	$accountSlug = $postData['account_slug'];

	$allAccounts[ $accountSlug ] = [
		'type'                 => 'manual',
		'account_name'         => give_stripe_convert_slug_to_title( $accountSlug ),
		'account_slug'         => $accountSlug,
		'account_email'        => '',
		'account_country'      => '',
		'account_id'           => '', // This parameter will be empty for manual API Keys Stripe account.
		'live_secret_key'      => $postData['live_secret_key'],
		'test_secret_key'      => $postData['test_secret_key'],
		'live_publishable_key' => $postData['live_publishable_key'],
		'test_publishable_key' => $postData['test_publishable_key'],
	];

	// Store the updated Stripe accounts multi-dimesional array.
	give_update_option( '_give_stripe_get_all_accounts', $allAccounts );

	// Update default account to fetch the correct API details.
	give_update_option( '_give_stripe_default_account', $accountSlug );
	wp_send_json_success();
}

add_action( 'wp_ajax_give_stripe_add_manual_account', 'give_stripe_add_manual_account' );

/**
 * This function will be used to disconnect manual Stripe account.
 *
 * @param array $args List of arguments.
 *
 * @since 2.2.6
 *
 * @return void
 */
function give_stripe_disconnect_manual_stripe_account( $args ) {
	// Bailout, if current user don't have administrator access.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$account_slug = ! empty( $args['account'] ) ? $args['account'] : false;

	// Disconnect Stripe Account.
	give_stripe_disconnect_account( $account_slug );

	// Send user to Stripe settings page.
	give_stripe_get_back_to_settings_page();
}

add_action( 'give_disconnect_manual_stripe_account', 'give_stripe_disconnect_manual_stripe_account' );

/**
 * This function is used to register domain for Apple Pay.
 *
 * @since 2.2.6
 *
 * @return void
 */
function give_stripe_register_domain() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( [ 'message' => esc_html__( 'Unauthorized access.', 'give-stripe' ) ] );
	}

	$post_data    = give_clean( $_POST );
	$connect_args = [];
	$accounts     = give_stripe_get_all_accounts();
	$account_slug = ! empty( $post_data['slug'] ) ? $post_data['slug'] : false;
	$secret_key   = ! empty( $post_data['secret_key'] ) ? $post_data['secret_key'] : false;
	$type         = ! empty( $post_data['type'] ) ? $post_data['type'] : false;
	$account_id   = ! empty( $post_data['account_id'] ) ? $post_data['account_id'] : false;

	$data = [
		'domain_name' => $_SERVER['HTTP_HOST'],
	];

	try {

		// Set Live Secret Key to register domain to Apple Pay.
		\Stripe\Stripe::setApiKey( $secret_key );

		if ( 'connect' === $type ) {
			$connect_args = [
				'stripe_account' => $account_id,
			];
		}

		// Domain registration should be processed with LIVE secret keys.
		$response = \Stripe\ApplePayDomain::create( $data, $connect_args );

		if ( isset( $accounts[ $account_slug ] ) ) {
			$accounts[ $account_slug ]['register_apple_pay'] = true;
		}

		give_update_option( '_give_stripe_get_all_accounts', $accounts );

		wp_send_json_success( [ 'response' => $response ] );

	} catch ( Exception $e ) {

		$message = sprintf(
			/* translators: %s Exception Message Body */
			esc_html__( 'Unable to register domain association with Apple Pay. Details: %s', 'give-stripe' ),
			$e->getMessage()
		);

		// Record Log.
		give_stripe_record_log(
			esc_html__( 'Apple Pay Registration - Error', 'give-stripe' ),
			$message
		);

		// Send JSON Error.
		wp_send_json_error( [ 'message' => $message ] );

	} // End try().
}

add_action( 'wp_ajax_give_stripe_register_domain', 'give_stripe_register_domain' );

/**
 * This function is used to reset domain for Apple Pay.
 *
 * @since 2.2.6
 *
 * @return void
 */
function give_stripe_reset_domain() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( [ 'message' => esc_html__( 'Unauthorized access.', 'give-stripe' ) ] );
	}

	$post_data    = give_clean( $_POST );
	$account_slug = ! empty( $post_data['slug'] ) ? $post_data['slug'] : false;
	$accounts     = give_stripe_get_all_accounts();

	if ( isset( $accounts[ $account_slug ] ) ) {
		$accounts[ $account_slug ]['register_apple_pay'] = false;
	}

	give_update_option( '_give_stripe_get_all_accounts', $accounts );
	wp_send_json_success();
}

add_action( 'wp_ajax_give_stripe_reset_domain', 'give_stripe_reset_domain' );
