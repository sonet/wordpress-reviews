<?php
/*
 * Plugin Name: Sonet Reviews
 * Description: Reviews plugin using a CPT
 * Author: Sonet SEO Company
 * Author URI: http://www.sonetseo.com
 * Plugin URI: http://www.sonetseo.com/wordpress
 * Version: 0.1
 *
*/

include 'metaboxes.php';

add_action('init', 'sonet_reviews_cpt');

function sonet_reviews_cpt() {

    register_post_type('sonet_review',
        array(
            'labels' => array(
                'name' =>  __('Reviews'),
                'singular_name' =>  __('Review'),
                'search_items' =>  __('Search Review'),
                'all_items' => __('All Reviews'),
                'parent_item' => __('Parent Review'),
                'parent_item_colon' => __('Parent Review:'),
                'edit_item' => __('Edit Review'),
                'update_item' => __('Update Review'),
                'add_new_item' => __('Add New Review'),
                'new_item_name' => __('New Review Name'),
            ),
            'description' => 'Customer reviews which we will be displayed on the website.',
            'menu_position' => 10,
            // 'menu_icon' => plugins_url( 'images/review_edit.png', __FILE__ ),
            'show_ui' => true,
            'public' => true,
            'show_in_menu' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'reviews'),
            'supports' => array(
                'title',
                'editor',
                'revisions'
            )
        )
    );

    register_taxonomy('sonet_review_source', array('sonet_review'), array(
            'hierarchical' => true,
            'labels' => array(
                'name' => _x( 'Review Sources', 'taxonomy general name' ),
                'singular_name' => _x( 'Review Source', 'taxonomy singular name' ),
                'search_items' =>  __( 'Search Review Sources' ),
                'all_items' => __( 'All Review Sources' ),
                'parent_item' => __( 'Parent Review Source' ),
                'parent_item_colon' => __( 'Parent Review Source:' ),
                'edit_item' => __( 'Edit Review Source' ),
                'update_item' => __( 'Update Review Source' ),
                'add_new_item' => __( 'Add New Review Source' ),
                'new_item_name' => __( 'New Review Source Name' )
            ),
            'show_ui' => true,
            'query_var' => true,
            'rewrite' => array( 'slug' => 'review-source' )
        )
    );

}

add_filter('post_updated_messages', 'review_updated_messages');

