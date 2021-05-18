<?php
/**
 * Plugin Name: Give - Form Field Manager
 * Plugin URI:  https://givewp.com/addons/form-field-manager/
 * Description: Easily add and control additional donation form fields using an easy-to-use interface.
 * Version:     1.6.0
 * Author:      GiveWP
 * Author URI:  https://givewp.com/
 * Text Domain: give-form-field-manager
 * Domain Path: /languages
 */

use GiveFormFieldManager\Tracking\TrackingServiceProvider;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Form_Fields_Manager
 */
final class Give_Form_Fields_Manager {

	/** Singleton *************************************************************/

	/**
	 * @since 1.0
	 * @var Give_Form_Fields_Manager The one true Give_Form_Fields_Manager
	 */
	private static $instance;

	/**
	 * @var string
	 */
	public $id = 'give-form-field-manager';

	/**
	 * The title of the FFM plugin.
	 *
	 * @var string
	 */
	public $title;

	/**
	 * @var Give_FFM_Render_Form
	 */
	public $render_form;

	/**
	 * @var Give_FFM_Setup
	 */
	public $setup;

	/**
	 * @var Give_FFM_Upload
	 */
	public $upload;

	/**
	 * @var Give_FFM_Frontend_Form
	 */
	public $frontend_form_post;

	/**
	 * @var Give_FFM_Admin_Form
	 */
	public $admin_form;

	/**
	 * @var Give_FFM_Admin_Posting
	 */
	public $admin_posting;

	/**
	 * Notices (array).
	 *
	 * @since 1.1.3
	 *
	 * @var array
	 */
	public $notices = [];

	/**
	 * @since 1.6.0
	 *
	 * @var string[]
	 */
	private $service_providers = [
		TrackingServiceProvider::class,
	];

	/**
	 * Main Give_Form_Fields_Manager Instance.
	 *
	 * Insures that only one instance of Give_Form_Fields_Manager exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since     1.0
	 * @staticvar array $instance
	 * @return Give_Form_Fields_Manager|object - The one true FFM.
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Setup FFM, loading scripts, styles and meta info.
	 *
	 * @since 1.0
	 * @return void
	 */
	private function setup() {

		self::$instance->define_globals();

		add_action( 'before_give_init', [ $this, 'register_service_providers' ] );
		add_action( 'give_init', [ $this, 'init' ], 10 );
		add_action( 'admin_init', [ $this, 'check_plugin_requirements' ] );
		add_action( 'admin_notices', [ $this, 'admin_notices' ] );
	}

	/**
	 * Setup Tribute.
	 *
	 * @since  1.4
	 *
	 * @access private
	 */
	public function init() {

		if ( ! $this->get_environment_warning() ) {
			return;
		}

		do_action( 'give_ffm_setup_actions' );

		self::$instance->load_textdomain();
		self::$instance->includes();
		self::$instance->setup();

		// Setup Instances
		self::$instance->render_form        = new Give_FFM_Render_Form;
		self::$instance->setup              = new Give_FFM_Setup;
		self::$instance->upload             = new Give_FFM_Upload;
		self::$instance->frontend_form_post = new Give_FFM_Frontend_Form;

		if ( is_admin() ) {
			self::$instance->admin_form    = new Give_FFM_Admin_Form;
			self::$instance->admin_posting = new Give_FFM_Admin_Posting;
		}
	}

	/**
	 * Defines all the globally used constants
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function define_globals() {

		$this->title = __( 'Form Field Manager', 'give-form-field-manager' );

		// Plugin Name.
		if ( ! defined( 'GIVE_FFM_PRODUCT_NAME' ) ) {
			define( 'GIVE_FFM_PRODUCT_NAME', 'Form Field Manager' );
		}

		// Plugin Version.
		if ( ! defined( 'GIVE_FFM_VERSION' ) ) {
			define( 'GIVE_FFM_VERSION', '1.6.0' );
		}

		// Min Give Version.
		if ( ! defined( 'GIVE_FFM_MIN_GIVE_VERSION' ) ) {
			define( 'GIVE_FFM_MIN_GIVE_VERSION', '2.10.0' );
		}

		// Plugin Root File.
		if ( ! defined( 'GIVE_FFM_PLUGIN_FILE' ) ) {
			define( 'GIVE_FFM_PLUGIN_FILE', __FILE__ );
		}

		// Plugin Folder Path.
		if ( ! defined( 'GIVE_FFM_PLUGIN_DIR' ) ) {
			define( 'GIVE_FFM_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . basename( dirname( GIVE_FFM_PLUGIN_FILE ) ) . '/' );
		}

		// Plugin Folder URL.
		if ( ! defined( 'GIVE_FFM_PLUGIN_URL' ) ) {
			define( 'GIVE_FFM_PLUGIN_URL', plugin_dir_url( GIVE_FFM_PLUGIN_FILE ) );
		}

		// Plugin basename.
		if ( ! defined( 'GIVE_FFM_BASENAME' ) ) {
			define( 'GIVE_FFM_BASENAME', apply_filters( 'give_ffm_plugin_basename', plugin_basename( GIVE_FFM_PLUGIN_FILE ) ) );
		}

		if ( class_exists( 'Give_License' ) ) {
			new Give_License( GIVE_FFM_PLUGIN_FILE, GIVE_FFM_PRODUCT_NAME, GIVE_FFM_VERSION, 'WordImpress' );
		}
	}

	/**
	 * Check Plugin Requirements.
	 *
	 * @return bool
	 */
	public function check_plugin_requirements() {

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
			$this->add_admin_notice( 'prompt_give_activate', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">Give</a> plugin installed and activated for Give - Form Field Manager to activate.', 'give-form-field-manager' ), 'https://givewp.com' ) );
			$is_working = false;
		}

		return $is_working;
	}

