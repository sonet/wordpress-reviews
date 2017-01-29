<?php
/*
Plugin Name: Sonet Reviews
Description: Reviews plugin using CPT
Author: Sonet Digital
Author URI: http://www.sonetseo.com
*/

#include 'metaboxes.php';

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
        'title'
        ))
    );
}