function review_updated_messages($messages)
{
    $messages['grid_reviews'] = array(
        0 => '', // Unused. Messages start at index 1.
        1 => sprintf(__('Review updated. <a href="%s">View Review</a>'), esc_url(get_permalink($post_ID))),
        2 => __('Custom field updated.'),
        3 => __('Custom field deleted.'),
        4 => __('Review updated.'),
        /* translators: %s: date and time of the revision */
        5 => isset($_GET['revision']) ? sprintf(__('Review restored to revision from %s'), wp_post_revision_title((int) $_GET['revision'], false)) : false,
        6 => sprintf(__('Review published. <a href="%s">View Review</a>'), esc_url(get_permalink($post_ID))),
        7 => __('Contact saved.'),
        8 => sprintf(__('Review submitted. <a target="_blank" href="%s">Preview Review</a>'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
        9 => sprintf(__('Review scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Review</a>'),
            // translators: Publish box date format, see http://php.net/date
            date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date)), esc_url(get_permalink($post_ID))),
        10 => sprintf(__('Review draft updated. <a target="_blank" href="%s">Preview Review</a>'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
    );

    return $messages;
}

/*
    Admin filter by source
*/
add_action('restrict_manage_posts','restrict_reviews_by_source');
function restrict_reviews_by_source() {
    global $typenow;
    global $wp_query;
    if ($typenow=='sonet_review') {

        $tax_slug = 'sonet_review_source';

        // retrieve the taxonomy object
        $tax_obj = get_taxonomy($tax_slug);
        $tax_name = $tax_obj->labels->name;
        // retrieve array of term objects per taxonomy
        $terms = get_terms($tax_slug);

        // output html for taxonomy dropdown filter
        echo "<select name='$tax_slug' id='$tax_slug' class='postform'>";
        echo "<option value=''>Show All $tax_name</option>";
        foreach ($terms as $term) {
            // output each select option line, check against the last $_GET to show the current option selected
            echo '<option value='. $term->slug, $_GET[$tax_slug] == $term->slug ? ' selected="selected"' : '','>' . $term->name .' (' . $term->count .')</option>';
        }
        echo "</select>";
    }
}

/*
    Shortcode
*/
add_shortcode('review', 'review_shortcode');
// define the shortcode function
function review_shortcode($atts) {
    extract(shortcode_atts(array(
        'src'	=> '',
        'id'	=> '',
        'view' => '',
        'featured' => '',
        'hidetitle' => '',
        'buttontext' => '',
        'des' => '',
        'maxdes' => '',
    ), $atts));

// for some reason the post ID is not available here
//  $post = get_post();
//  $id = ! empty( $post ) ? $post->ID : false;

//    echo '<pre>';
//    var_dump('SNT_ID: ' . $id);
//    var_dump('SNT_SRC: ' . $src);
//    echo '</pre>';

// the "Radio Buttons for Taxonomies" plugin wraps the slug in quotes

    // breaking the query
    $src = str_replace('&#8221;', '', $src);

    // stuff that loads when the shortcode is called goes here

    //$paged = ( get_query_var('page') ) ? get_query_var('page') : 1;
    $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;

    if ( ! empty($id) ) {
        $sonet_reviews = new WP_Query(array(
            'order'          => 'ASC',
            'orderby' 		 => 'menu_order ID',
            'p'	 			=> $id,
            'post_type'      => 'sonet_review',
            'post_status'    => null,
            'posts_per_page'    => 1) );
    } else {
        $sonet_reviews = new WP_Query(array(
            'order'          => 'ASC',
            'orderby' 		 => 'menu_order ID',
            'sonet_review_source'	 => $src,
            'post_type'      => 'sonet_review',
            'post_status'    => null,
            //'nopaging' 	=> 1,
            'posts_per_page' => 5,
            'paged' => $paged,
        ) );
//        var_dump( $GLOBALS['wp_query']->request );

    }
    // var_dump( $GLOBALS['wp_query']->request );
    // the above here is just getting the page itself

    $reviewShortcode .= '<ul id="reviews">';

    global $wpdb; $srcname = $wpdb->get_var("SELECT name FROM $wpdb->terms WHERE slug = '$src'");
    $countReviews='0';
    $reviewShortcode = '';

    if ( !empty( $src ) && $hidetitle != 'yes' ) { $reviewShortcode .= '<div class="review-srcname">' . $srcname . '</div>'; }

    $termId = $wpdb->get_var("SELECT term_id FROM $wpdb->terms WHERE slug = '$src'");

    $termDesc = $wpdb->get_var("SELECT description FROM $wpdb->term_taxonomy WHERE term_taxonomy_id = '$termId'");

// it seems that we're not using taxonomy terms ...
//    echo '<pre>';
    // this queries the page holding the short codes
    // SELECT   wp_posts.* FROM wp_posts  WHERE 1=1  AND (wp_posts.ID = '41') AND wp_posts.post_type = 'page'  ORDER BY wp_posts.post_date  DESC
//    var_dump($GLOBALS['wp_query']->request);
//    // all this is  empty??
//    var_dump('SRC NAME: '. $srcname );
//    var_dump('TERM ID: ' . $termId);
//    var_dump('TERM DESC: ' . $termDesc);
//    var_dump('SRC: ' . $src);
//    echo '</pre>';

    if ( !empty( $src ) ) { $reviewShortcode .= '<div class="reviewsrcdes">'.$termDesc.'</div>'; }


    if ($view == 'list' && $featured == 'yes'){
        $reviewShortcode .= '<table class="reviewtable" style="background: #eee;"><tbody>';
    }

    if ($view == 'list' && $featured != 'yes'){
        $reviewShortcode .= '<table class="reviewtable"><tbody>';
    }

    if ($view != 'list' && $featured != 'yes'){
        $reviewShortcode .= '<ul class="reviews">';
    }

    if ($view != 'list' && $featured == 'yes'){
        $reviewShortcode .= '<ul class="reviews" style="background: #eee;">';
    }

    if ( $sonet_reviews->have_posts() ) {
        while ($sonet_reviews->have_posts()) : $sonet_reviews->the_post();

            $countReviews++;

            $price = get_post_meta(get_the_ID(), '_sonet_review_price', true);

            if ($view == 'list') {
                $theimage = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'review-image');

                $reviewShortcode .= '<tr><td align="center"><a href="' . get_permalink() . '"><img src="' . $theimage[0] . '" alt="" /></a></td>';
                $reviewShortcode .= '<td><div class="review-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></div>';


                if ($buttontext == NULL) {
                    $reviewShortcode .= '<div class="review-excerpt"><p>' . get_the_excerpt() . '</p>';
                    if ($price != NULL) {
                        $reviewShortcode .= '<p><b>Price $' . $price . '</b></p>';
                    }
                    $reviewShortcode .= '</div></td></tr>';
                    $reviewShortcode .= '<tr><td colspan="2"><div class="reviewmoretag"><a href="' . get_permalink() . '">View Review</a></div></td></tr>';


                } else {
                    $reviewShortcode .= '<div class="review-excerpt"><p>' . get_the_excerpt() . '</p>';
                    if ($price != NULL) {
                        $reviewShortcode .= '<p><b>Price $' . $price . '</b></p>';
                    }
                    $reviewShortcode .= '</div></td></tr>';
                    $reviewShortcode .= '<tr><td colspan="2"><div class="reviewmoretag"><a href="' . get_permalink() . '">' . $buttontext . '</a></div></td></tr>';
                }


                $reviewShortcode .= '<tr><td colspan="2"><div class="spacer"></div></td></tr>';

            } else {

                $theimage = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'review-image2');

                $sources = get_terms( array(
                    'taxonomy' => 'sonet_review_source',
                    'hide_empty' => false,
                ) );

                $sources = wp_get_post_terms( get_the_ID(),'sonet_review_source',array(
                    'hide_empty' => false
                    )
                );

                echo '<pre> post_meta <b>L265</b>: ' . "\n";
                var_dump( get_post_meta(get_the_ID(), 'sonet_review_date', true) );
                var_dump($sources[0]->slug);
                //var_dump($sources);
                echo '</pre>';

                $reviewShortcode .= '<li>';
                $reviewShortcode .= '<a href="' . get_permalink() . '"><img src="' . $theimage[0] . '" style="max-width:195px;" alt="" />';
                $reviewShortcode .= '<h4>' . get_the_title() . '</h4>';
                if ($des != 'no') {
                    $paragraph = explode(' ', get_the_excerpt());

                    if (is_numeric($maxdes)) {
                        $paragraph = array_slice($paragraph, 0, $maxdes);
                    } else {
                        $paragraph = array_slice($paragraph, 0, '20');
                    }


                    $paragraph = implode(' ', $paragraph);
                    $reviewShortcode .= '<p>' . $paragraph . '...</p>';
                }

                if ($price != NULL) {
                    $reviewShortcode .= '<p><b>Price $' . $price . '</b></p>';
                }

                $reviewShortcode .= '</a>';

                if ($buttontext == NULL) {
                    $reviewShortcode .= '<div class="reviewmoreholder"><div class="reviewmoretag"><a href="' . get_permalink() . '">View Review</a></div></div>';
                } else {
                    $reviewShortcode .= '<div class="reviewmoreholder"><div class="reviewmoretag"><a href="' . get_permalink() . '">' . $buttontext . '</a></div></div>';
                }
                $reviewShortcode .= '</li>';

            }
            // end reviews loop
        endwhile;

    }
//            else {
//                echo '<p>Sorry, no posts matched your criteria.';
//            }

//    echo '<pre>';
//    var_dump( 'MAX_NUM_PAGES: ' . $sonet_reviews->max_num_pages );
//    var_dump( 'POST COUNT: '  . $countReviews);
//    echo '</pre>';

    // Pagination
    // http://callmenick.com/post/custom-wordpress-loop-with-pagination
    // check if the max number of pages is greater than 1
    $pagination_nav = '<nav class="prev-next-posts">';
    if ( $sonet_reviews->max_num_pages > 1 ) {
        $pagination_nav .= '<div class="prev-posts-link">';
        $pagination_nav .= get_next_posts_link( 'Older Entries', $sonet_reviews->max_num_pages ); // display older posts link
        $pagination_nav .= '</div><div class="next-posts-link">';
        $pagination_nav .= get_previous_posts_link( 'Newer Entries' ); // display newer posts link
        $pagination_nav .= '</div></nav>';
    }

    if ($view == 'list'){
        $reviewShortcode .= '</tbody></table>';
    }

    $reviewShortcode .= '</ul>';

    if ($view != 'list'){
        $reviewShortcode .= '</ul>';
    }

    $reviewShortcode .= $pagination_nav;

    if (function_exists(custom_pagination)) {
        custom_pagination($sonet_reviews->max_num_pages,"",$paged);
    }

    if ($countReviews == '0') {
        echo 'There are no reviews from this source.';
    }

    wp_reset_query(); wp_reset_postdata();

    $reviewShortcode = do_shortcode( $reviewShortcode );
    return (__($reviewShortcode));
} //end of the review_shortcode function

