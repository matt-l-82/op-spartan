<?php
/**
 * The template for displaying all single posts
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
                <h5><?php echo get_the_date(); ?></h5>
                <div class="underline"></div>
                <h1><?php the_title(); ?></h1>
            </div>
        </div>
    </div>
</section>

<div class="wrapper" id="single-wrapper">

	<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

		<div class="row">

		<div id="primary" class="col-md-8 offset-md-2">
			<main class="site-main" id="main">

				<?php

				while ( have_posts() ) {
					the_post();
					get_template_part( 'loop-templates/content', 'single' );
					understrap_post_nav();
				}

				?>

			</main><!-- #main -->
		</div>	

		</div><!-- .row -->

	</div><!-- #content -->

</div><!-- #single-wrapper -->

<?php
get_footer();
