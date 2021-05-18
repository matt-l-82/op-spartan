<?php
/**
 * Give - Stripe Premium | Apple Pay Integration
 *
 * @since 2.2.0
 *
 * @package    Give
 * @subpackage Stripe Premium
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

// Exit, if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give_Stripe_Apple_Pay' ) ) {

	/**
	 * Class Give_Stripe_Apple_Pay
	 *
	 * @since 2.2.0
	 */
	class Give_Stripe_Apple_Pay extends Give_Stripe_Gateway {

		/**
		 * Give_Stripe_Apple_Pay constructor.
		 *
		 * @since  2.2.0
		 * @access public
		 */
		public function __construct() {

			$this->id = 'stripe_apple_pay';

			parent::__construct();

			// Bailout, if Apple Pay is not an active payment gateway.
			if ( ! give_is_gateway_active( $this->id ) ) {
				return;
			}

			// Setup Error Messages.
			$this->errorMessages['accountConfiguredNoSsl']    = esc_html__( 'Apple Pay button is disabled because your site is not running securely over HTTPS.', 'give-stripe' );
			$this->errorMessages['accountNotConfiguredNoSsl'] = esc_html__( 'Apple Pay button is disabled because Stripe is not connected and your site is not running securely over HTTPS.', 'give-stripe' );
			$this->errorMessages['accountNotConfigured']      = esc_html__( 'Apple Pay button is disabled. Please connect and configure your Stripe account to accept donations.', 'give-stripe' );

			// Remove CC fields when this payment method is selected.
			add_action( 'give_stripe_apple_pay_cc_form', '__return_false' );
			add_action( 'give_donation_form_after_cc_form', array( $this, 'display_donate_button' ), 8898, 2 );

		}

		/**
		 * This function is used to display Google Pay donate button.
		 *
		 * @param mixed $content Default donate button content.
		 * @param int   $form_id Donation Form ID.
		 * @param array $args    List of essential arguments.
		 *
		 * @since  2.2.0
		 * @access public
		 *
		 * @return mixed
		 */
		public function display_donate_button( $form_id, $args ) {

			// Bailout, if not Apple Pay.
			if ( $this->id !== give_get_chosen_gateway( $form_id ) ) {
				return;
			}

			echo give_stripe_payment_request_donate_button( $form_id, $args, $this->canShowFields() );
		}

		/**
		 * This function is used for donation processing with Apple Pay via Stripe.
		 *
		 * @param array $donation_data List of donation data.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return void
		 */
		public function process_payment( $donation_data ) {

			// Bailout, if the current gateway and the posted gateway mismatched.
			if ( 'stripe_apple_pay' !== $donation_data['post_data']['give-gateway'] ) {
				return;
			}

			give_stripe_process_payment( $donation_data, $this );
		}
	}
}

new Give_Stripe_Apple_Pay();
