<?php
/**
 * Notification Actions Functions
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       1.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Sends notification if there is active tax notification and if any change between old and new terms
 *
 * @since WPAS 4.0
 *
 * @param string $app
 * @param int $pid
 * @param string $type
 * @param string $field
 * @param array $old_val
 * @param array $new_val
 *
 */
function emd_check_change_notify($app, $pid, $type, $field, $old_val, $new_val) {
	$notify_list = get_option($app . "_notify_list");
	if (!empty($notify_list)) {
		foreach ($notify_list as $mynotify) {
			if ($mynotify['active'] == 1) {
				$send_msg = 0;
				if ($type == 'tax' && isset($mynotify['object']) && $mynotify['object'] == $field) {
					//first see if there is any change
					$diff1 = array_diff($old_val, $new_val);
					$diff2 = array_diff($new_val, $old_val);
					if (empty($mynotify['ev_change_val']) && (!empty($diff1) || !empty($diff2))) {
						$send_msg = 1;
					} elseif (!empty($mynotify['ev_change_val'])) {
						$old_terms = emd_get_terms($old_val, $field);
						$new_terms = emd_get_terms($new_val, $field);
						if (in_array(strtolower($mynotify['ev_change_val']) , $new_terms) && !in_array(strtolower($mynotify['ev_change_val']) , $old_terms)) {
							$send_msg = 1;
						}
					}
				}
				if ($send_msg == 1) {
					emd_send_notification($app, $mynotify, 'change', $pid);
				}
			}
		}
	}
}
/**
 * Get terms of taxonomies by term taxonomy ids
 *
 * @since WPAS 4.0
 *
 * @param array $vals
 * @param string $field
 *
 * @return array $terms
 */
function emd_get_terms($vals, $field) {
	$terms = Array();
	if (!empty($vals)) {
		foreach ($vals as $myval) {
			$term = get_term_by('term_taxonomy_id', $myval, $field);
			$terms[] = strtolower($term->name);
		}
	}
	return $terms;
}
/**
 * Sends user and admin notifications
 *
 * @since WPAS 4.0
 *
 * @param string $app
 * @param array $mynotify
 * @param string $event
 * @param string $pid
 * @param array $rel_uniqs
 *
 */
