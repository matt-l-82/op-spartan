<?php
/**
 * Plugin Name: Give - Annual Receipts
 * Plugin URI:  https://givewp.com/addons/annual-receipts/
 * Description: Provide your donors with a quick and easy way to download a receipt for all their donations in a given year.
 * Version:     1.1.0
 * Author:      GiveWP
 * Author URI:  https://givewp.com/
 * License:     GNU General Public
 * License v2 or later License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: give-annual-receipts
 */

defined( 'ABSPATH' ) || exit;

use GiveAnnualReceipts\DonorDashboard\ServiceProvider as DonorDashboardServiceProvider;

/**-
 * Give_Annual_Receipts Class
 *
 * @package Give_Annual_Receipts
 * @since   1.0.0
 */
final class Give_Annual_Receipts {
	/**
	 * Instance.
	 *
	 * @since
	 * @access private
	 * @var Give_Annual_Receipts
	 */
	private static $instance;

	/**
	 * Give Annual Receipts Admin Object.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @var Give_Annual_Receipts_Admin
	 */
	public $plugin_admin;

	/**
	 * Give Annual Receipts Frontend Object.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @var Give_Annual_Receipts_Frontend
	 */
	public $plugin_public;

	/**
	 * Singleton pattern.
	 *
	 * @since
	 * @access private
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since
	 * @access public
	 *
	 * @return Give_Annual_Receipts
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Give_Annual_Receipts ) ) {
			self::$instance = new Give_Annual_Receipts();
			self::$instance->setup();
		}

		return self::$instance;
	}


	/**
	 * Setup
	 *
	 * @since
	 * @access private
	 */
	private function setup() {
		self::$instance->setup_constants();

		register_activation_hook( GIVE_ANNUAL_RECEIPTS_FILE, array( $this, 'install' ) );

		// Load service providers.
		add_action( 'before_give_init', function(){
			// Check Give min required version.
			if ( GiveAnnualReceipts\Infrastructure\Environment::giveMinRequiredVersionCheck() ) {
				give()->registerServiceProvider( DonorDashboardServiceProvider::class);
			}
		} );

		add_action( 'give_init', array( $this, 'init' ), 10, 1 );
	}

	/**
	 * Setup constants
	 *
	 * Defines useful constants to use throughout the add-on.
	 *
	 * @since
	 * @access private
	 */
	private function setup_constants() {

		// Defines addon version number for easy reference.
		if ( ! defined( 'GIVE_ANNUAL_RECEIPTS_VERSION' ) ) {
			define( 'GIVE_ANNUAL_RECEIPTS_VERSION', '1.1.0' );
		}

		// Set it to latest.
		if ( ! defined( 'GIVE_ANNUAL_RECEIPTS_MIN_GIVE_VERSION' ) ) {
			define( 'GIVE_ANNUAL_RECEIPTS_MIN_GIVE_VERSION', '2.10.0' );
		}

		if ( ! defined( 'GIVE_ANNUAL_RECEIPTS_FILE' ) ) {
			define( 'GIVE_ANNUAL_RECEIPTS_FILE', __FILE__ );
		}

		if ( ! defined( 'GIVE_ANNUAL_RECEIPTS_DIR' ) ) {
			define( 'GIVE_ANNUAL_RECEIPTS_DIR', plugin_dir_path( GIVE_ANNUAL_RECEIPTS_FILE ) );
		}

		if ( ! defined( 'GIVE_ANNUAL_RECEIPTS_URL' ) ) {
			define( 'GIVE_ANNUAL_RECEIPTS_URL', plugin_dir_url( GIVE_ANNUAL_RECEIPTS_FILE ) );
		}

		if ( ! defined( 'GIVE_ANNUAL_RECEIPTS_BASENAME' ) ) {
			define( 'GIVE_ANNUAL_RECEIPTS_BASENAME', plugin_basename( GIVE_ANNUAL_RECEIPTS_FILE ) );
		}

		if ( ! defined( 'GIVE_ANNUAL_RECEIPTS_SLUG' ) ) {
			define( 'GIVE_ANNUAL_RECEIPTS_SLUG', 'give-annual-receipts' );
		}

		if ( ! defined( 'GIVE_ANNUAL_RECEIPTS_ADDON_NAME' ) ) {
			define( 'GIVE_ANNUAL_RECEIPTS_ADDON_NAME', 'Annual Receipts');
		}
	}

