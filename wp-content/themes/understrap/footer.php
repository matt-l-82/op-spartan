<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after
 *
 * @package UnderStrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$container = get_theme_mod( 'understrap_container_type' );
?>

<?php get_template_part( 'sidebar-templates/sidebar', 'footerfull' ); ?>

<div class="wrapper" id="wrapper-footer">

	<div class="<?php echo esc_attr( $container ); ?>">

		<div class="row">

			<div class="col-lg-3 col-md-12 footer-col">	
				<div class="footer_logo">
					<img src="<?php bloginfo('stylesheet_directory'); ?>/images/Op Spartan logos colour-02.png"  alt="Op Spartan Logo"/>
				</div>
				<ul class="footer_social d-flex justify-content-around">
					<li><a href="/"><i class="fab fa-facebook-square fa-2x"></i></a></li>
					<li><a href="/"><i class="fab fa-instagram-square fa-2x"></i></a></li>
					<li><a href="/"><i class="fab fa-twitter-square fa-2x"></i></a></li>
				</ul>
			</div><!--col end -->

			<div class="col-lg-3 col-md-6 col-sm-12 footer-col">
				<h4>Popular Links</h4>
				<hr class="underline" />
				<ul class="footer_links">
					<li><a href="/">Kinship</a></li>
					<li><a href="/">Support</a></li>
					<li><a href="/">Missions</a></li>
					<li><a href="/">Allies</a></li>
				</ul>
			</div><!-- col end -->

			<div class="col-lg-3 col-md-6 col-sm-12 footer-col">
				<h4>How you can help</h4>
				<hr class="underline" />
				<ul class="footer_help">
					<li><a href="/">Make a donation</a></li>
					<li><a href="/">Become a member</a></li>
					<li><a href="/">Attend an event</a></li>
					<li><a href="/">List your business/charity</a></li>
				</ul>
			</div><!-- col end -->

			<div class="col-lg-3 col-md-6 col-sm-12 footer-col">
				<h4>Contacts</h4>
				<hr class="underline" />
				<ul class="footer_contacts">
					<li><a href="/"><i class="fal fa-phone-alt"></i><span>01234 222111<span></a></li>
					<li><a href="/"><i class="fal fa-paper-plane"></i><span>hello@opspartan.co.uk</span></a></li>
					<li><a href="/"><i class="fal fa-map-marker-alt"></i><span>3a Blue Sky Way<br>Monkton Business Park South<br> NE31 2EQ</span></a></li>
				</ul>
			</div><!-- col end -->

		</div><!-- row end -->

	</div><!-- container end -->

	<div class="footer-copyright">
		<div class="row">
			<div class="container">
				<div class="col-lg-12">
					<ul>
						<li><a href="/">Cookies and Privacy Policy</a></li>
						<li>Copyright 2021, Allrights reserved.</li>
					</ul>
				</div>
			</div>
		</div>
	</div>

</div><!-- wrapper end -->

</div><!-- #page we need this extra closing tag here -->

<?php wp_footer(); ?>

</body>

</html>

