<?php
/**
 * The template for displaying single review posts
 * Template Name: Sonet full-width single review  layout
 * Template Post Type: sonet_review
 * @package Sonet Reviews
 * @version 0.1
 */

get_header(); ?>

    <div class="wrap">
        <div id="primary" class="content-area">
            <main id="main" class="site-main" role="main">
                <?php
                /* Start the Loop */
                while ( have_posts() ) : the_post();

//                    get_template_part( 'template-parts/post/content', get_post_format() );

                echo '<h1>' . get_the_title() . '</h1>';

                $source = wp_get_post_terms( get_the_ID(),'sonet_review_source',array(
                        'hide_empty' => false
                    )
                )[0];

                if ($source->slug != '') {
                    echo '<div class="'.$source->slug.'-review-src">';
                } else {
                    $reviewShortcode .= '<div class="no-review-src">';
                }

                $id = ! empty( $post ) ? $post->ID : false;

                $meta = get_post_meta( $id);

//                echo "<pre>"; print_r( $meta ); echo "</pre>";
//                echo "<hr><pre>"; print_r( $source ); echo "</pre>";

                ?>

                    <div class="review-left">

                        <p><?=$meta['sonet_review_name'][0]?></p>
                        <p><?=$meta['sonet_review_date'][0]?></p>
                        <p><strong><?=$meta['sonet_review_location'][0]?></strong></p>

                        <div class="sonet-star-rating">
                            <svg id="sonet-star-rating" data-name="Star Rating" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 247.16 44.17">
                                <defs>
                                    <style>.sr_star{fill:#000;fill-rule:evenodd;/*stroke-width:1;stroke:rgb(0,0,0)*/}</style>
                                </defs>
                                <title>stars-rating</title>
                                <?php
                                for ($x = 1; $x <= $meta['sonet_review_rating'][0]; $x++) {
                                    echo "                                <path class=\"sr_star\"
                                      d=\"M" . ($x * 50 + 243) . ",2240.16l-14.8-7.63-14.34,7.63,2.8-16.23-11.8-11.06,16.54-2.4,7-14.48,7.43,14.76,16.16,2.12-12,11.51Z\"
                                      transform=\"translate(-304.84 -2195.99)\"/>";
                                }
                                ?>
                            </svg>
                        </div>

                        <p><a href="<?=$meta['sonet_review_url'][0]?>"><img src="<?=plugin_dir_url( __FILE__ )?>images/houzz-logo-icon.svg" alt="houzz" hright="200" width="200"></a></p>

                    </div>

                    <div class="review-right expandable">
                        <?=get_the_content();?>
                    </div>

                </div>

                <?php

                    // If comments are open or we have at least one comment, load up the comment template.
                    if ( comments_open() || get_comments_number() ) :
                        comments_template();
                    endif;

                    the_post_navigation( array(
                        'prev_text' => '<span class="screen-reader-text">' . __( 'Previous Post', 'twentyseventeen' ) . '</span><span aria-hidden="true" class="nav-subtitle">' . __( 'Previous', 'twentyseventeen' ) . '</span> <span class="nav-title"><span class="nav-title-icon-wrapper">' . twentyseventeen_get_svg( array( 'icon' => 'arrow-left' ) ) . '</span>%title</span>',
                        'next_text' => '<span class="screen-reader-text">' . __( 'Next Post', 'twentyseventeen' ) . '</span><span aria-hidden="true" class="nav-subtitle">' . __( 'Next', 'twentyseventeen' ) . '</span> <span class="nav-title">%title<span class="nav-title-icon-wrapper">' . twentyseventeen_get_svg( array( 'icon' => 'arrow-right' ) ) . '</span></span>',
                    ) );

                endwhile; // End of the loop.
                ?>

            </main><!-- #main -->
        </div><!-- #primary -->
        <?php get_sidebar(); ?>
    </div><!-- .wrap -->

<?php get_footer();

// scripts to go in the header and/or footer

function single_review_init() {

    if( ! is_admin() ) {
        wp_enqueue_script('jquery');
    }

    wp_enqueue_style('reviews',  plugins_url('styles.css?v=04', __FILE__), false, $review_version, 'screen');
}

add_action('init', 'single_review_init');

?>