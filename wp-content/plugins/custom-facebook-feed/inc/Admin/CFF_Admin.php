<?php
/**
 * CFF_Admin plugin.
 *
 * Contains everything about the Admin area
 *
 * @since 2.19
 */

namespace CustomFacebookFeed\Admin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class CFF_Admin{

	/**
	 * Admin constructor
	 *
	 * @since 2.19
	 */
	public function __construct(){
		$this->admin_hook();
		$this->register_assets();
	}


	/**
	 * Admin Hooks + Enqueue
	 *
	 * @since 2.19
	 */
	protected function admin_hook(){
		//Adding Dashboard Menu
		add_action( 'admin_menu', array(  $this, 'register_dashboard_menus' ), 9 );
	}


	/**
	 * Register CFF dashboard Menus.
	 *
	 * @since 2.19
	 */
	public function register_dashboard_menus(){
		$notice = '';
		if ( \cff_main()->cff_error_reporter->are_critical_errors() ) {
			$notice = ' <span class="update-plugins cff-error-alert"><span>!</span></span>';
		}

		$cap = current_user_can( 'manage_custom_facebook_feed_options' ) ? 'manage_custom_facebook_feed_options' : 'manage_options';
		$cap = apply_filters( 'cff_settings_pages_capability', $cap );

		add_menu_page(
			'',
			'Facebook Feed'. $notice,
			$cap,
			'cff-top',
			'cff_settings_page'
		);

		add_submenu_page(
			'cff-top',
			'Settings',
			'Settings'. $notice,
			$cap,
			'cff-top',
			'cff_settings_page'
		);
		add_submenu_page(
			'cff-top',
			'Customize',
			'Customize',
			$cap,
			'cff-style',
			'cff_style_page'
		);
		add_submenu_page(
			'cff-top',
			__( 'About Us', 'custom-facebook-feed' ),
			__( 'About Us', 'custom-facebook-feed' ),
			$cap,
			'cff-about',
			array( \cff_main()->cff_about , 'output')
		);

		add_submenu_page(
			'cff-top',
			__( 'oEmbeds', 'custom-facebook-feed' ),
			'<svg style="height: 14px; margin: 0 8px 0 0; position: relative; top: 2px;" aria-hidden="true" focusable="false" data-prefix="far" data-icon="code" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="svg-inline--fa fa-code fa-w-18 fa-2x"><path fill="currentColor" d="M234.8 511.7L196 500.4c-4.2-1.2-6.7-5.7-5.5-9.9L331.3 5.8c1.2-4.2 5.7-6.7 9.9-5.5L380 11.6c4.2 1.2 6.7 5.7 5.5 9.9L244.7 506.2c-1.2 4.3-5.6 6.7-9.9 5.5zm-83.2-121.1l27.2-29c3.1-3.3 2.8-8.5-.5-11.5L72.2 256l106.1-94.1c3.4-3 3.6-8.2.5-11.5l-27.2-29c-3-3.2-8.1-3.4-11.3-.4L2.5 250.2c-3.4 3.2-3.4 8.5 0 11.7L140.3 391c3.2 3 8.2 2.8 11.3-.4zm284.1.4l137.7-129.1c3.4-3.2 3.4-8.5 0-11.7L435.7 121c-3.2-3-8.3-2.9-11.3.4l-27.2 29c-3.1 3.3-2.8 8.5.5 11.5L503.8 256l-106.1 94.1c-3.4 3-3.6 8.2-.5 11.5l27.2 29c3.1 3.2 8.1 3.4 11.3.4z" class=""></path></svg>' . __( 'oEmbeds', 'custom-facebook-feed' ),
			$cap,
			'cff-oembeds',
			'cff_oembeds_page'
		);

    	//Show a Instagram plugin menu item if it isn't already installed
		if( !is_plugin_active( 'instagram-feed/instagram-feed.php' ) && !is_plugin_active( 'instagram-feed-pro/instagram-feed.php' ) ){
			add_submenu_page(
				'cff-top',
				__( 'Instagram Feed', 'custom-facebook-feed' ),
				'<span class="cff_get_sbi"><svg style="height: 14px; margin: 0 8px 0 0; position: relative; top: 2px;" aria-hidden="true" focusable="false" data-prefix="fab" data-icon="instagram" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="svg-inline--fa fa-instagram fa-w-14 fa-2x"><path fill="currentColor" d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z" class=""></path></svg>' . __( 'Instagram Feed', 'custom-facebook-feed' ) . '</span>',
				$cap,
				'admin.php?page=cff-top&tab=more',
				''
			);
		}

	    //Show a Twitter plugin menu item if it isn't already installed
		if( !is_plugin_active( 'custom-twitter-feeds/custom-twitter-feed.php' ) && !is_plugin_active( 'custom-twitter-feeds-pro/custom-twitter-feed.php' ) ){
			add_submenu_page(
				'cff-top',
				__( 'Twitter Feed', 'custom-facebook-feed' ),
				'<span class="cff_get_ctf"><svg style="height: 14px; margin: 0 8px 0 0; position: relative; top: 2px;" aria-hidden="true" focusable="false" data-prefix="fab" data-icon="twitter" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-twitter fa-w-16 fa-2x"><path fill="currentColor" d="M459.37 151.716c.325 4.548.325 9.097.325 13.645 0 138.72-105.583 298.558-298.558 298.558-59.452 0-114.68-17.219-161.137-47.106 8.447.974 16.568 1.299 25.34 1.299 49.055 0 94.213-16.568 130.274-44.832-46.132-.975-84.792-31.188-98.112-72.772 6.498.974 12.995 1.624 19.818 1.624 9.421 0 18.843-1.3 27.614-3.573-48.081-9.747-84.143-51.98-84.143-102.985v-1.299c13.969 7.797 30.214 12.67 47.431 13.319-28.264-18.843-46.781-51.005-46.781-87.391 0-19.492 5.197-37.36 14.294-52.954 51.655 63.675 129.3 105.258 216.365 109.807-1.624-7.797-2.599-15.918-2.599-24.04 0-57.828 46.782-104.934 104.934-104.934 30.213 0 57.502 12.67 76.67 33.137 23.715-4.548 46.456-13.32 66.599-25.34-7.798 24.366-24.366 44.833-46.132 57.827 21.117-2.273 41.584-8.122 60.426-16.243-14.292 20.791-32.161 39.308-52.628 54.253z" class=""></path></svg>' . __( 'Twitter Feed', 'custom-facebook-feed' ) . '</span>',
				$cap,
				'admin.php?page=cff-top&tab=more',
				''
			);
		}

    	//Show a YouTube plugin menu item if it isn't already installed
		if( !is_plugin_active( 'feeds-for-youtube/youtube-feed.php' ) && !is_plugin_active( 'youtube-feed-pro/youtube-feed.php' ) ){
			add_submenu_page(
				'cff-top',
				__( 'YouTube Feed', 'custom-facebook-feed' ),
				'<span class="cff_get_yt"><svg style="height: 14px; margin: 0 8px 0 0; position: relative; top: 2px;" aria-hidden="true" focusable="false" data-prefix="fab" data-icon="youtube" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="svg-inline--fa fa-youtube fa-w-18 fa-2x"><path fill="currentColor" d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z" class=""></path></svg>' . __( 'YouTube Feed', 'custom-facebook-feed' ) . '</span>',
				$cap,
				'admin.php?page=cff-top&tab=more',
				''
			);
		}
		add_submenu_page(
			'cff-top',
			__( 'Social Wall', 'custom-facebook-feed' ),
			'<span><svg style="height: 14px; margin: 0 8px 0 0; position: relative; top: 2px;" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="th" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-th fa-w-16 fa-2x"><path fill="currentColor" d="M149.333 56v80c0 13.255-10.745 24-24 24H24c-13.255 0-24-10.745-24-24V56c0-13.255 10.745-24 24-24h101.333c13.255 0 24 10.745 24 24zm181.334 240v-80c0-13.255-10.745-24-24-24H205.333c-13.255 0-24 10.745-24 24v80c0 13.255 10.745 24 24 24h101.333c13.256 0 24.001-10.745 24.001-24zm32-240v80c0 13.255 10.745 24 24 24H488c13.255 0 24-10.745 24-24V56c0-13.255-10.745-24-24-24H386.667c-13.255 0-24 10.745-24 24zm-32 80V56c0-13.255-10.745-24-24-24H205.333c-13.255 0-24 10.745-24 24v80c0 13.255 10.745 24 24 24h101.333c13.256 0 24.001-10.745 24.001-24zm-205.334 56H24c-13.255 0-24 10.745-24 24v80c0 13.255 10.745 24 24 24h101.333c13.255 0 24-10.745 24-24v-80c0-13.255-10.745-24-24-24zM0 376v80c0 13.255 10.745 24 24 24h101.333c13.255 0 24-10.745 24-24v-80c0-13.255-10.745-24-24-24H24c-13.255 0-24 10.745-24 24zm386.667-56H488c13.255 0 24-10.745 24-24v-80c0-13.255-10.745-24-24-24H386.667c-13.255 0-24 10.745-24 24v80c0 13.255 10.745 24 24 24zm0 160H488c13.255 0 24-10.745 24-24v-80c0-13.255-10.745-24-24-24H386.667c-13.255 0-24 10.745-24 24v80c0 13.255 10.745 24 24 24zM181.333 376v80c0 13.255 10.745 24 24 24h101.333c13.255 0 24-10.745 24-24v-80c0-13.255-10.745-24-24-24H205.333c-13.255 0-24 10.745-24 24z" class=""></path></svg>' . __( 'Social Wall', 'custom-facebook-feed' ) . '</span>',
			$cap,
			'cff-sw',
			'cff_social_wall_page'
		);
	}

