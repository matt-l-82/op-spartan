<?php
namespace GiveFormFieldManager\Tracking;

use Give\ServiceProviders\ServiceProvider;
use Give_FFM_Frontend_Form;

/**
 * Class TrackingServiceProvider
 * @package GiveFormFieldManager\Tracking
 *
 * @unreleased
 */
class TrackingServiceProvider implements ServiceProvider {
	/**
	 * @inheritdoc
	 */
	public function register() {
	}

	/**
	 * @inheritdoc
	 */
	public function boot() {
		add_filter(
			'give_telemetry_form_uses_addon_form_field_manager',
			static function( $result, $formId ) {
				return ! empty( give()->form_meta->get_meta( $formId, Give_FFM_Frontend_Form::$meta_key, true ) );
			},
			10,
			2
		);
	}
}
