<?php get_header(); ?>

<section class="hero">
    <div class="container">
        <div class="row">
            <div class="col-xl-12 text-center page-titles">
                <h5>Strap Line here</h5>
                <div class="underline"></div>
                <h1>contact</h1>
            </div>
        </div>
    </div>
</section>

<section class="contactForm">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2 class="text-center mt-4">Do not hesitate to reach out.</h2>
                <p class="text-center mt-2 mb-4">Fill out the contact form and we will reply as fat as possible</p>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <?php echo do_shortcode('[contact-form-7 id="25" title="Contact form 1"]'); ?>
            </div>
            <div class="col-md-6">
                <h4 class="contactFormTitle">Contact Details</h4>
                <p class="contactFormCopy">If you'd prefer to email us or call us our details are below.</p>
                <ul class="contactFormDetails">
                    <li><a href="/"><i class="icon fal fa-phone-alt"></i><span class="contactIcon">01234 222111<span></a></li>
					<li><a href="/"><i class="icon fal fa-paper-plane"></i><span class="contactIcon">hello@opspartan.co.uk</span></a></li>
                </ul>
                <div class="underlineExtend"></div>
                <h4 class="contactFormTitle">We're social too</h4>
                <p class="contactFormCopy">You can reach our via our social media channels below</p>
                <ul class="footer_social d-flex">
					<li><a href="/"><i class="icon fab fa-facebook-square"></i></a></li>
					<li><a href="/"><i class="icon fab fa-instagram"></i></a></li>
					<li><a href="/"><i class="icon fab fa-twitter"></i></a></li>
				</ul>
            </div>
        </div>
    </div>
</section>


<?php get_footer(); ?>