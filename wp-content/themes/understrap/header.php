<?php
/**
 * The header for our theme
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package UnderStrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$container = get_theme_mod( 'understrap_container_type' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php wp_head(); ?>
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	<script src="https://kit.fontawesome.com/f193bb892a.js" crossorigin="anonymous"></script>
</head>

<body <?php body_class(); ?> <?php understrap_body_attributes(); ?>>
<?php do_action( 'wp_body_open' ); ?>

<!-- FB POSTS -->
<div id="fb-root"></div>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v10.0&appId=1769278130035060&autoLogAppEvents=1" nonce="DXMyPrZn"></script>

<!-- Share Button on Kinship Page -->
<div id="fb-root"></div>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v10.0&appId=1769278130035060&autoLogAppEvents=1" nonce="JE9J7ABu"></script>
<div class="site" id="page">

	<!-- ******************* The Navbar Area ******************* -->
<div id="wrapper-navbar">

	<a class="skip-link sr-only sr-only-focusable" href="#content"><?php esc_html_e( 'Skip to content', 'understrap' ); ?></a>

	<nav id="main-nav" class="navbar navbar-expand-lg navbar-dark bg-primary" aria-labelledby="main-nav-label">
			
		<div class="container-fluid d-flex align-items-center">

				<a class="navbar-brand" href="http://localhost/opspartan/">
					<img src="<?php bloginfo('stylesheet_directory'); ?>/images/Op Spartan logos colour-05.png" class="d-inline-block align-top" alt="logo">
				</a>

				<div class="mobile-menu-dropdown ml-auto">
					<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle navigation', 'understrap' ); ?>">
						<span>Menu</span><i class="fas fa-bars"></i>
					</button>
				</div>		
			
				<div class="collapse navbar-collapse" id="navbarNavDropdown">
				<!-- The WordPress Menu goes here -->
				<?php
				wp_nav_menu(
					array(
						'theme_location'  => 'primary',
						'container_class' => 'collapse navbar-collapse',
						'container_id'    => 'navbarNavDropdown',
						'menu_class'      => 'navbar-nav mr-auto',
						'fallback_cb'     => '',
						'menu_id'         => 'main-menu',
						'depth'           => 2,
						'walker'          => new Understrap_WP_Bootstrap_Navwalker(),
					)
				);
				?>
				<ul class="nav navbar-right extra-menu">
					<li class="login"><a href="http://localhost/opspartan/login/"><?php echo do_shortcode('[mepr-login-link]'); ?></a></li>
					<li class="signup"><a href="http://localhost/opspartan/sign-up/"><?php echo do_shortcode('[mepr-membership-link id="143"]'); ?></a></li>
				</ul>
			</div>
		</div><!-- end container -->		
	</nav><!-- .site-navigation -->

</div><!-- #wrapper-navbar end -->
