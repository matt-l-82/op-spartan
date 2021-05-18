<?php

namespace GiveStripe\Donors;

use Give\Helpers\Hooks;
use GiveStripe\Donors\Controllers\AdminDonorProfileController;

/**
 * Class ServiceProvider
 * @package GiveStripe\Donor\Admin\DonorProfiles
 * @since 2.3.0
 */
class ServiceProvider implements \Give\ServiceProviders\ServiceProvider {
	/**
	 * @inheritDoc
	 */
	public function register() {
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		Hooks::addAction('give_donor_before_stats', AdminDonorProfileController::class, 'renderPlaidSection');
	}
}
