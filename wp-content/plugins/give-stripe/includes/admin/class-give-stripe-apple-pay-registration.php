<?php
/**
 * Stripe - Apple Pay Registration Process
 *
 * @package   Give
 * @copyright Copyright (c) 2016, GiveWP
 * @license   https://opensource.org/licenses/gpl-license GNU Public License
 * @since     2.0.8
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Stripe_Apple_Pay_Registration
 *
 * @since 2.0.8
 */
class Give_Stripe_Apple_Pay_Registration {
	/**
	 * File name.
	 *
	 * @var string
	 */
	private $fileName = 'apple-developer-merchantid-domain-association';

	/**
	 * Give_Stripe_Apple_Pay_Registration constructor.
	 *
	 * @since  2.0.8
	 * @access public
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'registerDomainVerificationUrl' ] );
		add_action( 'admin_init', [ $this, 'domainIsNotAccessibleNotice' ] );
		add_filter( 'query_vars', [ $this, 'registerWellKnownParameter' ] );
		add_action( 'parse_request', [ $this, 'outputDomainAssociationFile' ], 99 );
	}

	/**
	 * Register Domain Verification Url.
	 *
	 * @since  2.2.6
	 * @access public
	 *
	 * @return void
	 */
	public function registerDomainVerificationUrl() {
		add_rewrite_rule(
			sprintf( '^.well-known/(%1$s)/?$', $this->fileName ),
			'index.php?file=$matches[1]', 'top'
		);
	}

	/**
	 * Register `file` parameter to query vars.
	 *
	 * @since  2.2.6
	 * @access public
	 * @param array $vars
	 *
	 * @return array
	 */
	public function registerWellKnownParameter( $vars ) {
		$vars[] = 'file';

		return $vars;
	}


	/**
	 * Show notice if Apple  domain association file is not publicly accessible.
	 *
	 * @since 2.2.6
	 */
	public function domainIsNotAccessibleNotice(){
		// Validate domain association only if apple pay is active.
		if( ! give_is_gateway_active('stripe_apple_pay') ) {
			return;
		}

		$optionKey = 'give-stripe-apple-pay-domain-registration-completed';

		// Registration successful, so no need to show notice.
		if( get_option( $optionKey, 0 ) ) {
			return;
		}

		// Fetch the contents of the domain association file location inside the Give Stripe Premium add-on and check status.
		$statusCode =  wp_remote_retrieve_response_code( wp_remote_get( home_url( ".well-known/{$this->fileName}" ), [ 'sslverify' => false ] ) );

		if( 200 === $statusCode ){
			update_option( $optionKey, 1 );
			return;
		}

		Give()->notices->register_notice(
			[
				'id'          => 'give-stripe-apple-pay-domain-registration-error',
				'type'        => 'error',
				'description' => sprintf(
					'<strong>%1$s</strong>: %2$s',
					esc_html__( 'Stripe Error', 'give-stripe' ),
					esc_html__( 'We can not access the Apple Developer Merchant ID domain association file. Please contact the support team to fix this issue.', 'give-stripe' )
				),
			]
		);
	}

	/**
	 * Output Apple Pay Domain Association File after parsing the request.
	 *
	 * @param WP $wp
	 */
	public function outputDomainAssociationFile( $wp ) {
		// Bailout, if the URL accessed is not matched.
		if ( ! array_key_exists( 'file', $wp->query_vars ) || $this->fileName !== $wp->query_vars['file'] ) {
			return;
		}

		// Fetch the contents of the domain association file location inside the Give Stripe Premium add-on.
		echo file_get_contents( GIVE_STRIPE_PLUGIN_DIR . "/{$this->fileName}" );
		die();
	}
}

new Give_Stripe_Apple_Pay_Registration();
