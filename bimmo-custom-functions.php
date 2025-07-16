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


add_action( 'template_redirect', 'ftpi_redirect_based_on_locale' );
function ftpi_redirect_based_on_locale() {
	if ( is_admin() || wp_doing_ajax() ) {
		return;
	}

	$locale = get_locale();

	if ( ! is_singular( FRYMO_POST_TYPE ) ) {
		return;
	}

	$current_post = get_queried_object();
	error_log( "current_post ID\n" . print_r( $current_post->ID, true ) );

	$replacement_post = frymo_tpi_get_translated_post( $current_post, $locale );
	error_log( "replacement_post ID\n" . print_r( $replacement_post->ID, true ) . "\n" );

	if ( $replacement_post->ID !== $current_post->ID ) {
		$permalink = get_the_permalink( $replacement_post );

		wp_redirect( $permalink, 301 );
		exit;
	}
}





// /**
//  * Maybe use this filter to change language URL for switcher.
//  */
// add_filter( 'trp_pre_get_url_for_language', 'custom_trp_language_url', 10, 3 );
// function custom_trp_language_url( $url, $language_code, $original_url ) {

// 	error_log( "url\n" . print_r( $url, true ) );
// 	error_log( "language_code\n" . print_r( $language_code, true ) );
// 	error_log( "original_url\n" . print_r( $original_url, true ) . "\n" );

//    //  // Example: custom URL for French version
//    //  if ( $language_code === 'de' ) {
//    //      return home_url( '/fr/custom-path/' );
//    //  }

//     // Return unmodified URL for other languages
//     return $url;
// }




function frymo_tpi_get_translated_post( $current_post, $locale ) {
	$current_post_id = $current_post->ID;

	$post_ids_with_same_external_id = frymo_tpi_get_matching_post_ids_by_external_object_id( $current_post_id );
	// error_log( "post_ids_with_same_external_id\n" . print_r( $post_ids_with_same_external_id, true ) . "\n" );

	$translation_post_id = frymo_tpi_get_translated_object_id( $post_ids_with_same_external_id, $locale );

	if ( is_int( $translation_post_id ) ) {
		$translation_post = get_post( $translation_post_id );

		if (
			$translation_post instanceof WP_Post &&
			'publish' === $translation_post->post_status &&
			FRYMO_POST_TYPE === $translation_post->post_type
		) {
			return $translation_post;
		}
	}

	return $current_post;
}


/**
 * Get post IDs of the same post type with matching 'frymo_objektnr_extern' meta value.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param int $post_id The ID of the current post.
 * @return int[] Array of matching post IDs.
 */
function frymo_tpi_get_matching_post_ids_by_external_object_id( $post_id ) {
	global $wpdb;

	$post_id = absint( $post_id );

	if ( 0 === $post_id ) {
		return array();
	}

	// Get the meta value from the current post.
	$external_id = get_post_meta( $post_id, 'frymo_objektnr_extern', true );

	if ( empty( $external_id ) ) {
		return array();
	}

	// Prepare SQL query to fetch matching post IDs.
	$query = $wpdb->prepare(
		"
		SELECT p.ID
		FROM {$wpdb->posts} p
		INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
		WHERE p.post_type = %s
			AND p.ID != %d
			AND p.post_status != 'inherit'
			AND pm.meta_key = 'frymo_objektnr_extern'
			AND pm.meta_value = %s
		",
		FRYMO_POST_TYPE,
		$post_id,
		$external_id
	);

	$results = $wpdb->get_col( $query );

	// Ensure the result is an array of integers.
	return array_map( 'absint', $results );
}

function frymo_tpi_get_translated_object_id( $post_ids, $translation_locale ) {
	$translation_post_id = false;

	$translation_locale = strtolower( str_replace( '_', '-', $translation_locale ) );
	// error_log( "translation_locale\n" . print_r( $translation_locale, true ) );

	foreach ( $post_ids as $post_id ) {
		$terms = wp_get_object_terms( $post_id, 'immobilie_language' );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			continue;
		}

		$post_locale = strtolower( str_replace( '_', '-', $terms[0]->name ) );

		// error_log( "post_locale\n" . print_r( $post_locale, true ) . "\n" );


		if ( $translation_locale === $post_locale ) {
			$translation_post_id = $post_id;
			break;
		}
	}

	// error_log( "translation_post_id\n" . print_r( $translation_post_id, true ) . "\n" );

	return $translation_post_id;
}


































































