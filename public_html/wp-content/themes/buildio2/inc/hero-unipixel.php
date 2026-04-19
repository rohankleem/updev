<!-- Hero -->

<?php $imgpath = get_stylesheet_directory_uri() . "/dist/assets"; ?>

<div class="position-relative mb-5 mb-md-7 hero-bg px-3 px-md-0">

  <!-- SVG BACKGROUND (INLINE) -->
  <svg class="hero-bg-svg" viewBox="0 0 1440 550" preserveAspectRatio="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">

    <mask id="mask0_168_312" maskUnits="userSpaceOnUse" x="0" y="0" width="1440" height="550">
      <rect width="1440" height="550" fill="#ffffff" />
    </mask>

    <g mask="url(#mask0_168_312)">
      <rect width="1440" height="550" fill="url(#paint0_linear_168_312)" />
      <path
        d="M988.013 505.893C867.548 678.796 330.584 667.135 -42.1478 533.002C-274.494 379.06 -204.158 83.0084 -83.6926 -89.8952C36.7726 -262.799 570.568 -307.713 802.914 -153.77C1035.26 0.172529 1108.48 332.989 988.013 505.893Z"
        fill="url(#paint1_linear_168_312)" />
    </g>

    <defs>
      <linearGradient id="paint0_linear_168_312"
        x1="1440" y1="0"
        x2="589.819" y2="538.666"
        gradientUnits="userSpaceOnUse">
        <stop stop-color="#D0BED4" />
        <stop offset="1" stop-color="#711FE6" />
      </linearGradient>

      <linearGradient id="paint1_linear_168_312"
        x1="-38.4053" y1="90.456"
        x2="964.397" y2="470.761"
        gradientUnits="userSpaceOnUse">
        <stop offset="0.348207" stop-color="#711FE5" />
        <stop offset="1" stop-color="#AE84DA" />
      </linearGradient>
    </defs>

  </svg>
  <!-- END SVG BACKGROUND -->

  <div class="container content-space-t-2 content-space-b-1 content-space-b-lg-2">

    <div class="row justify-content-lg-between align-items-lg-center pt-lg-5">

      <div class="col-md-6 col-lg-6 mb-7 mb-md-0 position-relative zi-1">

        <div class="mb-5">
          <span class="text-cap text-white">Server-side Tracking Done Better</span>
          <h1 class="display-5 mb-3  text-white">
            <img class="img-fluid" src="<?php echo get_template_directory_uri(); ?>/img/unipixel-logo-hori-white.svg" alt="UniPixel Logo"><br />
            WordPress Plugin
          </h1>
          <p class="lead text-white">More accurate measurement through server-side data delivery for richer website events and eCommerce data for Meta, TikTok and Google.</p>

          <div>
          <a class="btn btn-lg btn-dark btn-transition btnUniPixelDownload"
            href="https://wordpress.org/plugins/unipixel/"
            target="_blank"
            rel="noopener noreferrer">
            Download Plugin (free)
          </a>
          </div>


        </div>


      </div>

      <div class="col-md-5 zi-1">
        <div class="position-relative">
          <img class="img-fluid" src="<?php echo get_template_directory_uri(); ?>/img/hero-8.png" alt="Buildio">
        </div>
      </div>

    </div>

  </div>
</div>

<!-- End Hero -->