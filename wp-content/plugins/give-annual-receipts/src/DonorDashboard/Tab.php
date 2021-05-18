<?php

namespace GiveAnnualReceipts\DonorDashboard;

use Give\DonorDashboards\Tabs\Contracts\Tab as TabAbstract;

use GiveAnnualReceipts\DonorDashboard\Routes\AnnualReceiptsRoute as AnnualReceiptsRoute;

class Tab extends TabAbstract {

	public static function id() {
		return 'annual-receipts';
	}

	public function routes() {
		return [
			AnnualReceiptsRoute::class,
		];
	}
}
