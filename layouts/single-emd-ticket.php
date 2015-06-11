<?php $ent_attrs = get_option('wp_ticket_com_attr_list'); ?>
<div class="emd-container">
<?php $blt_content = $post->post_content;
if (!empty($blt_content)) { ?>
   <div id="emd-ticket-blt-content-div" class="emd-single-div">
   <div id="emd-ticket-blt-content-key" class="emd-single-title">
   <?php _e('Message', 'wp-ticket-com'); ?>
   </div>
   <div id="emd-ticket-blt-content-val" class="emd-single-val">
   <?php echo $blt_content; ?>
   </div>
   </div>
<?php
} ?>
<?php $emd_ticket_duedate = emd_mb_meta('emd_ticket_duedate');
if (!empty($emd_ticket_duedate)) {
	$emd_ticket_duedate = emd_translate_date_format($ent_attrs['emd_ticket']['emd_ticket_duedate'], $emd_ticket_duedate, 1);
?>
   <div id="emd-ticket-emd-ticket-duedate-div" class="emd-single-div">
   <div id="emd-ticket-emd-ticket-duedate-key" class="emd-single-title">
   <?php _e('Due', 'wp-ticket-com'); ?>
   </div>
   <div id="emd-ticket-emd-ticket-duedate-val" class="emd-single-val">
   <?php echo esc_html($emd_ticket_duedate); ?>
   </div></div>
<?php
} ?>
<?php
$emd_ticket_id = emd_mb_meta('emd_ticket_id');
if (!empty($emd_ticket_id)) { ?>
   <div id="emd-ticket-emd-ticket-id-div" class="emd-single-div">
   <div id="emd-ticket-emd-ticket-id-key" class="emd-single-title">
<?php _e('Ticket ID', 'wp-ticket-com'); ?>
   </div>
   <div id="emd-ticket-emd-ticket-id-val" class="emd-single-val">
<?php echo $emd_ticket_id; ?>
   </div>
   </div>
<?php
} ?>
<?php
$emd_ticket_first_name = emd_mb_meta('emd_ticket_first_name');
if (!empty($emd_ticket_first_name)) { ?>
   <div id="emd-ticket-emd-ticket-first-name-div" class="emd-single-div">
   <div id="emd-ticket-emd-ticket-first-name-key" class="emd-single-title">
<?php _e('First Name', 'wp-ticket-com'); ?>
   </div>
   <div id="emd-ticket-emd-ticket-first-name-val" class="emd-single-val">
<?php echo $emd_ticket_first_name; ?>
   </div>
   </div>
<?php
} ?>
<?php
$emd_ticket_last_name = emd_mb_meta('emd_ticket_last_name');
if (!empty($emd_ticket_last_name)) { ?>
   <div id="emd-ticket-emd-ticket-last-name-div" class="emd-single-div">
   <div id="emd-ticket-emd-ticket-last-name-key" class="emd-single-title">
<?php _e('Last Name', 'wp-ticket-com'); ?>
   </div>
   <div id="emd-ticket-emd-ticket-last-name-val" class="emd-single-val">
<?php echo $emd_ticket_last_name; ?>
   </div>
   </div>
<?php
} ?>
<?php
$emd_ticket_email = emd_mb_meta('emd_ticket_email');
if (!empty($emd_ticket_email)) { ?>
   <div id="emd-ticket-emd-ticket-email-div" class="emd-single-div">
   <div id="emd-ticket-emd-ticket-email-key" class="emd-single-title">
<?php _e('Email', 'wp-ticket-com'); ?>
   </div>
   <div id="emd-ticket-emd-ticket-email-val" class="emd-single-val">
<?php echo $emd_ticket_email; ?>
   </div>
   </div>
<?php
} ?>
<?php
$emd_ticket_phone = emd_mb_meta('emd_ticket_phone');
if (!empty($emd_ticket_phone)) { ?>
   <div id="emd-ticket-emd-ticket-phone-div" class="emd-single-div">
   <div id="emd-ticket-emd-ticket-phone-key" class="emd-single-title">
<?php _e('Phone', 'wp-ticket-com'); ?>
   </div>
   <div id="emd-ticket-emd-ticket-phone-val" class="emd-single-val">
<?php echo $emd_ticket_phone; ?>
   </div>
   </div>
<?php
} ?>
<?php $emd_mb_file = emd_mb_meta('emd_ticket_attachment', 'type=file');
if (!empty($emd_mb_file)) { ?>
  <div id="emd-ticket-emd-ticket-attachment-div" class="emd-single-div">
  <div id="emd-ticket-emd-ticket-attachment-key" class="emd-single-title">
  <?php _e('Attachments', 'wp-ticket-com'); ?>
  </div>
  <div id="emd-ticket-emd-ticket-attachment-val" class="emd-single-val">
  <?php foreach ($emd_mb_file as $info) {
		$fsrc = wp_mime_type_icon($info['ID']);
?>
  <a href='<?php echo esc_url($info['url']); ?>' target='_blank' title='<?php echo esc_attr($info['title']); ?>'><img src='<?php echo $fsrc; ?>' title='<?php echo esc_html($info['name']); ?>' width='20' />
   </a>
  <?php
	} ?>
  </div>
  </div>
<?php
} ?>
<?php
$taxlist = get_object_taxonomies(get_post_type() , 'objects');
foreach ($taxlist as $taxkey => $mytax) {
	$termlist = get_the_term_list(get_the_ID() , $taxkey, '', ' , ', '');
	if (!empty($termlist)) { ?>
      <div id="emd-ticket-<?php echo esc_attr($taxkey); ?>-div" class="emd-single-div">
      <div id="emd-ticket-<?php echo esc_attr($taxkey); ?>-key" class="emd-single-title">
      <?php echo esc_html($mytax->labels->singular_name); ?>
      </div>
      <div id="emd-ticket-<?php echo esc_attr($taxkey); ?>-val" class="emd-single-val">
      <?php echo $termlist; ?>
      </div>
      </div>
   <?php
	}
} ?>
</div><!--container-end-->