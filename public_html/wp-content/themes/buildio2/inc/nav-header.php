<?php
$header_bg_class = 'header-soft'; // default for non-hero pages

if (!empty($has_hero)) {
	$header_bg_class = 'bg-white';
}
?>

<header id="header" class="navbar navbar-expand-lg navbar-end navbar-light <?php echo esc_attr($header_bg_class); ?>">

	<div class="container">
		<nav class="js-mega-menu navbar-nav-wrap">
			<a class="navbar-brand ps-3 ps-md-0" href="/" aria-label="UniPixel">
				<img class="navbar-brand-logo" src="<?php echo get_template_directory_uri(); ?>/img/unipixel-logo-hori.svg" alt="UniPixel">
			</a>


			<button class="navbar-toggler border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#navbarOffcanvas" aria-controls="navbarOffcanvas" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>

			<div class="offcanvas offcanvas-end offcanvas-lg" tabindex="-1" id="navbarOffcanvas" aria-labelledby="navbarOffcanvasLabel">
				<div class="offcanvas-header">
					<a href="/" aria-label="UniPixel">
						<img src="<?php echo get_template_directory_uri(); ?>/img/unipixel-logo-hori.svg" alt="UniPixel" style="height: 1.5rem;">
					</a>
					<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
				</div>
				<div class="offcanvas-body">
				<ul class="navbar-nav">

					<!-- Home -->
					<li class="nav-item">
						<a class="nav-link" href="/">Home</a>
					</li>

					<!-- Documentation -->
					<li class="nav-item">
						<a class="nav-link" href="/unipixel-docs/">Documentation</a>
					</li>

					<!-- Shop -->
					<li class="nav-item">
						<a class="nav-link" href="/shop/">Shop</a>
					</li>

					<!-- Blog -->
					<li class="hs-has-sub-menu nav-item">
						<a id="blogMegaMenu" class="hs-mega-menu-invoker nav-link dropdown-toggle" href="#" role="button" aria-expanded="false">Blog</a>
						<div class="hs-sub-menu dropdown-menu" aria-labelledby="blogMegaMenu" style="min-width: 14rem;">

							<?php
							$args = array(
								'post_type' => 'post',
								'posts_per_page' => 9,
							);

							$post_query = new WP_Query($args);
							$count = 0;

							if ($post_query->have_posts()) {
								while ($post_query->have_posts()) {
									$post_query->the_post();
							?>
									<a class="dropdown-item" href="<?php the_permalink(); ?>">
										<span class="text-truncate"><?php echo esc_html(get_the_title()); ?></span>
										<?php if ($count === 0) { ?>
											<span class="badge bg-success rounded-pill ms-2">New</span>
										<?php } ?>
									</a>
							<?php
									$count++;
								}
								wp_reset_postdata();
							}
							?>

							<div class="dropdown-divider"></div>
							<a class="dropdown-item" href="/blog/"><strong>View all articles...</strong></a>
						</div>
					</li>

					<!-- Download -->
					<li class="nav-item ms-lg-3">
						<a class="btn btn-primary btn-transition mt-4 mt-lg-0" href="https://wordpress.org/plugins/unipixel/" target="_blank" rel="noopener noreferrer">Download</a>
					</li>

				</ul>

				</div><!-- .offcanvas-body -->
			</div><!-- .offcanvas -->

		</nav>
	</div>
</header>