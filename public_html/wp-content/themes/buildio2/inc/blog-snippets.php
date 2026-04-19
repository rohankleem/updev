<div class="container">

    <span class="text-cap">From the</span>
    <h2>Notebook</h2>


    <?php
    $args = array(
        'posts_per_page' => 7,
        'orderby' => 'date',
        'order' => 'DESC'
    );

    $blog_snippet_list = new WP_Query($args);

    if ($blog_snippet_list->have_posts()) : ?>


        <div class="swiperBlogSnippets swiper">
            <div class="swiper-wrapper my-2">

                <?php while ($blog_snippet_list->have_posts()) : $blog_snippet_list->the_post(); ?>

                    <div class="swiper-slide">
                        <a class="card h-100 bg-light shadow-none card-transition" href="<?php echo esc_url(get_permalink()); ?>">
                            <?php if (has_post_thumbnail()) : ?>
                                <img src="<?php the_post_thumbnail_url('medium'); ?>" class="card-img-top" alt="<?php the_title(); ?>">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php the_title(); ?></h5>
                                <p class="card-text"><?php echo wp_strip_all_tags(get_the_excerpt()); ?></p>

                            </div>
                        </a>
                    </div>

                <?php endwhile; ?>


            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
        </div>


        <?php wp_reset_postdata(); ?>
    <?php else : ?>
        <p>No posts found.</p>
    <?php endif; ?>


</div>