<?php
/**
 * Give - Stripe Premium | Frontend Actions.
 *
 * @since 2.2.9
 *
 * @package    Give
 * @subpackage Stripe Premium
 * @copyright  Copyright (c) 2020, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

// Bailout, if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load Payment Request Button on Stripe Checkout Modal.
 *
 * @param int   $formId Donation Form ID.
 * @param array $args   List of additional arguments.
 *
 * @since 2.2.9
 *
 * @return void|mixed
 */
function give_stripe_add_payment_request_to_checkout( $formId, $args ) {
	$user_agent = give_get_user_agent();

	if (
		(
			preg_match( '/Chrome[\/\s](\d+\.\d+)/', $user_agent ) &&
			! preg_match( '/Edg[\/\s](\d+\.\d+)/', $user_agent )
		) ||
		(
			preg_match( '/Safari[\/\s](\d+\.\d+)/', $user_agent ) &&
			! preg_match( '/Edg[\/\s](\d+\.\d+)/', $user_agent )
		)
	) {
	// Load Payment Request Button Markup.
	echo give_stripe_payment_request_button_markup( $formId, $args );
	?>
	<div class="give-stripe-checkout-modal-else-part">
		<hr/>
		<?php esc_html_e( 'or Pay with Card', 'give-stripe' ); ?>
	</div>
	<?php
	}
}

add_action( 'give_stripe_checkout_modal_before_cc_fields', 'give_stripe_add_payment_request_to_checkout', 10, 2 );

