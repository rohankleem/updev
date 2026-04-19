<!-- Hero -->



<?php $imgpath = get_stylesheet_directory_uri() . "/dist/assets" ?>

<div class="position-relative bg-img-start mb-5 mb-md-7" style="background-image: url(<?php echo $imgpath ?>/svg/components/card-11.svg);">
	<div class="container content-space-t-2 content-space-b-1 content-space-b-lg-2">

	
		<div class="row justify-content-lg-between align-items-lg-center pt-lg-5">


			<div class="col-md-6 col-lg-5 mb-7 mb-md-0 position-relative position-relative zi-1">

				
				<div class="mb-5">
					<span class="text-cap">We help businesses grow</span>
					<h1 class="display-6 mb-3">Digital Streamlining for Your Organisation's Growth</h1>
					<p class="lead">Unlock business potential through digital solutions, integrations and automation. Based right here in Australia.</p>
				</div>

				<div class="d-grid d-sm-flex gap-3">
					<a class="btn btn-primary btn-transition" href="/contact">Contact</a>
					<a class="btn btn-link" href="#">Zoho CRM Specialists <i class="bi-chevron-right small ms-1"></i></a>
				</div>

			</div>

			<div class="col-md-6">
				<div class="position-relative">
					<!--<img class="img-fluid rounded-2" src="<?php echo $imgpath ?>/svg/components/card-10.svg" alt="Image Description">-->
					<!--<img class="img-fluid rounded-2" src="<?php echo get_stylesheet_directory_uri() ?>/img/hero-piece-1.jpg" alt="Image Description">-->
					<img class="img-fluid rounded-2" src="<?php echo get_stylesheet_directory_uri() ?>/img/illus-buildio-1.png" alt="Image Description">
					<div class="position-absolute top-0 end-0 w-100 h-100 bg-soft-primary rounded-2 zi-n1 mt-5 me-n5"></div>
				</div>
			</div>

			

			
		</div>

		<?php

			$location="mel";

		?>

		<?php if ($location==="syd") { ?>
			<img class="position-absolute" src="<?php echo get_stylesheet_directory_uri() ?>/img/hero-piece-syd-1.svg" style="bottom:0; left:0%; opacity: 0.42; width: 40%" alt="Image Description">
		<?php } ?>
		<?php if ($location==="mel") { ?>
			<img class="position-absolute" src="<?php echo get_stylesheet_directory_uri() ?>/img/hero-piece-mel-7.svg" style="bottom:0; left:0%; opacity: 0.45; width: 37%" alt="Image Description">
		<?php } ?>

	</div>
</div>
<!-- End Hero -->