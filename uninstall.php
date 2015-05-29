<?php
/**
 *  Uninstall Wp Ticket
 *
 * Uninstalling deletes notifications and terms initializations
 *
 * @package WP_TICKET_COM
 * @version 2.0.0
 * @since WPAS 4.0
 */
if (!defined('WP_UNINSTALL_PLUGIN')) exit;
if (!current_user_can('activate_plugins')) return;
function wp_ticket_com_uninstall() {
	//delete options
	$options_to_delete = Array(
		'wp_ticket_com_notify_list',
		'wp_ticket_com_ent_list',
		'wp_ticket_com_attr_list',
		'wp_ticket_com_shc_list',
		'wp_ticket_com_tax_list',
		'wp_ticket_com_rel_list',
		'wp_ticket_com_license_key',
		'wp_ticket_com_license_status',
		'wp_ticket_com_comment_list',
		'wp_ticket_com_access_views',
		'wp_ticket_com_limitby_auth_caps',
		'wp_ticket_com_limitby_caps',
		'wp_ticket_com_has_limitby_cap',
		'wp_ticket_com_setup_pages',
		'wp_ticket_com_emd_ticket_terms_init'
	);
	if (!empty($options_to_delete)) {
		foreach ($options_to_delete as $option) {
			delete_option($option);
		}
	}
	$emd_activated_plugins = get_option('emd_activated_plugins');
	if (!empty($emd_activated_plugins)) {
		$emd_activated_plugins = array_diff($emd_activated_plugins, Array(
			'wp-ticket-com'
		));
		update_option('emd_activated_plugins', $emd_activated_plugins);
	}
}
if (is_multisite()) {
	global $wpdb;
	$blogs = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);
	if ($blogs) {
		foreach ($blogs as $blog) {
			switch_to_blog($blog['blog_id']);
			wp_ticket_com_uninstall();
		}
		restore_current_blog();
	}
} else {
	wp_ticket_com_uninstall();
}
