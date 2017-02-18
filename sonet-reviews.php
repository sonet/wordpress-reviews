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

  // for some reason the post ID is not available here
  $post = get_post();
  $id = ! empty( $post ) ? $post->ID : false;

  // the "Radio Buttons for Taxonomies" plaugin wraps the slug in quotes
  // breaking the query
  $src = str_replace('&#8221;', '', $src);

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

      $reviewShortcode .= '<ul id="reviews">';

//
// 			global $wpdb; $srcname = $wpdb->get_var("SELECT name FROM $wpdb->terms WHERE slug = '$src'");
// 			$countReviews='0';
// 			$reviewShortcode = '';
//
// 			if ( !empty( $src ) && $hidetitle != 'yes' ) { $reviewShortcode .= '<div class="review-srcname">' . $srcname . '</div>'; }
//
//
// 		$termId = $wpdb->get_var("SELECT term_id FROM $wpdb->terms WHERE slug = '$src'");
//
// 		$termDesc = $wpdb->get_var("SELECT description FROM $wpdb->term_taxonomy WHERE term_taxonomy_id = '$termId'");
//
// 			if ( !empty( $src ) ) { $reviewShortcode .= '<div class="reviewsrcdes">'.$termDesc.'</div>'; }
//
//
// 			if ($view == 'list' && $featured == 'yes'){
// 			$reviewShortcode .= '<table class="reviewtable" style="background: #eee;"><tbody>';
// 			}
//
// 			if ($view == 'list' && $featured != 'yes'){
// 			$reviewShortcode .= '<table class="reviewtable"><tbody>';
// 			}
//
// 				if ($view != 'list' && $featured != 'yes'){
// 			$reviewShortcode .= '<ul class="reviews">';
// 			}
//
// 			if ($view != 'list' && $featured == 'yes'){
// 			$reviewShortcode .= '<ul class="reviews" style="background: #eee;">';
// 			}
//
// 			while($sonet_reviews->have_posts()): $sonet_reviews->the_post();
// 			$countReviews++;
//
// 			$price=get_post_meta( get_the_ID(), '_sonet_review_price', true );
//
//
//
// 			if ($view == 'list'){
// 				$theimage=wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()) , 'review-image');
//
// 				$reviewShortcode .= '<tr><td align="center"><a href="' . get_permalink() . '"><img src="'.$theimage[0].'" alt="" /></a></td>';
// 				$reviewShortcode .= '<td><div class="review-title"><a href="' . get_permalink() . '">'. get_the_title().'</a></div>';
//
//
//
//
// 				if ($buttontext == NULL){
// 				$reviewShortcode .= '<div class="review-excerpt"><p>'.get_the_excerpt().'</p>';
// 				if($price != NULL){
// 				$reviewShortcode .= '<p><b>Price $'.$price.'</b></p>';
// 				}
// 				$reviewShortcode .= '</div></td></tr>';
// 				$reviewShortcode .= '<tr><td colspan="2"><div class="reviewmoretag"><a href="' . get_permalink() . '">View Review</a></div></td></tr>';
//
//
// 	            }else{
// 	           $reviewShortcode .= '<div class="review-excerpt"><p>'.get_the_excerpt().'</p>';
// 	           	if($price != NULL){
// 				$reviewShortcode .= '<p><b>Price $'.$price.'</b></p>';
// 				}
// 	           $reviewShortcode .= '</div></td></tr>';
// 	           $reviewShortcode .= '<tr><td colspan="2"><div class="reviewmoretag"><a href="' . get_permalink() . '">'.$buttontext.'</a></div></td></tr>';
// 	            }
//
//
// 	$reviewShortcode .= '<tr><td colspan="2"><div class="spacer"></div></td></tr>';
//
// 	}else {
//
// 	$theimage=wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()) , 'review-image2');
//
// 	$reviewShortcode .= '<li>';
// 	$reviewShortcode .= '<a href="' . get_permalink() . '"><img src="'.$theimage[0].'" style="max-width:195px;" alt="" />';
// 	$reviewShortcode .= '<h4>'. get_the_title().'</h4>';
// 	if ($des != 'no'){
// 	$paragraph = explode (' ', get_the_excerpt());
//
// 	if (is_numeric($maxdes)) {
// 	$paragraph = array_slice ($paragraph, 0, $maxdes);
// 	} else {
// 	$paragraph = array_slice ($paragraph, 0, '20');
// 	}
//
//
//  	$paragraph=implode (' ', $paragraph);
// 	$reviewShortcode .= '<p>' .$paragraph. '...</p>';
// 	}
//
//
//
//
//
// 	if($price != NULL){
// 	$reviewShortcode .= '<p><b>Price $'.$price.'</b></p>';
// 	}
//
// 	$reviewShortcode .= '</a>';
//
//
// 	if ($buttontext == NULL){
// 		$reviewShortcode .= '<div class="reviewmoreholder"><div class="reviewmoretag"><a href="' . get_permalink() . '">View Review</a></div></div>';
//     }else{
//         $reviewShortcode .= '<div class="reviewmoreholder"><div class="reviewmoretag"><a href="' . get_permalink() . '">'.$buttontext.'</a></div></div>';
//     }
// 	$reviewShortcode .= '</li>';
//
//
//
//
// 	}
//
//
//
// 			endwhile; // end reviews loop
//
//
// 		if ($view == 'list'){
// 	$reviewShortcode .= '</tbody></table>';
// }

      $reviewShortcode .= '</ul>';


			// if ($view != 'list'){
			//   $reviewShortcode .= '</ul>';
			// }

      if ($countReviews == '0') {
        echo 'There are no reviews from this source.';
      }

			wp_reset_query();

	$reviewShortcode = do_shortcode( $reviewShortcode );
	return (__($reviewShortcode));
} //end of the review_shortcode function
