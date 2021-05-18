<?php get_header(); ?>

<section class="hero">
    <div class="container">
        <div class="row">
            <div class="col-xl-12 text-center page-titles">
                <h5>Strap Line here</h5>
                <div class="underline"></div>
                <h1>missions</h1>
            </div>
        </div>
    </div>
</section>

<section class="missions py-5">
    <div class="container">
        <div class="row h-100">
            <div class="col-lg-6 col-md-6 col-sm-12">
                <h2 class="mb-5">Start a support mission</h2>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Eu id dictum felis cum. Lectus gravida nisl, imperdiet ultrices sed elit nec. Et lorem ut velit nulla cursus gravida. Nunc amet sit quis sapien, elementum turpis.</p>
                <p>Malesuada tempor semper nunc sit leo facilisi senectus accumsan feugiat. Elementum, nibh est nunc turpis urna, amet tellus. Amet, mauris bibendum volutpat fermentum interdum consequat, facilisis. Tincidunt volutpat eget integer enim. Dignissim integer sed non mi nisl, porta egestas.</p>   
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12 d-flex flex-column align-items-center justify-content-center">
                <div class="d-flex flex-column text-center">
                    <a href="http://localhost/opspartan/mission-select/" class="missionsBtn">Start a Mission</a>
                    <div class="d-flex align-items-center mx-auto mt-4">
                        <i class="fal fa-question-circle fa-lg mr-3"></i>
                        <span>What is a mission?</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="last-chance py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-2">
                <h2 class="mb-5">Last chance to vote on these missions...</h2>
                <p>Tip: To vote for your top 5 login to your Op Spartan Account</p>
            </div><!-- end col --> 
                        
            <div class="col-10">
            <!--Start carousel-->
                <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <div class="row">
                            <div class="col-12 col-md d-flex flex-column align-items-start justify-content-start">
                                <img src="<?php bloginfo('stylesheet_directory'); ?>/images/missions1.png';">
                                <p class="carousel-title mt-4">1.<span class="ml-3">Mission title</span></p>
                            </div> 
                            <div class="col-12 col-md d-flex flex-column align-items-start justify-content-start">
                                <img src="<?php bloginfo('stylesheet_directory'); ?>/images/missions2.png';">
                                <p class="carousel-title mt-4">2.<span class="ml-3">Mission title</span></p>
                            </div> 
                            <div class="col-12 col-md d-flex flex-column align-items-start justify-content-start">
                                <img src="<?php bloginfo('stylesheet_directory'); ?>/images/missions3.png';">
                                <p class="carousel-title mt-4">3.<span class="ml-3">Mission title</span></p>
                            </div>                                 
                        </div>
                    </div>
                
                    <div class="carousel-item">
                        <div class="row">
                        <div class="col-12 col-md d-flex flex-column align-items-start justify-content-start">
                                <img src="<?php bloginfo('stylesheet_directory'); ?>/images/missions4.png';">
                                <p class="carousel-title mt-4">4.<span class="ml-3">Mission title</span></p>
                            </div> 
                            <div class="col-12 col-md d-flex flex-column align-items-start justify-content-start">
                                <img src="<?php bloginfo('stylesheet_directory'); ?>/images/missions5.png';">
                                <p class="carousel-title mt-4">5.<span class="ml-3">Mission title</span></p>
                            </div> 
                            <div class="col-12 col-md d-flex flex-column align-items-start justify-content-start">
                                <img src="<?php bloginfo('stylesheet_directory'); ?>/images/missions6.png';">
                                <p class="carousel-title mt-4">6.<span class="ml-3">Mission title</span></p>
                            </div>        
                        </div>
                    </div>
                </div>
            </div>
        </div> 
    </div>

                <!-- ARROWS -->
                <div class="container">
                    <div class="row">
                        <div class="col-12 d-flex align-items-center justify-content-end">
                            <a href="#carouselExampleIndicators" role="button" data-slide="prev">
                                <div class="carousel-nav-icon d-flex justify-content-end mt-3">
                                    <i class="fal fa-long-arrow-left"></i>
                                </div>
                            </a>
                
                            <a href="#carouselExampleIndicators" data-slide="next">
                                <div class="carousel-nav-icon d-flex justify-content-end mt-3">
                                    <i class="fal fa-long-arrow-right"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- end arrows -->

                 
            </div><!-- end container -->
        </div><!-- end row -->
    </div><!-- end container -->
