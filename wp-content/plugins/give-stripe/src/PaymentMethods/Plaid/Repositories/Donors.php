<?php

namespace GiveStripe\PaymentMethods\Plaid\Repositories;

/**
 * Class Donor
 * @package GiveStripe\PaymentMethods\Plaid\Repositories
 * @since 2.3.0
 */
class Donors {
	const PLAID_CLIENT_USER_ID = 'plaid_client_user_id';

	/**
	 * @param string $donorId
	 *
	 * @return string
	 */
	public function getClientUserId( $donorId ){
		return give()->donor_meta->get_meta( $donorId, self::PLAID_CLIENT_USER_ID, true );
	}
}
