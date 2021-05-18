<?php
/**
 * CustomFacebookFeed plugin.
 *
 * The main Custom_Facebook_Feed class that runs the plugins & registers all the ressources.
 *
 * @since 2.19
 */

namespace CustomFacebookFeed;
use CustomFacebookFeed\Admin\CFF_Admin;
use CustomFacebookFeed\Admin\CFF_About;
use CustomFacebookFeed\Admin\CFF_New_User;
use CustomFacebookFeed\Admin\CFF_Notifications;
use CustomFacebookFeed\Admin\CFF_Tracking;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



final class Custom_Facebook_Feed{

	/**
	 * Instance
	 *
	 * @since 2.19
	 * @access private
	 * @static
	 * @var Custom_Facebook_Feed
	 */
	private static $instance;


	/**
	 * CFF_Admin.
	 *
	 * Admin admin panel.
	 *
	 * @since 2.19
	 * @access public
	 *
	 * @var CFF_Admin
	 */
	public $cff_admin;


	/**
	 * CFF_About.
	 *
	 * About page panel.
	 *
	 * @since 2.19
	 * @access public
	 *
	 * @var CFF_About
	 */
	public $cff_about;

	/**
	 * CFF_Error_Reporter.
	 *
	 * Error Reporter panel.
	 *
	 * @since 2.19
	 * @access public
	 *
	 * @var CFF_Error_Reporter
	 */
	public $cff_error_reporter;

	/**
	 * cff_blocks.
	 *
	 * Blocks.
	 *
	 * @since 2.19
	 * @access public
	 *
	 * @var cff_blocks
	 */
	public $cff_blocks;

	/**
	 * CFF_Notifications.
	 *
	 * Notifications System.
	 *
	 * @since 2.19
	 * @access public
	 *
	 * @var CFF_Notifications
	 */
	public $cff_notifications;

	/**
	 * CFF_New_User.
	 *
	 * New User.
	 *
	 * @since 2.19
	 * @access public
	 *
	 * @var CFF_New_User
	 */
	public $cff_newuser;

	/**
	 * CFF_Oembed.
	 *
	 * Oembed Element.
	 *
	 * @since 2.19
	 * @access public
	 *
	 * @var CFF_Oembed
	 */
	public $cff_oembed;

	/**
	 * CFF_Tracking.
	 *
	 * Tracking System.
	 *
	 * @since 2.19
	 * @access public
	 *
	 * @var CFF_Tracking
	 */
	public $cff_tracking;

	/**
	 * CFF_Shortcode.
	 *
	 * Shortcode Class.
	 *
	 * @since 2.19
	 * @access public
	 *
	 * @var CFF_Shortcode
	 */
	public $cff_shortcode;

	/**
	 * CFF_SiteHealth.
	 *
	 *
	 * @since 2.19
	 * @access public
	 *
	 * @var CFF_SiteHealth
	 */
	public $cff_sitehealth;



	/**
	 * Custom_Facebook_Feed Instance.
	 *
	 * Just one instance of the Custom_Facebook_Feed class
	 *
	 * @since 2.19
	 * @access public
	 * @static
	 *
	 * @return Custom_Facebook_Feed
	 */
	public static function instance() {
		if ( null === self::$instance) {
			self::$instance = new self();

			if( !class_exists('CFF_Utils') ) include CFF_PLUGIN_DIR. 'inc/CFF_Utils.php';


			add_action( 'plugins_loaded', [ self::$instance, 'load_textdomain' ], 10 );
			add_action( 'init', [ self::$instance, 'init' ], 0 );



			add_action( 'wp_loaded', [ self::$instance, 'cff_check_for_db_updates' ] );

			add_action( 'wp_head', [ self::$instance, 'cff_custom_css' ] );
			add_action( 'wp_footer', [ self::$instance, 'cff_js' ] );

            add_filter( 'cron_schedules', [ self::$instance, 'cff_cron_custom_interval' ] );
            add_filter('widget_text', 'do_shortcode');

            add_action('wp_ajax_feed_locator', [self::$instance, 'cff_feed_locator']);
			add_action('wp_ajax_nopriv_feed_locator', [self::$instance, 'cff_feed_locator']);

			register_activation_hook( CFF_FILE, [ self::$instance, 'cff_activate' ] );
			register_deactivation_hook( CFF_FILE, [ self::$instance, 'cff_deactivate' ] );
			register_uninstall_hook( CFF_FILE, array('CustomFacebookFeed\Custom_Facebook_Feed','cff_uninstall'));


		}
		return self::$instance;
	}

