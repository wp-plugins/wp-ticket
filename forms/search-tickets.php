
<div class="form-alerts">
<?php
echo (isset($zf_error) ? $zf_error : (isset($error) ? $error : ''));
$form_list = get_option('wp_ticket_com_glob_forms_list');
$form_list_init = get_option('wp_ticket_com_glob_forms_init_list');
if (!empty($form_list['search_tickets'])) {
	$form_variables = $form_list['search_tickets'];
}
$form_variables_init = $form_list_init['search_tickets'];
$max_row = count($form_variables_init);
foreach ($form_variables_init as $fkey => $fval) {
	if (empty($form_variables[$fkey])) {
		$form_variables[$fkey] = $form_variables_init[$fkey];
	}
}
$ext_inputs = Array();
$ext_inputs = apply_filters('emd_ext_form_inputs', $ext_inputs, 'wp_ticket_com', 'search_tickets');
$form_variables = apply_filters('emd_ext_form_var_init', $form_variables, 'wp_ticket_com', 'search_tickets');
$req_hide_vars = emd_get_form_req_hide_vars('wp_ticket_com', 'search_tickets');
$glob_list = get_option('wp_ticket_com_glob_list');
?>
</div>
<fieldset>
<?php wp_nonce_field('search_tickets', 'search_tickets_nonce'); ?>
<input type="hidden" name="form_name" id="form_name" value="search_tickets">
<div class="search_tickets-btn-fields container-fluid">
<!-- search_tickets Form Attributes -->
<div class="search_tickets_attributes">
<div id="row11" class="row ">
<!-- text input-->
<?php if ($form_variables['emd_ticket_id']['show'] == 1) { ?>
<div class="col-md-<?php echo $form_variables['emd_ticket_id']['size']; ?> woptdiv">
<div class="form-group">
<label id="label_emd_ticket_id" class="control-label" for="emd_ticket_id">
<?php _e('Ticket ID', 'wp-ticket-com'); ?>
<span style="display: inline-flex;right: 0px; position: relative; top:-6px;">
<a data-html="true" href="#" data-toggle="tooltip" title="<?php _e('Unique identifier for a ticket', 'wp-ticket-com'); ?>" id="info_emd_ticket_id" class="helptip"><span class="field-icons icons-help"></span></a>
<?php if (in_array('emd_ticket_id', $req_hide_vars['req'])) { ?>
<a href="#" data-html="true" data-toggle="tooltip" title="<?php _e('Ticket ID field is required', 'wp-ticket-com'); ?>" id="info_emd_ticket_id" class="helptip">
<span class="field-icons icons-required"></span>
</a>
<?php
	} ?>
</span>
</label>
<?php echo $emd_ticket_id; ?>
</div>
</div>
<?php
} ?>
</div>
<div id="row12" class="row ">
<!-- text input-->
<?php if ($form_variables['emd_ticket_email']['show'] == 1) { ?>
<div class="col-md-<?php echo $form_variables['emd_ticket_email']['size']; ?> woptdiv">
<div class="form-group">
<label id="label_emd_ticket_email" class="control-label" for="emd_ticket_email">
<?php _e('Email', 'wp-ticket-com'); ?>
<span style="display: inline-flex;right: 0px; position: relative; top:-6px;">
<a data-html="true" href="#" data-toggle="tooltip" title="<?php _e('Our responses to your ticket will be sent to this email address.', 'wp-ticket-com'); ?>" id="info_emd_ticket_email" class="helptip"><span class="field-icons icons-help"></span></a>
<?php if (in_array('emd_ticket_email', $req_hide_vars['req'])) { ?>
<a href="#" data-html="true" data-toggle="tooltip" title="<?php _e('Email field is required', 'wp-ticket-com'); ?>" id="info_emd_ticket_email" class="helptip">
<span class="field-icons icons-required"></span>
</a>
<?php
	} ?>
</span>
</label>
<?php echo $emd_ticket_email; ?>
</div>
</div>
<?php
} ?>
</div>
<div id="row21" class="row ext-row">
<!-- rel-ent input-->
<?php
if (!empty($ext_inputs['rel_emd_ticket_woo_order']) && $ext_inputs['rel_emd_ticket_woo_order']['type'] != 'hidden' && !empty($form_variables['rel_emd_ticket_woo_order']) && $form_variables['rel_emd_ticket_woo_order']['show'] == 1) { ?>
<div class="col-md-<?php echo $form_variables['rel_emd_ticket_woo_order']['size']; ?>">
<div class="form-group">
<label id="label_rel_emd_ticket_woo_order" class="control-label" for="rel_emd_ticket_woo_order">
<?php _e('Orders', 'wp-ticket-com'); ?>
<span style="display: inline-flex;right: 0px; position: relative; top:-6px;">
<?php if (in_array('rel_emd_ticket_woo_order', $req_hide_vars['req'])) { ?>
<a href="#" data-html="true" data-toggle="tooltip" title="<?php _e('Orders is required', 'wp-ticket-com'); ?>" id="info_rel_emd_ticket_woo_order" class="helptip">
<span class="field-icons icons-required"></span>
</a>
<?php
	} ?>
</span>
</label>
<?php echo $rel_emd_ticket_woo_order; ?>
</div>
</div>
<?php
} ?>
</div>
<div id="row20" class="row ext-row">
<!-- rel-ent input-->
<?php
if (!empty($ext_inputs['rel_emd_ticket_woo_product']) && $ext_inputs['rel_emd_ticket_woo_product']['type'] != 'hidden' && !empty($form_variables['rel_emd_ticket_woo_product']) && $form_variables['rel_emd_ticket_woo_product']['show'] == 1) { ?>
<div class="col-md-<?php echo $form_variables['rel_emd_ticket_woo_product']['size']; ?>">
<div class="form-group">
<label id="label_rel_emd_ticket_woo_product" class="control-label" for="rel_emd_ticket_woo_product">
<?php _e('Products', 'wp-ticket-com'); ?>
<span style="display: inline-flex;right: 0px; position: relative; top:-6px;">
<?php if (in_array('rel_emd_ticket_woo_product', $req_hide_vars['req'])) { ?>
<a href="#" data-html="true" data-toggle="tooltip" title="<?php _e('Products is required', 'wp-ticket-com'); ?>" id="info_rel_emd_ticket_woo_product" class="helptip">
<span class="field-icons icons-required"></span>
</a>
<?php
	} ?>
</span>
</label>
<?php echo $rel_emd_ticket_woo_product; ?>
</div>
</div>
<?php
} ?>
</div>
<div id="row23" class="row ext-row">
<!-- rel-ent input-->
<?php
if (!empty($ext_inputs['rel_emd_ticket_edd_order']) && $ext_inputs['rel_emd_ticket_edd_order']['type'] != 'hidden' && !empty($form_variables['rel_emd_ticket_edd_order']) && $form_variables['rel_emd_ticket_edd_order']['show'] == 1) { ?>
<div class="col-md-<?php echo $form_variables['rel_emd_ticket_edd_order']['size']; ?>">
<div class="form-group">
<label id="label_rel_emd_ticket_edd_order" class="control-label" for="rel_emd_ticket_edd_order">
<?php _e('Orders', 'wp-ticket-com'); ?>
<span style="display: inline-flex;right: 0px; position: relative; top:-6px;">
<?php if (in_array('rel_emd_ticket_edd_order', $req_hide_vars['req'])) { ?>
<a href="#" data-html="true" data-toggle="tooltip" title="<?php _e('Orders is required', 'wp-ticket-com'); ?>" id="info_rel_emd_ticket_edd_order" class="helptip">
<span class="field-icons icons-required"></span>
</a>
<?php
	} ?>
</span>
</label>
<?php echo $rel_emd_ticket_edd_order; ?>
</div>
</div>
<?php
} ?>
</div>
<div id="row22" class="row ext-row">
<!-- rel-ent input-->
<?php
if (!empty($ext_inputs['rel_emd_ticket_edd_product']) && $ext_inputs['rel_emd_ticket_edd_product']['type'] != 'hidden' && !empty($form_variables['rel_emd_ticket_edd_product']) && $form_variables['rel_emd_ticket_edd_product']['show'] == 1) { ?>
<div class="col-md-<?php echo $form_variables['rel_emd_ticket_edd_product']['size']; ?>">
<div class="form-group">
<label id="label_rel_emd_ticket_edd_product" class="control-label" for="rel_emd_ticket_edd_product">
<?php _e('Downloads', 'wp-ticket-com'); ?>
<span style="display: inline-flex;right: 0px; position: relative; top:-6px;">
<?php if (in_array('rel_emd_ticket_edd_product', $req_hide_vars['req'])) { ?>
<a href="#" data-html="true" data-toggle="tooltip" title="<?php _e('Downloads is required', 'wp-ticket-com'); ?>" id="info_rel_emd_ticket_edd_product" class="helptip">
<span class="field-icons icons-required"></span>
</a>
<?php
	} ?>
</span>
</label>
<?php echo $rel_emd_ticket_edd_product; ?>
</div>
</div>
<?php
} ?>
</div>
<div id="row13" class="row ">
<!-- Taxonomy input-->
<?php if ($form_variables['ticket_topic']['show'] == 1) { ?>
<div class="col-md-<?php echo $form_variables['ticket_topic']['size']; ?>">
<div class="form-group">
<label id="label_ticket_topic" class="control-label" for="ticket_topic">
<?php _e('Topic', 'wp-ticket-com'); ?>
<span style="display: inline-flex;right: 0px; position: relative; top:-6px;">
<a data-html="true" href="#" data-toggle="tooltip" title="<?php _e('Topics are the categories for tickets.', 'wp-ticket-com'); ?>" id="info_ticket_topic" class="helptip"><span class="field-icons icons-help"></span></a>
<?php if (in_array('ticket_topic', $req_hide_vars['req'])) { ?>
<a href="#" data-html="true" data-toggle="tooltip" title="<?php _e('Topic field is required', 'wp-ticket-com'); ?>" id="info_ticket_topic" class="helptip">
<span class="field-icons icons-required"></span>
</a>
<?php
	} ?>
</span>
</label>
<?php echo $ticket_topic; ?>
</div>
</div>
<?php
} ?>
</div>
<div id="row14" class="row ">
<!-- Taxonomy input-->
<?php if ($form_variables['ticket_priority']['show'] == 1) { ?>
<div class="col-md-<?php echo $form_variables['ticket_priority']['size']; ?>">
<div class="form-group">
<label id="label_ticket_priority" class="control-label" for="ticket_priority">
<?php _e('Priority', 'wp-ticket-com'); ?>
<span style="display: inline-flex;right: 0px; position: relative; top:-6px;">
<a data-html="true" href="#" data-toggle="tooltip" title="<?php _e('When you create a ticket, you should prioritize the ticket based on the scope and impact of the request.', 'wp-ticket-com'); ?>" id="info_ticket_priority" class="helptip"><span class="field-icons icons-help"></span></a>
<?php if (in_array('ticket_priority', $req_hide_vars['req'])) { ?>
<a href="#" data-html="true" data-toggle="tooltip" title="<?php _e('Priority field is required', 'wp-ticket-com'); ?>" id="info_ticket_priority" class="helptip">
<span class="field-icons icons-required"></span>
</a>
<?php
	} ?>
</span>
</label>
<?php echo $ticket_priority; ?>
</div>
</div>
<?php
} ?>
</div>
<div id="row15" class="row ">
<!-- datetime-->
<?php if ($form_variables['emd_ticket_duedate']['show'] == 1) { ?>
<div class="col-md-<?php echo $form_variables['emd_ticket_duedate']['size']; ?> woptdiv">
<div class="form-group">
<label id="label_emd_ticket_duedate" class="control-label" for="emd_ticket_duedate">
<?php _e('Due', 'wp-ticket-com'); ?>
<span style="display: inline-flex;right: 0px; position: relative; top:-6px;"> <a data-html="true" href="#" data-toggle="tooltip" title="<?php _e('The due date of the ticket', 'wp-ticket-com'); ?>" id="info_emd_ticket_duedate" class="helptip"><span class="field-icons icons-help"></span></a>
<?php if (in_array('emd_ticket_duedate', $req_hide_vars['req'])) { ?>
<a href="#" data-html="true" data-toggle="tooltip" title="<?php _e('Due field is required', 'wp-ticket-com'); ?>" id="info_emd_ticket_duedate" class="helptip">
<span class="field-icons icons-required"></span>
</a>
<?php
	} ?> </span>
</label>
<?php echo $emd_ticket_duedate; ?>
</div>
</div>
<?php
} ?>
</div>
<?php
$cust_fields = Array();
$cust_fields = apply_filters('emd_get_cust_fields', $cust_fields, 'emd_ticket');
if (!empty($cust_fields)) {
	foreach ($cust_fields as $cfield => $clabel) {
		$max_row++;
		if ($form_variables[$cfield]['show'] == 1) { ?>
             <div id="row<?php echo $max_row; ?>" class="row">
             <!-- custom field text input-->
             <div class="col-md-<?php echo $form_variables[$cfield]['size']; ?> woptdiv">
             <div class="form-group">
             
             <label id="label_<?php echo $cfield; ?>" class="control-label" for="<?php echo $cfield; ?>" >
             <?php echo $clabel; ?>
             <span style="display: inline-flex;right: 0px; position: relative; top:-6px;">
             <?php if (in_array($cfield, $req_hide_vars['req'])) { ?>
                 <a href="#" data-html="true" data-toggle="tooltip" title="<?php printf(__('%s field is required.', 'wp-ticket-com') , $cfield); ?>" id="info_<?php echo $cfield; ?>" class="helptip">
                 <span class="field-icons icons-required"></span>
                 </a>
             <?php
			} ?>
             </span>
             </label>
             
             
             <?php echo $$cfield; ?>
             
             
             </div>
             </div>
             </div>
             <?php
		}
	}
}
?>
</div><!--form-attributes-->
<?php if ($show_captcha == 1) { ?>
<div class="row">
<div class="col-xs-12">
<div id="captcha-group" class="form-group">
<?php echo $captcha_image; ?>
<label style="padding:0px;" id="label_captcha_code" class="control-label" for="captcha_code">
<a id="info_captcha_code_help" class="helptip" data-html="true" data-toggle="tooltip" href="#" title="<?php _e('Please enter the characters with black color in the image above.', 'wp-ticket-com'); ?>">
<span class="field-icons icons-help"></span>
</a>
<a id="info_captcha_code_req" class="helptip" title="<?php _e('Security Code field is required', 'wp-ticket-com'); ?>" data-toggle="tooltip" href="#">
<span class="field-icons icons-required"></span>
</a>
</label>
<?php echo $captcha_code; ?>
</div>
</div>
</div>
<?php
} ?>
<!-- Button -->
<div class="row">
<div class="col-md-12">
<div class="wpas-form-actions">
<?php echo $singlebutton_search_tickets; ?>
</div>
</div>
</div>
</div><!--form-btn-fields-->
</fieldset>