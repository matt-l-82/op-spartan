<?php

namespace GiveAnnualReceipts\DonorDashboard\Routes;

use \WP_REST_Request;
use \Give\DonorDashboards\Tabs\Contracts\Route as RouteAbstract;
use \GiveAnnualReceipts\DonorDashboard\Repositories\AnnualReceiptRepository as AnnualReceiptRepository;

/**
 * @since 2.10.0
 */
class AnnualReceiptsRoute extends RouteAbstract {

	/** @var string */
	public function endpoint() {
		return 'annual-receipts';
	}

	public function args() {
		return [];
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 *
	 * @since 2.10.0
	 */
	public function handleRequest( WP_REST_Request $request ) {
		return $this->getData();
	}

	/**
	 * @return array
	 *
	 * @since 2.10.0
	 */
	protected function getData() {

		$query = (new AnnualReceiptRepository())->getByDonorId( give()->donorDashboard->getId() );

		return [
			'receipts' => $query,
		];
	}

}
