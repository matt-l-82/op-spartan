<?php

namespace GiveAnnualReceipts\DonorDashboard;

use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give\Helpers\Hooks;

use GiveAnnualReceipts\DonorDashboard\Tab as AnnualReceiptsTab;

class ServiceProvider implements ServiceProviderInterface {

	/**
	 * @inheritDoc
	 */
	public function register() {
        // Do nothing
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		// Register Tabs
		if ( give_is_setting_enabled( give_get_option( 'give_annual_receipts_enable_disable' ) ) ) {
			Hooks::addAction( 'init', AnnualReceiptsTab::class, 'registerTab' );
		}
	}
}