add_filter('manage_edit-sonet_reviews_columns', 'review_columns');
function review_columns($columns) {
    $columns = array(
        'cb' => '<input type="checkbox" />',
        'title' => __( 'Question' ),
        'grid_review_source' => __( 'Source' ),
        'date' => __( 'Date' )
    );
    return $columns;
}

add_action('manage_posts_custom_column',  'review_show_columns');
function review_show_columns($name) {
    global $post;
    switch ($name) {
        case 'grid_product_category':
            $grid_product_cats = get_the_terms(0, "grid_product_category");
            $cats_html = array();
            if(is_array($grid_product_cats)){
                foreach ($grid_product_cats as $term)
                    array_push($cats_html, '<a href="edit.php?post_type=grid_products&grid_product_category='.$term->slug.'">' . $term->name . '</a>');

                echo implode($cats_html, ", ");
            }
            break;
        default :
            break;
    }
}

// scripts to go in the header and/or footer

function review_init() {

    if( ! is_admin() ) {
        wp_enqueue_script('jquery');
    }

    wp_enqueue_style('reviews',  plugins_url('styles.css', __FILE__), false, $review_version, 'screen');
}

add_action('init', 'review_init');


add_action('admin_menu', 'add_sonet_review_option_page');

function add_sonet_review_option_page() {
    // hook in the options page function
    add_options_page('sonet Review', 'sonet Review', 'manage_options', __FILE__, 'sonet_review_options_page');

}

