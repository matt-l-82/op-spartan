<?php
/**
 * Custom Facebook Feed Main Shortcode Class
 *
 * @since 2.19
 */

namespace CustomFacebookFeed;

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class CFF_Shortcode extends CFF_Shortcode_Display{

	/**
	 * @var Class
	 */
	protected $fb_feed_settings;

	/**
	 * @var array
	 */
	protected $atts;

	/**
	 * @var array
	 */
	protected $options;

	/**
	 * @var id
	 */
	protected $page_id;

	/**
	 * @var string
	 */
	protected $access_token;


	/**
	 * Shortcode constructor
	 *
	 * @since 2.19
	 */
	public function __construct(){
		$this->init();
	}



	/**
	 * Init.
	 *
	 * @since 2.19
	 */
	public function init(){
		add_shortcode('custom-facebook-feed', array($this, 'display_cff'));
	}


	/**
	 * Get JSON data
	 *
	 * Returns a list of posts JSON form the FaceBook API API
	 *
	 * @since 2.19
	 * @return JSON OBJECT
	 */
	public function get_feed_json( $graph_query, $cff_post_limit, $cff_locale, $cff_show_access_token, $cache_seconds, $cff_cache_time, $show_posts_by ){
		//Is it SSL?
		$cff_ssl = is_ssl() ? '&return_ssl_resources=true' : '';
		$attachments_desc = ( $this->atts['salesposts'] == 'true' ) ? '' : ',description';
        $story_tags = ( $this->atts['storytags'] == 'true' ) ? '' : ',story_tags';


		$cff_posts_json_url = 'https://graph.facebook.com/v4.0/' . $this->page_id . '/' . $graph_query . '?fields=id,updated_time,from{picture,id,name,link},message,message_tags,story'. $story_tags .',status_type,created_time,backdated_time,call_to_action,attachments{title'. $attachments_desc . ',media_type,unshimmed_url,target{id},media{source}}&access_token=' . $this->access_token . '&limit=' . $cff_post_limit . '&locale=' . $cff_locale . $cff_ssl;

		if( $cff_show_access_token && strlen($this->access_token) > 130 ){
			//If using a Page Access Token then set caching time to be minimum of 5 minutes
			if( $cache_seconds < 300 || !isset($cache_seconds) ) $cache_seconds = 300;
		} else {
	        //Temporarily set caching time to be minimum of 1 hour
			if( $cache_seconds < 3600 || !isset($cache_seconds) ) $cache_seconds = 3600;
	        //Temporarily increase default caching time to be 4 hours
			if( $cache_seconds == 3600 ) $cache_seconds = 14400;
		}

		//Don't use caching if the cache time is set to zero
		if ($cff_cache_time != 0){
			//Create the transient name
	        //Split the Page ID in half and stick it together so we definitely have the beginning and end of it
			$trans_page_id = substr($this->page_id, 0, 16) . substr($this->page_id, -15);
			$transient_name = 'cff_' . substr($graph_query, 0, 1) . '_' . $trans_page_id . substr($cff_post_limit, 0, 3) . substr($show_posts_by, 0, 2) . substr($cff_locale, 0, 2);
	        //Limit to 45 chars max
			$transient_name = substr($transient_name, 0, 45);
			//Get any existing copy of our transient data
			if ( false === ( $posts_json = get_transient( $transient_name ) ) || $posts_json === null ) {
				//Get the contents of the Facebook page
				$posts_json = CFF_Utils::cff_fetchUrl($cff_posts_json_url);
	            //Check whether any data is returned from the API. If it isn't then don't cache the error response and instead keep checking the API on every page load until data is returned.
				$FBdata = json_decode($posts_json);
				if( !empty($FBdata) ) {
					//Error returned by API
					if( isset($FBdata->error) ){
                        //Cache the error JSON so doesn't keep making repeated requests
                        //See if a backup cache exists
						if ( false !== get_transient( '!cff_' . $transient_name ) ) {
							$posts_json = get_transient( '!cff_' . $transient_name );
                            //Add error message to backup cache so can be displayed at top of feed
							isset( $FBdata->error->message ) ? $error_message = $FBdata->error->message : $error_message = '';
							isset( $FBdata->error->type ) ? $error_type = $FBdata->error->type : $error_type = '';
							$prefix = '{';
							if (substr($posts_json, 0, strlen($prefix)) == $prefix) $posts_json = substr($posts_json, strlen($prefix));
							$posts_json = '{"cached_error": { "message": "'.$error_message.'", "type": "'.$error_type.'" }, ' . $posts_json;
						}
	                    //Posts data returned by API
					} else {
	                    //If a backup should be created for this data then create one
						set_transient( '!cff_' . $transient_name, $posts_json, YEAR_IN_SECONDS );
					}
	                //Set regular cache
					set_transient( $transient_name, $posts_json, $cache_seconds );

				}
			} else {
				$posts_json = get_transient( $transient_name );
	            //If we can't find the transient then fall back to just getting the json from the api
				if ($posts_json == false) $posts_json = CFF_Utils::cff_fetchUrl($cff_posts_json_url);
			}
		} else {
			$posts_json = CFF_Utils::cff_fetchUrl($cff_posts_json_url);
		}

		return json_decode($posts_json);

	}


	/**
	 * Get Graph Query & (Show only by others)
	 *
	 * Getting the FaceBook Graph Query depending on the settings
	 *
	 * @since 2.19
	 * @return array
	 */
	public function get_graph_query($show_posts_by, $cff_is_group){
		//Use posts? or feed?
		$old_others_option 		= get_option('cff_show_others'); //Use this to help depreciate the old option
		$show_others 			= $this->atts['others'];
		$graph_query 			= 'posts';
		$cff_show_only_others 	= false;
	    //If 'others' shortcode option is used then it overrides any other option
		if ($show_others || $old_others_option == 'on') {
	        //Show posts by everyone
			if ( $old_others_option == 'on' || $show_others == 'on' || $show_others == 'true' || $show_others == true || $cff_is_group ) $graph_query = 'feed';
	        //Only show posts by me
			if ( $show_others == 'false' ) $graph_query = 'posts';
		} else {
		    //Else use the settings page option or the 'showpostsby' shortcode option
	        //Only show posts by me
			if ( $show_posts_by == 'me' ) $graph_query = 'posts';
	        //Show posts by everyone
			if ( $show_posts_by == 'others' || $cff_is_group ) $graph_query = 'feed';
	        //Show posts ONLY by others
			if ( $show_posts_by == 'onlyothers' && !$cff_is_group ) {
				$graph_query = 'visitor_posts';
				$cff_show_only_others = true;
			}
		}

		return [
			'graph_query' 			=> $graph_query,
			'cff_show_only_others'  => $cff_show_only_others
		];

	}


	/**
	 * Get Posts Limit
	 *
	 * Getting the FaceBook Graph Query depending on the settings
	 *
	 * @since 2.19
	 * @return int
	 */
	public function get_post_limit($show_posts){
		$cff_post_limit = $this->atts['limit'];
		if ( isset($cff_post_limit) && $cff_post_limit !== '' ) {
			$cff_post_limit = $cff_post_limit;
		} else {
			if( intval($show_posts) >= 50 ) $cff_post_limit = intval(intval($show_posts) + 7);
			if( intval($show_posts) < 50 ) $cff_post_limit = intval(intval($show_posts) + 5);
			if( intval($show_posts) < 25  ) $cff_post_limit = intval(intval($show_posts) + 4);
			if( intval($show_posts) < 10  ) $cff_post_limit = intval(intval($show_posts) + 3);
			if( intval($show_posts) < 6  ) $cff_post_limit = intval(intval($show_posts) + 2);
			if( intval($show_posts) < 2  ) $cff_post_limit = intval(intval($show_posts) + 1);
		}
		if( $cff_post_limit >= 100 ) $cff_post_limit = 100;
		return $cff_post_limit;
	}


	function cff_get_shortcode_data_attribute_html( $feed_options ) {

	    //If an access token is set in the shortcode then set "use own access token" to be enabled
	    if( isset($feed_options['accesstoken']) ){
	        //Add an encryption string to protect token
	        if ( strpos($feed_options['accesstoken'], ',') !== false ) {
	            //If there are multiple tokens then just add the string after the colon to avoid having to de/reconstruct the array
	            $feed_options['accesstoken'] = str_replace(":", ":02Sb981f26534g75h091287a46p5l63", $feed_options['accesstoken']);
	        } else {
	            //Add an encryption string to protect token
	            $feed_options['accesstoken'] = substr_replace($feed_options['accesstoken'], '02Sb981f26534g75h091287a46p5l63', 25, 0);
	        }
	        $feed_options['ownaccesstoken'] = 'on';
	    }

	    if( !empty($feed_options) ){
	        $json_data = '{';
	        $i = 0;
	        $len = count($feed_options);
	        foreach( $feed_options as $key => $value ) {
	            if ($i == $len - 1) {
	                $json_data .= '&quot;'.$key.'&quot;: &quot;'.$value.'&quot;';
	            } else {
	                $json_data .= '&quot;'.$key.'&quot;: &quot;'.$value.'&quot;, ';
	            }
	            $i++;
	        }
	        $json_data .= '}';

	        return $json_data;
	    }

	}

	function cff_get_processed_options($feed_options){
		$page_id = $feed_options['id'];
		$cff_facebook_string = 'facebook.com';
		( stripos($page_id, $cff_facebook_string) !== false) ? $cff_page_id_url_check = true : $cff_page_id_url_check = false;
		if ( $cff_page_id_url_check === true ) {
	        //Remove trailing slash if exists
			$page_id = preg_replace('{/$}', '', $page_id);
	        //Get last part of url
			$page_id = substr( $page_id, strrpos( $page_id, '/' )+1 );
		}
	    //If the Page ID contains a query string at the end then remove it
		if ( stripos( $page_id, '?') !== false ) $page_id = substr($page_id, 0, strrpos($page_id, '?'));

	    //Always remove slash from end of Page ID
		$page_id = preg_replace('{/$}', '', $page_id);

	    //Update the page ID in the feed options array for use everywhere
		$feed_options['id'] = $page_id;


	    //If an 'account' is specified then use that instead of the Page ID/token from the settings
		$cff_account = trim($feed_options['account']);

		if( !empty( $cff_account ) ){
			$cff_connected_accounts = get_option('cff_connected_accounts');
			if( !empty($cff_connected_accounts) ){

	            //Replace both single and double quotes before decoding
				$cff_connected_accounts = str_replace('\"','"', $cff_connected_accounts);
				$cff_connected_accounts = str_replace("\'","'", $cff_connected_accounts);

				$cff_connected_accounts = json_decode( $cff_connected_accounts );

				if ( isset( $cff_account ) && is_object( $cff_connected_accounts ) ) {
		            //Grab the ID and token from the connected accounts setting
					if( isset( $cff_connected_accounts->{ $cff_account } ) ){
						$feed_options['id'] = $cff_connected_accounts->{ $cff_account }->{'id'};
						$feed_options['accesstoken'] = $cff_connected_accounts->{ $cff_account }->{'accesstoken'};
					}

				}

	            //Replace the encryption string in the Access Token
				if (strpos($feed_options['accesstoken'], '02Sb981f26534g75h091287a46p5l63') !== false) {
					$feed_options['accesstoken'] = str_replace("02Sb981f26534g75h091287a46p5l63","",$feed_options['accesstoken']);
				}
			}
		}

	    //Replace the encryption string in the Access Token
		if (strpos($feed_options['accesstoken'], '02Sb981f26534g75h091287a46p5l63') !== false) {
			$feed_options['accesstoken'] = str_replace("02Sb981f26534g75h091287a46p5l63","",$feed_options['accesstoken']);
		}
		$cff_connected_accounts = get_option('cff_connected_accounts');
		if(!empty($cff_connected_accounts)){
			$connected_accounts = (array)json_decode(stripcslashes($cff_connected_accounts));
			if(array_key_exists($feed_options['id'], $connected_accounts)){
				$feed_options['pagetype'] = $connected_accounts[$feed_options['id']]->pagetype;
			}
		}

		return $feed_options;
	}

	/**
	 * Display.
	 * The main Shortcode display
	 *
	 * @since 2.19
	 */
	public function display_cff($atts) {
		$this->options 			= get_option('cff_style_settings');
		$data_att_html 			= $this->cff_get_shortcode_data_attribute_html( $atts );
		$this->fb_feed_settings = new CFF_FB_Settings($atts, $this->options);
		$this->atts 			= $this->fb_feed_settings->get_settings();
		$id_and_token 			= $this->fb_feed_settings->get_id_and_token();
		$this->page_id 			= $id_and_token['id'];
		$this->access_token 	= $id_and_token['token'];
		$this->atts 			= $this->cff_get_processed_options( $this->atts  );

		#var_dump($this->atts);

		//Vars for the templates
		$atts 			= $this->atts;
		$options 		= $this->options;
		$access_token 	= $this->access_token;
		$page_id 		= $this->page_id;

        if ( $atts['cff_enqueue_with_shortcode'] === 'on' || $atts['cff_enqueue_with_shortcode'] === 'true' ) {
            wp_enqueue_style( 'cff' );
            wp_enqueue_script( 'cffscripts' );
        }

		/********** GENERAL **********/
		$cff_page_type = $this->atts[ 'pagetype' ];
		($cff_page_type == 'group') ? $cff_is_group = true : $cff_is_group = false;


		$cff_show_author = $this->atts[ 'showauthornew' ];
		$cff_cache_time = $this->atts[ 'cachetime' ];
		$cff_locale = $this->atts[ 'locale' ];
		if( empty($cff_locale) || !isset($cff_locale) || $cff_locale == '' ) $cff_locale = 'en_US';
		if(!isset($cff_cache_time) || $cff_cache_time == '' ) $cff_cache_time = 0;
		$cff_cache_time_unit = $this->atts[ 'cacheunit' ];

		$like_box = CFF_Utils::print_template_part( 'likebox', get_defined_vars());


		if($cff_cache_time == 'nocaching') $cff_cache_time = 0;

	   //Like box
		$cff_like_box_position = $this->atts[ 'likeboxpos' ];
		$cff_like_box_outside = $this->atts[ 'likeboxoutside' ];
	    //Open links in new window?
		$target = 'target="_blank"';
		/********** LAYOUT **********/
		$cff_show_author			= $this->check_show_section( 'author' );
		$cff_show_text				= $this->check_show_section( 'text' );
		$cff_show_desc				= $this->check_show_section( 'desc' );
		$cff_show_shared_links		= $this->check_show_section( 'sharedlink' );
		$cff_show_date				= $this->check_show_section( 'date' );
		$cff_show_media				= $this->check_show_section( 'media' );
		$cff_show_media_link		= $this->check_show_section( 'medialink' );
		$cff_show_event_title		= $this->check_show_section( 'eventtitle' );
		$cff_show_event_details		= $this->check_show_section( 'eventdetail' );
		$cff_show_meta				= $this->check_show_section( 'social' );
		$cff_show_link				= $this->check_show_section( ',link' );
		$cff_show_like_box			= $this->check_show_section( 'like' );

	    //Set free version to thumb layout by default as layout option not available on settings page
		$cff_preset_layout = 'thumb';

	    //If the old shortcode option 'showauthor' is being used then apply it
		$cff_show_author_old = $this->atts[ 'showauthor' ];
		if( $cff_show_author_old == 'false' ) $cff_show_author = false;
		if( $cff_show_author_old == 'true' ) $cff_show_author = true;

	    //See Less text
		$cff_posttext_link_color = str_replace('#', '', $this->atts['textlinkcolor']);
		$cff_title_link = CFF_Utils::check_if_on( $this->atts['textlink'] );

	    //Description Style
		$cff_body_styles = $this->get_style_attribute( 'body_description' );

	    //Shared link box
		$cff_disable_link_box = CFF_Utils::check_if_on( $this->atts['disablelinkbox'] );

		$cff_link_box_styles = $this->get_style_attribute( 'link_box' );

	    //Date
		$cff_date_position = ( !isset( $this->atts[ 'datepos' ] ) ) ? 'below' : $this->atts[ 'datepos' ];


	    //Show Facebook link
		$cff_link_to_timeline = $this->atts[ 'linktotimeline' ];

	    //Post Style settings
		$cff_post_style 			= $this->atts['poststyle'];
		$cff_post_bg_color_check 	= ($this->atts['postbgcolor'] !== '' && $this->atts['postbgcolor'] !== '#' && $cff_post_style != 'regular' ) ? true : false;
		$cff_box_shadow				= CFF_Utils::check_if_on( $this->atts['boxshadow'] ) && $cff_post_style == 'boxed';

	    //Text limits
		$body_limit = $this->atts['desclength'];

	    //Get show posts attribute. If not set then default to 25
		$show_posts = ( empty( $this->atts['num'] ) || $this->atts['num'] == 'undefined' ) ? 25 : $this->atts['num'];
	    $show_posts_number = isset( $this->atts['minnum'] ) ? $this->atts['minnum'] : $this->atts['num'];

	    //If the 'Enter my own Access Token' box is unchecked then don't use the user's access token, even if there's one in the field
		get_option('cff_show_access_token') ? $cff_show_access_token = true : $cff_show_access_token = false;

	    //Check whether a Page ID has been defined
		if ($this->page_id == '') {
			echo "Please enter the Page ID of the Facebook feed you'd like to display. You can do this in either the Custom Facebook Feed plugin settings or in the shortcode itself. For example, [custom-facebook-feed id=YOUR_PAGE_ID_HERE].<br /><br />";
			return false;
		}

	    //Is it a restricted page?
		$cff_restricted_page 	= CFF_Utils::check_if_on( $this->atts['restrictedpage'] );

		$show_posts_by 			= $this->atts['showpostsby'];
		$graph_info 			= $this->get_graph_query($show_posts_by, $cff_is_group);
		$graph_query 			= $graph_info['graph_query'];
		$cff_show_only_others 	= $graph_info['cff_show_only_others'];




		// If Mobile and Desktop post nums are not the same, use minnum for API requests.
		$mobile_num = isset( $this->atts['nummobile'] ) && (int)$this->atts['nummobile'] > 0 ? (int)$this->atts['nummobile'] : 0;
		$desk_num = $show_posts;
		if ( $desk_num < $mobile_num ) {
			$this->atts['minnum'] = $mobile_num;
		}

		$show_posts = isset( $this->atts['minnum'] ) ? $this->atts['minnum'] : $show_posts;
		$cff_post_limit = $this->get_post_limit($show_posts);

	    //If the number of posts is set to zero then don't show any and set limit to one
		if ( ($show_posts == '0' || $show_posts == 0) && $show_posts !== ''){
			$show_posts = 0;
			$cff_post_limit = 1;
		}


	    //Calculate the cache time in seconds
		if($cff_cache_time_unit == 'minutes') $cff_cache_time_unit = 60;
		if($cff_cache_time_unit == 'hours') $cff_cache_time_unit = 60*60;
		if($cff_cache_time_unit == 'days') $cff_cache_time_unit = 60*60*24;
		$cache_seconds = $cff_cache_time * $cff_cache_time_unit;





	    //Misc Settings
		$cff_nofollow = CFF_Utils::check_if_on( $this->atts['nofollow'] );
		( $cff_nofollow ) ? $cff_nofollow = ' rel="nofollow noopener"' : $cff_nofollow = '';
		$cff_nofollow_referrer = ' rel="nofollow noopener noreferrer"';

	    //If the number of posts is set to zero then don't show any and set limit to one
		if ( ($this->atts['num'] == '0' || $this->atts['num'] == 0) && $this->atts['num'] !== ''){
			$show_posts = 0;
			$cff_post_limit = 1;
		}

		//***START FEED***
		#$defined_vars = get_defined_vars();
		$cff_content = '';

	    //Create CFF container HTML
		$cff_content .= '<div class="cff-wrapper">';
		$cff_style_class = $this->feed_style_class_compiler();
		$cff_insider_style = $this->get_style_attribute( 'feed_wrapper_insider' );
		$cff_feed_height = CFF_Utils::get_css_distance( $this->atts[ 'height' ] ) ;
		//Feed header
		$cff_show_header 		= CFF_Utils::check_if_on( $this->atts['showheader'] );
		$cff_header_outside 	= CFF_Utils::check_if_on( $this->atts['headeroutside'] );
		$cff_header_type 		= strtolower( $this->atts['headertype'] );
		$cff_header 			= CFF_Utils::print_template_part( 'header', get_defined_vars(), $this);

	    //Add the page header to the outside of the top of feed
		if ($cff_show_header && $cff_header_outside) $cff_content .= $cff_header;

	    //Add like box to the outside of the top of feed
		if ($cff_like_box_position == 'top' && $cff_show_like_box && $cff_like_box_outside) $cff_content .= $like_box;


		//Get Custom Class and Compiled CSS

	    $custom_wrp_class = !empty($cff_feed_height) ? ' cff-wrapper-fixed-height' : '';

		$cff_content .= '<div class="cff-wrapper-ctn '.$custom_wrp_class.'" '.$cff_insider_style.'>';
		$cff_content .= '<div id="cff" ' . $cff_style_class['cff_custom_class'] . ' ' . $cff_style_class['cff_feed_styles'] . ' ' . $cff_style_class['cff_feed_attributes'] . '>';

	    //Add the page header to the inside of the top of feed
		if ($cff_show_header && !$cff_header_outside) $cff_content .= $cff_header;

	    //Add like box to the inside of the top of feed
		if ($cff_like_box_position == 'top' && $cff_show_like_box && !$cff_like_box_outside) $cff_content .= $like_box;
	    //Limit var
		$i_post = 0;

	    //Define array for post items
		$cff_posts_array = array();

	    //ALL POSTS

		$FBdata = $this->get_feed_json( $graph_query, $cff_post_limit, $cff_locale, $cff_show_access_token, $cache_seconds, $cff_cache_time, $show_posts_by );
		if( $cff_is_group ){
			$cff_ssl = is_ssl() ? '&return_ssl_resources=true' : '';
			$attachments_desc = ( $this->atts['salesposts'] == 'true' ) ? '' : ',description';
			$cff_posts_json_url = 'https://graph.facebook.com/v4.0/' . $this->page_id . '/' . $graph_query . '?fields=id,updated_time,from{picture,id,name,link},message,message_tags,story,story_tags,status_type,created_time,backdated_time,call_to_action,attachments{title'. $attachments_desc . ',media_type,unshimmed_url,target{id},media{source}}&access_token=' . $this->access_token . '&limit=' . $cff_post_limit . '&locale=' . $cff_locale . $cff_ssl;
			$this->atts['type'] = 'links_events_videos_photos_albums_statuses_';
			$groups_post = new CFF_Group_Posts($this->page_id, $this->atts, $cff_posts_json_url, $data_att_html, false);
			$groups_post_result = $groups_post->init_group_posts(json_encode($FBdata), false, $show_posts_number);
			$posts_json = $groups_post_result['posts_json'];
			$FBdata = json_decode($posts_json);
		}




		global $current_user;
		$user_id = $current_user->ID;

	        //Print Pretty Message Error
		$cff_content .= CFF_Utils::print_template_part( 'error-message', get_defined_vars());

		$numeric_page_id = '';
		if( !empty($FBdata->data) ){
			if ( ($cff_show_only_others || $show_posts_by == 'others') && count($FBdata->data) > 0 ) {
	                //Get the numeric ID of the page so can compare it to the author of each post
				$first_post_id = explode("_", $FBdata->data[0]->id);
				$numeric_page_id = $first_post_id[0];
			}
		}

        $cff_content .= '<div class="cff-posts-wrap">';

	        //***STARTS POSTS LOOP***
		if( isset($FBdata->data) ){
			foreach ($FBdata->data as $news )
			{
	            //Explode News and Page ID's into 2 values
				$PostID = '';
				$cff_post_id = '';
				if( isset($news->id) ){
					$cff_post_id = $news->id;
					$PostID = explode("_", $cff_post_id);
				}

	                //Reassign variable changes from API v3.3 update
				$news->link 		= isset($news->attachments->data[0]->unshimmed_url) 	? $news->attachments->data[0]->unshimmed_url : '';
				$news->description 	= isset($news->attachments->data[0]->description) 		? $news->attachments->data[0]->description : '';
				$news->object_id 	= isset($news->attachments->data[0]->target->id) 		? $news->attachments->data[0]->target->id : '';
				$news->source 		= isset($news->attachments->data[0]->media->source) 	? $news->attachments->data[0]->media->source : '';
				$news->name 		= isset($news->attachments->data[0]->title) 			? $news->attachments->data[0]->title : '';
				$news->caption 		= isset($news->attachments->data[0]->title) 			? $news->attachments->data[0]->title : '';

	            //Check the post type
				$cff_post_type 		= isset($news->attachments->data[0]->media_type) ? $news->attachments->data[0]->media_type : 'status';

				if ($cff_post_type == 'link') {
					isset($news->story) ? $story = $news->story : $story = '';
	                //Check whether it's an event
					$event_link_check = "facebook.com/events/";
					if( isset($news->link) ){
						$event_link_check = CFF_Utils::stripos($news->link, $event_link_check);
						if ( $event_link_check ) $cff_post_type = 'event';
					}
				}
				$cff_show_links_type = true;
			    $cff_show_event_type = true;
			    $cff_show_video_type = true;
			    $cff_show_photos_type = true;
			    $cff_show_status_type = true;
			    $cff_show_albums_type = true;
			    $cff_events_only = false;
			    //Are we showing ONLY events?
			    if ($cff_show_event_type && !$cff_show_links_type && !$cff_show_video_type && !$cff_show_photos_type && !$cff_show_status_type) $cff_events_only = true;
                //Should we show this post or not?
				$cff_show_post = false;
				switch ($cff_post_type) {
                    case 'link':
                        if ( $cff_show_links_type ) $cff_show_post = true;
                        break;
                    case 'event':
                        if ( $cff_show_event_type ) $cff_show_post = true;
                        break;
                    case 'video':
                         if ( $cff_show_video_type ) $cff_show_post = true;
                        break;
                    case 'swf':
                         if ( $cff_show_video_type ) $cff_show_post = true;
                        break;
                    case 'photo':
                         if ( $cff_show_photos_type ) $cff_show_post = true;
                        break;
                    case 'offer':
                         $cff_show_post = true;
                        break;
                    default:
                        //Check whether it's a status (author comment or like)
                        if ( $cff_show_status_type && !empty($news->message) ) $cff_show_post = true;
                        break;
                }
                //Is it a duplicate post?
				if (!isset($prev_post_message)) $prev_post_message = '';
				if (!isset($prev_post_link)) $prev_post_link = '';
				if (!isset($prev_post_description)) $prev_post_description = '';
				isset($news->message) ? $pm = $news->message : $pm = '';
				isset($news->link) ? $pl = $news->link : $pl = '';
				isset($news->description) ? $pd = $news->description : $pd = '';

				if ( ($prev_post_message == $pm) && ($prev_post_link == $pl) && ($prev_post_description == $pd) ) $cff_show_post = false;

	            //Offset. If the post index ($i_post) is less than the offset then don't show the post
				if( intval($i_post) < intval($this->atts['offset']) ){
					$cff_show_post = false;
					$i_post++;
				}

				//Check post type and display post if selected
				if ( $cff_show_post ) {
	            	//If it isn't then create the post
	                //Only create posts for the amount of posts specified
					if( intval($this->atts['offset']) > 0 ){
						//If offset is being used then stop after showing the number of posts + the offset
						if ( $i_post == (intval($show_posts) + intval($this->atts['offset'])) ) break;
					} else {
	                        //Else just stop after the number of posts to be displayed is reached
						if ( $i_post == $show_posts ) break;
					}
					$i_post++;
	                    //********************************//
	                    //***COMPILE SECTION VARIABLES***//
	                    //********************************//
	                    //Set the post link
					isset($news->link) ? $link = htmlspecialchars($news->link) : $link = '';
	                    //Is it a shared album?
					$shared_album_string = 'shared an album:';
					isset($news->story) ? $story = $news->story : $story = '';
					$shared_album = CFF_Utils::stripos($story, $shared_album_string);
					if ( $shared_album ) {
						$link = str_replace('photo.php?','media/set/?',$link);
					}
	                    //Check the post type
					isset($cff_post_type) ? $cff_post_type = $cff_post_type : $cff_post_type = '';
					if ($cff_post_type == 'link') {
						isset($news->story) ? $story = $news->story : $story = '';
	                        //Check whether it's an event
						$event_link_check = "facebook.com/events/";
	                        //Make sure URL doesn't include 'permalink' as that indicates someone else sharing a post from within an event (eg: https://www.facebook.com/events/617323338414282/permalink/617324268414189/) and the event ID is then not retrieved properly from the event URL as it's formatted like so: facebook.com/events/EVENT_ID/permalink/POST_ID
						$event_link_check = CFF_Utils::stripos($news->link, $event_link_check);
						$event_link_check_2 = CFF_Utils::stripos($news->link, "permalink/");
						if ( $event_link_check && !$event_link_check_2 ) $cff_post_type = 'event';
					}

	                    //If it's an event then check whether the URL contains facebook.com
					if(isset($news->link)){
						if( CFF_Utils::stripos($news->link, "events/") && $cff_post_type == 'event' ){
	                            //Facebook changed the event link from absolute to relative, and so if the link isn't absolute then add facebook.com to front
							( CFF_Utils::stripos($link, 'facebook.com') ) ? $link = $link : $link = 'https://facebook.com' . $link;
						}
					}

	                    //Is it an album?
					$cff_album = false;
					if( isset($news->status_type) ){
						if( $news->status_type == 'added_photos' ){
							if( isset($news->attachments) ){
								if( $news->attachments->data[0]->media_type == 'album' ) $cff_album = true;
							}
						}
					}

	                    //If there's no link provided then link to either the Facebook page or the individual status
					if (empty($news->link)) {
						if ($cff_link_to_timeline == true){
	                            //Link to page
							$link = 'https://facebook.com/' . $this->page_id;
						} else {
	                            //Link to status
							$link = "https://www.facebook.com/" . $this->page_id . "/posts/" . $PostID[1];
						}
					}

					$cff_date = CFF_Utils::print_template_part( 'item/date', get_defined_vars(), $this);




	                //Story/post text vars
					$post_text = '';
					$cff_story_raw = '';
					$cff_message_raw = '';
					$cff_name_raw = '';
					$text_tags = '';
					$post_text_story = '';
					$post_text_message = '';

					//STORY TAGS
					$cff_post_tags = $this->atts[ 'posttags' ];

	                    //Use the story
					if (!empty($news->story)) {
						$cff_story_raw = $news->story;
						$post_text_story .= htmlspecialchars($cff_story_raw);


	                        //Add message and story tags if there are any and the post text is the message or the story
						if( $cff_post_tags && isset($news->story_tags) && !$cff_title_link){

							$text_tags = $news->story_tags;

	                            //Does the Post Text contain any html tags? - the & symbol is the best indicator of this
							$cff_html_check_array = array('&lt;', '’', '“', '&quot;', '&amp;', '&gt;&gt;');

	                            //always use the text replace method
							if( CFF_Utils::cff_stripos_arr($post_text_story, $cff_html_check_array) !== false || ($cff_locale == 'el_GR' && count($news->story_tags) > 3) ) {

	                                //Loop through the tags
								foreach($text_tags as $message_tag ) {

									( isset($message_tag->id) ) ? $message_tag = $message_tag : $message_tag = $message_tag[0];

									$tag_name = $message_tag->name;
									$tag_link = '<a href="https://facebook.com/' . $message_tag->id . '">' . $message_tag->name . '</a>';

									$post_text_story = str_replace($tag_name, $tag_link, $post_text_story);
								}

							} else {

	                                //If it doesn't contain HTMl tags then use the offset to replace message tags
								$message_tags_arr = array();

								$tag = 0;
								foreach($text_tags as $message_tag ) {
									$tag++;
									( isset($message_tag->id) ) ? $message_tag = $message_tag : $message_tag = $message_tag[0];

									isset($message_tag->type) ? $tag_type = $message_tag->type : $tag_type = '';

									$message_tags_arr = CFF_Utils::cff_array_push_assoc(
										$message_tags_arr,
										$tag,
										array(
											'id' => $message_tag->id,
											'name' => $message_tag->name,
											'type' => isset($message_tag->type) ? $message_tag->type : '',
											'offset' => $message_tag->offset,
											'length' => $message_tag->length
										)
									);

								}

	                                //Keep track of the offsets so that if two tags have the same offset then only one is used. Need this as API 2.5 update changed the story_tag JSON format. A duplicate offset usually means '__ was with __ and 3 others'. We don't want to link the '3 others' part.
								$cff_story_tag_offsets = '';
								$cff_story_duplicate_offset = '';

	                                //Check if there are any duplicate offsets. If so, assign to the cff_story_duplicate_offset var.
								for($tag = count($message_tags_arr); $tag >= 1; $tag--) {
									$c = (string)$message_tags_arr[$tag]['offset'];
									if( strpos( $cff_story_tag_offsets, $c ) !== false && $c !== '0' ){
										$cff_story_duplicate_offset = $c;
									} else {
										$cff_story_tag_offsets .= $c . ',';
									}

								}

								for($tag = count($message_tags_arr); $tag >= 1; $tag--) {

	                                    //If the name is blank (aka the story tag doesn't work properly) then don't use it
									if( $message_tags_arr[$tag]['name'] !== '' ) {

	                                        //If it's an event tag or it has the same offset as another tag then don't display it
										if( $message_tags_arr[$tag]['type'] == 'event' || $message_tags_arr[$tag]['offset'] == $cff_story_duplicate_offset || $message_tags_arr[$tag]['type'] == 'page' ){
	                                            //Don't use the story tag in this case otherwise it changes '__ created an event' to '__ created an Name Of Event'
	                                            //Don't use the story tag if it's a page as it causes an issue when sharing a page: Smash Balloon Dev shared a Smash Balloon.
										} else {
											$b = '<a href="https://facebook.com/' . $message_tags_arr[$tag]['id'] . '" target="_blank">' . $message_tags_arr[$tag]['name'] . '</a>';
											$c = $message_tags_arr[$tag]['offset'];
											$d = $message_tags_arr[$tag]['length'];
											$post_text_story = CFF_Utils::cff_mb_substr_replace( $post_text_story, $b, $c, $d);
										}

									}

								}

	                            } // end if/else

	                        } //END STORY TAGS

	                    }

	                    //POST AUTHOR
	                    $cff_author = CFF_Utils::print_template_part( 'item/author', get_defined_vars(), $this);

	                    //Get the actual post text
	                    //Which content should we use?
	                    //Use the message
	                    if (!empty($news->message)) {
	                    	$cff_message_raw = $news->message;

	                    	$post_text_message = htmlspecialchars($cff_message_raw);

	                        //MESSAGE TAGS
	                        //Add message and story tags if there are any and the post text is the message or the story
	                    	if( $cff_post_tags && isset($news->message_tags) && !$cff_title_link){

	                    		$text_tags = $news->message_tags;

	                            //Does the Post Text contain any html tags? - the & symbol is the best indicator of this
	                    		$cff_html_check_array = array('&lt;', '’', '“', '&quot;', '&amp;', '&gt;&gt;', '&gt;');

	                            //always use the text replace method
	                    		if( CFF_Utils::cff_stripos_arr($post_text_message, $cff_html_check_array) !== false ) {
	                                //Loop through the tags
	                    			foreach($text_tags as $message_tag ) {

	                    				( isset($message_tag->id) ) ? $message_tag = $message_tag : $message_tag = $message_tag[0];

	                    				$tag_name = $message_tag->name;
	                    				$tag_link = '<a href="https://facebook.com/' . $message_tag->id . '">' . $message_tag->name . '</a>';

	                    				$post_text_message = str_replace($tag_name, $tag_link, $post_text_message);
	                    			}

	                    		} else {
	                            //If it doesn't contain HTMl tags then use the offset to replace message tags
	                    			$message_tags_arr = array();

	                    			$tag = 0;
	                    			foreach($text_tags as $message_tag ) {
	                    				$tag++;

	                    				( isset($message_tag->id) ) ? $message_tag = $message_tag : $message_tag = $message_tag[0];

	                    				$message_tags_arr = CFF_Utils::cff_array_push_assoc(
	                    					$message_tags_arr,
	                    					$tag,
	                    					array(
	                    						'id' => $message_tag->id,
	                    						'name' => $message_tag->name,
	                    						'type' => isset($message_tag->type) ? $message_tag->type : '',
	                    						'offset' => $message_tag->offset,
	                    						'length' => $message_tag->length
	                    					)
	                    				);
	                    			}

	                                //Keep track of the offsets so that if two tags have the same offset then only one is used. Need this as API 2.5 update changed the story_tag JSON format.
	                    			$cff_msg_tag_offsets = '';
	                    			$cff_msg_duplicate_offset = '';

	                                //Check if there are any duplicate offsets. If so, assign to the cff_duplicate_offset var.
	                    			for($tag = count($message_tags_arr); $tag >= 1; $tag--) {
	                    				$c = (string)$message_tags_arr[$tag]['offset'];
	                    				if( strpos( $cff_msg_tag_offsets, $c ) !== false && $c !== '0' ){
	                    					$cff_msg_duplicate_offset = $c;
	                    				} else {
	                    					$cff_msg_tag_offsets .= $c . ',';
	                    				}
	                    			}

	                                //Sort the array by the "offset" key as Facebook doesn't always return them in the correct order
	                    			usort($message_tags_arr, "CustomFacebookFeed\CFF_Utils::cffSortTags");

	                    			for($tag = count($message_tags_arr)-1; $tag >= 0; $tag--) {

	                                    //If the name is blank (aka the story tag doesn't work properly) then don't use it
	                    				if( $message_tags_arr[$tag]['name'] !== '' ) {

	                    					if( $message_tags_arr[$tag]['offset'] == $cff_msg_duplicate_offset ){
	                                            //If it has the same offset as another tag then don't display it
	                    					} else {
	                    						$b = '<a href="https://facebook.com/' . $message_tags_arr[$tag]['id'] . '">' . $message_tags_arr[$tag]['name'] . '</a>';
	                    						$c = $message_tags_arr[$tag]['offset'];
	                    						$d = $message_tags_arr[$tag]['length'];
	                    						$post_text_message = CFF_Utils::cff_mb_substr_replace( $post_text_message, $b, $c, $d);
	                    					}

	                    				}

	                    			}

	                            } // end if/else

	                        } //END MESSAGE TAGS

	                    }


	                    //Check to see whether it's an embedded video so that we can show the name above the post text if necessary
	                    $cff_soundcloud = false;
	                    $cff_is_video_embed = false;
	                    if ($cff_post_type == 'video' || $cff_post_type == 'music'){
	                    	if( isset($news->source) && !empty($news->source) ){
	                    		$url = $news->source;
	                    	} else if ( isset($news->link) ) {
	                    		$url = $news->link;
	                    	} else {
	                    		$url = '';
	                    	}
	                        //Embeddable video strings
	                    	$vimeo 				= 'vimeo';
	                    	$youtube 			= CFF_Utils::stripos($url, 'youtube');
	                    	$youtu 				= CFF_Utils::stripos($url, 'youtu');
	                    	$youtubeembed 		= CFF_Utils::stripos($url, 'youtube.com/embed');
	                    	$soundcloudembed 	= CFF_Utils::stripos($url, 'soundcloud.com');

	                        //Check whether it's a youtube video
	                    	if($youtube || $youtu || $youtubeembed || (stripos($url, $vimeo) !== false)) {
	                    		$cff_is_video_embed = true;
	                    	}
	                        //If it's soundcloud then add it into the shared link box at the bottom of the post
	                    	if( $soundcloudembed ) $cff_soundcloud = true;
	                    }

	                    //Add the story and message together
	                    $post_text = '';

	                    //DESCRIPTION
	                    $cff_description = '';
	                    if ( !empty($news->description) || !empty($news->caption) ) {
	                    	$description_text = '';

	                    	if ( !empty($news->description) ) {
	                    		$description_text = $news->description;
	                    	}

	                        //Replace ellipsis char in description text
	                    	$raw_desc = $description_text;
	                    	$description_text = str_replace( '…','...', $description_text);

	                        //If the description is the same as the post text then don't show it
	                    	if( $raw_desc ==  $cff_story_raw || $raw_desc ==  $cff_message_raw || $raw_desc ==  $cff_name_raw ){
	                    		$cff_description = '';
	                    	} else {
	                            //Add links and create HTML
	                    		$cff_description .= '<span class="cff-post-desc" '.$cff_body_styles.'>';

	                    		if ($cff_title_link) {
	                    			$cff_description_tagged = CFF_Utils::cff_wrap_span( htmlspecialchars($description_text) );
	                    		} else {
	                    			$cff_description_text = CFF_Autolink::cff_autolink( htmlspecialchars($description_text), $link_color=$cff_posttext_link_color );
	                    			$cff_description_tagged = CFF_Utils::cff_desc_tags($cff_description_text);
	                    		}

	                    		$cff_description .= $cff_description_tagged;
	                    		$cff_description .= ' </span>';
	                    	}

	                    	if( $cff_post_type == 'event' || $cff_is_video_embed || $cff_soundcloud ) $cff_description = '';
	                    }

	                    //Add the message
	                    if($cff_show_text) $post_text .= $post_text_message;

	                    $post_text = apply_filters( 'cff_post_text', $post_text );

		                //If it's a shared video post then add the video name after the post text above the video description so it's all one chunk
	                    if ($cff_post_type == 'video'){
	                    	if( !empty($cff_description) && $cff_description != '' ){
	                    		if( (!empty($post_text) && $post_text != '') && !empty($cff_video_name) ) $post_text .= '<br /><br />';
	                    		$post_text .=  $cff_video_name;
	                    	}
	                    }


	                    //Use the name if there's no other text, unless it's a shared link post as then it's already used as the shared link box title
	                    if ( !empty($news->name) && empty($news->message) && $cff_post_type != 'link' ) {
	                    	$cff_name_raw = $news->name;
	                    	$post_text = htmlspecialchars($cff_name_raw);
	                    }

	                    //OFFER TEXT
	                    if ($cff_post_type == 'offer'){
	                    	isset($news->story) ? $post_text = htmlspecialchars($news->story) . '<br /><br />' : $post_text = '';
	                    	$post_text .= htmlspecialchars($news->name);
	                    }

	                    //Add the description
	                    if( $cff_show_desc && $cff_post_type != 'offer' && $cff_post_type != 'link' ) $post_text .= $cff_description;

	                    //Change the linebreak element if the text issue setting is enabled
	                    $cff_format_issue = CFF_Utils::check_if_on( $this->atts['textissue'] );
	                    $cff_linebreak_el = ( $cff_format_issue ) ?  '<br />' : '<img class="cff-linebreak" />';

	                    //EVENT
	                    $cff_event_has_cover_photo = false;
	                    $cff_event = '';


	                    //Create note
	                    if ($cff_post_type == 'note') {
	                        //Notes don't include any post text and so just replace the post text with the note content
	                    	if($cff_show_text) $post_text = CFF_Utils::print_template_part( 'item/type/note', get_defined_vars(), $this);
	                    }

	                    $cff_post_text = CFF_Utils::print_template_part( 'item/post-text', get_defined_vars(), $this);

	                    //LINK
	                    //Display shared link
	                    $cff_shared_link = CFF_Utils::print_template_part( 'item/shared-link', get_defined_vars(), $this);

	                    //Link to the Facebook post if it's a link or a video
	                    if($cff_post_type == 'link' || $cff_post_type == 'video') $link = "https://www.facebook.com/" . $this->page_id . "/posts/" . $PostID[1];


	                    //If it's a shared post then change the link to use the Post ID so that it links to the shared post and not the original post that's being shared
	                    if( isset($news->status_type) ){
	                    	if( $news->status_type == 'shared_story' ) $link = "https://www.facebook.com/" . $cff_post_id;
	                    }

	                    //Create post action links HTML
	                    $cff_link = CFF_Utils::print_template_part( 'item/post-link', get_defined_vars(), $this);
	                    /* MEDIA LINK */
	                    $cff_media_link = CFF_Utils::print_template_part( 'item/media-link', get_defined_vars(), $this);
	                    //**************************//
	                    //***CREATE THE POST HTML***//
	                    //**************************//
	                    //Start the container
	                    $cff_post_item = CFF_Utils::print_template_part( 'item/container', get_defined_vars(), $this);

	                    //PUSH TO ARRAY
	                    $cff_posts_array = CFF_Utils::cff_array_push_assoc($cff_posts_array, $i_post, $cff_post_item);

	                } // End post type check

	                if (isset($news->message)) $prev_post_message = $news->message;
	                if (isset($news->link))  $prev_post_link = $news->link;
	                if (isset($news->description))  $prev_post_description = $news->description;

	            } // End the loop
	        } //End isset($FBdata->data)

	        //Sort the array in reverse order (newest first)
	        if(!$cff_is_group) ksort($cff_posts_array);

	    // End ALL POSTS


	    //Output the posts array
	        $p = 0;
	        foreach ($cff_posts_array as $post ) {
	        	if ( $p == $show_posts ) break;
	        	$cff_content .= $post;
	        	$p++;
	        }


	    //Add the Like Box inside
	        if ($cff_like_box_position == 'bottom' && $cff_show_like_box && !$cff_like_box_outside) $cff_content .= $like_box;
	        /* Credit link */

            $cff_content .= '</div>'; // End cff-posts-wrap

	        $cff_content .= CFF_Utils::print_template_part( 'credit', get_defined_vars());

	    //End the feed
	         $cff_content .= '<input class="cff-pag-url" type="hidden" data-cff-shortcode="'.$data_att_html.'" data-post-id="' . get_the_ID() . '" data-feed-id="'.$atts['id'].'">';
	        $cff_content .= '</div></div><div class="cff-clear"></div>';

	   	 	//Add the Like Box outside
	        if ($cff_like_box_position == 'bottom' && $cff_show_like_box && $cff_like_box_outside) $cff_content .= $like_box;

	    	//If the feed is loaded via Ajax then put the scripts into the shortcode itself
	        $cff_content .= $this->ajax_loaded();
	        $cff_content .= '</div>';

	        if( isset( $cff_posttext_link_color ) && !empty( $cff_posttext_link_color ) ) $cff_content .= '<style>#cff .cff-post-text a{ color: #'.$cff_posttext_link_color.'; }</style>';
	   	 	//Return our feed HTML to display
	        return $cff_content;
	    }


	}