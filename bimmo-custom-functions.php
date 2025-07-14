<?php
/**
 * Plugin Name: Bimmo Custom Functions
 * Plugin URI: https://frymo.de
 * Version: 0.1
 * Description:  Custom functions plugin for bimmo.ch website.
 * Text Domain: frymo
 * Author: Stark Systems UG
 * Author URI: https://frymo.de
 * Domain Path: /languages
 */



add_action( 'save_post', 'frymo_tpi_on_object_save' );
function frymo_tpi_on_object_save( $post_id ) {
	// Check if this is an autosave.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Skip revisions.
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	// Get the post object.
	$post = get_post( $post_id );

	// Defensive: if the post object is not found or not an object, return early.
	if ( ! $post instanceof WP_Post ) {
		return;
	}

   // Check if this is the correct post type.
	if ( ! defined( 'FRYMO_POST_TYPE' ) || FRYMO_POST_TYPE !== $post->post_type ) {
		return;
	}

	// Check if the user has permission to edit the post.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}



   


}