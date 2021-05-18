<?php

namespace GiveStripe\PaymentMethods\Plaid;

/**
 * Class SessionAccessor
 * @package GiveStripe\PaymentMethods\Plaid
 * @since 2.3.0
 */
class PlaidSessionAccessor {
	/**
	 * @var string
	 */
	private $sessionKey = 'give_plaid';

	/**
	 * @var \Give_Session
	 */
	private $session;

	/**
	 * SessionAccessor constructor.
	 * @since 2.3.0
	 */
	public function __construct() {
		$this->session = give( 'session' );
	}

	/**
	 * @since 2.3.0
	 *
	 * @param  string  $key
	 * @param  string|array  $default
	 *
	 * @return string|array
	 * @throws \Exception
	 */
	public function get( $key, $default = null ) {
		$sessionData = $this->session->get( $this->sessionKey, [] );

		if ( array_key_exists( $key, $sessionData ) ) {
			return $sessionData[ $key ];
		}

		return $default;
	}

	/**
	 * @since 2.3.0
	 *
	 * @param  string  $key
	 * @param  string|array  $value
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function set( $key, $value ) {
		$sessionData         = $this->session->get( $this->sessionKey, [] );
		$sessionData[ $key ] = $value;

		return $this->session->set( $this->sessionKey, $sessionData );
	}

	/**
	 * @since 2.3.0
	 *
	 * @param  string  $key
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function remove( $key ) {
		$sessionData = $this->session->get( $this->sessionKey, [] );

		unset( $sessionData[ $key ] );

		return $this->session->set( $this->sessionKey, $sessionData );
	}
}
