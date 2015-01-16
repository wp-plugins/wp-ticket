<?php
/**
 * Setup and Process submit and search forms
 * @package WP_TICKET_COM
 * @version 1.1
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
if (is_admin()) {
}
add_action('wp_loaded', 'wp_ticket_com_form_shortcodes');
/**
 * Start session and setup upload idr and current user id
 * @since WPAS 4.0
 *
 */
function wp_ticket_com_form_shortcodes() {
	global $current_user, $current_user_id, $file_upload_dir;
	get_currentuserinfo();
	$current_user_id = $current_user->ID;
	if (!isset($current_user_id) || $current_user_id == '') {
		$current_user_id = 'guest';
	}
	$upload_dir = wp_upload_dir();
	$file_upload_dir = $upload_dir['basedir'] . '/wpas-files/' . $current_user_id;
	if (!session_id()) {
		session_start();
	}
}
add_shortcode('submit_tickets', 'wp_ticket_com_process_submit_tickets');
add_shortcode('search_tickets', 'wp_ticket_com_process_search_tickets');
/**
 * Set each form field(attr,tax and rels) and render form
 *
 * @since WPAS 4.0
 *
 * @return object $form
 */
function wp_ticket_com_set_search_tickets() {
	global $file_upload_dir;
	$show_captcha = 1;
	if (is_user_logged_in()) {
		$show_captcha = 0;
	}
	require_once WP_TICKET_COM_PLUGIN_DIR . '/assets/ext/zebraform/Zebra_Form.php';
	$form = new Zebra_Form('search_tickets', 0, 'POST', '', array(
		'class' => 'form-container wpas-form wpas-form-stacked'
	));
	$form->form_properties['csrf_storage_method'] = false;
	//text
	$form->add('label', 'label_emd_ticket_id', 'emd_ticket_id', 'Ticket ID', array(
		'class' => 'control-label'
	));
	$obj = $form->add('text', 'emd_ticket_id', '', array(
		'class' => 'input-md form-control',
		'placeholder' => __('Ticket ID', 'wp-ticket-com')
	));
	$obj->set_rule(array());
	//text
	$form->add('label', 'label_emd_ticket_email', 'emd_ticket_email', 'Email', array(
		'class' => 'control-label'
	));
	$obj = $form->add('text', 'emd_ticket_email', '', array(
		'class' => 'input-md form-control',
		'placeholder' => __('Email', 'wp-ticket-com')
	));
	$obj->set_rule(array(
		'email' => array(
			'error',
			__('Email: Please enter a valid email address', 'wp-ticket-com')
		) ,
	));
	$form->assign('show_captcha', $show_captcha);
	if ($show_captcha == 1) {
		//Captcha
		$form->add('captcha', 'captcha_image', 'captcha_code', '', '<span style="font-weight:bold;" class="refresh-txt">Refresh</span>', 'refcapt');
		$form->add('label', 'label_captcha_code', 'captcha_code', __('Please enter the characters with black color.', 'wp-ticket-com'));
		$obj = $form->add('text', 'captcha_code', '', array(
			'placeholder' => __('Code', 'wp-ticket-com')
		));
		$obj->set_rule(array(
			'required' => array(
				'error',
				__('Captcha is required', 'wp-ticket-com')
			) ,
			'captcha' => array(
				'error',
				__('Characters from captcha image entered incorrectly!', 'wp-ticket-com')
			)
		));
	}
	$form->add('submit', 'singlebutton_search_tickets', '' . __('Search Tickets', 'wp-ticket-com') . ' ', array(
		'class' => 'wpas-button wpas-juibutton-primary wpas-button-large btn-block'
	));
	return $form;
}
/**
 * Process each form and show error or success
 *
 * @since WPAS 4.0
 *
 * @return html
 */
