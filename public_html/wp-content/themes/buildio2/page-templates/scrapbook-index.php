<?php
/*
Template Name: Scrapbook Index
Template Post Type: page
*/

get_header(); ?>

<div class="container mt-5">
    <h1 class="mb-4">From the Notebook</h1>
    <div class="row">
        <?php
        $paged = get_query_var('paged') ? get_query_var('paged') : 1;
        $args = array(
            'post_type' => 'post', // Fetch post type post
            'posts_per_page' => 50, // Number of posts per page
            'paged' => $paged, // Current page number for pagination
        );
        $scrapbook_query = new WP_Query($args);
        if ($scrapbook_query->have_posts()) :
            while ($scrapbook_query->have_posts()) : $scrapbook_query->the_post(); ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <?php if (has_post_thumbnail()) : ?>
                            <img src="<?php the_post_thumbnail_url('post-thumbnail'); ?>" class="card-img-top" alt="<?php the_title(); ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title mb-0"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h5>
                            <small class="text-muted"><?php echo get_the_date(); ?></small>
                            <p class="card-text mb-0"><?php the_excerpt(); ?></p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>

            <div class="col-12">
                <!-- Bootstrap Pagination here -->
                <?php pagination_echo_bootstrap($scrapbook_query); ?>
            </div>

        <?php else : ?>
            <p><?php _e('Sorry, no posts matched your criteria.', 'textdomain'); ?></p>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>