<?php
/**
 * Template Name: Events
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package UnderStrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header(); ?>

<div class="wrapper" id="page-wrapper">
	<div class="container events mt-5 mb-5">
        <div class="row">

            <main id="primary" class="col-md-12 col-lg-12">

                <?php 
                // the query
                $the_query = new WP_Query( array('post_type' => 'events') ); ?>

                <?php if ( $the_query->have_posts() ) : ?>

                <div class="eventWrapper mt-4 mb-5">

                    <!-- the loop -->
                    <?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>

                    <div class="row pt-5 pb-5">
                        <div class="col-md-5 col-sm-12">
                            <a href="<?php the_permalink(); ?>" title="<?php the_field('title'); ?>">
                                <?php the_post_thumbnail(); ?>
                            </a>
                        </div>
                        <div class="col-md-7 col-sm-12">
                            <h2><?php the_field('title'); ?></h2>
                                <ul class="eventDetails d-flex">
                                    <li><img src="<?php bloginfo('stylesheet_directory'); ?>/images/calendar-3.png" alt="calender icon"><?php the_field('date'); ?></li>
                                    <li><img src="<?php bloginfo('stylesheet_directory'); ?>/images/time-clock-circle.png" alt="clock icon"><?php the_field('time'); ?></li>
                                    <li><img src="<?php bloginfo('stylesheet_directory'); ?>/images/currency-pound-circle.png" alt="pound icon"><?php the_field('price'); ?></li>
                                </ul>
                                <?php the_content() ?>
                                <p  class="text-right"><a href="<?php the_permalink(); ?>">More Details<span><i class="fal fa-long-arrow-right ml-2"></i></span></a></p>
                        </div><!-- end col -->
                    </div>
   
                    <?php endwhile; ?>
                    <!-- end of the loop -->

                </div><!-- end row -->

                    <!-- pagination here -->

                    <?php wp_reset_postdata(); ?>

                <?php else : ?>
                    <p><?php _e( 'Sorry, no events matched your criteria.' ); ?></p>
                <?php endif; ?>  

            </main>

        </div> <!-- .row -->

	</div><!-- container -->

</div><!-- #page-wrapper -->

<?php 
get_footer(); ?>
