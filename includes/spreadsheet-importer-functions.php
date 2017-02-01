<?php
/**
 * Utility functions for the plugin.
 *
 * @package   CARES_Spreadsheet_Importer
 * @author    AuthorName
 * @license   GPL-2.0+
 * @link      http://www.communitycommons.org
 * @copyright 2017 Community Commons
 */

function cares_data_set_parse_source() {
	$data_set_id = get_the_ID();
	$data_set_source = get_post_meta( $data_set_id, 'cares_data_set_source', true );
	if ( ! $data_set_source ) {
		return;
	}
	$filepath = get_attached_file( $data_set_source );
	$filename = basename( $filepath );

	$filetype = wp_check_filetype( $filename, $mimes );
	switch ( $filetype['type'] ) {
		case 'application/vnd.oasis.opendocument.spreadsheet':
			// Open Office spreadsheet
			# code...
			break;
		case 'text/csv':
			// Comma-Separated Values
			$data = cares_csv_to_json( $filepath );
			break;
		case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
			// Excel xlsx
			# code...
			break;
		case 'application/vnd.ms-excel':
			// Excel xls
			# code...
			break;
		default:
			# code...
			break;
	}
	return $data;
}

function cares_csv_to_json( $filename='', $delimiter=',' ) {
    if ( ! file_exists( $filename ) || ! is_readable( $filename ) ){
        return;
    }

    $header = null;
    $data = array();
    if ( ( $handle = fopen( $filename, 'r' ) ) !== FALSE ) {
        while ( ( $row = fgetcsv( $handle, 1000, $delimiter ) ) !== FALSE ) {
            if ( ! $header ) {
                $header = $row;
            } else {
                $data[] = array_combine($header, $row);
            }
        }
        fclose($handle);
    }
    return json_encode( $data );
}

/**
 * Get the access term slug for a user id
 *
 * @since 1.0.0
 * @param int|bool $user_id Defaults to logged in user
 *
 * @return string The term slug
 */
function cares_data_set_get_user_access_term_slug( $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}
	$user_id = (int) $user_id;
	return 'cares_data_set_access_user_' . $user_id;
}

/**
 * Get the access term for a user id
 *
 * @since 1.0.0
 * @param int|bool $user_id Defaults to logged in user
 *
 * @return obj|false WP_Term object
 */
function cares_data_set_get_user_access_term( $user_id = false ) {
	$slug = cares_data_set_get_user_access_term_slug( $user_id );
	$term = get_term_by( 'slug', $slug, 'data_set_allowed_editors' );
	return $term;
}

/**
 * Find out which users can edit this data set.
 *
 * @since 1.0
 *
 * @return bool True if it's an existing data set post.
 */
function cares_data_set_get_allowed_editors( $post_id = 0 ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$editors = array();
	$terms = get_the_terms( $post_id, 'data_set_allowed_editors' );
	foreach ( $terms as $term ) {
		$editors[] = (int) str_replace( 'cares_data_set_access_user_', '', $term->slug );
	}

	return array_unique( $editors );
}

/**
 * Get the info of the users who can edit this data set.
 *
 * @since 1.0
 *
 * @return bool True if it's an existing data set post.
 */
function cares_data_set_get_allowed_editors_info( $post_id = 0 ) {
	$editor_ids = cares_data_set_get_allowed_editors( $post_id );

	// WP_User_Query arguments
	$args = array(
		'include' => $editor_ids,
		'fields'  => array( 'id', 'user_login', 'display_name' ),
	);

	$editors = new WP_User_Query( $args );
	// echo '<pre>'; var_dump( $editors->results ); echo '</pre>';
	return $editors->results;
}

/**
 * Are we looking at an existing doc?
 *
 * @since 1.0
 *
 * @return bool True if it's an existing data set post.
 */
function cares_data_set_is_data_set() {
	return is_singular( 'cares_data_set' );
}

/**
 * Is this the Docs create screen?
 *
 * @since 1.0
 * @return bool
 */
function cares_data_set_is_create() {
	return ( is_post_type_archive( 'cares_data_set' ) && ! empty( $_GET['create'] ) );
}