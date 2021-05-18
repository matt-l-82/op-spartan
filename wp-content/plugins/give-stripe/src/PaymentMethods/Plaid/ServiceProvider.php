<?php
namespace GiveStripe\PaymentMethods\Plaid;

use Give\Helpers\Hooks;
use GiveStripe\PaymentMethods\Plaid\Controllers\AchLinkTokenController;
use GiveStripe\PaymentMethods\Plaid\Controllers\AttachClientIdToDonorHandler;

/**
 * Class ServiceProvider
 * @package GiveStripe\PaymentMethos\Plaid
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
	 * @since 2.3.0
	 */
	public function boot() {
		Hooks::addAction( 'wp_ajax_give_stripe_get_ach_link_token', AchLinkTokenController::class, 'handle' );
		Hooks::addAction( 'wp_ajax_nopriv_give_stripe_get_ach_link_token', AchLinkTokenController::class, 'handle' );

		Hooks::addAction( 'wp_ajax_get_receipt', AttachClientIdToDonorHandler::class, 'handle', 9 );
		Hooks::addAction( 'wp_ajax_nopriv_get_receipt', AttachClientIdToDonorHandler::class, 'handle', 9 );
	}
}
