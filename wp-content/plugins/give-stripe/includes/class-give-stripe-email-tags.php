<?php
/**
 * Give Stripe - Email Tags
 *
 * @package    Give-Stripe
 * @subpackage Emails
 * @copyright  Copyright (c) 2016, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 * @since      2.0.8
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Stripe_Email_Tags
 *
 * @since 2.0.8
 */
class Give_Stripe_Email_Tags {

	/**
	 * Give_Stripe_Email_Tags constructor.
	 *
	 * @since  2.0.8
	 * @access public
	 */
	public function __construct() {

		add_action( 'give_add_email_tags', array( $this, 'give_stripe_setup_email_tags' ) );
	}

	/**
	 * Setup new email tags.
	 *
	 * @since  2.0.8
	 * @access public
	 */
	public function give_stripe_setup_email_tags() {

		give_add_email_tag( array(
			'tag'      => 'stripe_transaction_id',
			'desc'     => esc_html__( 'Stripe Transaction ID.', 'give-stripe' ),
			'func'     => array( $this, 'email_tag_stripe_transaction_id' ),
			'context'  => 'donation',
			'is_admin' => true,
		) );
	}

	/**
	 * Callback for email tag {stripe_transaction_id}
	 *
	 * @param array $tag_args List of email tag arguments.
	 *
	 * @since  2.0.8
	 * @access public
	 *
	 * @return string
	 */
	public function email_tag_stripe_transaction_id( $tag_args ) {

		$transaction_id   = '';
		$transaction_link = '';

		// Backward compatibility.
		$tag_args = __give_20_bc_str_type_email_tag_param( $tag_args );

		if ( give_check_variable( $tag_args, 'isset', 0, 'payment_id' ) ) {
			$transaction_id   = give_get_payment_transaction_id( $tag_args['payment_id'] );
			$transaction_link = give_stripe_get_transaction_link( $tag_args['payment_id'], $transaction_id );
		}

		if ( empty( $transaction_id ) ) {

			$gateways = give_get_enabled_payment_gateways();
			$transaction_link = sprintf(
				'Donation <a href="%1$s" target="_blank">%2$s</a> made with %3$s',
				esc_url_raw( admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=' . $tag_args['payment_id'] ) ),
				"#{$tag_args['payment_id']}",
				$gateways[ give_get_payment_gateway( $tag_args['payment_id'] ) ]['admin_label']
			);
		}

		/**
		 * Filter the {stripe_transaction_id} email tag output.
		 *
		 * @since 2.0.8
		 *
		 * @param string $transaction_link Stripe Transaction Link.
		 * @param string $transaction_id   Stripe Transaction ID.
		 * @param array  $tag_args         List of email tag arguments.
		 */
		$transaction_link = apply_filters( 'give_stripe_email_tag_transaction_link', $transaction_link, $transaction_id, $tag_args );


		return $transaction_link;
	}
}

new Give_Stripe_Email_Tags();
