<?php
/**
 * Enqueue Scripts Functions
 *
 * @package WP_TICKET_COM
 * @version 1.2
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
add_action('admin_enqueue_scripts', 'wp_ticket_com_load_admin_enq');
/**
 * Enqueue style and js for each admin entity pages and settings
 *
 * @since WPAS 4.0
 * @param string $hook
 *
 */
function wp_ticket_com_load_admin_enq($hook) {
	wp_enqueue_script('emd-share', WP_TICKET_COM_PLUGIN_URL . 'assets/js/emd-share.js', '', WP_TICKET_COM_VERSION, true);
	if ($hook == 'toplevel_page_wp_ticket_com' || $hook == 'wp-ticket_page_wp_ticket_com_notify') {
		wp_enqueue_script('accordion');
		return;
	} else if ($hook == 'wp-ticket_page_wp_ticket_com_store') {
		wp_enqueue_style('admin-tabs', WP_TICKET_COM_PLUGIN_URL . 'assets/css/admin-store.css');
		return;
	}
	global $post;
	if (isset($post) && in_array($post->post_type, Array(
		'emd_ticket'
	))) {
		$theme_changer_enq = 1;
		$datetime_enq = 0;
		$date_enq = 0;
		$sing_enq = 0;
		if ($hook == 'post.php' || $hook == 'post-new.php') {
			$unique_vars['msg'] = __('Please enter a unique value.', 'emd-plugins');
			wp_enqueue_script('unique_validate-js', WP_TICKET_COM_PLUGIN_URL . 'assets/js/unique_validate.js', array(
				'jquery',
				'jquery-validate'
			) , WP_TICKET_COM_VERSION, true);
			wp_localize_script("unique_validate-js", 'unique_vars', $unique_vars);
		}
		switch ($post->post_type) {
			case 'emd_ticket':
				$datetime_enq = 1;
				$sing_enq = 1;
			break;
		}
		wp_enqueue_style("jq-css", "//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/themes/smoothness/jquery-ui.css");
		if ($datetime_enq == 1) {
			wp_enqueue_script("jquery-ui-timepicker", WP_TICKET_COM_PLUGIN_URL . 'assets/ext/meta-box/js/jqueryui/jquery-ui-timepicker-addon.js', array(
				'jquery-ui-datepicker',
				'jquery-ui-slider'
			) , WP_TICKET_COM_VERSION, true);
		} elseif ($date_enq == 1) {
			wp_enqueue_script("jquery-ui-datepicker");
		}
		if ($sing_enq == 1) {
			wp_enqueue_script('radiotax', WP_TICKET_COM_PLUGIN_URL . 'includes/admin/singletax/singletax.js', array(
				'jquery'
			) , WP_TICKET_COM_VERSION, true);
		}
	}
}
add_action('wp_enqueue_scripts', 'wp_ticket_com_frontend_scripts');
/**
 * Enqueue style and js for each frontend entity pages and components
 *
 * @since WPAS 4.0
 *
 */
function wp_ticket_com_frontend_scripts() {
	$dir_url = WP_TICKET_COM_PLUGIN_URL;
	if (is_page()) {
		$grid_vars = Array();
		$local_vars['ajax_url'] = admin_url('admin-ajax.php');
		$local_vars['validate_msg']['required'] = __('This field is required.', 'emd-plugins');
		$local_vars['validate_msg']['remote'] = __('Please fix this field.', 'emd-plugins');
		$local_vars['validate_msg']['email'] = __('Please enter a valid email address.', 'emd-plugins');
		$local_vars['validate_msg']['url'] = __('Please enter a valid URL.', 'emd-plugins');
		$local_vars['validate_msg']['date'] = __('Please enter a valid date.', 'emd-plugins');
		$local_vars['validate_msg']['dateISO'] = __('Please enter a valid date ( ISO )', 'emd-plugins');
		$local_vars['validate_msg']['number'] = __('Please enter a valid number.', 'emd-plugins');
		$local_vars['validate_msg']['digits'] = __('Please enter only digits.', 'emd-plugins');
		$local_vars['validate_msg']['creditcard'] = __('Please enter a valid credit card number.', 'emd-plugins');
		$local_vars['validate_msg']['equalTo'] = __('Please enter the same value again.', 'emd-plugins');
		$local_vars['validate_msg']['maxlength'] = __('Please enter no more than {0} characters.', 'emd-plugins');
		$local_vars['validate_msg']['minlength'] = __('Please enter at least {0} characters.', 'emd-plugins');
		$local_vars['validate_msg']['rangelength'] = __('Please enter a value between {0} and {1} characters long.', 'emd-plugins');
		$local_vars['validate_msg']['range'] = __('Please enter a value between {0} and {1}.', 'emd-plugins');
		$local_vars['validate_msg']['max'] = __('Please enter a value less than or equal to {0}.', 'emd-plugins');
		$local_vars['validate_msg']['min'] = __('Please enter a value greater than or equal to {0}.', 'emd-plugins');
		$local_vars['unique_msg'] = __('Please enter a unique value.', 'emd-plugins');
		$wpas_shc_list = get_option('wp_ticket_com_shc_list');
		$check_content = "";
		if (!is_author() && !is_tax()) {
			$check_content = get_post(get_the_ID())->post_content;
		}
		if (!empty($check_content) && has_shortcode($check_content, 'submit_tickets')) {
			wp_enqueue_script('jquery');
			wp_enqueue_script('jvalidate-js', $dir_url . 'assets/ext/jvalidate1111/wpas.validate.min.js', array(
				'jquery'
			));
			wp_enqueue_style("jq-css", "//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/themes/smoothness/jquery-ui.css");
			wp_enqueue_style('wpasui', WP_TICKET_COM_PLUGIN_URL . 'assets/ext/wpas-jui/wpas-jui.min.css');
			wp_enqueue_style('submit-tickets-forms', $dir_url . 'assets/css/submit-tickets-forms.css');
			wp_enqueue_script('submit-tickets-forms-js', $dir_url . 'assets/js/submit-tickets-forms.js');
			wp_localize_script('submit-tickets-forms-js', 'submit_tickets_vars', $local_vars);
		} elseif (!empty($check_content) && has_shortcode($check_content, 'search_tickets')) {
			wp_enqueue_script('jquery');
			wp_enqueue_script('jvalidate-js', $dir_url . 'assets/ext/jvalidate1111/wpas.validate.min.js', array(
				'jquery'
			));
			wp_enqueue_style("jq-css", "//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/themes/smoothness/jquery-ui.css");
			wp_enqueue_style('wpasui', WP_TICKET_COM_PLUGIN_URL . 'assets/ext/wpas-jui/wpas-jui.min.css');
			wp_enqueue_style('allview-css', $dir_url . '/assets/css/allview.css');
			wp_enqueue_style('search-tickets-forms', $dir_url . 'assets/css/search-tickets-forms.css');
			wp_enqueue_script('search-tickets-forms-js', $dir_url . 'assets/js/search-tickets-forms.js');
			wp_localize_script('search-tickets-forms-js', 'search_tickets_vars', $local_vars);
		}
		return;
	}
	if (is_single() && get_post_type() == 'emd_ticket') {
		wp_enqueue_style("wp-ticket-com-default-single-css", WP_TICKET_COM_PLUGIN_URL . 'assets/css/wp-ticket-com-default-single.css');
	}
}
