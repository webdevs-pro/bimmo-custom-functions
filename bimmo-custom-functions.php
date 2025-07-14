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



// add_action( 'save_post', 'frymo_tpi_on_object_save' );
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




// add_action( 'frymo/process_xml/language_set', 'frymo_tpi_on_object_language_set' );
function frymo_tpi_on_object_language_set( $post_id ) {
	$object_exteral_id = get_post_meta( $post_id, 'frymo_objektnr_extern', true );
	$obgect_lang_terms = wp_get_object_terms( $post_id, 'immobilie_language' );

	if ( ! is_wp_error( $obgect_lang_terms ) && ! empty( $obgect_lang_terms ) ) {
		$obgect_lang = $obgect_lang_terms[0];
	}

	$object_ids_with_same_external_id = frymo_tpi_get_matching_post_ids_by_external_object_id( $post_id, $object_exteral_id );
}






















// add_filter( 'the_post', 'swap_post_with_translated_version', 999999 );
// function swap_post_with_translated_version( $post ) {

// 	error_log( "original post ID\n" . print_r( $post->ID, true ) );

// 	// Check if this is the correct post type.
// 	if ( ! defined( 'FRYMO_POST_TYPE' ) || FRYMO_POST_TYPE !== $post->post_type ) {
// 		return;
// 	}

// 	$current_language = get_locale();
// 	$default_lang = get_option( 'trp_settings' )['default-language'] ?? 'de_CH';

// 	$object_exteral_id = get_post_meta( $post->ID, 'frymo_objektnr_extern', true );
// 	$obgect_lang_terms = wp_get_object_terms( $post->ID, 'immobilie_language' );

// 	if ( ! is_wp_error( $obgect_lang_terms ) && ! empty( $obgect_lang_terms ) ) {
// 		$obgect_lang = $obgect_lang_terms[0];
// 	} else {
// 		$obgect_lang = 'de-CH';
// 	}

// 	$translated_object_ids = frymo_tpi_get_matching_post_ids_by_external_object_id( $post->ID, $obgect_lang );
// 	$translation_post_id = frymo_tpi_get_translation_object_id( $translated_object_ids, $current_language );

// 	if ( is_int( $translation_post_id ) ) {
//       //   remove_filter( 'the_post', 'swap_post_with_translated_version' );

//       $post = get_post( $translation_post_id );

//       //   add_filter( 'the_post', 'swap_post_with_translated_version' );
// 	}

// 	error_log( "translation post ID\n" . print_r( $post->ID, true ) . "\n" );


// 	return $post;
// }


// function frymo_tpi_get_translation_object_id( $post_ids, $translation_locale ) {
// 	$translation_post_id = false;

// 	$translation_locale = strtolower( str_replace( '_', '-', $translation_locale ) );
// 	error_log( "translation_locale\n" . print_r( $translation_locale, true ) );

// 	foreach ( $post_ids as $post_id ) {
// 		$terms = wp_get_object_terms( $post_id, 'immobilie_language' );

// 		if ( is_wp_error( $terms ) || empty( $terms ) ) {
// 			continue;
// 		}

// 		$post_locale = strtolower( str_replace( '_', '-', $terms[0]->name ) );

// 		error_log( "post_locale\n" . print_r( $post_locale, true ) . "\n" );


// 		if ( $translation_locale === $post_locale ) {
// 			$translation_post_id = $post_id;
// 			break;
// 		}
// 	}

// 	error_log( "translation_post_id\n" . print_r( $translation_post_id, true ) . "\n" );

// 	return $translation_post_id;
// }




// /**
//  * Get post IDs of the same post type with matching 'frymo_objektnr_extern' meta value.
//  *
//  * @global wpdb $wpdb WordPress database abstraction object.
//  *
//  * @param int $post_id The ID of the current post.
//  * @return int[] Array of matching post IDs.
//  */
// function frymo_tpi_get_matching_post_ids_by_external_object_id( $post_id, $object_exteral_id ) {
// 	global $wpdb;

// 	$post_id = absint( $post_id );

// 	if ( 0 === $post_id ) {
// 		return array();
// 	}

// 	// Get the meta value from the current post.
// 	$external_id = get_post_meta( $post_id, 'frymo_objektnr_extern', true );

// 	if ( empty( $external_id ) ) {
// 		return array();
// 	}

// 	// Prepare SQL query to fetch matching post IDs.
// 	$query = $wpdb->prepare(
// 		"
// 		SELECT p.ID
// 		FROM {$wpdb->posts} p
// 		INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
// 		WHERE p.post_type = %s
// 			AND p.ID != %d
// 			AND p.post_status != 'inherit'
// 			AND pm.meta_key = 'frymo_objektnr_extern'
// 			AND pm.meta_value = %s
// 		",
// 		FRYMO_POST_TYPE,
// 		$post_id,
// 		$external_id
// 	);

// 	$results = $wpdb->get_col( $query );

// 	// Ensure the result is an array of integers.
// 	return array_map( 'absint', $results );
// }
















add_action('template_redirect', 'replace_post_before_rendering');
function replace_post_before_rendering() {
	if (is_singular(FRYMO_POST_TYPE)) {
		global $wp_query;

		$current_post = $wp_query->get_queried_object();

		// Logic to find replacement post
		$replacement_post_id = 27356;

		$locale = get_locale();

		error_log( "locale\n" . print_r( $locale, true ) . "\n" );

		if ( $current_post->ID == 27354 && $locale == 'en_GB') {
			// Replace post object manually
			$replacement_post = get_post($replacement_post_id);
			$wp_query->post = $replacement_post;
			$wp_query->posts[0] = $replacement_post;
			$wp_query->queried_object = $replacement_post;
			$wp_query->queried_object_id = $replacement_post_id;

			// Optional: setup postdata if needed
			setup_postdata($replacement_post);
		}
	}
}