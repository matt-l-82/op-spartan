<?php
/**
 * Plugin Name: Give - PDF Receipts
 * Plugin URI:  https://givewp.com/addons/pdf-receipts/
 * Description: Creates PDF Receipts for each donation that is downloadable via email and donation history.
 * Author: GiveWP
 * Author URI: https://givewp.com
 * Contributors: GiveWP
 * Version: 2.3.11
 * Text Domain: give-pdf-receipts
 * Domain Path: /languages
 *
 * Copyright 2019 GiveWP
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'GIVE_PDF_PLUGIN_VERSION' ) ) {
	define( 'GIVE_PDF_PLUGIN_VERSION', '2.3.11' );
}

if ( ! defined( 'GIVE_PDF_MIN_GIVE_VERSION' ) ) {
	define( 'GIVE_PDF_MIN_GIVE_VERSION', '2.6.0' );
}

if ( ! defined( 'GIVE_PDF_MIN_PHP_VERSION' ) ) {
	define( 'GIVE_PDF_MIN_PHP_VERSION', '5.3' );
}

if ( ! defined( 'GIVE_PDF_PLUGIN_FILE' ) ) {
	define( 'GIVE_PDF_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'GIVE_PDF_PLUGIN_DIR' ) ) {
	define( 'GIVE_PDF_PLUGIN_DIR', plugin_dir_path( GIVE_PDF_PLUGIN_FILE ) );
}

if ( ! defined( 'GIVE_PDF_PLUGIN_URL' ) ) {
	define( 'GIVE_PDF_PLUGIN_URL', plugin_dir_url( GIVE_PDF_PLUGIN_FILE ) );
}

if ( ! defined( 'GIVE_PDF_PLUGIN_BASENAME' ) ) {
	define( 'GIVE_PDF_PLUGIN_BASENAME', plugin_basename( GIVE_PDF_PLUGIN_FILE ) );
}

if ( ! class_exists( 'Give_PDF_Receipts' ) ) :

	/**
	 * Give_PDF_Receipts Class
	 *
	 * @package Give_PDF_Receipts
	 * @since   1.0
	 *
	 * @property-read Give_PDF_Receipts_Engine $engine Give_PDF_Receipts_Engine class object.
	 */
	final class Give_PDF_Receipts {

		/**
		 * Holds the instance
		 *
		 * Ensures that only one instance of Give_PDF_Receipts exists in memory at any one
		 * time and it also prevents needing to define globals all over the place.
		 *
		 * TL;DR This is a static property property that holds the singleton instance.
		 *
		 * @var object
		 * @static
		 */
		private static $instance;

		/**
		 * Notices (array).
		 *
		 * @since     2.3.1
		 *
		 * @var array
		 */
		public $notices = array();

		/**
		 * Get the instance and store the class inside it. This plugin utilises
		 * the PHP singleton design pattern.
		 *
		 * @since     1.0
		 * @static
		 * @staticvar array $instance
		 * @access    public
		 * @see       give_pdf_receipts();
		 * @uses      Give_PDF_Receipts::includes() Loads all the classes
		 * @uses      Give_PDF_Receipts::hooks() Setup hooks and actions
		 *
		 * @return object self::$instance Instance
		 */
		public static function get_instance() {

			if ( null === self::$instance ) {
				self::$instance = new self();
				self::$instance->setup();
			}

			return self::$instance;
		}


		/**
		 * Setup Give PDF Receipts.
		 *
		 * @since  2.3.1
		 * @access private
		 */
		private function setup() {

			// Activation and deactivation hooks.
			$this->init_hooks();

			// Give init hook.
			add_action( 'init', array( $this, 'init' ), - 1 );
			add_action( 'give_init', array( $this, 'give_init' ), 10 );
			add_action( 'admin_init', array( $this, 'check_environment' ), 999 );
			add_action( 'admin_notices', array( $this, 'admin_notices' ), 15 );
		}

		/**
		 * Hook into actions and filters.
		 *
		 * @since  1.1.1
		 */
		public function init_hooks() {
			add_action( 'init', [ $this, 'give_pdf_register_post_type' ] );
			register_deactivation_hook( GIVE_PDF_PLUGIN_FILE, array( $this, 'deactivation' ) );
		}

		/**
		 * Setup Give PDF Receipts.
		 *
		 * @since  2.3.1
		 * @access private
		 */
		public function give_init() {

			if ( ! $this->get_environment_warning() ) {
				return;
			}

			$this->activation_banner();
			$this->hooks();
			$this->licensing();
			$this->includes();
		}

		/**
		 * Throw error on object clone.
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
		 * @since  1.0
		 * @access protected
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'give-pdf-receipts' ), '1.6' );
		}

		/**
		 * Disable unserializing of the class
		 *
		 * @since  1.0
		 * @access protected
		 * @return void
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'give-pdf-receipts' ), '1.0' );
		}

		/**
		 * Reset the instance of the class
		 *
		 * @since  1.0
		 * @access public
		 * @static
		 */
		public static function reset() {
			self::$instance = null;
		}

		/**
		 * Function fired on init.
		 *
		 * This function is called on WordPress 'init'. It's triggered from the
		 * constructor function.
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @uses   Give_PDF_Receipts::load_plugin_textdomain()
		 *
		 * @return void
		 */
		public function init() {

			do_action( 'give_pdf_before_init' );

			$this->load_plugin_textdomain();

			do_action( 'give_pdf_after_init' );

		}

		/**
		 * Includes.
		 *
		 * @since  1.0
		 * @access private
		 */
		private function includes() {

			require_once GIVE_PDF_PLUGIN_DIR. 'vendor/autoload.php';

			require_once( GIVE_PDF_PLUGIN_DIR . 'includes/functions.php' );
			require_once( GIVE_PDF_PLUGIN_DIR . 'includes/templates/template-blue-stripe.php' );
			require_once( GIVE_PDF_PLUGIN_DIR . 'includes/templates/template-default.php' );
			require_once( GIVE_PDF_PLUGIN_DIR . 'includes/templates/template-lines.php' );
			require_once( GIVE_PDF_PLUGIN_DIR . 'includes/templates/template-minimal.php' );
			require_once( GIVE_PDF_PLUGIN_DIR . 'includes/templates/template-traditional.php' );

			do_action( 'give_pdf_load_templates' );

			require_once( GIVE_PDF_PLUGIN_DIR . 'includes/class-give-pdf-receipts-engine.php' );
			require_once( GIVE_PDF_PLUGIN_DIR . 'includes/email-template-tag.php' );
			require_once( GIVE_PDF_PLUGIN_DIR . 'includes/template-functions.php' );
			require_once( GIVE_PDF_PLUGIN_DIR . 'includes/give-pdf-receipts-settings-functions.php' );
			require_once( GIVE_PDF_PLUGIN_DIR . 'includes/scripts.php' );
			require_once( GIVE_PDF_PLUGIN_DIR . 'includes/ajax-functions.php' );
			require_once( GIVE_PDF_PLUGIN_DIR . 'includes/plugin-compatibility.php' );

			self::$instance->engine = new Give_PDF_Receipts_Engine();

		}

		/**
		 * Hooks.
		 */
		public function hooks() {

			add_filter( 'plugin_action_links_' . GIVE_PDF_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ) );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
			add_filter( 'give-settings_get_settings_pages', array( $this, 'add_settings' ), 10, 1 );
		}

		/**
		 * Implement Give Licensing
		 */
		private function licensing() {
			if ( class_exists( 'Give_License' ) ) {
				new Give_License( GIVE_PDF_PLUGIN_FILE, 'PDF Receipts', GIVE_PDF_PLUGIN_VERSION, 'WordImpress' );
			}
		}

		/**
		 * Load Plugin Text Domain
		 *
		 * Looks for the plugin translation files in certain directories and loads
		 * them to allow the plugin to be localised
		 *
		 * @since  1.0
		 * @access public
		 * @return bool True on success, false on failure
		 */
		public function load_plugin_textdomain() {
			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), 'give-pdf-receipts' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'give-pdf-receipts', $locale );

			// Setup paths to current locale file
			$mofile_local = trailingslashit( GIVE_PDF_PLUGIN_DIR . 'languages' ) . $mofile;

			if ( file_exists( $mofile_local ) ) {
				// Look in the /wp-content/plugins/give-pdf-receipts/languages/ folder
				load_textdomain( 'give-pdf-receipts', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'give-pdf-receipts', false, trailingslashit( GIVE_PDF_PLUGIN_DIR . 'languages' ) );
			}

			return false;
		}

		/**
		 * Add PDF Receipts settings.
		 *
		 * @param $settings
		 *
		 * @return array
		 */
		function add_settings( $settings ) {

			require_once( GIVE_PDF_PLUGIN_DIR . 'includes/class-pdf-receipts-settings.php' );
			$settings[] = new Give_PDF_Receipts_Settings();

			return $settings;
		}

		/**
		 * Activation banner.
		 *
		 * Uses Give's core activation banners.
		 *
		 * @since 2.0.4
		 *
		 * @return bool
		 */
		public function activation_banner() {

			// Now that is passes move to activation.
			if ( ! defined( 'GIVE_PLUGIN_DIR' ) ) {
				return false;
			};

			// Check for activation banner inclusion.
			if (
				! class_exists( 'Give_Addon_Activation_Banner' )
				&& file_exists( GIVE_PLUGIN_DIR . 'includes/admin/class-addon-activation-banner.php' )
			) {
				include GIVE_PLUGIN_DIR . 'includes/admin/class-addon-activation-banner.php';
			}

			// Initialize activation welcome banner.
			if ( class_exists( 'Give_Addon_Activation_Banner' ) ) {

				//Show activation banner.
				$args = array(
					'file'              => GIVE_PDF_PLUGIN_FILE,
					'name'              => esc_html__( 'PDF Receipts', 'give-pdf-receipts' ),
					'version'           => GIVE_PDF_PLUGIN_VERSION,
					'settings_url'      => admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=pdf_receipts' ),
					'documentation_url' => 'http://docs.givewp.com/addon-pdf-receipts',
					'support_url'       => 'https://givewp.com/support/',
					'testing'           => false,
				);

				new Give_Addon_Activation_Banner( $args );

			}

			return true;

		}

		/**
		 * Add default templates.
		 *
		 * @since  2.2.0
		 * @access public
		 */
		public static function give_pdf_register_post_type() {
			// Define Give_PDF_Template post type.
			$template_args = array(
				'labels'      => array(
					'name'          => 'Give_PDF_Template',
					'singular_name' => 'Give_PDF_Template',
				),
				'public'      => false,
				'has_archive' => false,
			);

			// Register Give_PDF_Template post type.
			register_post_type( 'give_pdf_template', $template_args );

			// Flush rewrite rules because we created a new CPT.
			flush_rewrite_rules();
		}


		/**
		 * Deactivation function.
		 *
		 * Delete all default templates from database.
		 *
		 * @since      1.0
		 * @access     public
		 *
		 * @return void
		 */
		public static function deactivation() {

			$give_options                             = get_option( 'give_settings' );
			$options['pdf_receipt_template_upgraded'] = false;
			update_option( 'give_settings', $give_options );

		}

		/**
		 * Allow this class and other classes to add notices.
		 *
		 * @since 2.0.4
		 *
		 * @param $slug
		 * @param $class
		 * @param $message
		 */
		public function add_admin_notice( $slug, $class, $message ) {
			$this->notices[ $slug ] = array(
				'class'   => $class,
				'message' => $message,
			);
		}

		/**
		 * Handles the displaying of any notices in the admin area.
		 *
		 * @since  1.0
		 * @access public
		 * @return mixed
		 */
		public function admin_notices() {

			$allowed_tags = array(
				'a'      => array(
					'href'  => array(),
					'title' => array(),
				),
				'br'     => array(),
				'em'     => array(),
				'strong' => array(),
			);

			foreach ( (array) $this->notices as $notice_key => $notice ) {
				echo "<div class='" . esc_attr( $notice['class'] ) . "'><p>";
				echo wp_kses( $notice['message'], $allowed_tags );
				echo "</p></div>";
			}
		}


		/**
		 * Plugins row action links
		 *
		 * @since 2.3.1
		 *
		 * @param array $actions An array of plugin action links.
		 *
		 * @return array An array of updated action links.
		 */
		public function plugin_action_links( $actions ) {

			$new_actions = array(
				'settings' => sprintf(
					'<a href="%1$s">%2$s</a>',
					admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=pdf_receipts' ),
					esc_html__( 'Settings', 'give-pdf-receipts' )
				),
			);

			return array_merge( $new_actions, $actions );

		}

		/**
		 * Plugin row meta links.
		 *
		 * @since 2.0.4
		 *
		 * @param array $plugin_meta An array of the plugin's metadata.
		 * @param string $plugin_file Path to the plugin file, relative to the plugins directory.
		 *
		 * @return array
		 */
		function plugin_row_meta( $plugin_meta, $plugin_file ) {

			if ( $plugin_file != GIVE_PDF_PLUGIN_BASENAME ) {
				return $plugin_meta;
			}

			$new_meta_links = array(
				sprintf(
					'<a href="%1$s" target="_blank">%2$s</a>',
					esc_url( add_query_arg( array(
							'utm_source'   => 'plugins-page',
							'utm_medium'   => 'plugin-row',
							'utm_campaign' => 'admin',
						), 'http://docs.givewp.com/addon-pdf-receipts' )
					),
					esc_html__( 'Documentation', 'give-pdf-receipts' )
				),
				sprintf(
					'<a href="%1$s" target="_blank">%2$s</a>',
					esc_url( add_query_arg( array(
							'utm_source'   => 'plugins-page',
							'utm_medium'   => 'plugin-row',
							'utm_campaign' => 'admin',
						), 'https://givewp.com/addons/' )
					),
					esc_html__( 'Add-ons', 'give-pdf-receipts' )
				),
			);

			return array_merge( $plugin_meta, $new_meta_links );

		}

		/**
		 * Check plugin environment.
		 *
		 * @since  1.0.0
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
				$this->add_admin_notice( 'prompt_give_activate', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">Give</a> plugin installed and activated for Give - PDF Receipts to activate.', 'give-pdf-receipts' ), 'https://givewp.com' ) );
				$is_working = false;
			}

			return $is_working;
		}

		/**
		 * Check plugin for Give environment.
		 *
		 * @since  1.1.2
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
				&& version_compare( GIVE_VERSION, GIVE_PDF_MIN_GIVE_VERSION, '<' )
			) {

				/* Min. Give. plugin version. */
				// Show admin notice.
				$this->add_admin_notice( 'prompt_give_incompatible', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">Give</a> core version %s for the Give - PDF Receipts add-on to activate.', 'give-pdf-receipts' ), 'https://givewp.com', GIVE_PDF_MIN_GIVE_VERSION ) );

				$is_working = false;
			}

			if ( version_compare( phpversion(), GIVE_PDF_MIN_PHP_VERSION, '<' ) ) {
				$this->add_admin_notice( 'prompt_php_incompatible', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">PHP</a> version %s or above for the Give - PDF Receipts add-on to activate.', 'give-pdf-receipts' ), 'https://givewp.com/documentation/core/requirements/', GIVE_PDF_MIN_PHP_VERSION ) );

				$is_working = false;
			}

			return $is_working;
		}

	} //End Give_PDF_Receipts Class

	/**
	 * Loads a single instance of Give PDF Receipts
	 *
	 * This follows the PHP singleton design pattern.
	 *
	 * Use this function like you would a global variable, except without needing
	 * to declare the global.
	 *
	 * @example <?php $give_pdf_receipts = give_pdf_receipts(); ?>
	 *
	 * @since   1.0
	 *
	 * @see     Give_PDF_Receipts::get_instance()
	 *
	 * @return object Give_PDF_Receipts Returns an instance of the  class
	 */
	function give_pdf_receipts() {
		return Give_PDF_Receipts::get_instance();
	}

	give_pdf_receipts();

endif;

