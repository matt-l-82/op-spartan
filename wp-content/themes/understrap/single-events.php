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

<div class="wrapper" id="single-events-wrapper">

	<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

		<div class="row">

        	<div id="primary" class="col-md-9 col-lg-9">
				<main id="main" class="site-main" role="main">
 
					<?php while ( have_posts() ) : the_post(); ?>
			
					<?php get_template_part( 'content', 'events' ); ?>
							
				</main><!-- #main -->
			</div><!-- #primary -->
 
					<?php endwhile; // end of the loop. ?>

		</div><!-- .row -->

	</div><!-- #content -->

</div><!-- #single-wrapper -->

<?php
get_footer(); ?>
