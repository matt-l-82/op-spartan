<?php

namespace GiveStripe\PaymentMethods\Plaid\Api;

use GiveStripe\PaymentMethods\Plaid\Repositories\Plaid as PlaidRepository;

use function trailingslashit;

/**
 * Class Configuration
 * @package GiveStripe\PaymentMethods\Plaid
 */
class Configuration {
	private $apiUrl = 'https://{mode}.plaid.com/';
	private $mode;

	/**
	 * @var PlaidRepository
	 */
	private $plaidRepository;

	/**
	 * Configuration constructor.
	 * @since 2.3.0
	 */
	public function __construct( PlaidRepository $plaidRepository ) {
		$this->plaidRepository = $plaidRepository;
		$this->mode = $this->plaidRepository->getApiMode();
	}

	/**
	 * @since 2.3.0
	 * @return string
	 */
	public function getMode(){
		return $this->mode;
	}

	/**
	 * @since 2.3.0
	 * @return string
	 */
	public function getApiUrl(){
		return trailingslashit( str_replace( '{mode}', $this->mode, $this->apiUrl ) );
	}
}
