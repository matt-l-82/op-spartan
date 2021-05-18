<?php
/**
 * The template for displaying all single events posts
 *
 * @package UnderStrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();
$container = get_theme_mod( 'understrap_container_type' );
?>

<section class="hero">
    <div class="container">
        <div class="row">
            <div class="col-xl-12 text-center page-titles">
				<h5><?php echo the_field('date'); ?></h5>
                <div class="underline"></div>
                <h1><?php the_title(); ?></h1>
            </div>
        </div>
    </div>
</section>

<div class="single-events wrapper" id="single-events-wrapper">
	<div class="container">
		<div class="row">
			<div class="col-md-3 mt-2">
				<h4 class="sidebar-title mb-3 pb-3">Event</h4>
				<ul class="sidebar-events d-flex flex-column">
                    <li><img src="<?php bloginfo('stylesheet_directory'); ?>/images/calendar-3.png" alt="calender icon"><span class="ml-2"><?php the_field('date'); ?></span></li>
                    <li><img src="<?php bloginfo('stylesheet_directory'); ?>/images/time-clock-circle.png" alt="clock icon"><span class="ml-2"><?php the_field('time'); ?></span></li>
                    <li><img src="<?php bloginfo('stylesheet_directory'); ?>/images/currency-pound-circle.png" alt="pound icon"><span class="ml-2"><?php the_field('price'); ?></span></li>
                </ul>
				<h4 class="sidebar-title mt-5 pb-3">Organiser</h4>
				<p class="mt-4 mb-4"><?php the_field('organiser'); ?> - OP Spartan</p>
				<div class="events-sidebar-contact">
					<p>Phone</p><p><?php the_field('phone_number'); ?></p></p>
				</div>
				<div class="events-sidebar-contact">
					<p>Website<p><?php the_field('website'); ?></p></p>
				</div>
			</div>
			<div class="col-md-9">

				<div class="row">

				<div class="col-md-9 offset-md-1 col-sm-12">

					<?php while ( have_posts() ) : the_post(); ?>

					<?php get_template_part( 'content', 'events' ); ?>	

				</div>

				</div>

			</div><!-- end col -->

				<?php endwhile; // end of the loop. ?>

		</div><!-- .row -->
	</div><!-- container -->
</div><!-- #single-wrapper -->

<?php
get_footer(); ?>