function custom_pagination($numpages = '', $pagerange = '', $paged='') {

    if (empty($pagerange)) {
        $pagerange = 2;
    }

    /**
     * This first part of our function is a fallback
     * for custom pagination inside a regular loop that
     * uses the global $paged and global $wp_query variables.
     *
     * It's good because we can now override default pagination
     * in our theme, and use this function in default quries
     * and custom queries.
     */
    global $paged;
    if (empty($paged)) {
        $paged = 1;
    }
    if ($numpages == '') {
        global $sonet_reviews;
        $numpages = $sonet_reviews->max_num_pages;
        if(!$numpages) {
            $numpages = 1;
        }
    }

    /**
     * We construct the pagination arguments to enter into our paginate_links
     * function.
     */
    $pagination_args = array(
        'base'            => get_pagenum_link(1) . '%_%',
        'format'          => 'page/%#%',
        'total'           => $numpages,
        'current'         => $paged,
        'show_all'        => True,
        'end_size'        => 1,
        'mid_size'        => $pagerange,
        'prev_next'       => True,
        'prev_text'       => __('&laquo;'),
        'next_text'       => __('&raquo;'),
        'type'            => 'plain',
        'add_args'        => false,
        'add_fragment'    => ''
    );

    $paginate_links = paginate_links($pagination_args);

    if ($paginate_links) {
        echo "<nav class='custom-pagination'>";
        echo "<span class='page-numbers page-num'>Page " . $paged . " of " . $numpages . "</span> ";
        echo $paginate_links;
        echo "</nav>";
    }

}

function sonet_review_options_page() {
    ?>
    <div class="wrap" style="width:500px">
        <h2>Sonet Review Shortcodes</h2>

        <h3>Shortcode - [review]</h3>
        <h4>Full Shortcode With All Options Enabled :<br/><br/> [review cat="category-slug" hidetitle="yes" featured="yes" view="list"  buttontext="your text here"  des="no" maxdes="50"] <h4>
                <h4>Below are the review shortcode options explained in detail
                    <h4><font color="#FF0000">***Note ALL shortcode options are optional:</font><h4>
                            <ul>
                                <li><hr><h3>cat</h3> Used to display only produces in a certain category. If not set ALL reviews from any category will be shown.<br/><br/><b>Usage :</b> cat="category-slug"<br/><br/></li>
                                <li><hr><h3>id</h3> Used  to display a single review. <br/><b>* Note: </b>the cat & the id attributes are mutually exclusive. Don't use both in the same shortcode.
                                    <br/><br/><b>Usage :</b> id="1234" - where 1234 is the post ID.<br/><br/></li>
                                <li><hr><h3>hidetitle</h3> Used in conjunction with the "cat" shortcode to hide the category title incase you would like to use something else instead of the category name.<br/><br/><b>Usage :</b> hidetitle="yes"<br/><br/></li>
                                <li><hr><h3>featured</h3> Will set the background of the container to a default light grey.<br/><br/><b>Usage :</b> featured="yes"<br/><br/></li>
                                <li><hr><h3>view</h3> The default view is a grid view, if you would prefer to use "list" view set this
                                    attribute to equal list <br/><br/><b>Usage :</b> view="list"<br/><br/></li>
                                <li><hr><h3>buttontext</h3>The default button text is "View Review" if you would like to change the text use this attribute <br/><br/><b>Usage :</b> buttontext="your text here"<br/><br/></li>
                                <li><hr><h3>des</h3> Used to disable the review excerpt in the default grid view. <br/><br/><b>Usage :</b> des="no"<br/><br/></li>
                                <li><hr><h3>maxdes</h3>  Used to set the number of words used in the
                                    excerpt in the default grid view. (default - 20) must be a number.<br/><br/><b>Usage :</b> maxdes="50"<br/><br/><hr></li>
                            </ul>

    </div>
<?php } ?>
