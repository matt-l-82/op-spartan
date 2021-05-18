<?php

namespace GiveStripe\PaymentMethods\Plaid\Controllers;

use Give\Session\SessionDonation\DonationAccessor;
use GiveStripe\PaymentMethods\Plaid\PlaidSessionAccessor;
use GiveStripe\PaymentMethods\Plaid\Repositories\Donors;
use GiveStripe\PaymentMethods\Plaid\Repositories\Donors as DonorRepository;

/**
 * Class AttachClientIdToDonorHandler
 * @package GiveStripe\PaymentMethods\Plaid\Controllers
 * @since 2.3.0
 */
class AttachClientIdToDonorHandler {
	/**
	 * @var PlaidSessionAccessor
	 */
	private $plaidSessionAccessor;

	/**
	 * @var DonationAccessor
	 */
	private $donationSessionAccessor;

	/**
	 * AttachClientIdToDonorController constructor.
	 *
	 * @since 2.3.0
	 *
	 * @param  PlaidSessionAccessor  $plaidSessionAccessor
	 * @param  DonationAccessor  $donationSessionAccessor
	 */
	public function __construct( PlaidSessionAccessor $plaidSessionAccessor, DonationAccessor $donationSessionAccessor ) {
		$this->plaidSessionAccessor    = $plaidSessionAccessor;
		$this->donationSessionAccessor = $donationSessionAccessor;
	}

	/**
	 * @since 2.3.0
	 */
	public function handle() {
		$clientUserId = $this->plaidSessionAccessor->get( DonorRepository::PLAID_CLIENT_USER_ID );

		if( ! $clientUserId ) {
			return;
		}

		$this->plaidSessionAccessor->remove( DonorRepository::PLAID_CLIENT_USER_ID );
		$donorId = give_get_payment_donor_id( $this->donationSessionAccessor->getDonationId() );
		$donor = give()->donors->get_by( 'id', $donorId );

		if ( ! $donor ) {
			return;
		}

		give()->donor_meta->update_meta( $donor->id, Donors::PLAID_CLIENT_USER_ID, $clientUserId );
	}
}
