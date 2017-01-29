<?php

/*
Plugin Name: Sonet Reviews
Description: Reviews plugin using a CPT
Author: Sonet Digital
Author URI: http://www.sonetseo.com
*/

add_action( 'add_meta_boxes', 'sonet_register_review_box' );

/**
 * Register the meta box.
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
    	wp_nonce_field( plugin_basename( __FILE__ ), 'sonet_review_box_content_nonce' );
        // Location
    	echo '<div class="reviewfieldholder">Location: </div><input type="text" '
            . 'id="sonet_review_location" name="sonet_review_location" '
            . 'placeholder="Location" class="admininputfield"value="'
            . get_post_meta($post->ID, "sonet_review_location", true)    
            . '"> <br/>';
        // Date
    	echo '<div class="reviewfieldholder">Date: </div><input type="text" '
            . 'id="sonet_review_date" name="sonet_review_date" '
            . 'placeholder="mm/dd/yy" class="admininputfield"value="'
            . get_post_meta($post->ID, "sonet_review_date", true)    
            . '"> <br/>';
        // Stars Rating
        $my_rating = get_post_meta($post->ID, "sonet_review_rating", true);
        echo '<div class="reviewfieldholder">Rating: </div>'
          . '<select'
          . ' id="sonet_review_rating" name="sonet_review_rating" class="admindropdownfield"'
          . '>';
        echo "<option value=\"\">(Select One)</option>";
        $ratings = array("5", "4", "3", "2", "1");
        foreach($ratings as $rating) {
            $select = ($rating === $my_rating) ? " selected" : "";
            echo "<option value=\"$rating\"" . $select . ">$rating</option>";
        }
        echo '</select><br>';
}

add_action( 'save_post', 'sonet_review_meta_save' );

/**
 * 
 * @param type $post_id
 * @return type
 */
function sonet_review_meta_save( $post_id ) {

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;

    if ( (!(isset( $_POST['sonet_review_box_content_nonce'] ))) || (! (wp_verify_nonce( $_POST['sonet_review_box_content_nonce'], plugin_basename( __FILE__)))) )
        return;

    if ( 'post' == $_POST['post_type'] ) {
                error_log('POST TYPE: ' . $_POST['post_type']);
            if ( !current_user_can( 'edit_page', $post_id ) )
                return;
    } else {
        if ( !current_user_can( 'edit_post', $post_id ) )
            return;
    }

    update_post_meta( $post_id, 'sonet_review_location', $_POST['sonet_review_location'] );
    update_post_meta( $post_id, 'sonet_review_date', $_POST['sonet_review_date'] );
    update_post_meta( $post_id, 'sonet_review_rating', $_POST['sonet_review_rating'] );

}