function emd_send_notification($app, $mynotify, $event, $pid, $rel_uniqs = Array() , $comment_id = 0) {
	if (!empty($mynotify['user_msg'])) {
		$user_msg = $mynotify['user_msg'];
		$user_msg_arr = Array();
		$attr_list = get_option($app . '_attr_list');
		foreach ($mynotify['user_msg']['send_to'] as $send_to) {
			if ($send_to['active'] == 1 && $mynotify['level'] == 'com' && isset($send_to['com_email']) && $send_to['com_email'] == 1) {
				$comment = get_comment($comment_id);
				$comments = get_comments(array(
					'post_id' => $pid,
					'type' => $comment->comment_type
				));
				$com_sendto = Array();
				foreach ($comments as $pcomment) {
					if ($pcomment->comment_author_email != $comment->comment_author_email && !in_array($pcomment->comment_author_email, $com_sendto)) {
						$com_sendto[] = $pcomment->comment_author_email;
					}
				}
				if (!empty($com_sendto)) {
					foreach ($com_sendto as $sendto_email) {
						$user_msg_arr[$sendto_email]['message'] = emd_parse_template_tags($app, $user_msg['message'], $pid);
						$user_msg_arr[$sendto_email]['subject'] = emd_parse_template_tags($app, $user_msg['subject'], $pid);
					}
				}
			}
			if ($send_to['active'] == 1 && $event == 'front_add' && !empty($send_to['rel']) && isset($rel_uniqs[$send_to['rel']])) {
				$rel_post = get_post($rel_uniqs[$send_to['rel']]);
				if ($rel_post->post_type == $send_to['entity']) {
					if($attr_list[$send_to['entity']][$send_to['attr']]['display_type'] == 'user'){
						$user_id = emd_mb_meta($send_to['attr'], '', $rel_uniqs[$send_to['rel']]);
						$user_info = get_userdata($user_id);
						$sendto_email = $user_info->user_email;
					}
					else {
						$sendto_email = emd_mb_meta($send_to['attr'], '', $rel_uniqs[$send_to['rel']]);
					}
				}
				else {
					if($attr_list[$send_to['entity']][$send_to['attr']]['display_type'] == 'user'){
						$user_id = emd_mb_meta($send_to['attr'], '', $pid);
						$user_info = get_userdata($user_id);
						$sendto_email = $user_info->user_email;
					}
					else {
						$sendto_email = emd_mb_meta($send_to['attr'], '', $pid);
					}
				}
				$user_msg['message'] = emd_parse_template_tags($app, $user_msg['message'], $rel_post->ID, 0, 'rel');
				$user_msg['subject'] = emd_parse_template_tags($app, $user_msg['subject'], $rel_post->ID, 0, 'rel');
				$user_msg_arr[$sendto_email]['message'] = emd_parse_template_tags($app, $user_msg['message'], $pid , 1, 'rel');
				$user_msg_arr[$sendto_email]['subject'] = emd_parse_template_tags($app, $user_msg['subject'], $pid, 1, 'rel');
			} elseif ($send_to['active'] == 1 && $event != 'front_add' && !empty($send_to['rel'])) {
				if($send_to['entity'] == $mynotify['entity'] || $mynotify['level'] == 'com' || (($event == 'change' || $event == 'back_add') && $send_to['entity'] != $mynotify['entity'])){
					global $wpdb;
					$other = "from";
					if ($send_to['from_to'] == 'from') {
						$other = "to";
					}
					$conns = $wpdb->get_results("SELECT p2p_" . $other . " as pid FROM {$wpdb->p2p} WHERE p2p_type='" . $send_to['rel'] . "' AND p2p_" . $send_to['from_to'] . "='" . $pid . "'", ARRAY_A);
					foreach ($conns as $mycon) {
						if($attr_list[$send_to['entity']][$send_to['attr']]['display_type'] == 'user'){
							$user_id = emd_mb_meta($send_to['attr'], '', $mycon['pid']);
							$user_info = get_userdata($user_id);
							$sendto_email = $user_info->user_email;
						}
						else {
							$sendto_email = emd_mb_meta($send_to['attr'], '', $mycon['pid']);
						}
						$user_msg_arr[$sendto_email]['message'] = emd_parse_template_tags($app, $user_msg['message'], $pid);
						$user_msg_arr[$sendto_email]['subject'] = emd_parse_template_tags($app, $user_msg['subject'], $pid);
					}
				}
				else {
					if($attr_list[$send_to['entity']][$send_to['attr']]['display_type'] == 'user'){
						$user_id = emd_mb_meta($send_to['attr'],'',$pid);
						$user_info = get_userdata($user_id);
						$sendto_email = $user_info->user_email;
					}
					else {
						$sendto_email = emd_mb_meta($send_to['attr'], '', $pid);
					}
					$user_msg_arr[$sendto_email]['message'] = emd_parse_template_tags($app, $user_msg['message'], $pid);
					$user_msg_arr[$sendto_email]['subject'] = emd_parse_template_tags($app, $user_msg['subject'], $pid);
				}
			} elseif ($send_to['active'] == 1 && empty($send_to['rel']) && !empty($send_to['attr'])) {
				if($attr_list[$send_to['entity']][$send_to['attr']]['display_type'] == 'user'){
					$user_id = emd_mb_meta($send_to['attr'], '', $pid);
					$user_info = get_userdata($user_id);
					$sendto_email = $user_info->user_email;
				}
				else {
					$sendto_email = emd_mb_meta($send_to['attr'], '', $pid);
				}
				$user_msg_arr[$sendto_email]['message'] = emd_parse_template_tags($app, $user_msg['message'], $pid);
				$user_msg_arr[$sendto_email]['subject'] = emd_parse_template_tags($app, $user_msg['subject'], $pid);
			}
		}
		foreach($user_msg_arr as $msg_key => $msg_arr){
			$msg_arr['send_to'] = $msg_key;
			$msg_arr['reply_to'] = $user_msg['reply_to'];
			$msg_arr['cc'] = $user_msg['cc'];
			$msg_arr['bcc'] = $user_msg['bcc'];
			emd_send_email($msg_arr);
		}
	}
	if (!empty($mynotify['admin_msg'])) {
		$mynotify['admin_msg']['message'] = emd_parse_template_tags($app, $mynotify['admin_msg']['message'], $pid);
		$mynotify['admin_msg']['subject'] = emd_parse_template_tags($app, $mynotify['admin_msg']['subject'], $pid);
		emd_send_email($mynotify['admin_msg']);
	}
}
/**
 * Sends notification if there is active rel, attr, comment or entity events
 *
 * @since WPAS 4.0
 *
 * @param string $app
 * @param int $pid
 * @param string $type
 * @param string $event
 * @param array $rel_uniqs
 *
 */
