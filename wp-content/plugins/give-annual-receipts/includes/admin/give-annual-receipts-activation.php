<?php
defined( 'ABSPATH' ) || exit;

/**
 * Show plugin dependency notice
 *
 * @since 1.0.0
 */
function __give_annual_receipts_dependency_notice() {
	// Admin notice.
	$message = sprintf(
		'<strong>%1$s</strong> %2$s <a href="%3$s" target="_blank">%4$s</a>  %5$s %6$s+ %7$s.',
		__( 'Activation Error:', 'give-annual-receipts' ),
		__( 'You must have', 'give-annual-receipts' ),
		'https://givewp.com',
		__( 'Give', 'give-annual-receipts' ),
		__( 'version', 'give-annual-receipts' ),
		GIVE_ANNUAL_RECEIPTS_MIN_GIVE_VERSION,
		__( 'for the Annual Receipts add-on to activate', 'give-annual-receipts' )
	);

	Give()->notices->register_notice( array(
		'id'          => 'give-activation-error',
		'type'        => 'error',
		'description' => $message,
		'show'        => true,
	) );
}

/**
 * Notice for No Core Activation
 *
 * @since 1.0.0
 */
function __give_annual_receipts_inactive_notice() {
	// Admin notice.
	$message = sprintf(
		'<div class="notice notice-error"><p><strong>%1$s</strong> %2$s <a href="%3$s" target="_blank">%4$s</a> %5$s.</p></div>',
		__( 'Activation Error:', 'give-annual-receipts' ),
		__( 'You must have', 'give-annual-receipts' ),
		'https://givewp.com',
		__( 'Give', 'give-annual-receipts' ),
		__( ' plugin installed and activated for the Annual Receipts add-on to activate', 'give-annual-receipts' )
	);

	echo $message;
}