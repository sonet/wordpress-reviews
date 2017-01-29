<?php
/*
Plugin Name: Sonet Reviews
Description: Reviews plugin using CPT
Author: Sonet Digital
Author URI: http://www.sonetseo.com
*/

include 'metaboxes.php';

add_action( 'init', 'sonet_reviews_cpt' );

function sonet_reviews_cpt() {
    register_post_type( 'sonet_review',
        array(
        'labels' => array(
            'name' =>  __( 'Reviews' ),
            'singular_name' =>  __( 'Review' ),
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
        ),
        'taxonomies'  => array( 'category' )
        )
    );
}

add_action( 'save_post', 'sonet_review_location_save' );

function sonet_review_location_save( $post_id ) {

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
	return;

	if ( !wp_verify_nonce( $_POST['sonet_review_box_content_nonce'], plugin_basename( __FILE__ ) ) )
	return;

	if ( 'post' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ) )
		return;
	} else {
		if ( !current_user_can( 'edit_post', $post_id ) )
		return;
	}
}

update_post_meta( $post_id, 'sonet_review_location', $_POST['sonet_review_location'] );
