<?php

namespace GiveStripe\PaymentMethods\Plaid\Controllers;

use Give_Notices;
use GiveStripe\PaymentMethods\Plaid\Api\Endpoints\Token as TokenApiClient;
use GiveStripe\PaymentMethods\Plaid\Repositories\Donors;
use GiveStripe\PaymentMethods\Plaid\Repositories\Plaid as PlaidRepository;
use Exception;
use GiveStripe\PaymentMethods\Plaid\PlaidSessionAccessor;

/**
 * Class AchLinkTokenController
 * @package GiveStripe\PaymentMethods\Plaid\Controllers
 * @since 2.3.0
 */
class AchLinkTokenController {
	/**
	 * @var PlaidRepository
	 */
	private $plaidRepository;

	/**
	 * @var TokenApiClient
	 */
	private $tokenApiClient;

	/**
	 * @var Donors
	 */
	private $donorRepository;

	/**
	 * @var string
	 */
	private $clientUserId = '';

	/**
	 * @var \stdClass
	 */
	private $donor;

	/**
	 * @var bool
	 */
	private $clientUserIdAlreadyExist = false;

	/**
	 * AchLinkTokenController constructor.
	 *
	 * @param  PlaidRepository $plaidRepository
	 * @param  TokenApiClient  $tokenApiClient
	 * @param  Donors          $donorRepository
	 */
	public function __construct(
		PlaidRepository $plaidRepository,
		TokenApiClient $tokenApiClient,
		Donors $donorRepository
	) {
		$this->plaidRepository = $plaidRepository;
		$this->tokenApiClient  = $tokenApiClient;
		$this->donorRepository = $donorRepository;
	}

	/**
	 * @since 2.3.0
	 * @return self
	 */
	public function setupDonor() {
		if ( is_user_logged_in() ) {
			/* @var \WP_User $user */
			$user        = wp_get_current_user();
			$this->donor = give()->donors->get_donor_by( 'user_id', $user->ID );
		} else {
			$donorEmail  = give_clean( $_POST['donor-email'] );
			$this->donor = give()->donors->get_donor_by( 'email', $donorEmail );
		}

		return $this;
	}

	/**
	 * @since 2.3.0
	 * @return self
	 */
	public function setupClientUserId() {
		if ( $this->donor ) {
			$this->clientUserId             = $this->donorRepository->getClientUserId( $this->donor->id );
			$this->clientUserIdAlreadyExist = (bool) $this->clientUserId;
		}

		$this->clientUserId = $this->clientUserId ?: wp_generate_password();

		return $this;
	}

	/**
	 * @since 2.3.0
	 * @throws Exception
	 */
	private function setupSession() {
		if ( $this->clientUserIdAlreadyExist ) {
			return;
		}

		/* @var \Give_Session $session */
		$session = give( 'session' );
		$session->maybe_start_session();

		$sessionAccessor = new PlaidSessionAccessor();
		$sessionAccessor->set( Donors::PLAID_CLIENT_USER_ID, $this->clientUserId );
	}

	/**
	 * Handle ajax request to return ach tokenize link.
	 *
	 * @since 2.3.0
	 */
	public function handle() {
		$this->setupDonor()
		     ->setupClientUserId();

		try {
			$response = $this->tokenApiClient->getAchLink( $this->getApiQueryArguments() );
		} catch ( Exception $e ) {
			wp_send_json_error(
				[
					'error_message' => Give_Notices::print_frontend_notice(
						esc_html__(
							'There was an API error received from the payment gateway. Please try again.',
							'give-stripe'
						),
						false,
						'error'

					),
				]
			);
		}

		$this->setupSession();
		wp_send_json_success( $response );
	}

	/**
	 * @since 2.3.0
	 * @return array
	 */
	private function getApiQueryArguments() {
		return [
			'client_id'     => $this->plaidRepository->getClientId(),
			'secret'        => $this->plaidRepository->getClientSecretKey(),
			'client_name'   => get_bloginfo( 'sitename' ),
			'user'          => [
				'client_user_id' => $this->clientUserId,
			],
			'products'      => [ 'transactions' ],
			'country_codes' => [ 'US' ],
			'language'      => 'en',
		];
	}
}