	/**
 	 * Load Custom_Facebook_Feed textdomain.
 	 *
 	 * @since 2.19
 	 *
 	 * @return void
	 * @access public
 	*/
	public function load_textdomain(){
		load_plugin_textdomain( 'custom-facebook-feed' );
	}


	/**
	 * Init.
	 *
	 * Initialize Custom_Facebook_Feed plugin.
	 *
	 * @since 2.19
	 * @access public
	 */
	public function init() {
		//Load Composer Autoload
		require CFF_PLUGIN_DIR . 'vendor/autoload.php';
		$this->cff_tracking 		= new CFF_Tracking();
		$this->cff_oembed 			= new CFF_Oembed();
		$this->cff_error_reporter	= new CFF_Error_Reporter();
		$this->cff_admin 			= new CFF_Admin();
		$this->cff_blocks 			= new CFF_Blocks();
		$this->cff_shortcode		= new CFF_Shortcode();

		$this->cff_ppca_check_notice_dismiss();
		$this->register_assets();
		$this->group_posts_process();

		if ( $this->cff_blocks->allow_load() ) {
			$this->cff_blocks->load();
		}


		if ( is_admin() ) {
			$this->cff_about		= new CFF_About();
			if ( version_compare( PHP_VERSION,  '5.3.0' ) >= 0 && version_compare( get_bloginfo('version'), '4.6' , '>' ) ) {
				$this->cff_notifications = new CFF_Notifications();
				$this->cff_notifications->init();

				$this->cff_newuser = new CFF_New_User();
				$this->cff_newuser->init();

				require_once trailingslashit( CFF_PLUGIN_DIR ) . 'admin/addon-functions.php';
				$this->cff_sitehealth = new CFF_SiteHealth();
				if ( $this->cff_sitehealth->allow_load() ) {
					$this->cff_sitehealth->load();
				}
			}
		}
	}

	/**
 	 * Launch the Group Posts Cache Process
 	 *
 	 *
 	 * @return void
	 * @access public
 	*/
	function group_posts_process(){
		$cff_cron_schedule = 'hourly';
		$cff_cache_time = get_option( 'cff_cache_time' );
		$cff_cache_time_unit = get_option( 'cff_cache_time_unit' );
		if( $cff_cache_time_unit == 'hours' && $cff_cache_time > 5 ) $cff_cron_schedule = 'twicedaily';
		if( $cff_cache_time_unit == 'days' ) $cff_cron_schedule = 'daily';
		CFF_Group_Posts::group_schedule_event(time(), $cff_cron_schedule);
	}

	/**
	 * Register Assets
	 *
	 * @since 2.19
	 */
	public function register_assets(){
		add_action( 'wp_enqueue_scripts' , array( $this, 'enqueue_styles_assets' ) );
		add_action( 'wp_enqueue_scripts' , array( $this, 'enqueue_scripts_assets' ) );
	}


	/**
	 * Enqueue & Register Styles
	 *
	 * @since 2.19
	 */
	public function enqueue_styles_assets(){
		//Minify files?
	    $options = get_option('cff_style_settings');
	    isset($options[ 'cff_minify' ]) ? $cff_minify = $options[ 'cff_minify' ] : $cff_minify = '';
	    $cff_minify ? $cff_min = '.min' : $cff_min = '';

	    // Respects SSL, Style.css is relative to the current file
	    wp_register_style(
	    	'cff',
	    	CFF_PLUGIN_URL . 'assets/css/cff-style'.$cff_min.'.css' ,
	    	array(),
	    	CFFVER
	    );
	    
        $options['cff_enqueue_with_shortcode'] = isset( $options['cff_enqueue_with_shortcode'] ) ? $options['cff_enqueue_with_shortcode'] : false;
        if ( isset( $options['cff_enqueue_with_shortcode'] ) && !$options['cff_enqueue_with_shortcode'] ) {
            wp_enqueue_style( 'cff' );
        }

	    $options = get_option('cff_style_settings');

	    if ( CFF_GDPR_Integrations::doing_gdpr( $options ) ) {
		    $options[ 'cff_font_source' ] = 'local';
	    }
	    if( !isset( $options[ 'cff_font_source' ] ) ){
	        wp_enqueue_style( 'sb-font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' );
	    } else {

	        if( $options[ 'cff_font_source' ] == 'none' ){
	            //Do nothing
	        } else if( $options[ 'cff_font_source' ] == 'local' ){
	            wp_enqueue_style(
	            	'sb-font-awesome',
	    			CFF_PLUGIN_URL . 'assets/css/font-awesome.min.css',
	            	array(),
	            	'4.7.0'
	            );
	        } else {
	            wp_enqueue_style( 'sb-font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' );
	        }

	    }
	}


