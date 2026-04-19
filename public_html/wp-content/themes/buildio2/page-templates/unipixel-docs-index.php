<?php
/*
Template Name: UniPixel Docs Index
Template Post Type: page
*/

get_header();

/**
 * CONFIG
 * The "UniPixel Documentation" parent page ID.
 */
$unipixel_docs_root_id = 0;

if (isset($_ENV['UNIPIXEL_DOC_PARENT_PAGE_ID'])) {
	$unipixel_docs_root_id = (int) $_ENV['UNIPIXEL_DOC_PARENT_PAGE_ID'];
}

if ($unipixel_docs_root_id < 1) {
	$unipixel_docs_root_id = 346;
}


/**
 * Pagination
 */
$paged = 1;

$paged_var = get_query_var('paged');
if (!empty($paged_var)) {
	$paged = (int) $paged_var;
}

$page_var = get_query_var('page');
if ($paged === 1 && !empty($page_var)) {
	$paged = (int) $page_var;
}

if ($paged < 1) {
	$paged = 1;
}


$per_page = 40;

/**
 * Query: pages that are direct children of the docs root
 */
$args = array(
	'post_type'      => 'page',
	'post_status'    => 'publish',
	'posts_per_page' => $per_page,
	'paged'          => $paged,
	'post_parent'    => $unipixel_docs_root_id,
	'orderby'        => array(
		'menu_order' => 'ASC',
		'title'      => 'ASC',
	),
);

$docs_query = new WP_Query($args);

/**
 * Root page info (for header / intro)
 */
$root_post = get_post($unipixel_docs_root_id);
$root_title = '';
$root_url = '';
if (!empty($root_post) && !is_wp_error($root_post)) {
	$root_title = get_the_title($unipixel_docs_root_id);
	$root_url   = get_permalink($unipixel_docs_root_id);
}

?>

<div class="container mt-5">

	<?php
	// Pull in editor content for the index page (intro text, etc.)
	while (have_posts()) :
		the_post();
	?>
		<h1 class="mb-3"><?php echo esc_html(get_the_title()); ?></h1>

		<?php
		$index_content = get_the_content();
		if (!empty(trim(wp_strip_all_tags($index_content)))) {
			echo '<div class="mb-4">';
			the_content();
			echo '</div>';
		} else {
			// No editor content — skip the self-referential link
		}
	endwhile;
	?>

	<div class="row">
		<?php if ($docs_query->have_posts()) : ?>
			<?php while ($docs_query->have_posts()) : $docs_query->the_post(); ?>

				<?php
				$doc_id = get_the_ID();
				$doc_url = get_permalink($doc_id);
				$doc_title = get_the_title($doc_id);

				$excerpt = get_the_excerpt($doc_id);
				if (empty($excerpt)) {
					// fallback: short trimmed content
					$raw = get_post_field('post_content', $doc_id);
					$excerpt = wp_trim_words(wp_strip_all_tags($raw), 24, '…');
				}

				$thumb_url = '';
				if (has_post_thumbnail($doc_id)) {
					$thumb_url = get_the_post_thumbnail_url($doc_id, 'medium');
				}
				?>

				<div class="col-12 col-md-6 col-lg-4 mb-4">
					<div class="card h-100">
						<?php if (!empty($thumb_url)) : ?>
							<a href="<?php echo esc_url($doc_url); ?>">
								<img
									src="<?php echo esc_url($thumb_url); ?>"
									class="card-img-top"
									alt="<?php echo esc_attr($doc_title); ?>">
							</a>
						<?php endif; ?>

						<div class="card-body">
							<h5 class="card-title mb-0">
								<a href="<?php echo esc_url($doc_url); ?>">
									<?php echo esc_html($doc_title); ?>
								</a>
							</h5>

							<p class="card-text mb-0">
								<?php echo esc_html($excerpt); ?>
							</p>
						</div>
					</div>
				</div>

			<?php endwhile; ?>

			<div class="col-12">
				<?php
				// Pagination: use your existing helper if you have it
				if (function_exists('pagination_echo_bootstrap')) {
					pagination_echo_bootstrap($docs_query);
				} else {
					// Fallback pagination (still Bootstrap-friendly)
					$big = 999999999;
					$links = paginate_links(array(
						'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
						'format'    => '?paged=%#%',
						'current'   => max(1, $paged),
						'total'     => $docs_query->max_num_pages,
						'type'      => 'list',
					));

					if (!empty($links)) {
						echo '<nav aria-label="Docs pagination">';
						// Add Bootstrap pagination classes to the UL if possible
						$links = str_replace("<ul class='page-numbers'>", "<ul class='page-numbers pagination'>", $links);
						$links = str_replace('page-numbers', 'page-numbers page-link', $links);
						$links = str_replace("<li>", "<li class='page-item'>", $links);
						echo $links;
						echo '</nav>';
					}
				}
				?>
			</div>

		<?php else : ?>

			<div class="col-12">
				<div class="alert alert-secondary">
					No documentation pages were found under UniPixel Documentation (ID <?php echo esc_html($unipixel_docs_root_id); ?>).
				</div>
			</div>

		<?php endif; ?>
	</div>
</div>

<?php
wp_reset_postdata();
get_footer();
