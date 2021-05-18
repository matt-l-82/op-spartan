<?php
namespace GiveStripe\PaymentMethods\Plaid\Repositories;

/**
 * Class Plaid
 * @package GiveStripe\PaymentMethods\Plaid
 * @since 2.3.0
 */
class Plaid {
	/**
	 * @since 2.3.0
	 * @return string
	 */
	public function getApiMode(){
		return give_get_option( 'plaid_api_mode', 'sandbox' );
	}

	/**
	 * @since 2.3.0
	 * @return string
	 */
	public function getClientId(){
		return give_get_option( 'plaid_client_id', '' );
	}

	/**
	 * @since 2.3.0
	 * @return string
	 */
	public function getClientSecretKey(){
		return give_get_option( 'plaid_secret_key', '' );
	}
}
