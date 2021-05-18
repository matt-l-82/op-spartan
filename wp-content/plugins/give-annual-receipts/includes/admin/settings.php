<?php
/**
 *  Give Annual Receipts Settings.
 *
 * Registers all the settings required for the plugin.
 *
 * @package     Give Annual Receipts
 * @since       1.0.0
 */

defined( 'ABSPATH' ) || exit;

class Give_Annual_Receipts_Settings extends Give_Settings_Page {
	/**
	 * Give_Annual_Receipts_Settings constructor.
	 */
	public function __construct() {
		$this->id          = 'annual_receipts';
		$this->label       = __( 'Annual Receipts', 'give-annual-receipts' );
		$this->default_tab = 'annual_receipts';
		parent::__construct();
	}

	/**
	 * Add setting sections.
	 *
	 * @return array
	 */
	function get_sections() {
		$sections = array(
			'annual_receipts' => __( 'Annual Receipts Settings', 'give-annual-receipts' ),
		);

		return $sections;
	}

	/**
	 * Get setting.
	 *
	 * @return array
	 */
	function get_settings() {
		do_action( 'give_annual_receipts_pre_settings' );
		$global_settings = give_annual_receipts_settings();
		$settings        = apply_filters( 'give_settings_annual_receipts', $global_settings );

		return $settings;
	}
}
