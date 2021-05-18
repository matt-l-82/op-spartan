<?php

namespace GiveStripe\PaymentMethods\Plaid\Api\Endpoints;

use GiveStripe\PaymentMethods\Plaid\Api\Exceptions\ApiRequestException;
use GiveStripe\PaymentMethods\Plaid\Api\Exceptions\RequestException;
use GiveStripe\PaymentMethods\Plaid\Api\ApiClient;

/**
 * Class Token
 * @package GiveStripe\PaymentMethods\Plaid\Api
 * @since 2.3.0
 */
class Token {
	/**
	 * @var ApiClient
	 */
	private $apiClient;

	/**
	 * Token constructor.
	 *
	 * @param  ApiClient  $apiClient
	 */
	public function __construct( ApiClient $apiClient ) {
		$this->apiClient = $apiClient;
	}

	/**
	 * @since 2.3.0
	 *
	 * @param  array  $bodyArguments
	 *
	 * @return \stdClass
	 * @throws RequestException|ApiRequestException
	 */
	public function getAchLink( $bodyArguments ) {
		return $this->apiClient
			->get( 'link/token/create', $bodyArguments )
			->getResponseBody();
	}
}