	/**
	 * Enqueue & Register Scripts
	 *
	 *
	 * @since 2.19
	 * @access public
	 */
	public function enqueue_scripts_assets(){
		//Minify files?
	    $options = get_option('cff_style_settings');
	    isset($options[ 'cff_minify' ]) ? $cff_minify = $options[ 'cff_minify' ] : $cff_minify = '';
	    $cff_minify ? $cff_min = '.min' : $cff_min = '';

	    //Register the script to make it available
	    wp_register_script(
	    	'cffscripts',
	    	CFF_PLUGIN_URL . 'assets/js/cff-scripts'.$cff_min.'.js' ,
	    	array('jquery'),
	    	CFFVER,
	    	true
	    );
	    $options['cff_enqueue_with_shortcode'] = isset( $options['cff_enqueue_with_shortcode'] ) ? $options['cff_enqueue_with_shortcode'] : false;		
        if ( isset( $options['cff_enqueue_with_shortcode'] ) && !$options['cff_enqueue_with_shortcode'] ) {
            wp_enqueue_script( 'cffscripts' );
        }
	}


	/**
	 * DB Update Checker.
	 *
	 * Check for the db updates
	 *
	 * @since 2.19
	 * @access public
	 */
	function cff_check_for_db_updates(){
	    $db_ver = get_option( 'cff_db_version', 0 );
	    if ( (float) $db_ver < 1.0 ) {
	        global $wp_roles;
	        $wp_roles->add_cap( 'administrator', 'manage_custom_facebook_feed_options' );
	        $cff_statuses_option = get_option( 'cff_statuses', array() );
	        if ( ! isset( $cff_statuses_option['first_install'] ) ) {
	            $options_set = get_option( 'cff_page_id', false );
	            if ( $options_set ) {
	                $cff_statuses_option['first_install'] = 'from_update';
	            } else {
	                $cff_statuses_option['first_install'] = time();
	            }
	            $cff_rating_notice_option = get_option( 'cff_rating_notice', false );
	            if ( $cff_rating_notice_option === 'dismissed' ) {
	                $cff_statuses_option['rating_notice_dismissed'] = time();
	            }
	            $cff_rating_notice_waiting = get_transient( 'custom_facebook_rating_notice_waiting' );
	            if ( $cff_rating_notice_waiting === false
	                 && $cff_rating_notice_option === false ) {
	                $time = 2 * WEEK_IN_SECONDS;
	                set_transient( 'custom_facebook_rating_notice_waiting', 'waiting', $time );
	                update_option( 'cff_rating_notice', 'pending', false );
	            }
	            update_option( 'cff_statuses', $cff_statuses_option, false );
	        }
	        update_option( 'cff_db_version', CFF_DBVERSION );
	    }
		if ( (float) $db_ver < 1.1 ) {
			if ( ! wp_next_scheduled( 'cff_feed_issue_email' ) ) {
				$timestamp = strtotime( 'next monday' );
				$timestamp = $timestamp + (3600 * 24 * 7);
				$six_am_local = $timestamp + CFF_Utils::cff_get_utc_offset() + (6*60*60);
				wp_schedule_event( $six_am_local, 'cffweekly', 'cff_feed_issue_email' );
			}
			update_option( 'cff_db_version', CFF_DBVERSION );
		}
		if ( (float) $db_ver < 1.2 ) {
			if ( ! wp_next_scheduled( 'cff_notification_update' ) ) {
				$timestamp = strtotime( 'next monday' );
				$timestamp = $timestamp + (3600 * 24 * 7);
				$six_am_local = $timestamp + CFF_Utils::cff_get_utc_offset() + (6*60*60);

				wp_schedule_event( $six_am_local, 'cffweekly', 'cff_notification_update' );
			}
			update_option( 'cff_db_version', CFF_DBVERSION );
		}

		if ( (float) $db_ver < 1.3 ) {
			CFF_Feed_Locator::create_table();
			update_option( 'cff_db_version', CFF_DBVERSION );
		}
	}