	/**
	 * Plugin installation
	 *
	 * @since
	 * @access public
	 */
	public function install() {
		if ( ! self::$instance->check_environment() ) {
			return;
		}

		require_once GIVE_ANNUAL_RECEIPTS_DIR . 'includes/give-annual-receipts-settings-functions.php';

		// Save default settings during installation.
		self::save_default_settings();

		$current_version = get_option( 'give_annual_receipts_version' );

		if ( $current_version ) {
			// Add Upgraded from option.
			update_option( 'give_annual_receipts_upgraded_from', $current_version );
		}

		// Save plugin version during installation.
		if ( GIVE_ANNUAL_RECEIPTS_VERSION !== $current_version ) {
			update_option( 'give_annual_receipts_version', GIVE_ANNUAL_RECEIPTS_VERSION );
		}

	}

	/**
	 * Plugin installation
	 *
	 * @since
	 * @access public
	 *
	 * @param Give $give
	 *
	 * @return void
	 */
	public function init( $give ) {

		if ( ! self::$instance->check_environment() ) {
			return;
		}

		self::$instance->load_files();
		self::$instance->setup_hooks();
		self::$instance->load_license();
		self::$instance->load_plugin_textdomain();
	}

	/**
	 * Check plugin environment
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return bool|null
	 */
	public function check_environment() {

		// Load helper functions.
		require_once GIVE_ANNUAL_RECEIPTS_DIR . 'includes/admin/give-annual-receipts-activation.php';

		// Flag to check whether deactivate plugin or not.
		$is_deactivate_plugin = false;

		// Verify dependency cases.
		switch ( true ) {
			case doing_action( 'give_init' ):
				if (
					defined( 'GIVE_VERSION' ) &&
					version_compare( GIVE_VERSION, GIVE_ANNUAL_RECEIPTS_MIN_GIVE_VERSION, '<' )
				) {
					add_action( 'admin_notices', '__give_annual_receipts_dependency_notice' );
					$is_deactivate_plugin = true;
				}
				break;

			case doing_action( 'activate_' . GIVE_ANNUAL_RECEIPTS_BASENAME ):
			case doing_action( 'plugins_loaded' ) && ! did_action( 'give_init' ):
				// Check for if give plugin activate or not.
				$is_give_active = defined( 'GIVE_PLUGIN_BASENAME' ) ? is_plugin_active( GIVE_PLUGIN_BASENAME ) : false;

				if ( ! $is_give_active ) {
					add_action( 'admin_notices', '__give_annual_receipts_inactive_notice' );

					$is_deactivate_plugin = true;
				}
				break;
		}

		// Don't let this plugin activate.
		if ( $is_deactivate_plugin ) {
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}

			return false;
		}

