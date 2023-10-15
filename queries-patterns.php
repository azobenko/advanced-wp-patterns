<?php
/**
 * Multi-Sorting of Users:
 *  1 - the first sorting is by related custom  post-type title, its ID saved as User meta-field;
 *  2 - the second sorting is by login
 */
add_action('pre_get_users', 'get_custom_sorted_users');


/**
 * Set query parameters according to needed fields
 *
 * @param $query WP_User_Query
 * @return WP_User_Query
 */
function get_custom_sorted_users( $query ) : WP_User_Query
{
    // Allow only for wp-admin
    if( !is_admin() ) return $query;

    // Allow only for Users List page
    $screen = get_current_screen();
    if ( isset( $screen->id ) && $screen->id !== 'users' ) return $query;

    // Ordering by the title of related_post_type
    if ( $query->get( 'orderby' ) == 'related_post_type' ) {
        // Create double order
        $query->set( 'order', $_GET[ 'order' ] . ' asc' );
        // Set order by include and login
        $query->set( 'orderby', 'include login' );
        // Retrieve included IDs
        $query->set( 'include', get_sorted_users( $query, 'related_post_type', $_GET[ 'order' ] ) );
    }
}

/**
 * Returns sorted array with Users IDs according to the Query
 *
 * @param $query WP_User_Query
 * @param $field string
 * @param $order string
 *
 * @return array
 */
function get_sorted_users( $query, $field, $order ) : array
{
    // Get Users with needed role
    $all_users = get_users([ 'role' => $query->query_vars['role'] ]);
    // Create $sorted array and fill-up it with pairs user_id->post_title of custom_post_type
    $sorted = [];
    if ( is_array($all_users) ) {
        foreach ( $all_users as $user ) {
            $related_post_title = get_the_title( intval( get_user_meta($user->ID, $field, true) ) );
            if ( empty($related_post_title) ) $related_post_title = 'zzz'; //last in sorting
            $sorted[$user->ID] = $related_post_title;
        }
    }

    if ( $order == 'asc' ) {
        asort($sorted, SORT_STRING);
    }
    if ( $order == 'desc' ) {
        arsort($sorted, SORT_STRING);
    }

    return array_keys( $sorted );
}



