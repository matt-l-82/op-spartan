<?php

namespace GiveFunds\Admin;

use GiveFunds\Repositories\Funds as FundsRepository;
use WP_Post;

class FundOptions {

	/**
	 * @var FundsRepository
	 */
	private $fundsRepository;

	/**
	 * FundOptions constructor.
	 *
	 * @param FundsRepository $fundsRepository
	 */
	public function __construct( FundsRepository $fundsRepository ) {
		$this->fundsRepository = $fundsRepository;
	}

	/**
	 * Register "Fund Options" section on edit donation form page
	 *
	 * @param array $sections section array
	 * @param int   $formId donation form id
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function registerSection( $sections, $formId ) {
		$sections['form_fund_options'] = [
			'id'        => 'form_fund_options',
			'title'     => esc_html__( 'Fund Options', 'give-funds' ),
			'icon-html' => '<span class="fas fa-hand-holding-usd"></span>',
			'fields'    => $this->getFields( $formId ),
		];

		return $sections;
	}

	/**
	 * Get section fields
	 *
	 * @param $formId
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function getFields( $formId ) {

		$default = 0;
		$options = [];
		$funds   = $this->fundsRepository->getFunds();

		// Get funds
		foreach ( $funds as $fund ) {
			$options[ $fund->getId() ] = $fund->getTitle();
			// Set default fund
			if ( $fund->isDefault() ) {
				$default                    = $fund->getId();
				$options[ $fund->getId() ] .= sprintf( ' (%s)', esc_html__( 'Default fund', 'give-funds' ) );
			}
		}

		// Show fund options
		return [
			[
				'name'          => esc_html__( 'Designation', 'give-funds' ),
				'id'            => 'give_funds_form_choice',
				'wrapper_class' => 'give_funds_options_choice',
				'type'          => 'multiradio',
				'default'       => 'admin_choice',
				'options'       =>
					[
						'admin_choice' => [
							'label'       => esc_html__( 'Admin\'s choice', 'give-funds' ),
							'description' => esc_html__( 'Automatically assign revenue from this form to one fund.', 'give-funds' )
						],
						'donor_choice' => [
							'label'       => esc_html__( 'Donor\'s choice', 'give-funds' ),
							'description' => esc_html__( 'Allow donor to designate one fund from a set of funds.', 'give-funds' ),
						]
					],
			],
			[
				'id'            => 'give_funds_admin_choice',
				'name'          => esc_html__( 'Fund', 'give-funds' ),
				'wrapper_class' => 'give_funds_form_options give-hidden',
				'default'       => $default,
				'type'          => 'select',
				'options'       => $options,
				'desc'          => esc_html__( 'All future revenue from this form will be assigned to this fund.', 'give-funds' )
			],
			[
				'id'            => 'give_funds_label',
				'name'          => esc_html__( 'Designation Label', 'give-funds' ),
				'wrapper_class' => 'give_funds_form_options give_funds_donor_choice_field give-hidden',
				'default'       => esc_html__( 'Where can we designate your gift?', 'give-funds' ),
				'type'          => 'text'
			],
			[
				'id'            => 'give_funds_donor_choice',
				'name'          => esc_html__( 'Funds', 'give-funds' ),
				'wrapper_class' => 'give_funds_form_options give-hidden',
				'default'       => $default,
				'type'          => 'multicheck',
				'options'       => $options,
				'desc'          => esc_html__( 'Select which funds are available for designation within this donation form.', 'give-funds' )
			]
		];
	}


	/**
	 * Update Form Fund relationship on Form update/save
	 *
	 * @param int $formId
	 * @param WP_Post $post
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function handleFormSelectedFunds( $formId, $post ) {
		if ( ! isset( $_POST['give_funds_form_choice'] ) ) {
			return;
		}

		$funds       = [];
		$displayType = give_clean( $_POST['give_funds_form_choice'] );

		switch ( $displayType ) {
			case 'admin_choice':
				$funds = isset( $_POST['give_funds_admin_choice'] ) ? (int) $_POST['give_funds_admin_choice'] : null;
				break;

			case 'donor_choice':
				$funds = isset( $_POST['give_funds_donor_choice'] ) ? (array) $_POST['give_funds_donor_choice'] : null;
				break;
		}

		if ( ! $funds ) {
			$funds = $this->fundsRepository->getDefaultFundId();
		}

		if ( $funds ) {
			$this->fundsRepository->associateFormWithFunds( $formId, $funds );
		}
	}

	/**
	 * Get associated funds from give_funds_form_relationship table insted of give_formmeta
	 *
	 * @param mixed $value
	 * @param array $field
	 * @param int $formId
	 *
	 * @return array|mixed
	 */
	public function getFieldValue( $value, $field, $formId ) {
		$values = [];

		$funds = $this->fundsRepository->getFormAssociatedFunds( $formId );

		foreach ( $funds as $fund ) {
			array_push( $values, $fund->getId() );
		}

		if ( 'give_funds_admin_choice' === $field['id'] ) {
			return reset( $values );
		}

		return $values;
	}
}