function emd_check_notify($app, $pid, $type, $event, $rel_uniqs = Array()) {
	$notify_list = get_option($app . "_notify_list");
	$attr_list = get_option($app . "_attr_list");
	$comment_id = 0;
	if ($type != 'rel' && $type != 'com') {
		$mypost = get_post($pid);
		$ptype = $mypost->post_type;
	}
	if (!empty($notify_list)) {
		foreach ($notify_list as $mynotify) {
			if ($mynotify['active'] == 1) {
				$send_msg = 0;
				if ($type == 'attr' && isset($mynotify['object']) && $event == 'change' && isset($_POST[$mynotify['object']])) {
					$old_val = emd_mb_meta($mynotify['object'], '', $pid);
					$new_val = $_POST[$mynotify['object']];
					$new_val = emd_translate_date_format($attr_list[$ptype][$mynotify['object']], $new_val, 0);
					if (empty($mynotify['ev_change_val']) && $old_val != $new_val) {
						$send_msg = 1;
					} elseif (!empty($mynotify['ev_change_val']) && $new_val == $mynotify['ev_change_val'] && $old_val != $new_val) {
						$send_msg = 1;
					}
				} elseif(!empty($rel_uniqs) && $type == 'rel' && isset($mynotify['object']) && $mynotify['level'] == $type && isset($mynotify['ev_front_add']) && $mynotify['ev_front_add'] == 1) {
					$send_msg = 1;
				} elseif ($type == 'rel' && isset($mynotify['object']) && $mynotify['level'] == $type && isset($mynotify['ev_' . $event]) && $mynotify['ev_' . $event] == 1) {
					$connection = p2p_get_connection($pid);
					if (!empty($connection) && $mynotify['object'] == $connection->p2p_type) {
						$send_msg = 1;
						$pid = $connection->p2p_to;
						$to_p2p = get_post($connection->p2p_to);
						if ($to_p2p->post_type == $mynotify['entity']) {
							$pid = $connection->p2p_from;
						}
					}
				} elseif ($type == 'com' && isset($mynotify['object']) && $mynotify['level'] == $type && isset($mynotify['ev_' . $event]) && $mynotify['ev_' . $event] == 1) {
					$comment = get_comment($pid);
					if(!empty($comment)){
						$comment_id = $pid;
						if (isset($mynotify['object']) && $mynotify['object'] == $comment->comment_type) {
							$send_msg = 1;
							$pid = $comment->comment_post_ID;
						}
					}
				} elseif ($type == 'entity' && $mynotify['level'] == $type && isset($mynotify['ev_' . $event]) && $mynotify['ev_' . $event] == 1 && $mynotify['entity'] == $ptype) {
					$send_msg = 1;
				}
				if ($send_msg == 1) {
					emd_send_notification($app, $mynotify, $event, $pid, $rel_uniqs, $comment_id);
				}
			}
		}
	}
}
/**
 * Use wp_mail to send notifications
 *
 * @since WPAS 4.0
 *
 * @param array $conf_arr
 *
 */
function emd_send_email($conf_arr) {
	if(!empty($conf_arr['send_to'])){
		$from_name = get_bloginfo('name');
		$from_email = get_option('admin_email');
		$headers = "From: " . stripslashes_deep(html_entity_decode($from_name, ENT_COMPAT, 'UTF-8')) . " <$from_email>\r\n";
		$headers.= 'Content-type: text/html; charset=UTF-8' . "\r\n";
		if ($conf_arr['reply_to'] != '') {
			$headers.= "Reply-To: " . $conf_arr['reply_to'] . "\r\n";
		} else {
			$headers.= "Reply-To: " . $from_email . "\r\n";
		}
		if ($conf_arr['cc'] != '') {
			$headers.= "Cc: " . $conf_arr['cc'] . "\r\n";
		}
		if ($conf_arr['bcc'] != '') {
			$headers.= "Bcc: " . $conf_arr['bcc'] . "\r\n";
		}
		wp_mail($conf_arr['send_to'], $conf_arr['subject'], $conf_arr['message'], $headers);
	}
}
add_action('emd_notify', 'emd_check_notify', 10, 5);
add_action('emd_change_notify', 'emd_check_change_notify', 10, 6);
add_action( 'login_redirect', 'emd_login_redirect', 10, 3);
/**
 * Check if login is from a notification email and forward it to redirect if a user is logged in
 *
 * @since WPAS 4.6
 *
 */
function emd_login_redirect($redirect_to,$request, $user){
      if(preg_match('/fr_emd_notify/', $redirect_to)){
              $redirect_to = preg_replace('/fr_emd_notify.*/','',$redirect_to);
                $my_user = wp_get_current_user();
                if(!empty($my_user) && $my_user->ID != 0){
                        global $user;
                        $user = $my_user;
                        return $redirect_to;
                }
                else {
                        return $redirect_to;
                }
        }
        return $redirect_to;
}

