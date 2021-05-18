<?php
namespace GiveFeeRecovery\Helpers\Form;

use function give_get_payment_meta as getDonationMetaData;

class Form{
	/**
	 * Return whether or not form accept fee.
	 *
	 * @param $formId
	 * @return bool
	 * @since 1.7.9
	 */
	public static function canRecoverFee( $formId ) {
		// Get the value of fee recovery enable or not.
		$optionValue = give_get_meta( $formId, '_form_give_fee_recovery', true );
		$optionValue = ! empty( $optionValue ) ? $optionValue : 'global';

		return give_is_setting_enabled( $optionValue ) ||
		       ( give_is_setting_enabled( $optionValue, 'global' ) &&
		         give_is_setting_enabled( give_get_option( 'give_fee_recovery' ) )
		       );
	}

	/**
	 * Return whether or not donation has fee.
	 *
	 * @param int $donationId
	 *
	 * @return mixed
	 * @since 1.7.9
	 */
	public static function hasFeeAmount( $donationId ) {
		return (bool) getDonationMetaData( $donationId, '_give_fee_amount', true );
	}

	/**
	 * Check whether the fee is enabled or not?
	 *
	 * Note: Pass `$formId` to check whether the fee is enabled for a form or not.
	 * Otherwise, it will return fee enabled or not globally.
	 *
	 * @param int    $formId Donation Form Id.
	 * @param string $for    Fee Enabled for? Global or Per Form.
	 *
	 * @since 1.8.0 add support for max fee coverage.
	 *
	 * @return bool
	 */
	public static function isFeeEnabled( $formId = 0, $for = 'global' ) {
		$isEnabled = give_is_setting_enabled( give_get_option( 'give_fee_recovery', 'disabled' ) );

		if ( $formId > 0 && 'form' === $for ) {
			$isEnabled = give_is_setting_enabled( give_get_meta( $formId, '_form_give_fee_recovery', true, 'global' ) );
		}

		return $isEnabled;
	}

	/**
	 * Check whether fee is supported with which payment gateway?
	 *
	 * @param int $formId Donation Form ID.
	 *
	 * @since 1.8.0 add support for max fee coverage.
	 *
	 * @return string
	 */
	public static function getFeeGatewaySupport( $formId = 0 ) {
		$feeGateway = give_get_option( 'give_fee_configuration', 'all_gateways' );

		if (
			$formId > 0 &&
			self::isFeeEnabled( $formId )
		) {
			$feeGateway = give_get_meta( $formId, '_form_give_fee_configuration', true, 'all_gateways' );
		}

		return $feeGateway;
	}

	/**
	 * This helper fn will be used to get maximum fee amount.
	 *
	 * @param int    $formId          Donation Form ID.
	 * @param string $selectedGateway Selected Payment Gateway.
	 *
	 * @since 1.8.0 add support for max fee coverage.
	 *
	 * @return float|int
	 */
	public static function getMaximumFeeAmount( $formId = 0, $selectedGateway = '' ) {
		$amount = give_get_option( 'give_fee_maximum_fee_amount', 0.00 );

		if ( ! empty( $selectedGateway ) && self::isFeeEnabled( $formId, 'form' ) ) {
			$amount = give_get_meta( $formId, "_form_gateway_fee_maximum_fee_amount_{$selectedGateway}", true, '0.00' );
		} else if ( self::isFeeEnabled( $formId, 'form' ) ) {
			$amount = give_get_meta( $formId, '_form_give_fee_maximum_fee_amount', true );
		} else if ( ! empty( $selectedGateway ) ) {
			$amount = give_get_option( "give_fee_gateway_fee_maximum_fee_amount_{$selectedGateway}", '0.00' );
		}

		return give_sanitize_amount_for_db( $amount );
	}

}
