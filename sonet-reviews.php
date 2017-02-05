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

add_action( 'init', 'sonet_reviews_cpt' );

function sonet_reviews_cpt() {
    register_post_type( 'sonet_review',
        array(
        'labels' => array(
            'name' =>  __( 'Reviews' ),
            'singular_name' =>  __( 'Review' ),
            'search_items' =>  __( 'Search Review' ),
            'all_items' => __( 'All Reviews' ),
            'parent_item' => __( 'Parent Review' ),
            'parent_item_colon' => __( 'Parent Review:' ),
            'edit_item' => __( 'Edit Review' ),
            'update_item' => __( 'Update Review' ),
            'add_new_item' => __( 'Add New Review' ),
            'new_item_name' => __( 'New Review Name' ),
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
      )//,
        //'taxonomies'  => array( 'category' )
        )
    );
}

add_filter('post_updated_messages', 'review_updated_messages');
function review_updated_messages( $messages ) {

$messages['grid_reviews'] = array(
0 => '', // Unused. Messages start at index 1.
1 => sprintf( __('Review updated. <a href="%s">View Review</a>'), esc_url( get_permalink($post_ID) ) ),
2 => __('Custom field updated.'),
3 => __('Custom field deleted.'),
4 => __('Review updated.'),
/* translators: %s: date and time of the revision */
5 => isset($_GET['revision']) ? sprintf( __('Review restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
6 => sprintf( __('Review published. <a href="%s">View Review</a>'), esc_url( get_permalink($post_ID) ) ),
7 => __('Contact saved.'),
8 => sprintf( __('Review submitted. <a target="_blank" href="%s">Preview Review</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
9 => sprintf( __('Review scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Review</a>'),
  // translators: Publish box date format, see http://php.net/date
  date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
10 => sprintf( __('Review draft updated. <a target="_blank" href="%s">Preview Review</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
);

return $messages;
}
