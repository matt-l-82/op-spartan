<?php

namespace GiveStripe\Donors\Views;

use Give_Donor;

/**
 * Class DonorProfilePlaidSection
 * @package GiveStripe\Donor\Views
 *
 * Renders the Plaid Section on the Donor Profile in the admin screen
 */
class DonorProfilePlaidSection {
	/**
	 * @since 2.3.0
	 *
	 * @param Give_Donor $donor
	 * @param string     $plaidClientUserId
	 *
	 * @return string
	 */
	public function __invoke( Give_Donor $donor, $plaidClientUserId ) {
		return sprintf(
			'<div class="donor-section postbox clear" id="plaid-information-section"><div class="donor-section__card"><strong>%1$s:</strong> <code>%2$s</code></div></div>',
			esc_html__( 'Plaid Client User ID', 'give-stripe' ),
			$plaidClientUserId
		);
	}
}
