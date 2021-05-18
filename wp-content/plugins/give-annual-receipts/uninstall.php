<?php
/**
 * Give Annual Receipts Uninstall
 *
 * @link              https://givewp.com
 * @since             1.0.0
 * @package           Give_Annual_Receipts
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete current version so activation runs if reactivated.
delete_option( 'give_annual_receipts_version' );

// Delete upgraded from option during uninstall.
delete_option( 'give_annual_receipts_upgraded_from' );
