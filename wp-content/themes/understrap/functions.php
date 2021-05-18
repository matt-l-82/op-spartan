<?php
/**
 * UnderStrap functions and definitions
 *
 * @package UnderStrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// UnderStrap's includes directory.
$understrap_inc_dir = get_template_directory() . '/inc';

// Array of files to include.
$understrap_includes = array(
	'/theme-settings.php',                  // Initialize theme default settings.
	'/setup.php',                           // Theme setup and custom theme supports.
	'/widgets.php',                         // Register widget area.
	'/enqueue.php',                         // Enqueue scripts and styles.
	'/template-tags.php',                   // Custom template tags for this theme.
	'/pagination.php',                      // Custom pagination for this theme.
	'/hooks.php',                           // Custom hooks.
	'/extras.php',                          // Custom functions that act independently of the theme templates.
	'/customizer.php',                      // Customizer additions.
	'/custom-comments.php',                 // Custom Comments file.
	'/class-wp-bootstrap-navwalker.php',    // Load custom WordPress nav walker. Trying to get deeper navigation? Check out: https://github.com/understrap/understrap/issues/567.
	'/editor.php',                          // Load Editor functions.
	'/deprecated.php',                      // Load deprecated functions.
);

// Load WooCommerce functions if WooCommerce is activated.
if ( class_exists( 'WooCommerce' ) ) {
	$understrap_includes[] = '/woocommerce.php';
}

// Load Jetpack compatibility file if Jetpack is activiated.
if ( class_exists( 'Jetpack' ) ) {
	$understrap_includes[] = '/jetpack.php';
}

// Include files.
foreach ( $understrap_includes as $file ) {
	require_once $understrap_inc_dir . $file;
}

//REMOVE SEACRH WIDGET
	function remove_search_widget() {
		unregister_widget('WP_Widget_Search');
	}
	add_action( 'widgets_init', 'remove_search_widget' );

// CUSTOM POST TYPE INC
include get_template_directory() . '/inc/post-types/CPT.php';

//Portfolio Custom Post Type
include get_template_directory() . '/inc/post-types/register-events.php';

function remove_post_info_single_page($post_info) {
if ( is_single() ) {
	$post_info = '';
	return $post_info;
}}
add_action( 'author_link', 'remove_post_info_single_page' );

add_action('widgets_init', 'wp_event_sidebar');
function wp_event_sidebar() {
	register_sidebar(
		array(
			'name' 			=> 'Event Sidebar',
			'id'  			=> 'sidebar-1',
			'description'  	=> 'This is the event page sidebar. You can add your widgets here.',
			'before_widget' => '<div class="widget-wrapper">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</div>'
		)
	);
}