	/**
	 * Register Assets
	 *
	 * @since 2.19
	 */
	public function register_assets(){
		add_action( 'admin_enqueue_scripts' , array( $this, 'enqueue_styles_assets' ) );
		add_action( 'admin_enqueue_scripts' , array( $this, 'enqueue_scripts_assets' ) );
	}



	/**
	 * Enqueue & Register Styles
	 *
	 * @since 2.19
	 */
	public function enqueue_styles_assets(){
		wp_register_style(
			'custom_wp_admin_css',
			CFF_PLUGIN_URL . 'admin/assets/css/cff-admin-style.css',
			false,
			CFFVER
		);
        wp_enqueue_style( 'custom_wp_admin_css' );
        wp_enqueue_style(
        	'cff-font-awesome',
        	'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css',
        	array(),
        	'4.5.0'
        );
        wp_enqueue_style( 'wp-color-picker' );
	}


	/**
	 * Enqueue & Register Scripts
	 *
	 * @since 2.19
	 */
	public function enqueue_scripts_assets(){
	    //Declare color-picker as a dependency
	    wp_enqueue_script(
	    	'cff_admin_script',
	    	CFF_PLUGIN_URL . 'admin/assets/js/cff-admin-scripts.js',
	    	false,
	    	CFFVER
	    );

		wp_localize_script( 'cff_admin_script', 'cffA', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'cff_nonce' => wp_create_nonce( 'cff_nonce' )
			)
		);
		$strings = array(
			'addon_activate'                  => esc_html__( 'Activate', 'custom-facebook-feed' ),
			'addon_activated'                 => esc_html__( 'Activated', 'custom-facebook-feed' ),
			'addon_active'                    => esc_html__( 'Active', 'custom-facebook-feed' ),
			'addon_deactivate'                => esc_html__( 'Deactivate', 'custom-facebook-feed' ),
			'addon_inactive'                  => esc_html__( 'Inactive', 'custom-facebook-feed' ),
			'addon_install'                   => esc_html__( 'Install Addon', 'custom-facebook-feed' ),
			'addon_error'                     => esc_html__( 'Could not install addon. Please download from smashballoon.com and install manually.', 'custom-facebook-feed' ),
			'plugin_error'                    => esc_html__( 'Could not install a plugin. Please download from WordPress.org and install manually.', 'custom-facebook-feed' ),
			'addon_search'                    => esc_html__( 'Searching Addons', 'custom-facebook-feed' ),
			'ajax_url'                        => admin_url( 'admin-ajax.php' ),
			'cancel'                          => esc_html__( 'Cancel', 'custom-facebook-feed' ),
			'close'                           => esc_html__( 'Close', 'custom-facebook-feed' ),
			'nonce'                           => wp_create_nonce( 'cff-admin' ),
			'almost_done'                     => esc_html__( 'Almost Done', 'custom-facebook-feed' ),
			'oops'                            => esc_html__( 'Oops!', 'custom-facebook-feed' ),
			'ok'                              => esc_html__( 'OK', 'custom-facebook-feed' ),
			'plugin_install_activate_btn'     => esc_html__( 'Install and Activate', 'custom-facebook-feed' ),
			'plugin_install_activate_confirm' => esc_html__( 'needs to be installed and activated to import its forms. Would you like us to install and activate it for you?', 'custom-facebook-feed' ),
			'plugin_activate_btn'             => esc_html__( 'Activate', 'custom-facebook-feed' ),
		);
		$strings = apply_filters( 'cff_admin_strings', $strings );

		wp_localize_script(
			'cff_admin_script',
			'cff_admin',
			$strings
		);
	    if( !wp_script_is('jquery-ui-draggable') ) {
	        wp_enqueue_script(
	            array(
	            'jquery',
	            'jquery-ui-core',
	            'jquery-ui-draggable'
	            )
	        );
	    }
	    wp_enqueue_script(
	        array(
	        'hoverIntent',
	        'wp-color-picker'
	        )
	    );
	}





}