	/**
	 * Check plugin for Give environment.
	 *
	 * @since  1.4
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
			&& version_compare( GIVE_VERSION, GIVE_FFM_MIN_GIVE_VERSION, '<' )
		) {

			/* Min. Give. plugin version. */
			// Show admin notice.
			$this->add_admin_notice( 'prompt_give_incompatible', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">Give</a> core version %s for the Give - Form Field Manager add-on to activate.', 'give-form-field-manager' ), 'https://givewp.com', GIVE_FFM_MIN_GIVE_VERSION ) );

			$is_working = false;
		}

		return $is_working;
	}

	/**
	 * Allow this class and other classes to add notices.
	 *
	 * @since 1.1.3
	 *
	 * @param $slug
	 * @param $class
	 * @param $message
	 */
	public function add_admin_notice( $slug, $class, $message ) {
		$this->notices[ $slug ] = [
			'class'   => $class,
			'message' => $message,
		];
	}

	/**
	 * Handles the displaying of any notices in the admin area.
	 *
	 * @since  1.1.3
	 * @access public
	 * @return mixed
	 */
	public function admin_notices() {

		$allowed_tags = [
			'a'      => [
				'href'  => [],
				'title' => [],
			],
			'br'     => [],
			'em'     => [],
			'strong' => [],
		];

		foreach ( (array) $this->notices as $notice_key => $notice ) {
			echo "<div class='" . esc_attr( $notice['class'] ) . "'><p>";
			echo wp_kses( $notice['message'], $allowed_tags );
			echo "</p></div>";
		}

	}

	/**
	 * Loads the plugin language files.
	 *
	 * @since  v1.0
	 * @access private
	 * @uses   dirname()
	 * @uses   plugin_basename()
	 * @uses   apply_filters()
	 * @uses   load_textdomain()
	 * @uses   get_locale()
	 * @uses   load_plugin_textdomain()
	 */
	private function load_textdomain() {

		// Set filter for plugin's languages directory.
		$give_lang_dir = apply_filters( 'give_languages_directory', dirname( GIVE_FFM_BASENAME ) . '/languages/' );

		// Traditional WordPress plugin locale filter.
		$locale = apply_filters( 'plugin_locale', get_locale(), 'give-form-field-manager' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'give-form-field-manager', $locale );

		// Setup paths to current locale file.
		$mofile_local  = $give_lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/give-form-field-manager/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/give-form-field-manager folder.
			load_textdomain( 'give-form-field-manager', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/give-form-field-manager/languages/ folder.
			load_textdomain( 'give-form-field-manager', $mofile_local );
		} else {
			// Load the default language files
			load_plugin_textdomain( 'give-form-field-manager', false, $give_lang_dir );
		}
	}

	/**
	 * Include all files.
	 *
	 * @since 1.0.0
	 * @return void|bool
	 */
	private function includes() {

		//We need Give to continue.
		if ( ! class_exists( 'Give' ) ) {
			return false;
		}

		self::includes_general();
		self::includes_admin();
	}

	/**
	 * Load general files.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function includes_general() {
		$files = [
			'class-setup.php',
			'class-render-form.php',
			'class-frontend-form.php',
			'class-upload.php',
			'class-emails.php',
			'functions.php',
		];

		foreach ( $files as $file ) {
			require( sprintf( '%s/includes/%s', untrailingslashit( GIVE_FFM_PLUGIN_DIR ), $file ) );
		}
	}

	/**
	 * Load admin files.
	 *
	 * @since 1.0
	 * @return void
	 */
	private function includes_admin() {
		if ( is_admin() ) {
			$files = [
				'admin-activation.php',
				'admin-settings.php',
				'admin-form.php',
				'admin-posting.php',
				'admin-template.php',
				'export-donations.php',
			];

			foreach ( $files as $file ) {
				require( sprintf( '%s/includes/admin/%s', untrailingslashit( GIVE_FFM_PLUGIN_DIR ), $file ) );
			}
		}
	}

	public function register_service_providers() {
		if ( ! Give_FFM()->get_environment_warning() ) {
			return;
		}

		foreach ( $this->service_providers as $service_provider ) {
			give()->registerServiceProvider( $service_provider );
		}
	}
}

require_once __DIR__ . '/vendor/autoload.php';


/**
 * The main function responsible for returning the one true Give_Form_Fields_Manager
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $give_ffm = Give_FFM(); ?>
 *
 * @since 1.0
 * @return Give_Form_Fields_Manager The one true Give_Form_Fields_Manager instance.
 */

function Give_FFM() {
	return Give_Form_Fields_Manager::instance();
}

/**
 * Calling instance of Give FFM.
 *
 * @see     Give_FFM()
 * @since  1.4
 */
Give_FFM();
