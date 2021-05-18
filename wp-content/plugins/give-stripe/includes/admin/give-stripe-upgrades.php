<?php
/**
 * Stripe Upgrades.
 *
 * @package     Give
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Stripe_Upgrades
 *
 * @since  1.2
 */
class Give_Stripe_Upgrades {

	/**
	 * Give_Stripe_Upgrades constructor.
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'do_automatic_upgrades' ), 0 );
		add_action( 'give_upgrades', array( $this, 'do_automatic_upgrades' ), 0 );

	}

	/**
	 * Do automatic database upgrades when necessary.
	 *
	 * @since  2.2.0
	 *
	 * @return void
	 */
	public function do_automatic_upgrades() {

		$did_upgrade         = false;
		$give_stripe_version = preg_replace( '/[^0-9.].*/', '', get_option( 'give_stripe_version' ) );

		if ( ! $give_stripe_version ) {
			// 1.0 is the first version to use this option so we must add it.
			$give_stripe_version = '1.0';
		}

		switch ( true ) {
			case version_compare( $give_stripe_version, '1.2', '<' ):
				// No version option saved.
				$this->update_v12_preapproval_metakey();
				$did_upgrade = true;
				break;

			case version_compare( $give_stripe_version, '1.3', '<' ):
				// Ensure people who update from previous version have billing fields undisturbed.
				$this->update_v13_default_billing_fields();
				$did_upgrade = true;
				break;

			case version_compare( $give_stripe_version, '1.5', '<' ):
				// Ensure that Stripe checkout options are updated properly.
				$this->update_v15_update_options();
				$did_upgrade = true;
				break;

			case version_compare( $give_stripe_version, '2.0.4', '<' ):
				// Update Stripe + Plaid API Key Mode to production instead of live.
				$this->update_v204_update_api_key_mode_options();
				$did_upgrade = true;
				break;

			case version_compare( $give_stripe_version, '2.2.0', '<' ):
				// Ensure that Apple/Google Pay gateways are enabled based on the settings.
				$this->update_v220_enable_apple_google_pay();
				$did_upgrade = true;
				break;
			case version_compare( $give_stripe_version, '2.2.6', '<' ):
				$this->update_v226_flush_rules_for_domain_association_file();
				$did_upgrade = true;
				break;

			case version_compare( $give_stripe_version, '2.2.9', '<' ):
				give_delete_option('stripe_preapprove_only');
				$did_upgrade = true;
				break;
		}

		if ( $did_upgrade || version_compare( $give_stripe_version, GIVE_STRIPE_VERSION, '<' ) ) {
			update_option( 'give_stripe_version', preg_replace( '/[^0-9.].*/', '', GIVE_STRIPE_VERSION ), false );
		}
	}

	/**
	 * Update 1.2 Preapproval Metakey.
	 *
	 * Updates a required metakey value due to a typo causing a bug.
	 *
	 * @see        : https://github.com/impress-org/give-stripe/pull/1 and https://github.com/impress-org/give-stripe/pull/2
	 */
	private function update_v12_preapproval_metakey() {

		global $wpdb;
		$sql = "UPDATE $wpdb->postmeta SET `meta_key` = '_give_stripe_customer_id' WHERE `meta_key` LIKE '_give_stripe_stripe_customer_id'";
		$wpdb->query( $sql );

	}

	/**
	 * Update 1.3 Collect Billing Details
	 *
	 * Sets the default option to display Billing Details as to not mess with any donation forms without consent
	 *
	 * @see https://github.com/impress-org/give-stripe/issues/11
	 */
	private function update_v13_default_billing_fields() {

		give_update_option( 'stripe_collect_billing', 'on' );

	}

	/**
	 * Update 1.5 Update Options
	 *
	 * Sets the default option to display Billing Details as to not mess with any donation forms without consent
	 *
	 * @see https://github.com/impress-org/give-stripe/issues/11
	 */
	private function update_v15_update_options() {

		give_update_option( 'stripe_checkout_zip_verify', 'on' );
		give_update_option( 'stripe_checkout_remember_me', 'on' );

	}

	/**
	 * Update options for 2.0.4
	 *
	 * Set Plaid API mode for LIVE to PRODUCTION
	 *
	 * @since 2.0.4
	 *
	 * @see https://github.com/impress-org/give-stripe/issues/199
	 */
	private function update_v204_update_api_key_mode_options() {
		$plaid_api_mode = give_get_option( 'plaid_api_mode' );
		if ( 'live' === $plaid_api_mode ) {
			give_update_option( 'plaid_api_mode', 'production' );
		}
	}

	/**
	 * This upgrade function will enable the separate Apple/Google Pay payment gateways for existing users who have enabled it.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	private function update_v220_enable_apple_google_pay() {

		if ( give_is_setting_enabled( give_get_option( 'stripe_enable_apple_google_pay', 'disabled' ) ) ) {

			$enabled_gateways = give_get_option( 'gateways', array() );

			// Set Apple and Google Pay as active gateway.
			$enabled_gateways['stripe_google_pay'] = 1;
			$enabled_gateways['stripe_apple_pay']  = 1;

			// Update the enabled gateways in database.
			give_update_option( 'gateways', $enabled_gateways );
		}
	}

	/**
	 * Flush Rewrite Rules to ensure that the domain association file loads properly.
	 *
	 * @since  2.2.6
	 * @access public
	 *
	 * @return void
	 */
	public function update_v226_flush_rules_for_domain_association_file() {
		flush_rewrite_rules();
	}
}

return new Give_Stripe_Upgrades();