	/**
	 * Activate
	 *
	 * CFF activation action.
	 *
	 * @since 2.19
	 * @access public
	 */
	function cff_activate() {
	    $options = get_option('cff_style_settings');

	    //Show all post types
	    $options[ 'cff_show_links_type' ] = true;
	    $options[ 'cff_show_event_type' ] = true;
	    $options[ 'cff_show_video_type' ] = true;
	    $options[ 'cff_show_photos_type' ] = true;
	    $options[ 'cff_show_status_type' ] = true;
	    $options[ 'cff_show_albums_type' ] = true;
	    $options[ 'cff_show_author' ] = true;
	    $options[ 'cff_show_text' ] = true;
	    $options[ 'cff_show_desc' ] = true;
	    $options[ 'cff_show_shared_links' ] = true;
	    $options[ 'cff_show_date' ] = true;
	    $options[ 'cff_show_media' ] = true;
	    $options[ 'cff_show_media_link' ] = true;
	    $options[ 'cff_show_event_title' ] = true;
	    $options[ 'cff_show_event_details' ] = true;
	    $options[ 'cff_show_meta' ] = true;
	    $options[ 'cff_show_link' ] = true;
	    $options[ 'cff_show_like_box' ] = true;
	    $options[ 'cff_show_facebook_link' ] = true;
	    $options[ 'cff_show_facebook_share' ] = true;
	    $options[ 'cff_event_title_link' ] = true;

	    update_option( 'cff_style_settings', $options );

	    get_option('cff_show_access_token');
	    update_option( 'cff_show_access_token', true );

	    //Run cron twice daily when plugin is first activated for new users
		if ( ! wp_next_scheduled( 'cff_cron_job' ) ) {
			wp_schedule_event( time(), 'twicedaily', 'cff_cron_job' );
		}
		if ( ! wp_next_scheduled( 'cff_feed_issue_email' ) ) {
			CFF_Utils::cff_schedule_report_email();
		}
		// set usage tracking to false if fresh install.
		$usage_tracking = get_option( 'cff_usage_tracking', false );

		if ( ! is_array( $usage_tracking ) ) {
			$usage_tracking = array(
				'enabled' => false,
				'last_send' => 0
			);
			update_option( 'cff_usage_tracking', $usage_tracking, false );
		}

		if ( ! wp_next_scheduled( 'cff_notification_update' ) ) {
			$timestamp = strtotime( 'next monday' );
			$timestamp = $timestamp + (3600 * 24 * 7);
			$six_am_local = $timestamp + CFF_Utils::cff_get_utc_offset() + (6*60*60);
			wp_schedule_event( $six_am_local, 'cffweekly', 'cff_notification_update' );
		}
	}


	/**
	 * Deactivate
	 *
	 * CFF deactivation action.
	 *
	 * @since 2.19
	 * @access public
	 */
	function cff_deactivate() {
	    wp_clear_scheduled_hook('cff_cron_job');
		wp_clear_scheduled_hook('cff_notification_update');
	}


	/**
	 * Uninstall
	 *
	 * CFF uninstallation action.
	 *
	 * @since 2.19
	 * @access public
	 */
	public static function cff_uninstall(){
	    if ( ! current_user_can( 'activate_plugins' ) ){
	        return;
	    }
	    //If the user is preserving the settings then don't delete them
	    $cff_preserve_settings = get_option('cff_preserve_settings');
	    if($cff_preserve_settings) return;

	    //Settings
	    delete_option( 'cff_show_access_token' );
	    delete_option( 'cff_access_token' );
	    delete_option( 'cff_page_id' );
	    delete_option( 'cff_num_show' );
	    delete_option( 'cff_post_limit' );
	    delete_option( 'cff_show_others' );
	    delete_option( 'cff_cache_time' );
	    delete_option( 'cff_cache_time_unit' );
	    delete_option( 'cff_locale' );
	    delete_option( 'cff_ajax' );
	    delete_option( 'cff_preserve_settings' );
	    //Style & Layout
	    delete_option( 'cff_title_length' );
	    delete_option( 'cff_body_length' );
	    delete_option('cff_style_settings');

		wp_clear_scheduled_hook( 'cff_feed_issue_email' );

		delete_option( 'cff_usage_tracking_config' );
		delete_option( 'cff_usage_tracking' );

		delete_option( 'cff_statuses' );
		delete_option( 'cff_rating_notice' );
		delete_option( 'cff_db_version' );
		delete_option( 'cff_newuser_notifications' );
		delete_option( 'cff_notifications' );

		global $wp_roles;
		$wp_roles->remove_cap( 'administrator', 'manage_custom_facebook_feed_options' );
		wp_clear_scheduled_hook( 'cff_usage_tracking_cron' );
	}



