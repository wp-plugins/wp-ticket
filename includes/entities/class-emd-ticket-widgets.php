<?php
/**
 * Entity Widget Classes
 *
 * @package WP_TICKET_COM
 * @version 1.4
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Entity widget class extends Emd_Widget class
 *
 * @since WPAS 4.0
 */
class wp_ticket_com_recent_tickets_sidebar_widget extends Emd_Widget {
	public $title;
	public $text_domain = 'wp-ticket-com';
	public $class_label;
	public $class = 'emd_ticket';
	public $type = 'entity';
	public $has_pages = false;
	public $css_label = 'recent-tickets';
	public $id = 'wp_ticket_com_recent_tickets_sidebar_widget';
	public $query_args = array(
		'post_type' => 'emd_ticket',
		'post_status' => 'publish',
		'orderby' => 'date',
		'order' => 'DESC'
	);
	public $filter = '';
	/**
	 * Instantiate entity widget class with params
	 *
	 * @since WPAS 4.0
	 */
	function wp_ticket_com_recent_tickets_sidebar_widget() {
		$this->Emd_Widget(__('Recent Tickets', 'wp-ticket-com') , __('Tickets', 'wp-ticket-com') , __('The most recent tickets', 'wp-ticket-com'));
	}
	/**
	 * Returns widget layout
	 *
	 * @since WPAS 4.0
	 */
	public static function layout() {
		$layout = "* <a title=\"" . esc_html(emd_mb_meta('emd_ticket_id')) . " - " . get_the_date() . " - " . get_the_time() . "\" href=\"" . get_permalink() . "\">" . get_the_title() . "</a><br />
";
		return $layout;
	}
}
$access_views = get_option('wp_ticket_com_access_views', Array());
if (empty($access_views['widgets']) || (!empty($access_views['widgets']) && in_array('recent_tickets_sidebar', $access_views['widgets']) && current_user_can('view_recent_tickets_sidebar'))) {
	register_widget('wp_ticket_com_recent_tickets_sidebar_widget');
}
