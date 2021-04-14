<?php get_header(); ?>

<section class="hero">
    <div class="container">
        <div class="row">
            <div class="col-xl-12 text-center page-titles">
                <h5>Strap Line here</h5>
                <div class="underline"></div>
                <h1>blog</h1>
            </div>
        </div>
    </div>
</section>

<section class="blog">
    <div class="container">
        <div class="row">
            <div id="primary" class="col-lg-12 col-md-12">
            
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

            </div>           
                           
            </div> <!-- end col -->
        </div> <!-- end row -->
    </div> <!-- end container -->
</section>

<?php get_footer(); ?>