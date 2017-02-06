<?php
/*
 * Plugin Name: Sonet Reviews
 * Description: Reviews plugin using a CPT
 * Author: Sonet Digital
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

	// stuff that loads when the shortcode is called goes here

		if ( ! empty($id) ) {
				$sonet_reviews = new WP_Query(array(
				'order'          => 'ASC',
				'orderby' 		 => 'menu_order ID',
				'p'	 			=> $id,
				'post_type'      => 'sonet_reviews',
				'post_status'    => null,
				'posts_per_page'    => 1) );
			} else {
				$sonet_reviews = new WP_Query(array(
				'order'          => 'ASC',
				'orderby' 		 => 'menu_order ID',
				'sonet_review_source'	 => $src,
				'post_type'      => 'sonet_reviews',
				'post_status'    => null,
				'nopaging' 	=> 1,
				'posts_per_page' => -10) );
			}


			global $wpdb; $srcname = $wpdb->get_var("SELECT name FROM $wpdb->terms WHERE slug = '$src'");
			$countreviews='0';
			$review_shortcode = '';

			if ( !empty( $src ) && $hidetitle != 'yes' ) { $review_shortcode .= '<div class="review-srcname">' . $srcname . '</div>'; }


		$thetermid = $wpdb->get_var("SELECT term_id FROM $wpdb->terms WHERE slug = '$src'");


		$thetermdes = $wpdb->get_var("SELECT description FROM $wpdb->term_taxonomy WHERE term_taxonomy_id = '$thetermid'");


			if ( !empty( $src ) ) { $review_shortcode .= '<div class="reviewsrcdes">'.$thetermdes.'</div>'; }


			if ($view == 'list' && $featured == 'yes'){
			$review_shortcode .= '<table class="reviewtable" style="background: #eee;"><tbody>';
			}

			if ($view == 'list' && $featured != 'yes'){
			$review_shortcode .= '<table class="reviewtable"><tbody>';
			}




				if ($view != 'list' && $featured != 'yes'){
			$review_shortcode .= '<ul class="reviews">';
			}

			if ($view != 'list' && $featured == 'yes'){
			$review_shortcode .= '<ul class="reviews" style="background: #eee;">';
			}




			while($sonet_reviews->have_posts()): $sonet_reviews->the_post();
			$countreviews++;

			$price=get_post_meta( get_the_ID(), '_sonet_review_price', true );



			if ($view == 'list'){
				$theimage=wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()) , 'review-image');

				$review_shortcode .= '<tr><td align="center"><a href="' . get_permalink() . '"><img src="'.$theimage[0].'" alt="" /></a></td>';
				$review_shortcode .= '<td><div class="review-title"><a href="' . get_permalink() . '">'. get_the_title().'</a></div>';




				if ($buttontext == NULL){
				$review_shortcode .= '<div class="review-excerpt"><p>'.get_the_excerpt().'</p>';
				if($price != NULL){
				$review_shortcode .= '<p><b>Price $'.$price.'</b></p>';
				}
				$review_shortcode .= '</div></td></tr>';
				$review_shortcode .= '<tr><td colspan="2"><div class="reviewmoretag"><a href="' . get_permalink() . '">View Review</a></div></td></tr>';


	            }else{
	           $review_shortcode .= '<div class="review-excerpt"><p>'.get_the_excerpt().'</p>';
	           	if($price != NULL){
				$review_shortcode .= '<p><b>Price $'.$price.'</b></p>';
				}
	           $review_shortcode .= '</div></td></tr>';
	           $review_shortcode .= '<tr><td colspan="2"><div class="reviewmoretag"><a href="' . get_permalink() . '">'.$buttontext.'</a></div></td></tr>';
	            }


	$review_shortcode .= '<tr><td colspan="2"><div class="spacer"></div></td></tr>';

	}else {

	$theimage=wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()) , 'review-image2');

	$review_shortcode .= '<li>';
	$review_shortcode .= '<a href="' . get_permalink() . '"><img src="'.$theimage[0].'" style="max-width:195px;" alt="" />';
	$review_shortcode .= '<h4>'. get_the_title().'</h4>';
	if ($des != 'no'){
	$paragraph = explode (' ', get_the_excerpt());

	if (is_numeric($maxdes)) {
	$paragraph = array_slice ($paragraph, 0, $maxdes);
	} else {
	$paragraph = array_slice ($paragraph, 0, '20');
	}


 	$paragraph=implode (' ', $paragraph);
	$review_shortcode .= '<p>' .$paragraph. '...</p>';
	}





	if($price != NULL){
	$review_shortcode .= '<p><b>Price $'.$price.'</b></p>';
	}

	$review_shortcode .= '</a>';


	if ($buttontext == NULL){
		$review_shortcode .= '<div class="reviewmoreholder"><div class="reviewmoretag"><a href="' . get_permalink() . '">View Review</a></div></div>';
    }else{
        $review_shortcode .= '<div class="reviewmoreholder"><div class="reviewmoretag"><a href="' . get_permalink() . '">'.$buttontext.'</a></div></div>';
    }
	$review_shortcode .= '</li>';




	}



			endwhile; // end slideshow loop


		if ($view == 'list'){
	$review_shortcode .= '</tbody></table>';
}


			if ($view != 'list'){
			$review_shortcode .= '</ul>';
			}

if ($countreviews == '0') {
echo 'No reviews are currently posted for this source';
}

			wp_reset_query();

	$review_shortcode = do_shortcode( $review_shortcode );
	return (__($review_shortcode));
} //end of the review_shortcode function
