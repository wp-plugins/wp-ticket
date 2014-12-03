<?php
/**
 * Common Functions
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       1.0
 */
if (!defined('ABSPATH')) exit;
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
global $wp_version;
/**
 * Generates hidden func value
 *
 * @since WPAS 4.0
 * @param string $hidden_func
 *
 * @return string $val
 */
function emd_get_hidden_func($hidden_func) {
	global $current_user;
	get_currentuserinfo();
	switch ($hidden_func) {
		case 'user_login':
			$val = $current_user->user_login;
		break;
		case 'user_email':
			$val = $current_user->user_email;
		break;
		case 'user_firstname':
			$val = $current_user->user_firstname;
		break;
		case 'user_lastname':
			$val = $current_user->user_lastname;
		break;
		case 'user_displayname':
			$val = $current_user->display_name;
		break;
		case 'user_id':
			$val = $current_user->ID;
		break;
		case 'date_mm_dd_yyyy':
			//$val = date("m-d-Y");
			$val = date("Y-m-d");
		break;
		case 'date_dd_mm_yyyy':
			//$val = date("d-m-Y");
			$val = date("Y-m-d");
		break;
		case 'current_year':
			$val = date("Y");
		break;
		case 'current_month':
			$val = date("F");
		break;
		case 'current_month_num':
			$val = date("m");
		break;
		case 'current_day':
			$val = date("d");
		break;
		case 'now':
			$val = date("Y-m-d H:i:s");
		break;
		case 'current_time':
			$val = date("H:i:s");
		break;
		case 'user_ip':
			$val = $_SERVER['REMOTE_ADDR'];
		break;
		case 'unique_id':
			$val = 'emd_uid';
		break;
	}
	if ($current_user->ID == 0 && $val == '') {
		$val = "Visitor";
	}
	return $val;
}
/**
 * Sets query parameters for author and search on frontend
 *
 * @since WPAS 4.0
 * @param string $app
 * @param object $query
 * @param bool $has_limit_by
 *
 * @return object $query
 */
function emd_limit_author_search($app, $query, $has_limitby = 0) {
	$current_ptype = isset($query->query_vars['post_type']) ? $query->query_vars['post_type'] : Array();
	$cap_post_types = get_post_types();
	foreach ($cap_post_types as $ptype) {
		if (!in_array($ptype, Array(
			'attachment',
			'revision',
			'nav_menu_item'
		)) && current_user_can('edit_' . $ptype . 's')) {
			$set_types[] = $ptype;
		}
	}
	if (!empty($set_types)) {
		$latest_ptypes = array_unique(array_merge($current_ptype, $set_types));
		$query->set('post_type', array_values($latest_ptypes));
		if ($has_limitby == 1) {
			$pids = Array();
			foreach (array_values($latest_ptypes) as $ptype) {
				$pids = apply_filters('emd_limit_by', $pids, $app, $ptype);
			}
			$query->set('post__in', $pids);
		}
	}
	return $query;
}
/**
 * Has_shortcode func if wp version is < 3.6
 *
 * @since WPAS 4.0
 * @param string $content
 * @param string $shc
 *
 * @return bool
 */
if (version_compare($wp_version, "3.6", "<")) {
	function has_shortcode($content, $shc) {
		global $shortcode_tags;
		if (array_key_exists($shc, $shortcode_tags)) {
			preg_match_all('/' . get_shortcode_regex() . '/s', $content, $matches, PREG_SET_ORDER);
			if (empty($matches)) return false;
			foreach ($matches as $shortcode) {
				if ($shc === $shortcode[2]) return true;
			}
		}
		return false;
	}
}
