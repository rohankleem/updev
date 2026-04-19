<?php
/*
Template Name: Contact Page Copy
Template Post Type: page
*/


// Exit if accessed directly.
defined('ABSPATH') || exit;
?>
<?php get_header(); ?>

<?php

$scriptsversion = "14";

?>




<?php

$name;
$email;
$phone;
$message = "";
$captcha;
$product = "";
$product_type = "";

$pageTitle = "Get In Touch";
$formHeading = "Send Us Your Enquiry";
$msgLabel = "Message *";

$roboterror = false;
$submitsuccess = false;

if (isset($_GET['product'])) {
	$product = sc_get_content_substr($_GET['product']);
}

if (isset($_GET['cubby'])) {
	$message = $_GET['cubby'];
	$product_type = "cubby";
	$message = str_replace("XX", "\n", $message);
	if (strlen($message) === 0) {
		if (isset($_GET['product'])) {
			$message = sc_get_content_substr($_GET['product']);
		}
	}

	$intro = "I am interested in a quote and info for:\n";
	$message = $intro . $message;

	$pageTitle = "Cubby Enquiry";
	$formHeading = "Let's Get Your Cubby Request Underway";
	$msgLabel = "Your pre-filled cubby details *";
}



?>





<!-- Contact Form Section -->
<div class="container space-top-2 space-bottom-2">


	<div class="row">

		<div class="col-lg-6 mb-9 mb-lg-0 ">

			<div class="mb-5">
				<h1><?php echo $pageTitle ?></h1>
				<p>We have locations and distributors throughout New South Wales, Victoria, ACT and South Australia. We'd love to hear how we can help you.</p>
			</div>

			<div class="row">

				<div class="col-md-6 mb-3 mb-lg-0">
					<div class="card">
						<div class="card-body p-3">

							<h5><i class="fas fa-map-marker-alt"></i> SteelChief NSW</h5>
							<p class="text-body mb-0">

								<span class="h5"><a href="tel:(02) 4632 4222"><i class="fas fa-phone"></i> (02) 4632 4222</span></a><br />
								2/6 Cattle Way<br />Gregory Hills NSW 2567<br />
								<b>Personal service &amp; displays</b><br />
								<i class="fas fa-envelope small"></i> contact@steelchief.com.au
							</p>

						</div>
					</div>
				</div>


				<div class="col-md-6 mb-3 mb-lg-0">
					<div class="card">
						<div class="card-body p-3">

							<h5><i class="fas fa-map-marker-alt"></i> SteelChief VIC</h5>
							<p class="text-body mb-0">

								<span class="h5"><a href="tel:(03) 5334 1954"><i class="fas fa-phone"></i> (03) 5334 1954</span></a><br />
								23 Ring Road<br />Ballarat VIC 3350<br />
								<b>Personal service &amp; displays</b><br />
								<i class="fas fa-envelope small"></i> contact@steelchief.com.au
							</p>

						</div>
					</div>
				</div>

			</div>

			<div class="row mt-2">

				<div class="col-md-6 mb-3 mb-lg-0">
					<div class="card">
						<div class="card-body p-3">

							<h5><i class="fas fa-map-marker-alt"></i> SteelChief Adelaide</h5>
							<p class="text-body mb-0">

								<span class="h5"><a href="tel:(08) 7444 4625"><i class="fas fa-phone"></i> (08) 7444 4625</span></a><br />
								4 De Laine Ave<br />Edwardstown SA 5039<br />
								<b>Personal service &amp; displays</b><br />
								<i class="fas fa-envelope small"></i> contact@steelchief.com.au
							</p>

						</div>
					</div>
				</div>


				<div class="col-md-6 mb-3 mb-lg-0">
					<div class="card">
						<div class="card-body p-3">

							<h5><i class="fas fa-map-marker-alt"></i> ShedCraft (Melbourne)</h5>
							<p class="text-body mb-0">

								<span class="h5"><a href="tel:(03) 9336 3136"><i class="fas fa-phone"></i> (03) 9336 3136</a></span><br />
								U24/16A Keilor Park Drive<br />
								Keilor East VIC 3033<br />
								<b>Personal service &amp; displays</b><br />
								<i class="fas fa-envelope small"></i> info@shedcraft.com.au
							</p>

						</div>
					</div>
				</div>

			</div>


			<hr />
			<h3>Find Your Nearest SteelChief Dealer</h3>
			<p>We have distributors and displays around NSW and VIC.</p>
			<a class="btn btn-primary transition-3d-hover" href="<?php echo get_permalink(3130) ?>" title="Garden Sheds Near Me"><i class="fas fa-search"></i> Find Nearest Locations <i class="fas fa-angle-right fa-sm ml-1"></i></a>

			<hr />

			<h3 class="mt-3">SteelChief Industries Pty Ltd</h3>
			<p>ABN: 83087293034</p>

			<div class="row">
				<div class="col-sm-6">
					<div class="mb-3">
						<span class="d-block h5 mb-1">Head Office:</span>
						<span class="d-block text-body font-size-1">23 Ring Road, Ballarat Victoria 3350, Australia</span>
					</div>

				</div>
				<div class="col-sm-6">
					<div class="mb-3">
						<span class="d-block h5 mb-1">Postal:</span>
						<span class="d-block text-body font-size-1">PO Box 343, Ballarat Victoria 3350, Australia</span>
					</div>
				</div>


			</div>
		</div>

		<div class="col-lg-6 ">
			<div class="ml-lg-5">

				<?php if ($roboterror) { ?>

					<div class="alert alert-danger">
						<a href="#" class="close" data-dismiss="alert">&times;</a>
						<h4 class="alert-heading">Please complete the following</h4>
						</hr />
						<ul>
							<li>Please check the "I am not a robot box to submit this form"</li>
						</ul>
					</div>

				<?php } ?>


				<!-- Form -->
				<form class="card shadow-lg mb-4" id="contactform">
					<div class="card-header border-0 bg-light text-center py-4 px-4 px-md-6">
						<h2 class="h4 mb-0"><i class="fas fa-envelope-open-text"></i> <?php echo $formHeading ?></h2>
					</div>

					<!-- Card Body -->
					<div class="card-body p-4 p-md-5">

						<div class="w-100 text-center mx-md-auto sc-formloader mt-5 d-none">
							<img id="progressImg" src="<?php echo get_stylesheet_directory_uri() ?>/img/loading-arrows.gif" class="loading-img" style="max-width:65px;" alt="Loading" />
							<div>One moment please ...</div>
						</div>

						<div class="alert alert-success d-none" id="thankyousuccess">
							<h4 class="alert-heading">Thank You! Enquiry Submitted</h4>
							</hr />
							<p>Your enquiry is important to us and all enquiries are carefully managed and responded to with due attention. In peak times, response turn-arounds may extend out to 2 days.</p>
						</div>

						<div class="alert alert-danger d-none" id="submissionunsuccessful">
							<h4 class="alert-heading">Uh oh! Looks Like We Encountered A Problem</h4>
							</hr />
							<p>In trying to log your enquiry, looks like we've hit a snag. Sorry about this. We'll look into this and get it fixed. In the mean time are you please able to give us a call and we'll handle your enquiry right away.</p>
						</div>

						<!-- START form field block-->
						<div class="row" id="contactformblock">


							<?php if ($product) { ?>
								<div class="col-sm-12">
									<!-- Form Group -->
									<div class="js-form-message form-group">
										<label for="product" class="input-label">Product</label>
										<input maxlength="50" class="form-control" name="product" id="product" placeholder="" aria-label="Product" value="<?php echo $product ?>" disabled>
									</div>
									<!-- End Form Group -->
								</div>
							<?php } ?>


							<div class="col-sm-12">
								<!-- Form Group -->
								<div class="js-form-message form-group">
									<label for="fullName" class="input-label">Name *</label>
									<input type="text" maxlength="50" class="form-control" name="fullName" id="fullName" placeholder="Your Name" aria-label="Your Name" required data-msg="Please enter your name">
								</div>
								<!-- End Form Group -->
							</div>

							<div class="col-sm-12">
								<!-- Form Group -->
								<div class="js-form-message form-group">
									<label for="emailAddress" class="input-label">Email address *</label>
									<input type="email" maxlength="50" class="form-control" name="emailAddress" id="emailAddress" placeholder="youremail@email.com" aria-label="youremail@email.com" required data-msg="Please enter a valid email address">
								</div>
								<!-- End Form Group -->
							</div>

							<div class="col-sm-12">
								<!-- Form Group -->
								<div class="js-form-message form-group">
									<label for="phone" class="input-label">Phone *</label>
									<input type="tel" maxlength="50" class="form-control" name="phone" id="phone" placeholder="Phone" aria-label="Phone" required data-msg="Please enter your phone number">
								</div>
								<!-- End Form Group -->
							</div>

							<div class="col-sm-12">
								<!-- Form Group -->
								<div class="js-form-message form-group">
									<label for="postcode" class="input-label">Postcode *</label>
									<input type="tel" minlength="4" maxlength="4" class="form-control" name="postcode" id="postcode" placeholder="0000" aria-label="Postcode" required data-msg="A valid postcode helps direct your enquiry">
									<small id="postcodeHelp" class="form-text text-muted">A postcode helps us direct your enquiry to the right location</small>
								</div>
								<!-- End Form Group -->
							</div>

							<div class="col-sm-12">
								<!-- Form Group -->
								<div class="js-form-message form-group">
									<label for="message" class="input-label"><?php echo $msgLabel ?></label>
									<div class="input-group">
										<textarea maxlength="1000" class="form-control" rows="4" name="message" id="message" placeholder="Hi there, I would like to ..." aria-label="Hi there, I would like to ..." required data-msg="Please enter a message"><?php echo $message ?></textarea>
									</div>
								</div>
								<!-- End Form Group -->
							</div>

							<?php wp_nonce_field('contacty_noncy_action', 'contacty_noncy'); ?>

							<div class="col-sm-12">
								<div class="form-group">
									<div class="alert alert-danger d-none" id="captcha-error">! Please tick the "I am not a robot box"</div>
									<div class="g-recaptcha mb-3" data-sitekey="6LfumcIZAAAAAGLpvpHYlD89wkq0-mQWc09AIuyk" data-callback="reCaptchaCallBackValid" data-expired-callback="reCaptchaCallBackExpired" data-error-callback="reCaptchaCallBackError">
									</div>
									<input type="hidden" id="gclid_field" name="gclid_field" value="">
									<input type="hidden" id="utm_social_fields" name="utm_social_fields" value="">
									<input type="hidden" id="product_type" name="product_type" value="<?php echo $product_type ?>">
									<button type="submit" id="contactsubmitbutton" name="contactsubmitbutton" class="btn btn-block btn-primary transition-3d-hover">Submit</button>
								</div>
							</div>

							<div class="text-center">
								<p class="small">Your enquiry is important to us, all enquiries are carefully managed and responded to as soon as possible. In peak times, response turn-arounds may extend out to 2 days.</p>
							</div>

						</div>
						<!-- End form field block-->

					</div>
					<!-- End Card Body -->
				</form>
				<!-- End Form -->




			</div>
		</div>
	</div>
</div>
<!-- End Contact Form Section -->


<script>






</script>





<?php get_footer(); ?>