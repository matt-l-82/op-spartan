<?php
/**
 * Give - Stripe Premium | Admin Filters.
 *
 * @since      2.2.0
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
 * Plugins row action links
 *
 * @param array $actions An array of plugin action links.
 *
 * @return array An array of updated action links.
 * @since 1.5
 *
 */
function give_stripe_plugin_action_links( $actions ) {
	$new_actions = array(
		'settings' => sprintf(
			'<a href="%1$s">%2$s</a>',
			admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings' ),
			esc_html__( 'Settings', 'give-stripe' )
		),
	);

	return array_merge( $new_actions, $actions );
}

add_filter( 'plugin_action_links_' . GIVE_STRIPE_BASENAME, 'give_stripe_plugin_action_links' );


/**
 * Plugin row meta links
 *
 * @param array  $plugin_meta An array of the plugin's metadata.
 * @param string $plugin_file Path to the plugin file, relative to the plugins directory.
 *
 * @return array
 * @since 1.5
 *
 */
function give_stripe_plugin_row_meta( $plugin_meta, $plugin_file ) {

	if ( GIVE_STRIPE_BASENAME !== $plugin_file ) {
		return $plugin_meta;
	}

	$new_meta_links = array(
		sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_url(
				add_query_arg(
					array(
						'utm_source'   => 'plugins-page',
						'utm_medium'   => 'plugin-row',
						'utm_campaign' => 'admin',
					),
					'http://docs.givewp.com/addon-stripe'
				)
			),
			esc_html__( 'Documentation', 'give-stripe' )
		),
		sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_url(
				add_query_arg(
					array(
						'utm_source'   => 'plugins-page',
						'utm_medium'   => 'plugin-row',
						'utm_campaign' => 'admin',
					),
					'https://givewp.com/addons/'
				)
			),
			esc_html__( 'Add-ons', 'give-stripe' )
		),
	);

	return array_merge( $plugin_meta, $new_meta_links );
}

add_filter( 'plugin_row_meta', 'give_stripe_plugin_row_meta', 10, 2 );

/**
 * This function will change the connect banner text when manual API keys used.
 *
 * @return string
 * @since 2.2.0
 *
 */
function give_stripe_premium_change_connect_banner_text() {
	return __( 'GiveWP has implemented a more secure way to connect with Stripe.', 'give-stripe' );
}

add_filter( 'give_stripe_change_connect_banner_text', 'give_stripe_premium_change_connect_banner_text' );

/**
 * Add this filter when the `give_stripe_link_transaction_id` function exists.
 *
 * @since 2.2.6
 */
if ( function_exists( 'give_stripe_link_transaction_id' ) ) {
	add_filter( 'give_payment_details_transaction_id-stripe_ach', 'give_stripe_link_transaction_id', 10, 2 );
	add_filter( 'give_payment_details_transaction_id-stripe_apple_pay', 'give_stripe_link_transaction_id', 10, 2 );
	add_filter( 'give_payment_details_transaction_id-stripe_google_pay', 'give_stripe_link_transaction_id', 10, 2 );
	add_filter( 'give_payment_details_transaction_id-stripe_ideal', 'give_stripe_link_transaction_id', 10, 2 );
}