</section>

<section class="member-missions py-5">
    <div class="container">
        <div class="row">
            <div class="col text-center">
                <h2 class="mb-5">Op Spartan Member Missions</h2>
            </div>
        </div><!-- end row -->
    </div><!-- end container -->

    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-sm-6 col-xs-12">
                <div class="card">
                    <img class="card-img-top" src="<?php bloginfo('stylesheet_directory'); ?>/images/missions1.png" alt="Card image cap">
                    <div class="card-body">
                        <h5 class="card-title">Mission Title</h5>
                        <p class="card-text">By Stephen Burns</p>
                    </div>
                </div>
            </div><!-- end col -->
            <div class="col-lg-4 col-sm-6 col-xs-12">
                <div class="card">
                    <img class="card-img-top" src="<?php bloginfo('stylesheet_directory'); ?>/images/missions2.png" alt="Card image cap">
                    <div class="card-body">
                        <h5 class="card-title">Mission Title</h5>
                        <p class="card-text">By Stephen Burns</p>
                    </div>
                </div>
            </div><!-- end col -->
            <div class="col-lg-4 col-sm-6 col-xs-12">
                <div class="card">
                    <img class="card-img-top" src="<?php bloginfo('stylesheet_directory'); ?>/images/missions3.png" alt="Card image cap">
                    <div class="card-body">
                        <h5 class="card-title">Mission Title</h5>
                        <p class="card-text">By Stephen Burns</p>
                    </div>
                </div>
            </div><!-- end col -->
            <div class="col-lg-4 col-sm-6 col-xs-12">
                <div class="card">
                    <img class="card-img-top" src="<?php bloginfo('stylesheet_directory'); ?>/images/missions4.png" alt="Card image cap">
                    <div class="card-body">
                        <h5 class="card-title">Mission Title</h5>
                        <p class="card-text">By Stephen Burns</p>
                    </div>
                </div>
            </div><!-- end col -->
            <div class="col-lg-4 col-sm-6 col-xs-12">
                <div class="card">
                    <img class="card-img-top" src="<?php bloginfo('stylesheet_directory'); ?>/images/missions5.png" alt="Card image cap">
                    <div class="card-body">
                        <h5 class="card-title">Mission Title</h5>
                        <p class="card-text">By Stephen Burns</p>
                    </div>
                </div>
            </div><!-- end col -->
            <div class="col-lg-4 col-sm-6 col-xs-12">
                <div class="card">
                    <img class="card-img-top" src="<?php bloginfo('stylesheet_directory'); ?>/images/missions6.png" alt="Card image cap">
                    <div class="card-body">
                        <h5 class="card-title">Mission Title</h5>
                        <p class="card-text">By Stephen Burns</p>
                    </div>
                </div>
            </div><!-- end col -->
            <div class="col-lg-4 col-sm-6 col-xs-12">
                <div class="card">
                    <img class="card-img-top" src="<?php bloginfo('stylesheet_directory'); ?>/images/missions7.png" alt="Card image cap">
                    <div class="card-body">
                        <h5 class="card-title">Mission Title</h5>
                        <p class="card-text">By Stephen Burns</p>
                    </div>
                </div>
            </div><!-- end col -->
            <div class="col-lg-4 col-sm-6 col-xs-12">
                <div class="card">
                    <img class="card-img-top" src="<?php bloginfo('stylesheet_directory'); ?>/images/missions8.png" alt="Card image cap">
                    <div class="card-body">
                        <h5 class="card-title">Mission Title</h5>
                        <p class="card-text">By Stephen Burns</p>
                    </div>
                </div>
            </div><!-- end col -->
            <div class="col-lg-4 col-sm-6 col-xs-12">
                <div class="card">
                    <img class="card-img-top" src="<?php bloginfo('stylesheet_directory'); ?>/images/missions9.png" alt="Card image cap">
                    <div class="card-body">
                        <h5 class="card-title">Mission Title</h5>
                        <p class="card-text">By Stephen Burns</p>
                    </div>
                </div>
            </div><!-- end col -->
        </div><!--end row -->
    </div><!-- end container -->
</section>

    <section>
        <?php do_shortcode('[give_form id="149"]'); ?>
    </section>



<?php get_footer(); ?>