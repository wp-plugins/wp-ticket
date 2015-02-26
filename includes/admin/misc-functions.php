<?php
/**
 * Misc Admin Functions
 *
 * @package WP_TICKET_COM
 * @version 1.3.0
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
add_action('edit_form_advanced', 'wp_ticket_com_force_post_builtin');
/**
 * Add required js check for builtin fields and taxonomies
 *
 * @since WPAS 4.0
 *
 * @return js
 */
function wp_ticket_com_force_post_builtin() {
	$post = get_post();
	if (in_array($post->post_type, Array(
		'emd_ticket'
	))) { ?>
   <script type='text/javascript'>
       jQuery('#publish').click(function(){
           var msg = [];
           <?php if (in_array($post->post_type, Array(
			'emd_ticket'
		))) { ?>
   var title = jQuery('[id^="titlediv"]').find('#title');
   if(title.val().length < 1) {
       jQuery('#title').addClass('error');
       msg.push('<?php _e('Title', 'wp-ticket-com'); ?>');
   }
<?php
		} ?>
           <?php if (in_array($post->post_type, Array(
			'emd_ticket'
		))) { ?>
   var content = jQuery('[id^="wp-content-editor-container"]').find('#content');
   if(content.val().length < 1){
       jQuery('#wp-content-wrap').addClass('error');
       msg.push('<?php _e('Content', 'wp-ticket-com'); ?>');
   }
<?php
		} ?>
           
           <?php if (in_array($post->post_type, Array(
			'emd_ticket'
		))) { ?>
      var tcount = jQuery("input[name='radio_tax_input[ticket_topic][]']:checked").length;
      if(tcount < 1){
         jQuery('#radio-tagsdiv-ticket_topic').css({'border-left':'4px solid #DD3D36'});
         msg.push('<?php _e('Topics Taxonomy', 'wp-ticket-com'); ?>');
       }else{
         jQuery('#radio-tagsdiv-ticket_topic').attr('style','');
       }
<?php
		} ?>
           if(msg.length > 0){
              jQuery('#publish').removeClass('button-primary-disabled');
              jQuery('#ajax-loading').attr( 'style','');
              jQuery('#post').siblings('#message').remove();
              jQuery('#post').before('<div id="message" class="error"><p>'+msg.join(', ')+' <?php _e('required', 'wp-ticket-com'); ?>.</p></div>');
              return false; 
           }
       }); 
    </script>
<?php
	}
}
