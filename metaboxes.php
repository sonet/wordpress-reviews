<?php

add_action( 'add_meta_boxes', 'sonet_register_review_box' );

/**
 * Register meta box(es).
 */
function sonet_register_review_box() {
    add_meta_box(
      'sonet_review_box',
      __( 'Reviews', 'sonet_textdomain' ),
      'sonet_review_box_content',
      'sonet_review',
      'normal',
      'high'
    );
}

/**
 * Meta box display callback.
 *
 * @param WP_Post $post Current post object.
 */
function sonet_review_box_content( $post ) {
    // Display code/markup goes here. Don't forget to include nonces!
    	wp_nonce_field( plugin_basename( __FILE__ ), '_sonet_review_box_content_nonce' );
    	echo '<div class="reviewfieldholder">Location: </div><input type="text" id="sonet_review_location" name="sonet_review_location" placeholder="Location" class="admininputfield"value="'.get_post_meta( $_GET[post], 'sonet_review_location', true ).'"> <br/>';
}
