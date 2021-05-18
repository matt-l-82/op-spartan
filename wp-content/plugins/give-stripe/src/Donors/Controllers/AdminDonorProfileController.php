<?php

namespace GiveStripe\Donors\Controllers;

use Give_Donor;
use GiveStripe\Donors\Views\DonorProfilePlaidSection;
use GiveStripe\PaymentMethods\Plaid\Repositories\Donors;

/**
 * Class AdminDonorProfileController
 * @package GiveStripe\Donors\Controllers
 *
 * Handles requests for the Donor Profile in the admin-side
 */
class AdminDonorProfileController {
	/**
	 * Renders the Plaid section of the profile
	 *
	 * @since 2.3.0
	 *
	 * @param Give_Donor $donor
	 */
	public function renderPlaidSection( Give_Donor $donor ) {
		/** @var Donors $donorRepository */
		$donorRepository = give( Donors::class );

		$plaidClientUserId = $donorRepository->getClientUserId( $donor->id );

		if ( empty( $plaidClientUserId ) ) {
			return;
		}

		$view = new DonorProfilePlaidSection();
		echo $view( $donor, $plaidClientUserId );
	}
}
