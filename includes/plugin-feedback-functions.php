<?php
/**
 * Plugin Page Feedback Functions
 *
 * @package WP_TICKET_COM
 * @since WPAS 5.3
 */
if (!defined('ABSPATH')) exit;
add_filter('plugin_row_meta', 'wp_ticket_com_plugin_row_meta', 10, 2);
add_filter('plugin_action_links', 'wp_ticket_com_plugin_action_links', 10, 2);
add_action('wp_ajax_wp_ticket_com_send_deactivate_reason', 'wp_ticket_com_send_deactivate_reason');
global $pagenow;
if ('plugins.php' === $pagenow) {
	add_action('admin_footer', 'wp_ticket_com_deactivation_feedback_box');
}
add_action('wp_ajax_wp_ticket_com_show_rateme', 'wp_ticket_com_show_rateme_action');
//check min entity count if its not -1 then show notice
$min_trigger = get_option('wp_ticket_com_show_rateme_plugin_min', 10);
if ($min_trigger != - 1) {
	add_action('admin_notices', 'wp_ticket_com_show_rateme_notice');
}
function wp_ticket_com_show_rateme_action() {
	if (!wp_verify_nonce($_POST['rateme_nonce'], 'wp_ticket_com_rateme_nonce')) {
		exit;
	}
	$min_trigger = get_option('wp_ticket_com_show_rateme_plugin_min', 10);
	if ($min_trigger == - 1) {
		die;
	}
	if (10 === $min_trigger) {
		$min_trigger = 20;
	} else {
		$min_trigger = - 1;
	}
	update_option('wp_ticket_com_show_rateme_plugin_min', $min_trigger);
	echo 1;
	die;
}
function wp_ticket_com_show_rateme_notice() {
	if (!current_user_can('manage_options')) {
		return;
	}
	$min_count = 0;
	$ent_list = get_option('wp_ticket_com_ent_list');
	$min_trigger = get_option('wp_ticket_com_show_rateme_plugin_min', 10);
	$count_posts = wp_count_posts('emd_ticket');
	if ($count_posts->publish > $min_trigger) {
		$min_count = $count_posts->publish;
		$label = $ent_list['emd_ticket']['label'];
	}
	if ($min_count > 10) {
		$message_start = '<div class="emd-show-rateme update-nag success">
                        <span class=""><b>WP Ticket</b></span>
                        <div>';
		$message_start.= sprintf(__("Hi, I noticed you just crossed the %d %s on WP Ticket - that's awesome!", "wp-ticket-com") , $min_trigger, $label);
		$message_level1 = __("Could you please do me a BIG favor and give it a 5-star rating on WordPress? Just to help us spread the word and boost our motivation.", "wp-ticket-com");
		$message_level2 = sprintf(__("Would you like to upgrade now to get more out of your %s?", "wp-ticket-com") , $label);
		$message_end = '<br/><br/>
                        <strong><em>Safiye Duman</em></strong>
                        </div>
                        <ul data-nonce="' . wp_create_nonce('wp_ticket_com_rateme_nonce') . '">';
		$message_end1 = '<li><a data-rate-action="do-rate" data-plugin="wp_ticket_com" href="https://wordpress.org/support/plugin/wp-ticket/reviews/#postform">' . __('Ok, you deserve it', 'wp-ticket-com') . '</a>
       </li>
        <li><a data-rate-action="done-rating" data-plugin="wp_ticket_com" href="#">' . __('I already did', 'wp-ticket-com') . '</a></li>
        <li><a data-rate-action="not-enough" data-plugin="wp_ticket_com" href="#">' . __('Maybe later', 'wp-ticket-com') . '</a></li>';
		$message_end2 = '<li><a data-rate-action="upgrade-now" data-plugin="wp_ticket_com" href="https://emdplugins.com/plugin_tag/wp-ticket-com">' . __('I want to upgrade', 'wp-ticket-com') . '</a>
       </li>
        <li><a data-rate-action="not-enough" data-plugin="wp_ticket_com" href="#">' . __('Maybe later', 'wp-ticket-com') . '</a></li>';
	}
	if ($min_count > 20 && $min_trigger == 20) {
		echo $message_start . ' ' . $message_level2 . ' ' . $message_end . ' ' . $message_end2 . '</ul></div>';
	} elseif ($min_count > 10) {
		echo $message_start . ' ' . $message_level1 . ' ' . $message_end . ' ' . $message_end1 . '</ul></div>';
	}
}
/**
 * Adds links under plugin description
 *
 * @since WPAS 5.3
 * @param array $input
 * @param string $file
 * @return array $input
 */
function wp_ticket_com_plugin_row_meta($input, $file) {
	if ($file != 'wp-ticket/wp-ticket.php') return $input;
	$links = array(
		'<a href="https://docs.emdplugins.com/docs/wp-ticket-community-documentation/">' . __('Docs', 'wp-ticket-com') . '</a>',
		'<a href="https://emdplugins.com/plugin_tag/wp-ticket-com">' . __('Pro Version', 'wp-ticket-com') . '</a>'
	);
	$input = array_merge($input, $links);
	return $input;
}
/**
 * Adds links under plugin description
 *
 * @since WPAS 5.3
 * @param array $input
 * @param string $file
 * @return array $input
 */
