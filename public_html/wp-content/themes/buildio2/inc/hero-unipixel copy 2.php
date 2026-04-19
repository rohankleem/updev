<!-- Hero -->
<?php $imgpath2 = get_stylesheet_directory_uri() . "/dist/assets" ?>

<div class="backGradient1 position-relative overflow-hidden mb-5 mb-md-7">

  <div class="container content-space-t-3 content-space-b-2 content-space-b-lg-3">
    <div class="row justify-content-lg-between align-items-lg-center pt-lg-5">

      <div class="col-md-6 col-lg-5 mb-7 mb-md-0 position-relative zi-1">
        <div class="mb-5">
          <h1 class="display-5 mb-3">
            <img class="img-fluid mb-3" src="<?php echo $imgpath ?>/unipixel-logo-hori-4.svg" alt="UniPixel Logo"><br>
            WordPress Plugin
          </h1>
          <p class="lead">
            More accurate measurement through server-side data delivery for richer website events and eCommerce data for Meta and Google.
          </p>
        </div>
      </div>

      <div class="col-md-6 position-relative">
        <div class="position-relative">
          <img class="img-fluid rounded-2 position-relative"
               src="<?php echo get_stylesheet_directory_uri() ?>/img/illus-buildio-1.png"
               alt="Hero Illustration">

          <!-- Keeping glow here but does nothing until you restore styles -->
          <div class="hero-image-glow position-absolute top-50 start-50 translate-middle"></div>
        </div>
      </div>

    </div>

    <!-- Optional Decorative Shape -->
    <?php $location="mel"; ?>
    <?php if ($location==="mel") { ?>
      <img class="position-absolute hero-shape d-none"
           src="<?php echo get_stylesheet_directory_uri() ?>/img/hero-piece-mel-7.svg"
           alt="Decorative Shape">
    <?php } ?>

  </div>
</div>
<!-- End Hero -->