function wp_ticket_com_process_search_tickets() {
	$show_form = 1;
	$access_views = get_option('wp_ticket_com_access_views', Array());
	if (!current_user_can('view_search_tickets') && !empty($access_views['forms']) && in_array('search_tickets', $access_views['forms'])) {
		$show_form = 0;
	}
	if ($show_form == 1) {
		$noresult_msg = __('Your search returned no results.', 'wp-ticket-com');
		return emd_search_php_form('search_tickets', 'wp_ticket_com', 'emd_ticket', $noresult_msg, 'search_tickets');
	} else {
		return "<div class='alert alert-info not-authorized'>" . __('<p>You are not allowed to access to this area. Please contact the site administrator.</p>', 'wp-ticket-com') . "</div>";
	}
}
/**
 * Set each form field(attr,tax and rels) and render form
 *
 * @since WPAS 4.0
 *
 * @return object $form
 */
function wp_ticket_com_set_submit_tickets() {
	global $file_upload_dir;
	$show_captcha = 1;
	if (is_user_logged_in()) {
		$show_captcha = 0;
	}
	require_once WP_TICKET_COM_PLUGIN_DIR . '/assets/ext/zebraform/Zebra_Form.php';
	$form = new Zebra_Form('submit_tickets', 0, 'POST', '', array(
		'class' => 'form-container wpas-form wpas-form-stacked'
	));
	//hidden_func
	$emd_ticket_id = emd_get_hidden_func('unique_id');
	$form->add('hidden', 'emd_ticket_id', $emd_ticket_id);
	$form->add('label', 'label_ticket_topic', 'ticket_topic', 'Topic', array(
		'class' => 'control-label'
	));
	$obj = $form->add('selectadv', 'ticket_topic', 'Please Select', array(
		'class' => 'input-md'
	) , '', '{"allowClear":true,"placeholder":"' . __("Please Select", "wp-ticket-com") . '","placeholderOption":"first"}');
	//get taxonomy values
	$txn_arr = Array();
	$txn_arr[''] = 'Please select';
	$txn_obj = get_terms('ticket_topic', array(
		'hide_empty' => 0
	));
	foreach ($txn_obj as $txn) {
		$txn_arr[$txn->slug] = $txn->name;
	}
	$obj->add_options($txn_arr);
	$obj->set_rule(array(
		'required' => array(
			'error',
			__('Topic is required!', 'wp-ticket-com')
		) ,
	));
	//text
	$form->add('label', 'label_emd_ticket_first_name', 'emd_ticket_first_name', 'First Name', array(
		'class' => 'control-label'
	));
	$obj = $form->add('text', 'emd_ticket_first_name', '', array(
		'class' => 'input-md form-control',
		'placeholder' => __('First Name', 'wp-ticket-com')
	));
	$obj->set_rule(array(
		'required' => array(
			'error',
			__('First Name is required', 'wp-ticket-com')
		) ,
	));
	//text
	$form->add('label', 'label_emd_ticket_last_name', 'emd_ticket_last_name', 'Last Name', array(
		'class' => 'control-label'
	));
	$obj = $form->add('text', 'emd_ticket_last_name', '', array(
		'class' => 'input-md form-control',
		'placeholder' => __('Last Name', 'wp-ticket-com')
	));
	$obj->set_rule(array(
		'required' => array(
			'error',
			__('Last Name is required', 'wp-ticket-com')
		) ,
	));
	//text
	$form->add('label', 'label_emd_ticket_email', 'emd_ticket_email', 'Email', array(
		'class' => 'control-label'
	));
	$obj = $form->add('text', 'emd_ticket_email', '', array(
		'class' => 'input-md form-control',
		'placeholder' => __('Email', 'wp-ticket-com')
	));
	$obj->set_rule(array(
		'required' => array(
			'error',
			__('Email is required', 'wp-ticket-com')
		) ,
		'email' => array(
			'error',
			__('Email: Please enter a valid email address', 'wp-ticket-com')
		) ,
	));
	//text
	$form->add('label', 'label_blt_title', 'blt_title', 'Subject', array(
		'class' => 'control-label'
	));
	$obj = $form->add('text', 'blt_title', '', array(
		'class' => 'input-md form-control',
		'placeholder' => __('Subject', 'wp-ticket-com')
	));
	$obj->set_rule(array(
		'required' => array(
			'error',
			__('Subject is required', 'wp-ticket-com')
		) ,
	));
	//wysiwyg
	$form->add('label', 'label_blt_content', 'blt_content', 'Message', array(
		'class' => 'control-label'
	));
	$obj = $form->add('wysiwyg', 'blt_content', '', array(
		'placeholder' => __('Enter text ...', 'wp-ticket-com') ,
		'style' => 'width: 100%; height: 200px',
		'class' => 'wyrj'
	));
	$obj->set_rule(array(
		'required' => array(
			'error',
			__('Message is required', 'wp-ticket-com')
		) ,
	));
	//text
	$form->add('label', 'label_emd_ticket_phone', 'emd_ticket_phone', 'Phone', array(
		'class' => 'control-label'
	));
	$obj = $form->add('text', 'emd_ticket_phone', '', array(
		'class' => 'input-md form-control',
		'placeholder' => __('Phone', 'wp-ticket-com')
	));
	$obj->set_rule(array());
	//file
	$obj = $form->add('file', 'emd_ticket_attachment', '');
	$obj->set_rule(array(
		'upload' => array(
			$file_upload_dir,
			true,
			'error',
			'File could not be uploaded.'
		) ,
	));
	//hidden
	$obj = $form->add('hidden', 'wpas_form_name', 'submit_tickets');
	//hidden_func
	$wpas_form_submitted_by = emd_get_hidden_func('user_login');
	$form->add('hidden', 'wpas_form_submitted_by', $wpas_form_submitted_by);
	//hidden_func
	$wpas_form_submitted_ip = emd_get_hidden_func('user_ip');
	$form->add('hidden', 'wpas_form_submitted_ip', $wpas_form_submitted_ip);
	$form->assign('show_captcha', $show_captcha);
	if ($show_captcha == 1) {
		//Captcha
		$form->add('captcha', 'captcha_image', 'captcha_code', '', '<span style="font-weight:bold;" class="refresh-txt">Refresh</span>', 'refcapt');
		$form->add('label', 'label_captcha_code', 'captcha_code', __('Please enter the characters with black color.', 'wp-ticket-com'));
		$obj = $form->add('text', 'captcha_code', '', array(
			'placeholder' => __('Code', 'wp-ticket-com')
		));
		$obj->set_rule(array(
			'required' => array(
				'error',
				__('Captcha is required', 'wp-ticket-com')
			) ,
			'captcha' => array(
				'error',
				__('Characters from captcha image entered incorrectly!', 'wp-ticket-com')
			)
		));
	}
	$form->add('submit', 'singlebutton_submit_tickets', '' . __('Submit Ticket', 'wp-ticket-com') . ' ', array(
		'class' => 'wpas-button wpas-juibutton-success wpas-button-large btn-block'
	));
	return $form;
}
/**
 * Process each form and show error or success
 *
 * @since WPAS 4.0
 *
 * @return html
 */
function wp_ticket_com_process_submit_tickets() {
	$show_form = 1;
	$access_views = get_option('wp_ticket_com_access_views', Array());
	if (!current_user_can('view_submit_tickets') && !empty($access_views['forms']) && in_array('submit_tickets', $access_views['forms'])) {
		$show_form = 0;
	}
	if ($show_form == 1) {
		return emd_submit_php_form('submit_tickets', 'wp_ticket_com', 'emd_ticket', 'publish', 'draft', 'Thanks for your submission.', 'There has been an error when submitting your entry. Please contact the site administrator.', 0, 1);
	} else {
		return "<div class='alert alert-info not-authorized'>" . __('<p>You are not allowed to access to this area. Please contact the site administrator.</p>', 'wp-ticket-com') . "</div>";
	}
}
