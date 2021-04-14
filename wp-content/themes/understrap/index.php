<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package UnderStrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

$container = get_theme_mod( 'understrap_container_type' );
?>

<?php if ( is_front_page() && is_home() ) : ?>
	<?php get_template_part( 'global-templates/hero' ); ?>
<?php endif; ?>

<div class="wrapper" id="index-wrapper">

	<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

		<div class="row">

			<main class="site-main col-lg-12 col-md-12" id="main">

			<?php 
                    $args = array(
                        'post_type'         => 'post',
                        'post_per_page'     => 6,
                    );

                    $blog_posts = new WP_Query( $args );
                ?>

            <div class="row">
            
            <?php if ( have_posts()) : while( $blog_posts->have_posts() ) :  $blog_posts->the_post(); ?>
        
                <div class="col-md-6">

                    <!-- Post-->
                    <article class="post post_class mt-3">
                        <div class="post-preview"></div>
                        <div class="post-wrapper">
                            <div class="post-header">
                                <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail(); ?></a>
                            </div>
                            <div class="post-content">
                                <h2 class="post-title">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h2>
                                <?php the_excerpt(); ?>
                            </div>
                        </div>
                    </article>
                    <!-- Post end-->
                </div>

                <?php  endwhile; else: ?>

                <p>There's nothing to be displayed!</p>

                <?php endif; ?>

                <?php wp_reset_query(); ?>


			</main><!-- #main -->

			<!-- The pagination component -->
			<?php understrap_pagination(); ?>

			<!-- Do the right sidebar check -->
			<?php get_template_part( 'global-templates/right-sidebar-check' ); ?>

		</div><!-- .row -->

	</div><!-- #content -->

</div><!-- #index-wrapper -->

<?php
get_footer();
