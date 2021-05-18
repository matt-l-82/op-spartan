<?php

namespace GiveFunds\Form;

use GiveFunds\Repositories\Form;
use GiveFunds\Repositories\Funds as FundsRepository;
use GiveFunds\Repositories\Revenue as RevenueRepository;
use GiveFunds\Factories\Funds as FundsFactory;
use GiveFunds\Infrastructure\View;


/**
 * Add Fund page
 *
 * @package GiveFunds\Funds\Pages
 */
class SelectFund {
	/**
	 * @var FundsRepository
	 */
	private $fundsRepository;

	/**
	 * @var RevenueRepository
	 */
	private $revenueRepository;

	/**
	 * @var FundsFactory
	 */
	private $fundsFactory;

	/**
	 * AddFund constructor.
	 *
	 * @param FundsRepository $fundsRepository
	 * @param RevenueRepository $revenueRepository
	 * @param FundsFactory $fundsFactory
	 */
	public function __construct(
		FundsRepository $fundsRepository,
		RevenueRepository $revenueRepository,
		FundsFactory $fundsFactory
	) {
		$this->fundsFactory      = $fundsFactory;
		$this->fundsRepository   = $fundsRepository;
		$this->revenueRepository = $revenueRepository;
	}

	/**
	 * Render Add fund page
	 *
	 * @param int $formId
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function renderDropdown( $formId ) {
		/* @var Form $formRepository */
		$formRepository = give( Form::class );

		if ( 'donor_choice' !== $formRepository->getFundDisplayType( $formId ) ) {
			return;
		}

		$funds = $this->fundsRepository->getFormAssociatedFunds( $formId );

		if ( count( $funds ) > 0 ) {
			View::render(
				'form/select-fund',
				[
					'funds' => $funds,
					'label' => give_get_meta( $formId, 'give_funds_label', true )
				]
			);
		}
	}
}