		return true;
	}


	/**
	 * Load plugin files.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function load_files() {

		require_once GIVE_ANNUAL_RECEIPTS_DIR . 'includes/give-annual-receipts-pdf-functions.php';
		require_once GIVE_ANNUAL_RECEIPTS_DIR . 'includes/give-annual-receipts-helpers.php';

		do_action( 'give_annual_receipts_load_templates' );

		if ( is_admin() ) {
			require_once GIVE_ANNUAL_RECEIPTS_DIR . 'includes/give-annual-receipts-settings-functions.php';
			require_once GIVE_ANNUAL_RECEIPTS_DIR . 'includes/admin/give-annual-receipts-admin.php';
			self::$instance->plugin_admin = new Give_Annual_Receipts_Admin();
		}

		if ( ! is_admin() ) {
			require_once GIVE_ANNUAL_RECEIPTS_DIR . 'includes/frontend/give-annual-receipts-frontend.php';
			self::$instance->plugin_public = new Give_Annual_Receipts_Frontend();
		}
	}

	/**
	 * Setup hooks
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function setup_hooks() {
		add_filter( 'plugin_action_links_' . GIVE_ANNUAL_RECEIPTS_BASENAME, array( $this, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		add_filter( 'give-settings_get_settings_pages', array( $this, 'register_settings' ), 20, 1 );
		add_action( 'admin_init', array( $this, 'activation_banner' ) );
		add_action( 'admin_init', array( $this, 'automatic_updates' ) );
	}

	/**
	 * Load license
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function load_license() {
		new Give_License(
			GIVE_ANNUAL_RECEIPTS_FILE,
			'Annual Receipts',
			GIVE_ANNUAL_RECEIPTS_VERSION,
			'WordImpress',
			'give_annual_receipts_license_key'
		);
	}

	/**
	 * Load Plugin Text Domain
	 *
	 * Looks for the plugin translation files in certain directories and loads
	 * them to allow the plugin to be localised
	 *
	 * @since  1.0.0
	 * @access public
	 * @return bool True on success, false on failure
	 */
	public function load_plugin_textdomain() {
		// Traditional WordPress plugin locale filter
		$locale = apply_filters( 'plugin_locale', get_locale(), 'give-annual-receipts' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'give-annual-receipts', $locale );

		// Setup paths to current locale file
		$mofile_local = trailingslashit( GIVE_ANNUAL_RECEIPTS_DIR . 'languages' ) . $mofile;

		if ( file_exists( $mofile_local ) ) {
			load_textdomain( 'give-annual-receipts', $mofile_local );
		} else {
			// Load the default language files
			load_plugin_textdomain( 'give-annual-receipts', false, trailingslashit( GIVE_ANNUAL_RECEIPTS_DIR . 'languages' ) );
		}

		return false;
	}

	/**
	 * Add Annual Receipts settings.
	 *
	 * @param $settings
	 *
	 * @return array
	 */
	function register_settings( $settings ) {
		require_once GIVE_ANNUAL_RECEIPTS_DIR . 'includes/admin/settings.php';
		$settings[] = new Give_Annual_Receipts_Settings();

		return $settings;
	}

	/**
	 * Save default settings.
	 *
	 * @since 1.0.0
	 */
	private static function save_default_settings() {
		$give_options             = give_get_settings();
		$annual_receipts_settings = give_annual_receipts_settings();

		// Default tax month needs explicitly set since it is a child field.
		if ( ! isset( $give_options['give_annual_receipts_tax_month'] ) ) {
			$give_options['give_annual_receipts_tax_month'] = 12;
		}

		// Default tax day needs explicitly set since it is a child field.
		if ( ! isset( $give_options['give_annual_receipts_tax_day'] ) ) {
			$give_options['give_annual_receipts_tax_day'] = 31;
		}

		// Loop through the rest of the settings to process defaults.
		foreach ( $annual_receipts_settings as $setting ) {
			$id = $setting['id'];

			// Skip if setting has already been saved or has no default.
			if ( isset( $give_options[ $id ] ) || ! isset( $setting['default'] ) ) {
				continue;
			}

			$give_options[ $id ] = $setting['default'];
		}

		// Save the updated settings.
		update_option( 'give_settings', $give_options );
	}


	/**
	 * Give Annual Receipts Activation Banner.
	 *
	 * Includes and initializes Give activation banner class.
	 *
	 * @since 1.1
	 */
	public function activation_banner() {

		if ( ! class_exists( 'Give' ) ) {
			return false;
		}

		// Check for activation banner inclusion.
		if ( ! class_exists( 'Give_Addon_Activation_Banner' )
			 && file_exists( GIVE_PLUGIN_DIR . 'includes/admin/class-addon-activation-banner.php' )
		) {

			include GIVE_PLUGIN_DIR . 'includes/admin/class-addon-activation-banner.php';
		}

		// Initialize activation welcome banner.
		if ( class_exists( 'Give_Addon_Activation_Banner' ) ) {

			$args = array(
				'file'              => GIVE_ANNUAL_RECEIPTS_FILE,
				'name'              => __( 'Annual Receipts', 'give-annual-receipts' ),
				'version'           => GIVE_ANNUAL_RECEIPTS_VERSION,
				'settings_url'      => admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=annual_receipts' ),
				'documentation_url' => 'http://docs.givewp.com/addon-annual-receipts',
				'support_url'       => 'https://givewp.com/support/',
				'testing'           => false, // Never leave as true!
			);

			new Give_Addon_Activation_Banner( $args );

		}

		return false;

	}


	/**
	 * Adds a "Settings" link next to the "Deactivate" within the plugin actions on plugin listing page.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $plugin_meta An array of the plugin's metadata.
	 *
	 * @return array
	 */
	public function plugin_action_links( $plugin_meta ) {

		$new_meta_links['setting'] = sprintf(
			'<a href="%1$s">%2$s</a>',
			admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=annual_receipts' ),
			__( 'Settings', 'give-annual-receipts' )
		);

		return array_merge( $new_meta_links, $plugin_meta );
	}

	/**
	 * Plugin row meta links
	 *
	 * @since 1.3
	 *
	 * @param array  $plugin_meta An array of the plugin's metadata.
	 * @param string $plugin_file Path to the plugin file, relative to the plugins directory.
	 *
	 * @return array
	 */
	public function plugin_row_meta( $plugin_meta, $plugin_file ) {

		if ( $plugin_file != GIVE_ANNUAL_RECEIPTS_BASENAME ) {
			return $plugin_meta;
		}

		$new_meta_links = array(
			sprintf(
				'<a href="%1$s" target="_blank">%2$s</a>',
				esc_url( add_query_arg( array(
						'utm_source'   => 'plugins-page',
						'utm_medium'   => 'plugin-row',
						'utm_campaign' => 'admin',
					), 'http://docs.givewp.com/addon-annual-receipts' )
				),
				esc_html__( 'Documentation', 'give-annual-receipts' )
			),
			sprintf(
				'<a href="%1$s" target="_blank">%2$s</a>',
				esc_url( add_query_arg( array(
						'utm_source'   => 'plugins-page',
						'utm_medium'   => 'plugin-row',
						'utm_campaign' => 'admin',
					), 'https://givewp.com/addons/' )
				),
				esc_html__( 'Add-ons', 'give-annual-receipts' )
			),
		);

		return array_merge( $plugin_meta, $new_meta_links );
	}

	/**
	 * This function will responsible for updating addon version and run automatic updates.
	 *
	 * @since 1.0.1
	 *
	 */
	public function automatic_updates() {
		$did_upgrade = false;
		$plugin_version = preg_replace( '/[^0-9.].*/', '', get_option( 'give_annual_receipts_version' ) );

		if ( ! $plugin_version ) {
			// 1.0.0 is the first version to use this option so we must add it.
			$plugin_version = '1.0.0';
		}

		// switch ( true ) {
		// 	case version_compare( $plugin_version, '1.0.0', '<' ):
		// 		_your_function_;
		// 		$did_upgrade = true;
		// }

		if ( $did_upgrade || version_compare( $plugin_version, GIVE_ANNUAL_RECEIPTS_VERSION, '<' ) ) {
			update_option( 'give_annual_receipts_version', preg_replace( '/[^0-9.].*/', '', GIVE_ANNUAL_RECEIPTS_VERSION ), false );
			update_option( 'give_annual_receipts_version_upgraded_from', preg_replace( '/[^0-9.].*/', '', $plugin_version ), false );
		}
	}


}

/**
 * The main function responsible for returning the one true Give_Annual_Receipts instance
 * to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $receipts = Give_Annual_Receipts(); ?>
 *
 * @since 1.0.0
 *
 * @return Give_Annual_Receipts|bool
 */
function Give_Annual_Receipts() {
	return Give_Annual_Receipts::get_instance();
}

Give_Annual_Receipts();

require_once GIVE_ANNUAL_RECEIPTS_DIR . 'vendor/autoload.php';
