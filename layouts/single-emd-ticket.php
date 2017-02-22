<?php $real_post = $post;
$ent_attrs = get_option('wp_ticket_com_attr_list');
get_header('emdplugins'); 
?>
<div style="position:relative" class="emd-container">
<div class="ticket-wrap">
    <div class="ticket-well well">
        <div class="row">
            <?php if (emd_is_item_visible('ent_ticket_id', 'wp_ticket_com', 'attribute')) { ?> 
            <div class="col-sm-6">
                <div class="col-sm-6"> <strong><?php _e('Ticket ID', 'wp-ticket-com'); ?>:</strong> </div>
                <div class="col-sm-6">
                    <div style="font-size:80%"><?php echo esc_html(emd_mb_meta('emd_ticket_id')); ?>
</div>
                </div>
            </div>
            <?php
} ?> <?php if (emd_is_item_visible('tax_ticket_priority', 'wp_ticket_com', 'taxonomy')) { ?> 
            <div class="col-sm-6">
                <div class="col-sm-6"> <strong><?php _e('Priority', 'wp-ticket-com'); ?>:</strong> </div>
                <div class="col-sm-6">
                    <div class="ticket-tax <?php echo emd_get_tax_slugs(get_the_ID() , 'ticket_priority') ?>"> <?php echo emd_get_tax_vals(get_the_ID() , 'ticket_priority', 1); ?> </div>
                </div>
                <?php
} ?> 
            </div>
        </div>
        <div class="row">
            <?php if (emd_is_item_visible('tax_ticket_topic', 'wp_ticket_com', 'taxonomy')) { ?> 
            <div class="col-sm-6">
                <div class="col-sm-6"> <strong><?php _e('Topic', 'wp-ticket-com'); ?>:</strong> </div>
                <div class="col-sm-6">
                    <div class="ticket-tax <?php echo emd_get_tax_slugs(get_the_ID() , 'ticket_topic') ?>"> <?php echo emd_get_tax_vals(get_the_ID() , 'ticket_topic', 1); ?> </div>
                </div>
            </div>
            <?php
} ?> <?php if (emd_is_item_visible('tax_ticket_status', 'wp_ticket_com', 'taxonomy')) { ?> 
            <div class="col-sm-6">
                <div class="col-sm-6"> <strong><?php _e('Status', 'wp-ticket-com'); ?>:</strong> </div>
                <div class="col-sm-6">
                    <div class="ticket-tax <?php echo emd_get_tax_slugs(get_the_ID() , 'ticket_status') ?>"> <?php echo emd_get_tax_vals(get_the_ID() , 'ticket_status', 1); ?> </div>
                </div>
            </div>
            <?php
} ?> 
        </div>
    </div>
    <div class="ticket-inner">
        <?php if (emd_is_item_visible('ent_ticket_first_name', 'wp_ticket_com', 'attribute')) { ?> 
        <div class="row">
            <div class="col-sm-6"> <strong><?php _e('First Name', 'wp-ticket-com'); ?>:</strong> </div>
            <div class="col-sm-6"> <?php echo esc_html(emd_mb_meta('emd_ticket_first_name')); ?>
 </div>
        </div>
        <?php
} ?> <?php if (emd_is_item_visible('ent_ticket_last_name', 'wp_ticket_com', 'attribute')) { ?> 
        <div class="row">
            <div class="col-sm-6"> <strong><?php _e('Last Name', 'wp-ticket-com'); ?>:</strong> </div>
            <div class="col-sm-6"> <?php echo esc_html(emd_mb_meta('emd_ticket_last_name')); ?>
 </div>
        </div>
        <?php
} ?> <?php if (emd_is_item_visible('ent_ticket_duedate', 'wp_ticket_com', 'attribute')) { ?> 
        <div class="row">
            <div class="col-sm-6"> <strong><?php _e('Due', 'wp-ticket-com'); ?>:</strong> </div>
            <div class="col-sm-6"> <?php echo esc_html(emd_translate_date_format($ent_attrs['emd_ticket']['emd_ticket_duedate'], emd_mb_meta('emd_ticket_duedate') , 1)); ?>
 </div>
        </div>
        <?php
} ?> <?php $cust_fields = get_metadata('post', get_the_ID());
$real_cust_fields = Array();
$ent_map_list = get_option('wp_ticket_com_ent_map_list', Array());
foreach ($cust_fields as $ckey => $cval) {
	if (empty($ent_attrs['emd_ticket'][$ckey]) && !preg_match('/^(_|wpas_|emd_)/', $ckey)) {
		$cust_key = str_replace('-', '_', sanitize_title($ckey));
		if (empty($ent_map_list) || (!empty($ent_map_list) && empty($ent_map_list['emd_ticket']['cust_fields'][$cust_key]))) {
			$real_cust_fields[$ckey] = $cval;
		}
	}
}
if (!empty($real_cust_fields)) {
	$fcount = 0;
	foreach ($real_cust_fields as $rkey => $rval) {
		$val = implode($rval, " ");
		$fcount++;
?><div id='cust-field-<?php echo $fcount; ?>'> 
        <div class="row cust-field">
            <div class="col-sm-6"> <strong><?php echo $rkey; ?>:</strong> </div>
            <div class="col-sm-6"> <?php echo $val; ?> </div>
        </div>
        </div><?php
	}
}
?> <?php if (emd_is_item_visible('ent_ticket_attachment', 'wp_ticket_com', 'attribute')) { ?> 
        <div class="row">
            <div class="col-sm-6"> <strong><?php _e('Attachments', 'wp-ticket-com'); ?>:</strong> </div>
            <div class="col-sm-6"> <?php
	$emd_mb_file = emd_mb_meta('emd_ticket_attachment', 'type=file');
	if (!empty($emd_mb_file)) {
		foreach ($emd_mb_file as $info) {
			$fsrc = wp_mime_type_icon($info['ID']);
?>
 <div class='att-file' style='padding:5px;display:inline-block;'><a class='att-link' href='<?php echo esc_url($info['url']); ?>' target='_blank' title='<?php echo esc_attr($info['title']); ?>'><img src='<?php echo esc_url($fsrc); ?>' title='<?php echo esc_attr($info['name']); ?>' width='48' height='64'/></a><div class='att-filename' style='padding:2px;font-size:80%;'><?php echo $info['title']; ?></div></div>
<?php
		}
	}
?>
 </div>
        </div>
        <?php
} ?> 
        <div class="ticket-content"> <?php echo $post->post_content; ?> </div>
        <div>
            <div class="ticket-connections"> <?php if (shortcode_exists('wpas_woo_order_woo_ticket')) {
	echo do_shortcode("[wpas_woo_order_woo_ticket con_name='woo_ticket' app_name='wp_ticket_com' type='layout' post= " . get_the_ID() . "]");
} ?>
 <?php if (shortcode_exists('wpas_woo_product_woo_ticket')) {
	echo do_shortcode("[wpas_woo_product_woo_ticket con_name='woo_ticket' app_name='wp_ticket_com' type='layout' post= " . get_the_ID() . "]");
} ?>
 <?php if (shortcode_exists('wpas_edd_order_edd_ticket')) {
	echo do_shortcode("[wpas_edd_order_edd_ticket con_name='edd_ticket' app_name='wp_ticket_com' type='layout' post= " . get_the_ID() . "]");
} ?>
 <?php if (shortcode_exists('wpas_edd_product_edd_ticket')) {
	echo do_shortcode("[wpas_edd_product_edd_ticket con_name='edd_ticket' app_name='wp_ticket_com' type='layout' post= " . get_the_ID() . "]");
} ?>
 </div>
        </div>
    </div>
</div>
</div><!--container-end-->