	/**
	 * Custom CSS
	 *
	 * Adding custom CSS
	 *
	 * @since 2.19
	 * @access public
	 */
	function cff_custom_css() {
	    $options = get_option('cff_style_settings');
	    isset($options[ 'cff_custom_css' ]) ? $cff_custom_css = $options[ 'cff_custom_css' ] : $cff_custom_css = '';

	    if( !empty($cff_custom_css) ) echo "\r\n";
	    if( !empty($cff_custom_css) ) echo '<!-- Custom Facebook Feed Custom CSS -->';
	    if( !empty($cff_custom_css) ) echo "\r\n";
	    if( !empty($cff_custom_css) ) echo '<style type="text/css">';
	    if( !empty($cff_custom_css) ) echo "\r\n";
	    if( !empty($cff_custom_css) ) echo stripslashes($cff_custom_css);
	    if( !empty($cff_custom_css) ) echo "\r\n";
	    if( !empty($cff_custom_css) ) echo '</style>';
	    if( !empty($cff_custom_css) ) echo "\r\n";
	}


	/**
	 * Custom JS
	 *
	 * Adding custom JS
	 *
	 * @since 2.19
	 * @access public
	 */
	function cff_js() {
	    $options = get_option('cff_style_settings');
	    $cff_custom_js = isset($options[ 'cff_custom_js' ]) ? $options[ 'cff_custom_js' ] : '';

	    //Link hashtags?
	    isset($options[ 'cff_link_hashtags' ]) ? $cff_link_hashtags = $options[ 'cff_link_hashtags' ] : $cff_link_hashtags = 'true';
	    ($cff_link_hashtags == 'true' || $cff_link_hashtags == 'on') ? $cff_link_hashtags = 'true' : $cff_link_hashtags = 'false';

	    //If linking the post text then don't link the hashtags
	    isset($options[ 'cff_title_link' ]) ? $cff_title_link = $options[ 'cff_title_link' ] : $cff_title_link = false;
	    ($cff_title_link == 'true' || $cff_title_link == 'on') ? $cff_title_link = true : $cff_title_link = false;
	    if ($cff_title_link) $cff_link_hashtags = 'false';

	    echo '<!-- Custom Facebook Feed JS -->';
	    echo "\r\n";
	    echo '<script type="text/javascript">';
	    echo 'var cffajaxurl = "' . admin_url('admin-ajax.php') . '";';
	    echo "\r\n";
	    echo 'var cfflinkhashtags = "' . $cff_link_hashtags . '";';
	    echo "\r\n";
	    if( !empty($cff_custom_js) ) echo "jQuery( document ).ready(function($) {";
	    if( !empty($cff_custom_js) ) echo "\r\n";
	    if( !empty($cff_custom_js) ) echo stripslashes($cff_custom_js);
	    if( !empty($cff_custom_js) ) echo "\r\n";
	    if( !empty($cff_custom_js) ) echo "});";
	    if( !empty($cff_custom_js) ) echo "\r\n";
	    echo '</script>';
	    echo "\r\n";
	}


	/**
	 * Notice Dismiss
	 *
	 * PPCA Check Notice Dismiss
	 *
	 * @since 2.19
	 * @access public
	 */
	function cff_ppca_check_notice_dismiss() {
	    global $current_user;
		$user_id = $current_user->ID;
	    if ( isset($_GET['cff_ppca_check_notice_dismiss']) && '0' == $_GET['cff_ppca_check_notice_dismiss'] ) {
	    	add_user_meta($user_id, 'cff_ppca_check_notice_dismiss', 'true', true);
	    }
	}


	/**
	 * Cron Custom Interval
	 *
	 * Cron Job Custom Interval
	 *
	 * @since 2.19
	 * @access public
	 */
	function cff_cron_custom_interval( $schedules ) {
		$schedules['cffweekly'] = array(
			'interval' => 3600 * 24 * 7,
			'display'  => __( 'Weekly' )
		);
		return $schedules;
	}

	/**
	 * Feed Locator Ajax Call
	 *
	 *
	 * @since 2.19
	 * @access public
	 */
	function cff_feed_locator(){
		$feed_locator_data_array = isset($_POST['feedLocatorData']) && !empty($_POST['feedLocatorData']) && is_array($_POST['feedLocatorData']) ? $_POST['feedLocatorData'] : false;
	  	if($feed_locator_data_array != false):
	  		foreach ($feed_locator_data_array as $single_feed_locator) {
	  			$feed_details = array(
					'feed_id' => $single_feed_locator['feedID'],
					'atts' =>  $single_feed_locator['shortCodeAtts'],
					'location' => array(
						'post_id' => $single_feed_locator['postID'],
						'html' => $single_feed_locator['location']
					)
				);
				$locator = new CFF_Feed_Locator( $feed_details );
				$locator->add_or_update_entry();
	  		}
	  	endif;
	    die();
	}
}


