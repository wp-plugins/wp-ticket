<?php
/**
 * Setup and Process submit and search forms
 * @package WP_TICKET_COM
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
if (is_admin()) {
	add_action('wp_ajax_nopriv_emd_check_unique', 'emd_check_unique');
}
add_action('init', 'wp_ticket_com_form_shortcodes', -2);
/**
 * Start session and setup upload idr and current user id
 * @since WPAS 4.0
 *
 */
function wp_ticket_com_form_shortcodes() {
	global $file_upload_dir;
	$upload_dir = wp_upload_dir();
	$file_upload_dir = $upload_dir['basedir'];
	if (!empty($_POST['emd_action'])) {
		if ($_POST['emd_action'] == 'wp_ticket_com_user_login' && wp_verify_nonce($_POST['emd_login_nonce'], 'emd-login-nonce')) {
			emd_process_login($_POST, 'wp_ticket_com');
		} elseif ($_POST['emd_action'] == 'wp_ticket_com_user_register' && wp_verify_nonce($_POST['emd_register_nonce'], 'emd-register-nonce')) {
			emd_process_register($_POST, 'wp_ticket_com');
		}
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
function wp_ticket_com_set_search_tickets($atts) {
	global $file_upload_dir;
	$show_captcha = 0;
	$form_variables = get_option('wp_ticket_com_glob_forms_list');
	$form_init_variables = get_option('wp_ticket_com_glob_forms_init_list');
	$attr_list = get_option('wp_ticket_com_attr_list');
	if (!empty($atts['set'])) {
		$set_arrs = emd_parse_set_filter($atts['set']);
	}
	if (!empty($form_variables['search_tickets']['captcha'])) {
		switch ($form_variables['search_tickets']['captcha']) {
			case 'never-show':
				$show_captcha = 0;
			break;
			case 'show-always':
				$show_captcha = 1;
			break;
			case 'show-to-visitors':
				if (is_user_logged_in()) {
					$show_captcha = 0;
				} else {
					$show_captcha = 1;
				}
			break;
		}
	}
	$req_hide_vars = emd_get_form_req_hide_vars('wp_ticket_com', 'search_tickets');
	$form = new Zebra_Form('search_tickets', 0, 'POST', '', array(
		'class' => 'form-container wpas-form wpas-form-stacked',
		'session_obj' => WP_TICKET_COM()->session
	));
	$csrf_storage_method = (isset($form_variables['search_tickets']['csrf']) ? $form_variables['search_tickets']['csrf'] : $form_init_variables['search_tickets']['csrf']);
	if ($csrf_storage_method == 0) {
		$form->form_properties['csrf_storage_method'] = false;
	}
	if (!in_array('emd_ticket_id', $req_hide_vars['hide'])) {
		//text
		$form->add('label', 'label_emd_ticket_id', 'emd_ticket_id', __('Ticket ID', 'wp-ticket-com') , array(
			'class' => 'control-label'
		));
		$attrs = array(
			'class' => 'input-md form-control',
			'placeholder' => __('Ticket ID', 'wp-ticket-com')
		);
		if (!empty($_GET['emd_ticket_id'])) {
			$attrs['value'] = sanitize_text_field($_GET['emd_ticket_id']);
		} elseif (!empty($set_arrs['attr']['emd_ticket_id'])) {
			$attrs['value'] = $set_arrs['attr']['emd_ticket_id'];
		}
		$obj = $form->add('text', 'emd_ticket_id', '', $attrs);
		$zrule = Array(
			'dependencies' => array() ,
		);
		if (in_array('emd_ticket_id', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('Ticket ID is required', 'wp-ticket-com')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	if (!in_array('emd_ticket_email', $req_hide_vars['hide'])) {
		//text
		$form->add('label', 'label_emd_ticket_email', 'emd_ticket_email', __('Email', 'wp-ticket-com') , array(
			'class' => 'control-label'
		));
		$attrs = array(
			'class' => 'input-md form-control',
			'placeholder' => __('Email', 'wp-ticket-com')
		);
		if (!empty($_GET['emd_ticket_email'])) {
			$attrs['value'] = sanitize_email($_GET['emd_ticket_email']);
		} elseif (!empty($set_arrs['attr']['emd_ticket_email'])) {
			$attrs['value'] = $set_arrs['attr']['emd_ticket_email'];
		}
		$obj = $form->add('text', 'emd_ticket_email', '', $attrs);
		$zrule = Array(
			'dependencies' => array() ,
			'email' => array(
				'error',
				__('Email: Please enter a valid email address', 'wp-ticket-com')
			) ,
		);
		if (in_array('emd_ticket_email', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('Email is required', 'wp-ticket-com')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	if (!in_array('ticket_topic', $req_hide_vars['hide'])) {
		$form->add('label', 'label_ticket_topic', 'ticket_topic', __('Topic', 'wp-ticket-com') , array(
			'class' => 'control-label'
		));
		$attrs = array(
			'multiple' => 'multiple',
			'class' => 'input-md'
		);
		if (!empty($_GET['ticket_topic'])) {
			$attrs['value'] = sanitize_text_field($_GET['ticket_topic']);
		} elseif (!empty($set_arrs['tax']['ticket_topic'])) {
			$attrs['value'] = $set_arrs['tax']['ticket_topic'];
		}
		$obj = $form->add('selectadv', 'ticket_topic[]', '', $attrs, '', '{"allowClear":true,"placeholder":"' . __("Please Select", "wp-ticket-com") . '","placeholderOption":"first"}');
		//get taxonomy values
		$txn_arr = Array();
		$txn_obj = get_terms('ticket_topic', array(
			'hide_empty' => 0
		));
		foreach ($txn_obj as $txn) {
			$txn_arr[$txn->slug] = $txn->name;
		}
		$obj->add_options($txn_arr);
		$zrule = Array(
			'dependencies' => array() ,
		);
		if (in_array('ticket_topic', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('Topic is required!', 'wp-ticket-com')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	if (!in_array('ticket_priority', $req_hide_vars['hide'])) {
		$form->add('label', 'label_ticket_priority', 'ticket_priority', __('Priority', 'wp-ticket-com') , array(
			'class' => 'control-label'
		));
		$attrs = array(
			'multiple' => 'multiple',
			'class' => 'input-md'
		);
		if (!empty($_GET['ticket_priority'])) {
			$attrs['value'] = sanitize_text_field($_GET['ticket_priority']);
		} elseif (!empty($set_arrs['tax']['ticket_priority'])) {
			$attrs['value'] = $set_arrs['tax']['ticket_priority'];
		}
		$obj = $form->add('selectadv', 'ticket_priority[]', '', $attrs, '', '{"allowClear":true,"placeholder":"' . __("Please Select", "wp-ticket-com") . '","placeholderOption":"first"}');
		//get taxonomy values
		$txn_arr = Array();
		$txn_obj = get_terms('ticket_priority', array(
			'hide_empty' => 0
		));
		foreach ($txn_obj as $txn) {
			$txn_arr[$txn->slug] = $txn->name;
		}
		$obj->add_options($txn_arr);
		$zrule = Array(
			'dependencies' => array() ,
		);
		if (in_array('ticket_priority', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('Priority is required!', 'wp-ticket-com')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	if (!in_array('emd_ticket_duedate', $req_hide_vars['hide'])) {
		//datetime
		$form->add('label', 'label_emd_ticket_duedate', 'emd_ticket_duedate', __('Due', 'wp-ticket-com') , array(
			'class' => 'control-label'
		));
		$obj = $form->add('datetime', 'emd_ticket_duedate', '', array(
			'class' => 'input-md form-control',
			'placeholder' => __('Due', 'wp-ticket-com')
		));
		$obj->format('m-d-Y H:i');
		$zrule = Array(
			'dependencies' => array() ,
			'date' => array(
				'error',
				__('Due: Please enter a valid date format', 'wp-ticket-com')
			) ,
		);
		if (in_array('emd_ticket_duedate', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('Due is required', 'wp-ticket-com')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	$ext_inputs = Array();
	$ext_inputs = apply_filters('emd_ext_form_inputs', $ext_inputs, 'wp_ticket_com', 'search_tickets');
	foreach ($ext_inputs as $input_param) {
		$inp_name = $input_param['name'];
		if (!in_array($input_param['name'], $req_hide_vars['hide'])) {
			if ($input_param['type'] == 'hidden') {
				$obj = $form->add('hidden', $input_param['name'], $input_param['vals']);
			} elseif ($input_param['type'] == 'select') {
				$form->add('label', 'label_' . $input_param['name'], $input_param['name'], $input_param['label'], array(
					'class' => 'control-label'
				));
				$ext_class['class'] = 'input-md';
				if (!empty($input_param['multiple'])) {
					$ext_class['multiple'] = 'multiple';
					$input_param['name'] = $input_param['name'] . '[]';
				}
				$obj = $form->add('select', $input_param['name'], '', $ext_class, '', '{"allowClear":true,"placeholder":"' . __("Please Select", "wp-ticket-com") . '","placeholderOption":"first"}');
				$obj->add_options($input_param['vals']);
				$obj->disable_spam_filter();
			} elseif ($input_param['type'] == 'text') {
				$form->add('label', 'label_' . $input_param['name'], $input_param['name'], $input_param['label'], array(
					'class' => 'control-label'
				));
				$obj = $form->add('text', $input_param['name'], '', array(
					'class' => 'input-md form-control',
					'placeholder' => $input_param['label']
				));
			}
			if ($input_param['type'] != 'hidden' && in_array($inp_name, $req_hide_vars['req'])) {
				$zrule = Array(
					'dependencies' => $input_param['dependencies'],
					'required' => array(
						'error',
						$input_param['label'] . __(' is required', 'wp-ticket-com')
					)
				);
				$obj->set_rule($zrule);
			}
		}
	}
	$cust_fields = Array();
	$cust_fields = apply_filters('emd_get_cust_fields', $cust_fields, 'emd_ticket');
	foreach ($cust_fields as $ckey => $clabel) {
		if (!in_array($ckey, $req_hide_vars['hide'])) {
			$form->add('label', 'label_' . $ckey, $ckey, $clabel, array(
				'class' => 'control-label'
			));
			$obj = $form->add('text', $ckey, '', array(
				'class' => 'input-md form-control',
				'placeholder' => $clabel
			));
			if (in_array($ckey, $req_hide_vars['req'])) {
				$zrule = Array(
					'required' => array(
						'error',
						$clabel . __(' is required', 'wp-ticket-com')
					)
				);
				$obj->set_rule($zrule);
			}
		}
	}
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
		'class' => 'wpas-button wpas-juibutton-primary wpas-button-large btn-block col-md-12 col-lg-12 col-xs-12 col-sm-12'
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
function wp_ticket_com_process_search_tickets($atts) {
	$show_form = 1;
	$access_views = get_option('wp_ticket_com_access_views', Array());
	if (!current_user_can('view_search_tickets') && !empty($access_views['forms']) && in_array('search_tickets', $access_views['forms'])) {
		$show_form = 0;
	}
	$form_init_variables = get_option('wp_ticket_com_glob_forms_init_list');
	$form_variables = get_option('wp_ticket_com_glob_forms_list');
	if ($show_form == 1) {
		if (!empty($form_init_variables['search_tickets']['login_reg'])) {
			$show_login_register = (isset($form_variables['search_tickets']['login_reg']) ? $form_variables['search_tickets']['login_reg'] : $form_init_variables['search_tickets']['login_reg']);
			if (!is_user_logged_in() && $show_login_register != 'none') {
				do_action('emd_show_login_register_forms', 'wp_ticket_com', 'search_tickets', 'none');
				return;
			}
		}
		wp_enqueue_script('wpas-jvalidate-js');
		wp_enqueue_style('wpasui');
		wp_enqueue_style('jq-css');
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('jquery-ui-timepicker');
		wp_enqueue_style('jquery-ui-timepicker');
		wp_enqueue_style('search-tickets-forms');
		wp_enqueue_script('search-tickets-forms-js');
		wp_enqueue_style('pagination-cdn');
		wp_enqueue_style('wp-ticket-com-allview-css');
		wp_ticket_com_enq_custom_css();
		do_action('emd_ext_form_enq', 'wp_ticket_com', 'search_tickets');
		$noresult_msg = (isset($form_variables['search_tickets']['noresult_msg']) ? $form_variables['search_tickets']['noresult_msg'] : $form_init_variables['search_tickets']['noresult_msg']);
		return emd_search_php_form('search_tickets', 'wp_ticket_com', 'emd_ticket', $noresult_msg, 'search_tickets', $atts);
	} else {
		$noaccess_msg = (isset($form_variables['search_tickets']['noaccess_msg']) ? $form_variables['search_tickets']['noaccess_msg'] : $form_init_variables['search_tickets']['noaccess_msg']);
		return "<div class='alert alert-info not-authorized'>" . $noaccess_msg . "</div>";
	}
}
/**
 * Set each form field(attr,tax and rels) and render form
 *
 * @since WPAS 4.0
 *
 * @return object $form
 */
function wp_ticket_com_set_submit_tickets($atts) {
	global $file_upload_dir;
	$show_captcha = 0;
	$form_variables = get_option('wp_ticket_com_glob_forms_list');
	$form_init_variables = get_option('wp_ticket_com_glob_forms_init_list');
	$current_user = wp_get_current_user();
	$attr_list = get_option('wp_ticket_com_attr_list');
	if (!empty($atts['set'])) {
		$set_arrs = emd_parse_set_filter($atts['set']);
	}
	if (!empty($form_variables['submit_tickets']['captcha'])) {
		switch ($form_variables['submit_tickets']['captcha']) {
			case 'never-show':
				$show_captcha = 0;
			break;
			case 'show-always':
				$show_captcha = 1;
			break;
			case 'show-to-visitors':
				if (is_user_logged_in()) {
					$show_captcha = 0;
				} else {
					$show_captcha = 1;
				}
			break;
		}
	}
	$req_hide_vars = emd_get_form_req_hide_vars('wp_ticket_com', 'submit_tickets');
	$form = new Zebra_Form('submit_tickets', 0, 'POST', '', array(
		'class' => 'form-container wpas-form wpas-form-stacked',
		'session_obj' => WP_TICKET_COM()->session
	));
	$csrf_storage_method = (isset($form_variables['submit_tickets']['csrf']) ? $form_variables['submit_tickets']['csrf'] : $form_init_variables['submit_tickets']['csrf']);
	if ($csrf_storage_method == 0) {
		$form->form_properties['csrf_storage_method'] = false;
	}
	//hidden_func
	$emd_ticket_id = emd_get_hidden_func('unique_id');
	$form->add('hidden', 'emd_ticket_id', $emd_ticket_id);
	if (!in_array('ticket_topic', $req_hide_vars['hide'])) {
		$form->add('label', 'label_ticket_topic', 'ticket_topic', __('Topic', 'wp-ticket-com') , array(
			'class' => 'control-label'
		));
		$attrs = array(
			'class' => 'input-md'
		);
		if (!empty($_GET['ticket_topic'])) {
			$attrs['value'] = sanitize_text_field($_GET['ticket_topic']);
		} elseif (!empty($set_arrs['tax']['ticket_topic'])) {
			$attrs['value'] = $set_arrs['tax']['ticket_topic'];
		}
		$obj = $form->add('selectadv', 'ticket_topic', 'uncategorized', $attrs, '', '{"allowClear":true,"placeholder":"' . __("Please Select", "wp-ticket-com") . '","placeholderOption":"first"}');
		//get taxonomy values
		$txn_arr = Array();
		$txn_arr[''] = __('Please Select', 'wp-ticket-com');
		$txn_obj = get_terms('ticket_topic', array(
			'hide_empty' => 0
		));
		foreach ($txn_obj as $txn) {
			$txn_arr[$txn->slug] = $txn->name;
		}
		$obj->add_options($txn_arr);
		$zrule = Array(
			'dependencies' => array() ,
		);
		if (in_array('ticket_topic', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('Topic is required!', 'wp-ticket-com')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	if (!in_array('emd_ticket_first_name', $req_hide_vars['hide'])) {
		//text
		$form->add('label', 'label_emd_ticket_first_name', 'emd_ticket_first_name', __('First Name', 'wp-ticket-com') , array(
			'class' => 'control-label'
		));
		$attrs = array(
			'class' => 'input-md form-control',
			'placeholder' => __('First Name', 'wp-ticket-com')
		);
		if (!empty($_GET['emd_ticket_first_name'])) {
			$attrs['value'] = sanitize_text_field($_GET['emd_ticket_first_name']);
		} elseif (!empty($set_arrs['attr']['emd_ticket_first_name'])) {
			$attrs['value'] = $set_arrs['attr']['emd_ticket_first_name'];
		} elseif (!empty($current_user) && !empty($attr_list['emd_ticket']['emd_ticket_first_name']['user_map'])) {
			$attrs['value'] = (string)$current_user->$attr_list['emd_ticket']['emd_ticket_first_name']['user_map'];
		}
		$obj = $form->add('text', 'emd_ticket_first_name', '', $attrs);
		$zrule = Array(
			'dependencies' => array() ,
		);
		if (in_array('emd_ticket_first_name', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('First Name is required', 'wp-ticket-com')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	if (!in_array('emd_ticket_last_name', $req_hide_vars['hide'])) {
		//text
		$form->add('label', 'label_emd_ticket_last_name', 'emd_ticket_last_name', __('Last Name', 'wp-ticket-com') , array(
			'class' => 'control-label'
		));
		$attrs = array(
			'class' => 'input-md form-control',
			'placeholder' => __('Last Name', 'wp-ticket-com')
		);
		if (!empty($_GET['emd_ticket_last_name'])) {
			$attrs['value'] = sanitize_text_field($_GET['emd_ticket_last_name']);
		} elseif (!empty($set_arrs['attr']['emd_ticket_last_name'])) {
			$attrs['value'] = $set_arrs['attr']['emd_ticket_last_name'];
		} elseif (!empty($current_user) && !empty($attr_list['emd_ticket']['emd_ticket_last_name']['user_map'])) {
			$attrs['value'] = (string)$current_user->$attr_list['emd_ticket']['emd_ticket_last_name']['user_map'];
		}
		$obj = $form->add('text', 'emd_ticket_last_name', '', $attrs);
		$zrule = Array(
			'dependencies' => array() ,
		);
		if (in_array('emd_ticket_last_name', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('Last Name is required', 'wp-ticket-com')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	if (!in_array('emd_ticket_email', $req_hide_vars['hide'])) {
		//text
		$form->add('label', 'label_emd_ticket_email', 'emd_ticket_email', __('Email', 'wp-ticket-com') , array(
			'class' => 'control-label'
		));
		$attrs = array(
			'class' => 'input-md form-control',
			'placeholder' => __('Email', 'wp-ticket-com')
		);
		if (!empty($current_user) && !empty($current_user->user_email)) {
			$attrs['value'] = (string)$current_user->user_email;
		}
		if (!empty($_GET['emd_ticket_email'])) {
			$attrs['value'] = sanitize_email($_GET['emd_ticket_email']);
		} elseif (!empty($set_arrs['attr']['emd_ticket_email'])) {
			$attrs['value'] = $set_arrs['attr']['emd_ticket_email'];
		}
		$obj = $form->add('text', 'emd_ticket_email', '', $attrs);
		$zrule = Array(
			'dependencies' => array() ,
			'email' => array(
				'error',
				__('Email: Please enter a valid email address', 'wp-ticket-com')
			) ,
		);
		if (in_array('emd_ticket_email', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('Email is required', 'wp-ticket-com')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	if (!in_array('blt_title', $req_hide_vars['hide'])) {
		//text
		$form->add('label', 'label_blt_title', 'blt_title', __('Subject', 'wp-ticket-com') , array(
			'class' => 'control-label'
		));
		$attrs = array(
			'class' => 'input-md form-control',
			'placeholder' => __('Subject', 'wp-ticket-com')
		);
		if (!empty($_GET['blt_title'])) {
			$attrs['value'] = sanitize_text_field($_GET['blt_title']);
		} elseif (!empty($set_arrs['attr']['blt_title'])) {
			$attrs['value'] = $set_arrs['attr']['blt_title'];
		}
		$obj = $form->add('text', 'blt_title', '', $attrs);
		$zrule = Array();
		if (in_array('blt_title', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('Subject is required', 'wp-ticket-com')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	if (!in_array('blt_content', $req_hide_vars['hide'])) {
		//wysiwyg
		$form->add('label', 'label_blt_content', 'blt_content', __('Message', 'wp-ticket-com') , array(
			'class' => 'control-label'
		));
		$obj = $form->add('wysiwyg', 'blt_content', '', array(
			'placeholder' => __('Enter text ...', 'wp-ticket-com') ,
			'style' => 'width: 100%; height: 200px',
			'class' => 'wyrj'
		));
		$zrule = Array();
		if (in_array('blt_content', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('Message is required', 'wp-ticket-com')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	if (!in_array('emd_ticket_phone', $req_hide_vars['hide'])) {
		//text
		$form->add('label', 'label_emd_ticket_phone', 'emd_ticket_phone', __('Phone', 'wp-ticket-com') , array(
			'class' => 'control-label'
		));
		$attrs = array(
			'class' => 'input-md form-control',
			'placeholder' => __('Phone', 'wp-ticket-com')
		);
		if (!empty($_GET['emd_ticket_phone'])) {
			$attrs['value'] = sanitize_text_field($_GET['emd_ticket_phone']);
		} elseif (!empty($set_arrs['attr']['emd_ticket_phone'])) {
			$attrs['value'] = $set_arrs['attr']['emd_ticket_phone'];
		}
		$obj = $form->add('text', 'emd_ticket_phone', '', $attrs);
		$zrule = Array(
			'dependencies' => array() ,
		);
		if (in_array('emd_ticket_phone', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('Phone is required', 'wp-ticket-com')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	if (!in_array('emd_ticket_attachment', $req_hide_vars['hide'])) {
		//file
		$obj = $form->add('file', 'emd_ticket_attachment', '');
		$zrule = Array(
			'dependencies' => array() ,
			'upload' => array(
				$file_upload_dir,
				true,
				'error',
				'File could not be uploaded.'
			) ,
		);
		$obj->set_rule($zrule);
	}
	if (!in_array('ticket_priority', $req_hide_vars['hide'])) {
		$form->add('label', 'label_ticket_priority', 'ticket_priority', __('Priority', 'wp-ticket-com') , array(
			'class' => 'control-label'
		));
		$attrs = array(
			'class' => 'input-md'
		);
		if (!empty($_GET['ticket_priority'])) {
			$attrs['value'] = sanitize_text_field($_GET['ticket_priority']);
		} elseif (!empty($set_arrs['tax']['ticket_priority'])) {
			$attrs['value'] = $set_arrs['tax']['ticket_priority'];
		}
		$obj = $form->add('selectadv', 'ticket_priority', 'uncategorized', $attrs, '', '{"allowClear":true,"placeholder":"' . __("Please Select", "wp-ticket-com") . '","placeholderOption":"first"}');
		//get taxonomy values
		$txn_arr = Array();
		$txn_arr[''] = __('Please Select', 'wp-ticket-com');
		$txn_obj = get_terms('ticket_priority', array(
			'hide_empty' => 0
		));
		foreach ($txn_obj as $txn) {
			$txn_arr[$txn->slug] = $txn->name;
		}
		$obj->add_options($txn_arr);
		$zrule = Array(
			'dependencies' => array() ,
		);
		if (in_array('ticket_priority', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('Priority is required!', 'wp-ticket-com')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	if (!in_array('emd_ticket_duedate', $req_hide_vars['hide'])) {
		//datetime
		$form->add('label', 'label_emd_ticket_duedate', 'emd_ticket_duedate', __('Due', 'wp-ticket-com') , array(
			'class' => 'control-label'
		));
		$obj = $form->add('datetime', 'emd_ticket_duedate', '', array(
			'class' => 'input-md form-control',
			'placeholder' => __('Due', 'wp-ticket-com')
		));
		$obj->format('m-d-Y H:i');
		$zrule = Array(
			'dependencies' => array() ,
			'date' => array(
				'error',
				__('Due: Please enter a valid date format', 'wp-ticket-com')
			) ,
		);
		if (in_array('emd_ticket_duedate', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('Due is required', 'wp-ticket-com')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	//hidden
	$obj = $form->add('hidden', 'wpas_form_name', 'submit_tickets');
	//hidden_func
	$wpas_form_submitted_by = emd_get_hidden_func('user_login');
	$form->add('hidden', 'wpas_form_submitted_by', $wpas_form_submitted_by);
	//hidden_func
	$wpas_form_submitted_ip = emd_get_hidden_func('user_ip');
	$form->add('hidden', 'wpas_form_submitted_ip', $wpas_form_submitted_ip);
	$ext_inputs = Array();
	$ext_inputs = apply_filters('emd_ext_form_inputs', $ext_inputs, 'wp_ticket_com', 'submit_tickets');
	foreach ($ext_inputs as $input_param) {
		$inp_name = $input_param['name'];
		if (!in_array($input_param['name'], $req_hide_vars['hide'])) {
			if ($input_param['type'] == 'hidden') {
				$obj = $form->add('hidden', $input_param['name'], $input_param['vals']);
			} elseif ($input_param['type'] == 'select') {
				$form->add('label', 'label_' . $input_param['name'], $input_param['name'], $input_param['label'], array(
					'class' => 'control-label'
				));
				$ext_class['class'] = 'input-md';
				if (!empty($input_param['multiple'])) {
					$ext_class['multiple'] = 'multiple';
					$input_param['name'] = $input_param['name'] . '[]';
				}
				$obj = $form->add('select', $input_param['name'], '', $ext_class, '', '{"allowClear":true,"placeholder":"' . __("Please Select", "wp-ticket-com") . '","placeholderOption":"first"}');
				$obj->add_options($input_param['vals']);
				$obj->disable_spam_filter();
			} elseif ($input_param['type'] == 'text') {
				$form->add('label', 'label_' . $input_param['name'], $input_param['name'], $input_param['label'], array(
					'class' => 'control-label'
				));
				$obj = $form->add('text', $input_param['name'], '', array(
					'class' => 'input-md form-control',
					'placeholder' => $input_param['label']
				));
			}
			if ($input_param['type'] != 'hidden' && in_array($inp_name, $req_hide_vars['req'])) {
				$zrule = Array(
					'dependencies' => $input_param['dependencies'],
					'required' => array(
						'error',
						$input_param['label'] . __(' is required', 'wp-ticket-com')
					)
				);
				$obj->set_rule($zrule);
			}
		}
	}
	$cust_fields = Array();
	$cust_fields = apply_filters('emd_get_cust_fields', $cust_fields, 'emd_ticket');
	foreach ($cust_fields as $ckey => $clabel) {
		if (!in_array($ckey, $req_hide_vars['hide'])) {
			$form->add('label', 'label_' . $ckey, $ckey, $clabel, array(
				'class' => 'control-label'
			));
			$obj = $form->add('text', $ckey, '', array(
				'class' => 'input-md form-control',
				'placeholder' => $clabel
			));
			if (in_array($ckey, $req_hide_vars['req'])) {
				$zrule = Array(
					'required' => array(
						'error',
						$clabel . __(' is required', 'wp-ticket-com')
					)
				);
				$obj->set_rule($zrule);
			}
		}
	}
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
		'class' => 'wpas-button wpas-juibutton-success wpas-button-large btn-block col-md-12 col-lg-12 col-xs-12 col-sm-12'
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
function wp_ticket_com_process_submit_tickets($atts) {
	$show_form = 1;
	$access_views = get_option('wp_ticket_com_access_views', Array());
	if (!current_user_can('view_submit_tickets') && !empty($access_views['forms']) && in_array('submit_tickets', $access_views['forms'])) {
		$show_form = 0;
	}
	$form_init_variables = get_option('wp_ticket_com_glob_forms_init_list');
	$form_variables = get_option('wp_ticket_com_glob_forms_list');
	if ($show_form == 1) {
		if (!empty($form_init_variables['submit_tickets']['login_reg'])) {
			$show_login_register = (isset($form_variables['submit_tickets']['login_reg']) ? $form_variables['submit_tickets']['login_reg'] : $form_init_variables['submit_tickets']['login_reg']);
			if (!is_user_logged_in() && $show_login_register != 'none') {
				do_action('emd_show_login_register_forms', 'wp_ticket_com', 'submit_tickets', $show_login_register);
				return;
			}
		}
		wp_enqueue_script('wpas-jvalidate-js');
		wp_enqueue_style('wpasui');
		wp_enqueue_style('jq-css');
		wp_enqueue_script('wpas-filepicker-js');
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('jquery-ui-timepicker');
		wp_enqueue_style('jquery-ui-timepicker');
		wp_enqueue_style('submit-tickets-forms');
		wp_enqueue_script('submit-tickets-forms-js');
		wp_ticket_com_enq_custom_css();
		do_action('emd_ext_form_enq', 'wp_ticket_com', 'submit_tickets');
		$success_msg = (isset($form_variables['submit_tickets']['success_msg']) ? $form_variables['submit_tickets']['success_msg'] : $form_init_variables['submit_tickets']['success_msg']);
		$error_msg = (isset($form_variables['submit_tickets']['error_msg']) ? $form_variables['submit_tickets']['error_msg'] : $form_init_variables['submit_tickets']['error_msg']);
		return emd_submit_php_form('submit_tickets', 'wp_ticket_com', 'emd_ticket', 'publish', 'publish', $success_msg, $error_msg, 1, 1, $atts);
	} else {
		$noaccess_msg = (isset($form_variables['submit_tickets']['noaccess_msg']) ? $form_variables['submit_tickets']['noaccess_msg'] : $form_init_variables['submit_tickets']['noaccess_msg']);
		return "<div class='alert alert-info not-authorized'>" . $noaccess_msg . "</div>";
	}
}