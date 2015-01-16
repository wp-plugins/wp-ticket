
<div class="form-alerts">
<?php
echo (isset($zf_error) ? $zf_error : (isset($error) ? $error : ''));
?>
</div>
<fieldset>
<div class="search_tickets-btn-fields">
<!-- search_tickets Form Attributes -->
<div class="search_tickets_attributes">
<div id="row9" class="row">
<!-- text input-->
<div class="col-md-12 woptdiv">
<div class="form-group">
<label id="label_emd_ticket_id" class="control-label" for="emd_ticket_id">
<?php _e('Ticket ID', 'wp-ticket-com'); ?>
<span style="display: inline-flex;right: 0px; position: relative; top:-6px;">
<a href="#" data-toggle="tooltip" title="<?php _e('Unique identifier for a ticket', 'wp-ticket-com'); ?>" id="info_emd_ticket_id" class="helptip"><span class="field-icons icons-help"></span></a>
</span>
</label>
<?php echo $emd_ticket_id; ?>
</div>
</div>
</div>
<div id="row10" class="row">
<!-- text input-->
<div class="col-md-12 woptdiv">
<div class="form-group">
<label id="label_emd_ticket_email" class="control-label" for="emd_ticket_email">
<?php _e('Email', 'wp-ticket-com'); ?>
<span style="display: inline-flex;right: 0px; position: relative; top:-6px;">
<a href="#" data-toggle="tooltip" title="<?php _e('Our responses to your ticket will be sent to this email address.', 'wp-ticket-com'); ?>" id="info_emd_ticket_email" class="helptip"><span class="field-icons icons-help"></span></a>
</span>
</label>
<?php echo $emd_ticket_email; ?>
</div>
</div>
</div>
<div id="row11" class="row">
<!-- HR-->
<hr>
</div>
</div><!--form-attributes-->
<?php if ($show_captcha == 1) { ?>
<div class="row">
<div class="col-xs-12">
<div id="captcha-group" class="form-group">
<?php echo $captcha_image; ?>
<label style="padding:0px;" id="label_captcha_code" class="control-label" for="captcha_code">
<a id="info_captcha_code_help" class="helptip" data-toggle="tooltip" href="#" title="<?php _e('Please enter the characters with black color in the image above.', 'wp-ticket-com'); ?>">
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
<?php wp_nonce_field('search_tickets', 'search_tickets_nonce'); ?>
<input type="hidden" name="form_name" id="form_name" value="search_tickets">
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