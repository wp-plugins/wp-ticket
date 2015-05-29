<?php
/**
 * Query Filter Functions
 *
 * @package WP_TICKET_COM
 * @version 2.0.0
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Change query parameters before wp_query is processed
 *
 * @since WPAS 4.0
 * @param object $query
 *
 * @return object $query
 */
function wp_ticket_com_query_filters($query) {
	$has_limitby = get_option("wp_ticket_com_has_limitby_cap");
	if (!is_admin() && $query->is_main_query()) {
		if ($query->is_author || $query->is_search) {
			$query = emd_limit_author_search('wp_ticket_com', $query, $has_limitby);
		}
	}
	return $query;
}
add_action('pre_get_posts', 'wp_ticket_com_query_filters');
