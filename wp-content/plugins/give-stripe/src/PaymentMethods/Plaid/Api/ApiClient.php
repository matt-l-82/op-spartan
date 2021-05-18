<?php

namespace GiveStripe\PaymentMethods\Plaid\Api;

use GiveStripe\Framework\Log;
use GiveStripe\PaymentMethods\Plaid\Api\Exceptions\ApiRequestException;
use GiveStripe\PaymentMethods\Plaid\Api\Exceptions\RequestException;
use WP_Error;

use function esc_html__;
use function is_wp_error;
use function wp_remote_post;
use function wp_remote_retrieve_response_code;

/**
 * Class ApiClient
 * @package GiveStripe\PaymentMethods\Plaid
 * @since 2.3.0
 */
class ApiClient {
	/**
	 * @var Configuration
	 */
	private $configuration;

	/**
	 * @var array|WP_Error
	 */
	private $response;

	/**
	 * @var array
	 */
	private $bodyArguments = [];

	/**
	 * ApiClient constructor.
	 *
	 * @since 2.3.0
	 *
	 * @param  Configuration  $configuration
	 */
	public function __construct( Configuration $configuration ) {
		$this->configuration = $configuration;
	}

	/**
	 * @since 2.3.0
	 */
	public function get( $requestPath, $bodyArgument ) {
		$this->bodyArguments = $bodyArgument;
		$this->response      = wp_remote_post(
			$this->configuration->getApiUrl() . $requestPath,
			[
				'headers'     => [ 'Content-Type' => 'application/json; charset=utf-8' ],
				'data_format' => 'body',
				'body'        => json_encode( $bodyArgument ),
			]
		);

		$this->validateResponse();

		return $this;
	}

	/**
	 * @since 2.3.0
	 * @return array|\WP_Error
	 */
	public function getResponse() {
		return $this->response;
	}

	/**
	 * @since 2.3.0
	 * @return \stdClass
	 */
	public function getResponseBody() {
		return json_decode( wp_remote_retrieve_body( $this->response ) );
	}

	/**
	 * @since 2.3.0
	 * @throws RequestException
	 * @throws ApiRequestException
	 */
	public function validateResponse() {
		if ( is_wp_error( $this->response ) ) {
			Log::http(
				esc_html__( 'Plaid Api Request Error', 'give-stripe' ),
				[
					'category'      => 'Plaid Api Request',
					'Response'      => $this->response->get_error_message(),
					'bodyArguments' => $this->bodyArguments,
				]
			);

			throw new RequestException(
				$this->response->get_error_message(),
				$this->response->get_error_code()
			);
		}

		$responseBody = $this->getResponseBody();
		if ( property_exists( $responseBody, 'error_type' ) ) {
			Log::http(
				esc_html__( 'Plaid Api Error', 'give-stripe' ),
				[
					'category'      => 'Plaid Api Request',
					'Response'      => $this->response,
					'bodyArguments' => $this->bodyArguments,
				]
			);

			throw new ApiRequestException(
				$responseBody->display_message ?: $responseBody->error_message,
				wp_remote_retrieve_response_code( $this->response ),
				$responseBody
			);
		}
	}
}
