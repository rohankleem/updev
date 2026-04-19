<!-- Hero -->



<?php $imgpath2 = get_stylesheet_directory_uri() . "/dist/assets" ?>


<div class="position-relative bg-img-start mb-5 mb-md-7" style="background-image: url(<?php echo $imgpath2 ?>/svg/components/card-11.svg);">

	<div class="container content-space-t-2 content-space-b-1 content-space-b-lg-2">

		<div class="row justify-content-lg-between align-items-lg-center pt-lg-5">

			<div class="col-md-6 col-lg-5 mb-7 mb-md-0 position-relative position-relative zi-1">

				<div class="mb-5 ">

					<h1 class="display-5 mb-3">
						<img class="img-fluid" src="<?php echo $imgpath ?>/unipixel-logo-hori-4.svg" alt="UniPixel Logo"><br/>
						WordPress Plugin
					</h1>
					<p class="lead">More accurate measurement through server-side data delivery for richer website events and eCommerce data for Meta, TikTok and Google.</p>
				</div>


			</div>

			<div class="col-md-6">
				<div class="position-relative">
					<!--<img class="img-fluid rounded-2" src="<?php echo $imgpath2 ?>/svg/components/card-10.svg" alt="Image Description">-->
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