<?php
/*
Template Name: UniPixel Doc Page
Template Post Type: page
*/

get_header();

/**
 * CONFIG
 */
$unipixel_docs_root_id = 0;

if (isset($_ENV['UNIPIXEL_DOC_PARENT_PAGE_ID'])) {
	$unipixel_docs_root_id = (int) $_ENV['UNIPIXEL_DOC_PARENT_PAGE_ID'];
}

if ($unipixel_docs_root_id < 1) {
	$unipixel_docs_root_id = 346;
}

$current_id = 0;
if (is_singular('page')) {
	$current_id = (int) get_queried_object_id();
}

$root_post = get_post($unipixel_docs_root_id);

$root_children = get_pages(array(
	'post_type'   => 'page',
	'parent'      => $unipixel_docs_root_id,
	'sort_column' => 'menu_order,post_title',
	'sort_order'  => 'ASC',
	'post_status' => 'publish',
));

// Build grandchildren map for nesting
$grandchildren_map = array();
if (!empty($root_children)) {
	foreach ($root_children as $child_page) {
		$child_id = (int) $child_page->ID;
		$grandkids = get_pages(array(
			'post_type'   => 'page',
			'parent'      => $child_id,
			'sort_column' => 'menu_order,post_title',
			'sort_order'  => 'ASC',
			'post_status' => 'publish',
		));
		if (!empty($grandkids)) {
			$grandchildren_map[$child_id] = $grandkids;
		}
	}
}

// Determine if current page is a grandchild
$current_parent_id = 0;
if ($current_id > 0) {
	$current_post_obj = get_post($current_id);
	if ($current_post_obj && (int) $current_post_obj->post_parent !== $unipixel_docs_root_id) {
		$current_parent_id = (int) $current_post_obj->post_parent;
	}
}
?>

<div class="container-fluid docs-layout px-4 px-lg-5 mt-5">
	<div class="row">

		<!-- LEFT SIDEBAR NAV -->
		<div class="col-12 col-lg-2 mb-4 mb-lg-0">
			<nav class="docs-sidebar">
				<span class="nav-subtitle text-cap">Documentation</span>

				<div class="nav nav-vertical nav-pills nav-sm">

					<?php
					// Root link
					if (!empty($root_post) && !is_wp_error($root_post)) :
						$root_url   = get_permalink($unipixel_docs_root_id);
						$root_title = get_the_title($unipixel_docs_root_id);
						$root_active = ($current_id === $unipixel_docs_root_id) ? ' active' : '';
					?>
						<a class="nav-link<?php echo $root_active; ?>" href="<?php echo esc_url($root_url); ?>">
							<span class="docs-chevron">&rsaquo;</span>
							<?php echo esc_html($root_title); ?>
						</a>
					<?php endif; ?>

					<?php if (!empty($root_children)) : ?>
						<?php foreach ($root_children as $child_page) :
							$child_id    = (int) $child_page->ID;
							$child_url   = get_permalink($child_id);
							$child_title = get_the_title($child_id);
							$is_active   = ($current_id === $child_id || $current_parent_id === $child_id) ? ' active' : '';
						?>
							<a class="nav-link<?php echo $is_active; ?>" href="<?php echo esc_url($child_url); ?>">
								<span class="docs-chevron">&rsaquo;</span>
								<?php echo esc_html($child_title); ?>
							</a>

							<?php if (!empty($grandchildren_map[$child_id])) : ?>
								<div class="nav-collapse">
									<?php foreach ($grandchildren_map[$child_id] as $grandchild) :
										$gc_id     = (int) $grandchild->ID;
										$gc_url    = get_permalink($gc_id);
										$gc_title  = get_the_title($gc_id);
										$gc_active = ($current_id === $gc_id) ? ' active' : '';
									?>
										<a class="nav-link<?php echo $gc_active; ?>" href="<?php echo esc_url($gc_url); ?>">
											<span class="docs-chevron">&rsaquo;</span>
											<?php echo esc_html($gc_title); ?>
										</a>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					<?php endif; ?>

				</div>
			</nav>
		</div>

		<!-- MAIN CONTENT -->
		<div class="col-12 col-lg-8">
			<?php while (have_posts()) : the_post(); ?>
				<h1 class="mb-4"><?php echo esc_html(get_the_title()); ?></h1>
				<div class="docs-content" id="docs-content">
					<?php the_content(); ?>
				</div>
			<?php endwhile; ?>
		</div>

		<!-- RIGHT TOC -->
		<div class="col-12 col-lg-2">
			<div class="docs-toc" id="docs-toc">
				<span class="nav-subtitle text-cap">On this page</span>
				<ul class="docs-toc-list" id="docs-toc-list"></ul>
			</div>
		</div>

	</div>
</div>

<script>
(function () {
	var content = document.getElementById('docs-content');
	var tocList = document.getElementById('docs-toc-list');
	var tocWrap = document.getElementById('docs-toc');
	if (!content || !tocList) return;

	var headings = content.querySelectorAll('h2, h3');
	if (headings.length === 0) {
		if (tocWrap) tocWrap.style.display = 'none';
		return;
	}

	var tocItems = [];
	headings.forEach(function (h, i) {
		if (!h.id) h.id = 'doc-heading-' + i;

		var li = document.createElement('li');
		if (h.tagName === 'H3') li.className = 'docs-toc-h3';

		var a = document.createElement('a');
		a.href = '#' + h.id;
		a.textContent = h.textContent;
		li.appendChild(a);
		tocList.appendChild(li);
		tocItems.push({ el: h, link: a });
	});

	function updateActive() {
		var scrollPos = window.scrollY + 100;
		var current = null;
		for (var i = 0; i < tocItems.length; i++) {
			if (tocItems[i].el.offsetTop <= scrollPos) current = tocItems[i];
		}
		tocItems.forEach(function (item) { item.link.classList.remove('active'); });
		if (current) current.link.classList.add('active');
	}

	window.addEventListener('scroll', updateActive);
	updateActive();
})();
</script>

<?php get_footer(); ?>
