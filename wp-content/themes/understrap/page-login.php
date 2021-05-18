<?php
/**
* Template Name: Login Page
*
* Page template
* 
* @package underscores theme
*/
?>

<?php get_header(); ?>

<section class="login">
    <div class="container">
        <div class="row h-100">
            <div class="col-md-6 offset-md-3 my-auto">
                <div class="login-wrapper py-5 px-5">
                    <div class="logo-header d-flex align-items-center">
                        <img src="<?php bloginfo('stylesheet_directory'); ?>/images/login-logo.png" alt="logo" />
                        <h3 class="ml-3 mb-0">Op Spartan</h3>
                    </div>
                    <div class="content">
                        <p class="lead mt-4">
                            Welcome back! Enter your e-mail and password to continue
                        </p>

                        <div>
                            <p class="lead create-account">
                                First time here? <a href="/">Create an account</a>
                            </p>
                        </div>
                    </div><!-- end content -->
                    <div>
                        <?php echo do_shortcode('[mepr-login-form]'); ?>
                    </div>
                </div><!-- end login wrapper -->
            </div><!-- end col -->
        </div><!-- end row -->
    </div><!-- end container -->
</section>


<?php get_footer(); ?>