function wp_ticket_com_plugin_action_links($links, $file) {
	if ($file != 'wp-ticket/wp-ticket.php') return $links;
	foreach ($links as $key => $link) {
		if ('deactivate' === $key) {
			$links[$key] = $link . '<i class="wp_ticket_com-deactivate-slug" data-slug="wp_ticket_com-deactivate-slug"></i>';
		}
	}
	$new_links['settings'] = '<a href="' . admin_url('admin.php?page=wp_ticket_com_settings') . '">' . __('Settings', 'wp-ticket-com') . '</a>';
	$links = array_merge($new_links, $links);
	return $links;
}
function wp_ticket_com_deactivation_feedback_box() {
	wp_enqueue_style("emd-plugin-modal", WP_TICKET_COM_PLUGIN_URL . 'assets/css/emd-plugin-modal.css');
	$feedback_vars['submit'] = __('Submit & Deactivate', 'wp-ticket-com');
	$feedback_vars['skip'] = __('Skip & Deactivate', 'wp-ticket-com');
	$feedback_vars['cancel'] = __('Cancel', 'wp-ticket-com');
	$feedback_vars['ask_reason'] = __('Kindly tell us the reason so we can improve', 'wp-ticket-com');
	$feedback_vars['nonce'] = wp_create_nonce('wp_ticket_com_deactivate_nonce');
	$reasons[1] = __('I no longer need the plugin', 'wp-ticket-com');
	$reasons[2] = __('I found a better plugin', 'wp-ticket-com');
	$reasons[8] = __('I haven\'t found a feature that I need', 'wp-ticket-com');
	$reasons[3] = __('I only needed the plugin for a short period', 'wp-ticket-com');
	$reasons[4] = __('The plugin broke my site', 'wp-ticket-com');
	$reasons[5] = __('The plugin suddenly stopped working', 'wp-ticket-com');
	$reasons[6] = __('It\'s a temporary deactivation. I\'m just debugging an issue', 'wp-ticket-com');
	$reasons[7] = __('Other', 'wp-ticket-com');
	$feedback_vars['msg'] = __('If you have a moment, please let us know why you are deactivating', 'wp-ticket-com');
	$feedback_vars['disclaimer'] = __('No private information is sent during your submission. Thank you very much for your help improving our plugin.', 'wp-ticket-com');
	$feedback_vars['reasons'] = '';
	foreach ($reasons as $key => $reason) {
		$feedback_vars['reasons'].= '<li class="reason';
		if ($key == 2 || $key == 7 || $key == 8) {
			$feedback_vars['reasons'].= ' has-input';
		}
		$feedback_vars['reasons'].= '"';
		if ($key == 2 || $key == 7 || $key == 8) {
			$feedback_vars['reasons'].= 'data-input-type="textfield"';
			if ($key == 2) {
				$feedback_vars['reasons'].= 'data-input-placeholder="' . __('What\'s the plugin\'s name?', 'wp-ticket-com') . '"';
			} elseif ($key == 8) {
				$feedback_vars['reasons'].= 'data-input-placeholder="' . __('What feature do you need?', 'wp-ticket-com') . '"';
			}
		}
		$feedback_vars['reasons'].= '><label><span>
                                        <input type="radio" name="selected-reason" value="' . $key . '"/>
                                        </span><span>' . $reason . '</span></label></li>';
	}
	wp_enqueue_script('emd-plugin-feedback', WP_TICKET_COM_PLUGIN_URL . 'assets/js/emd-plugin-feedback.js');
	wp_localize_script("emd-plugin-feedback", 'plugin_feedback_vars', $feedback_vars);
	wp_enqueue_script('wp-ticket-com-feedback', WP_TICKET_COM_PLUGIN_URL . 'assets/js/wp-ticket-com-feedback.js');
	$wp_ticket_com_vars['plugin'] = 'wp_ticket_com';
	wp_localize_script("wp-ticket-com-feedback", 'wp_ticket_com_vars', $wp_ticket_com_vars);
}
function wp_ticket_com_send_deactivate_reason() {
	if (empty($_POST['deactivate_nonce']) || !isset($_POST['reason_id'])) {
		exit;
	}
	if (!wp_verify_nonce($_POST['deactivate_nonce'], 'wp_ticket_com_deactivate_nonce')) {
		exit;
	}
	$reason_info = isset($_POST['reason_info']) ? sanitize_text_field($_POST['reason_info']) : '';
	$postfields['reason_id'] = intval($_POST['reason_id']);
	$postfields['plugin_name'] = sanitize_text_field($_POST['plugin_name']);
	if (!empty($reason_info)) {
		$postfields['reason_info'] = $reason_info;
	}
	$args = array(
		'body' => $postfields,
		'sslverify' => false,
		'timeout' => 15,
	);
	$resp = wp_remote_post('https://api.emarketdesign.com/deactivate_info.php', $args);
	echo 1;
	exit;
}
