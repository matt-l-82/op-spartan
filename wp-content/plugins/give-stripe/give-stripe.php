<?php
/**
 * Plugin Name: Give - Stripe Gateway
 * Plugin URI:  https://givewp.com/addons/stripe-gateway/
 * Description: Adds the Stripe.com payment gateway to the available GiveWP payment methods.
 * Version:     2.3.0
 * Author:      GiveWP
 * Author URI:  https://givewp.com/
 * Text Domain: give-stripe
 * Domain Path: /languages
 *
 * @package    Give
 * @subpackage Stripe Premium
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define constants.
 *
 * Required minimum versions, paths, urls, etc.
 */
if ( ! defined( 'GIVE_STRIPE_VERSION' ) ) {
	define( 'GIVE_STRIPE_VERSION', '2.3.0' );
}
if ( ! defined( 'GIVE_STRIPE_MIN_GIVE_VER' ) ) {
	define( 'GIVE_STRIPE_MIN_GIVE_VER', '2.10.0' );
}
if ( ! defined( 'GIVE_STRIPE_MIN_PHP_VER' ) ) {
	define( 'GIVE_STRIPE_MIN_PHP_VER', '5.6.0' );
}
if ( ! defined( 'GIVE_STRIPE_PLUGIN_FILE' ) ) {
	define( 'GIVE_STRIPE_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'GIVE_STRIPE_PLUGIN_DIR' ) ) {
	define( 'GIVE_STRIPE_PLUGIN_DIR', dirname( GIVE_STRIPE_PLUGIN_FILE ) );
}
if ( ! defined( 'GIVE_STRIPE_PLUGIN_URL' ) ) {
	define( 'GIVE_STRIPE_PLUGIN_URL', plugin_dir_url( GIVE_STRIPE_PLUGIN_FILE ) );
}
if ( ! defined( 'GIVE_STRIPE_BASENAME' ) ) {
	define( 'GIVE_STRIPE_BASENAME', plugin_basename( GIVE_STRIPE_PLUGIN_FILE ) );
}


if ( ! class_exists( 'Give_Stripe_Premium' ) ) :

	/**
	 * Class Give_Stripe.
	 */
	class Give_Stripe_Premium {

		/**
		 * The reference the *Singleton* instance of this class.
		 *
		 * @var Give_Stripe
		 */
		private static $instance;

		/**
		 * Stripe Add-on Upgrades.
		 *
		 * @var Give_Stripe_Upgrades.
		 */
		public $upgrades;

		/**
		 * Notices (array)
		 *
		 * @var array
		 */
		public $notices = array();

		/**
		 * @since 2.3.0
		 *
		 * @var array Array of Service Providers to load
		 */
		private $serviceProviders = [
			\GiveStripe\PaymentMethods\Plaid\ServiceProvider::class,
			\GiveStripe\Donors\ServiceProvider::class
		];

		/**
		 * Returns the *Singleton* instance of this class.
		 *
		 * @return Give_Stripe The *Singleton* instance.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
				self::$instance->setup();
			}

			return self::$instance;
		}

		/**
		 * Setup Give Stripe.
		 *
		 * @since  2.1.1
		 * @access private
		 */
		private function setup() {
			// Give init hook.
			add_action( 'give_init', [ $this, 'init' ], 10 );
			add_action( 'admin_init', [ $this, 'check_environment' ], 999 );
			add_action( 'admin_notices', [ $this, 'admin_notices' ], 15 );
			add_action( 'before_give_init', [ $this, 'registerServiceProvider' ] );
		}

		/**
		 * Private clone method to prevent cloning of the instance of the
		 * *Singleton* instance.
		 *
		 * @return void
		 */
		private function __clone() {
		}

		/**
		 * Init the plugin after plugins_loaded so environment variables are set.
		 *
		 * @since 2.1.1
		 */
		public function init() {

			$this->licensing();
			load_plugin_textdomain( 'give-stripe', false, dirname( GIVE_STRIPE_BASENAME ) . '/languages' );

			// Don't hook anything else in the plugin if we're in an incompatible environment.
			if ( ! $this->get_environment_warning() ) {
				return;
			}

			$this->activation_banner();

			add_filter( 'give_payment_gateways', array( $this, 'register_gateway' ) );


			$this->includes();

			if (
				defined( 'GIVE_RECURRING_VERSION' ) &&
				version_compare( GIVE_RECURRING_VERSION, '1.9.0', '<' )
			) {
				add_action( 'admin_notices', array( $this, 'legacy_recurring_upgrade_notice' ) );
			}

		}

		/**
		 * Allow this class and other classes to add notices.
		 *
		 * @param string $slug Notice Slug.
		 * @param string $class Notice Class.
		 * @param string $message Notice Message.
		 */
		public function add_admin_notice( $slug, $class, $message ) {
			$this->notices[ $slug ] = array(
				'class'   => $class,
				'message' => $message,
			);
		}

		/**
		 * Display admin notices.
		 */
		public function admin_notices() {

			$allowed_tags = array(
				'a'      => array(
					'href'  => array(),
					'title' => array(),
					'class' => array(),
					'id'    => array(),
				),
				'br'     => array(),
				'em'     => array(),
				'span'   => array(
					'class' => array(),
				),
				'strong' => array(),
			);

			foreach ( (array) $this->notices as $notice_key => $notice ) {
				echo "<div class='" . esc_attr( $notice['class'] ) . "'><p>";
				echo wp_kses( $notice['message'], $allowed_tags );
				echo '</p></div>';
			}

		}

		/**
		 * Give Stripe Includes.
		 */
		private function includes() {

			// Include admin files.
			$this->include_admin_files();

			// Bailout, if any of the Stripe gateway is not active.
			if ( ! give_stripe_is_any_payment_method_active() ) {
				return;
			}

			// Include frontend files.
			$this->include_frontend_files();
		}

		/**
		 * This function will include admin files.
		 *
		 * @since  2.2.1
		 * @access public
		 *
		 * @return void
		 */
		public function include_admin_files() {
			// These are files contain common functions that are used in frontend as well as admin.
			require_once GIVE_STRIPE_PLUGIN_DIR . '/includes/give-stripe-scripts.php';
			require_once GIVE_STRIPE_PLUGIN_DIR . '/includes/admin/admin-filters.php';
			require_once GIVE_STRIPE_PLUGIN_DIR . '/includes/admin/admin-actions.php';
			require_once GIVE_STRIPE_PLUGIN_DIR . '/includes/admin/admin-helpers.php';
			require_once GIVE_STRIPE_PLUGIN_DIR . '/includes/admin/give-stripe-upgrades.php';
			require_once GIVE_STRIPE_PLUGIN_DIR . '/includes/admin/class-give-stripe-apple-pay-registration.php';

			// Bailout, if these files not accessed from admin.
			if ( ! is_admin() ) {
				return;
			}

			require_once GIVE_STRIPE_PLUGIN_DIR . '/includes/admin/give-stripe-settings.php';
		}

		/**
		 * This function will include frontend files.
		 *
		 * @since  2.2.1
		 * @access public
		 *
		 * @return void
		 */
		public function include_frontend_files() {
			require_once GIVE_STRIPE_PLUGIN_DIR . '/includes/filters.php';
			require_once GIVE_STRIPE_PLUGIN_DIR . '/includes/actions.php';
			require_once GIVE_STRIPE_PLUGIN_DIR . '/includes/deprecated/deprecated-functions.php';
			require_once GIVE_STRIPE_PLUGIN_DIR . '/includes/give-stripe-helpers.php';
			require_once GIVE_STRIPE_PLUGIN_DIR . '/includes/payment-methods/class-give-stripe-ach.php';
			require_once GIVE_STRIPE_PLUGIN_DIR . '/includes/payment-methods/class-give-stripe-ideal.php';
			require_once GIVE_STRIPE_PLUGIN_DIR . '/includes/payment-methods/class-give-stripe-google-pay.php';
			require_once GIVE_STRIPE_PLUGIN_DIR . '/includes/payment-methods/class-give-stripe-apple-pay.php';
			require_once GIVE_STRIPE_PLUGIN_DIR . '/includes/class-give-stripe-email-tags.php';
		}

		/**
		 * Upgrade notice.
		 *
		 * Tells the admin that they need to upgrade the Recurring add-on.
		 *
		 * @since  2.2.0
		 * @access public
		 */
		public function legacy_recurring_upgrade_notice() {

			$message = sprintf(
				/* translators: 1. GiveWP account login page, 2. GiveWP Account downloads page */
				__( '<strong>Attention:</strong> The Stripe Premium plugin requires the latest version of the Recurring donations add-on to process donations properly. Please update to the latest version of Recurring donations plugin to resolve this issue. If your license is active you should see the update available in WordPress. Otherwise, you can access the latest version by <a href="%1$s" target="_blank">logging into your account</a> and visiting <a href="%2$s" target="_blank">your downloads</a> page on the GiveWP website.', 'give-stripe' ),
				'https://givewp.com/my-account',
				'https://givewp.com/my-downloads/'
			);

			if ( class_exists( 'Give_Notices' ) ) {
				Give()->notices->register_notice(
					array(
						'id'          => 'give-activation-error',
						'type'        => 'error',
						'description' => $message,
						'show'        => true,
					)
				);
			} else {
				$class = 'notice notice-error';
				printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
			}
		}

		/**
		 * Register the Stripe payment gateways.
		 *
		 * @access public
		 * @since  1.0
		 *
		 * @param array $gateways List of registered gateways.
		 *
		 * @return array
		 */
		public function register_gateway( $gateways ) {

			global $is_safari, $is_chrome;

			// Register Stripe + ACH (Plaid) as a gateway.
			$gateways['stripe_ach'] = array(
				'admin_label'    => __( 'Stripe + Plaid', 'give-stripe' ),
				'checkout_label' => __( 'Bank Account', 'give-stripe' ),
			);

			// Register Stripe iDEAL as a gateway.
			$gateways['stripe_ideal'] = array(
				'admin_label'    => __( 'Stripe iDEAL', 'give-stripe' ),
				'checkout_label' => __( 'iDEAL', 'give-stripe' ),
			);

			// Register Apple Pay as a gateway.
			$gateways['stripe_apple_pay'] = array(
				'admin_label'    => __( 'Stripe - Apple Pay', 'give-stripe' ),
				'admin_tooltip'  => __( 'If enabled, donors will be able to make donations using Apple Pay on desktops or iPhones using the Safari browser.', 'give-stripe' ),
				'checkout_label' => __( 'Apple Pay', 'give-stripe' ),
				'is_visible'     => $is_safari,
			);

			// Register Google Pay as a gateway.
			$gateways['stripe_google_pay'] = array(
				'admin_label'    => __( 'Stripe - Google Pay', 'give-stripe' ),
				'admin_tooltip'  => __( 'If enabled, donors will be able to make donations using Google Pay on desktops or Android devices using the Chrome browser.', 'give-stripe' ),
				'checkout_label' => __( 'Google Pay', 'give-stripe' ),
				'is_visible'     => $is_chrome,
			);

			return $gateways;
		}

		/**
		 * Plugin Licensing.
		 */
		public function licensing() {
			if ( class_exists( 'Give_License' ) ) {
				new Give_License( GIVE_STRIPE_PLUGIN_FILE, 'Stripe Gateway', GIVE_STRIPE_VERSION, 'WordImpress', 'stripe_license_key' );
			}
		}

		/**
		 * Check plugin environment.
		 *
		 * @since  2.1.1
		 * @access public
		 *
		 * @return bool
		 */
		public function check_environment() {
			// Flag to check whether plugin file is loaded or not.
			$is_working = true;

			// Load plugin helper functions.
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . '/wp-admin/includes/plugin.php';
			}

			/* Check to see if Give is activated, if it isn't deactivate and show a banner. */
			// Check for if give plugin activate or not.
			$is_give_active = defined( 'GIVE_PLUGIN_BASENAME' ) ? is_plugin_active( GIVE_PLUGIN_BASENAME ) : false;

			if ( empty( $is_give_active ) ) {
				// Show admin notice.
				$this->add_admin_notice( 'prompt_give_activate', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">Give</a> plugin installed and activated for GiveWP - Stripe to activate.', 'give-stripe' ), 'https://givewp.com' ) );
				$is_working = false;
			}

			return $is_working;
		}

		/**
		 * Check plugin for Give environment.
		 *
		 * @since  2.1.1
		 * @access public
		 *
		 * @return bool
		 */
		public function get_environment_warning() {
			// Flag to check whether plugin file is loaded or not.
			$is_working = true;

			// Verify dependency cases.
			if (
				defined( 'GIVE_VERSION' )
				&& version_compare( GIVE_VERSION, GIVE_STRIPE_MIN_GIVE_VER, '<' )
			) {

				/* Min. Give. plugin version. */
				// Show admin notice.
				$this->add_admin_notice( 'prompt_give_incompatible', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">Give</a> core version %s for the GiveWP - Stripe add-on to activate.', 'give-stripe' ), 'https://givewp.com', GIVE_STRIPE_MIN_GIVE_VER ) );

				$is_working = false;
			}

			if ( version_compare( phpversion(), GIVE_STRIPE_MIN_PHP_VER, '<' ) ) {
				$this->add_admin_notice( 'prompt_give_incompatible', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">PHP</a> version %s or above for the GiveWP - Stripe gateway add-on to activate.', 'give-stripe' ), 'https://givewp.com/documentation/core/requirements/', GIVE_STRIPE_MIN_PHP_VER ) );

				$is_working = false;
			}

			if ( ! function_exists( 'curl_init' ) ) {
				$this->add_admin_notice( 'prompt_give_incompatible', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">cURL</a> installed for the GiveWP - Stripe gateway add-on to activate.', 'give-stripe' ), 'https://givewp.com/documentation/core/requirements/' ) );

				$is_working = false;
			}

			return $is_working;
		}

		/**
		 * Give Stripe activation banner.
		 *
		 * Includes and initializes Give activation banner class.
		 *
		 * @since 2.1.1
		 */
		public function activation_banner() {

			// Check for activation banner inclusion.
			if (
				! class_exists( 'Give_Addon_Activation_Banner' )
				&& file_exists( GIVE_PLUGIN_DIR . 'includes/admin/class-addon-activation-banner.php' )
			) {
				include GIVE_PLUGIN_DIR . 'includes/admin/class-addon-activation-banner.php';
			}

			// Initialize activation welcome banner.
			if ( class_exists( 'Give_Addon_Activation_Banner' ) ) {

				// Only runs on admin.
				$args = array(
					'file'              => GIVE_STRIPE_PLUGIN_FILE,
					'name'              => esc_html__( 'Stripe Gateway', 'give-stripe' ),
					'version'           => GIVE_STRIPE_VERSION,
					'settings_url'      => admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings' ),
					'documentation_url' => 'http://docs.givewp.com/addon-stripe',
					'support_url'       => 'https://givewp.com/support/',
					'testing'           => false
				);

				new Give_Addon_Activation_Banner( $args );

			}

			return true;

		}

		/**
		 * Register a Service Provider for bootstrapping
		 *
		 * @since 2.3.0
		 */
		public function registerServiceProvider() {
			foreach( $this->serviceProviders as $serviceProvider ) {
				give()->registerServiceProvider($serviceProvider );
			}
		}
	}

	$GLOBALS['give_stripe'] = Give_Stripe_Premium::get_instance();

endif; // End if class_exists check.

require_once GIVE_STRIPE_PLUGIN_DIR . '/vendor/autoload.php';
