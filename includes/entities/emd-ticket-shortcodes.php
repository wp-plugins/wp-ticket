<?php
/**
 * Entity Related Shortcode Functions
 *
 * @package WP_TICKET_COM
 * @version 1.2
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Shortcode function
 *
 * @since WPAS 4.0
 * @param array $atts
 * @param array $args
 * @param string $form_name
 * @param int $pageno
 *
 * @return html
 */
function wp_ticket_com_search_tickets_set_shc($atts, $args = Array() , $form_name = '', $pageno = 1) {
	$fields = Array(
		'app' => 'wp_ticket_com',
		'class' => 'emd_ticket',
		'shc' => 'search_tickets',
		'form' => $form_name,
		'has_pages' => true,
		'pageno' => $pageno,
		'theme' => 'jui'
	);
	$args_default = array(
		'posts_per_page' => '10',
		'post_status' => 'publish',
		'orderby' => 'date',
		'order' => 'DESC'
	);
	return emd_shc_get_layout_list($atts, $args, $args_default, $fields);
}
add_filter('widget_text', 'shortcode_unautop');
add_filter('widget_text', 'do_shortcode', 11);
