<?php

namespace GiveStripe\PaymentMethods\Plaid\Api\Exceptions;

use Exception;
use stdClass;

/**
 * Class RequestException
 * @package GiveStripe\PaymentMethods\Plaid\Api\Exceptions
 */
class RequestException extends Exception {
	/**
	 * @var stdClass
	 */
	private $response;

	/**
	 * RequestException constructor.
	 *
	 * @param  string  $message
	 * @param  int  $code
	 * @param  stdClass|null  $response
	 */
	public function __construct( $message, $code, stdClass $response = null ) {
		$this->response = $response;
		parent::__construct( $message, $code );
	}

	/**
	 * @since 2.3.0
	 * @return stdClass
	 */
	public function getResponse() {
		return $this->response;
	}
}
