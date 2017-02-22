<?php
/**
 * Shortcode Functions
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       1.0
 */
// Exit if accessed directly
if (!defined('ABSPATH')) exit;
/**
 * Gets shortcode layout
 *
 * @since WPAS 4.0
 * @param array $atts shortcode attributes
 * @param array $args query args
 * @param array $args_default default query args
 * @param array $fields
 * @return string layout html
 */
if (!function_exists('emd_shc_get_layout_list')) {
	function emd_shc_get_layout_list($atts, $args, $args_default, $fields) {
		global $wp_rewrite;
		if(!empty($atts['filter'])){
			$atts_filters = explode(";",$atts['filter']);
			foreach($atts_filters as $afilter){
				$afilter_arr = explode("::",$afilter);
				if($afilter_arr[0] == 'misc'){
					$misc_filters[$afilter_arr[1]] = $afilter_arr[3];
				}
			}
			if(!empty($misc_filters)){
				foreach($misc_filters as $kmisc => $vmisc){
					if(isset($args_default[$kmisc])){
						$args_default[$kmisc] = $vmisc;
					}
				}
				if(isset($misc_filters['has_pages'])){
					$fields['has_pages'] = ($misc_filters['has_pages'] == 'false') ? false : true;
				}
				if(isset($misc_filters['author'])){
					$cur_user = get_current_user_id();
					if($misc_filters['author'] == 'current_user' && $cur_user != 0){
						$args_default['author'] = $cur_user;
					}	
					else {
						$args_default['author'] = $misc_filters['author'];
					}
				}
			}
		}
		$fields['ajax_nav'] = 0;
		if(!empty($args['ajax_nav'])){
			$fields['ajax_nav'] = 1;
			unset($args['ajax_nav']);
		}
		
		//fields -- app , class, shc , form, has_pages , pageno, theme
		if ($fields['has_pages'] && empty($args)) {
			if (is_front_page() && get_query_var('pagename') == get_post(get_query_var('page_id'))->post_name) {
				$fields['pageno'] = get_query_var('paged');
			}
			elseif($wp_rewrite->permalink_structure == '/%postname%/' || $wp_rewrite->permalink_structure == '' || $fields['ajax_nav'] == 1){
				if (get_query_var('pageno')) $fields['pageno'] = get_query_var('pageno');
			}
			else {
				$fields['pageno'] = get_query_var('paged');
			}
			if($fields['pageno'] == 0){
				$fields['pageno'] = 1;	
			}	
		}
		if(!empty($args_default['filter'])){
			$emd_query_def = new Emd_Query($fields['class'], $fields['app'], $fields['shc']);
			$emd_query_def->args_filter($args_default['filter']);
			$args_default = array_merge($args_default,$emd_query_def->args);
		}
			
		if (empty($args)) {
			if (is_array($atts) && !empty($atts['filter'])) {
				$emd_query = new Emd_Query($fields['class'], $fields['app'], $fields['shc']);
				$emd_query->args_filter($atts['filter']);
				$args = $emd_query->args;
			}
			$args['post_type'] = $fields['class'];
		}
		if ($fields['has_pages']) {
			$args_default['paged'] = $fields['pageno'];
		} else {
			$args_default['no_found_rows'] = true;
		}
		$args = array_merge($args, $args_default);
		if ($fields['form'] != '' && class_exists('Emd_Session')) {
			$sess_name = strtoupper($fields['app']);
			$session_class = $sess_name();
			$session_class->session->set($fields['form'] . '_args',$args);
		}
		$front_ents = emd_find_limitby('frontend', $fields['app']);
                if(!empty($front_ents) && in_array($args['post_type'],$front_ents)){
			$pids = apply_filters('emd_limit_by', Array() , $fields['app'], $args['post_type'], 'frontend');
			if(!empty($pids)){
				if(!empty($args['post__in'])){
					$pids_intersect = array_intersect($args['post__in'],$pids);
					if(empty($pids_intersect)){
						$args['post__in'] = Array('0');
					}
				}
				else {
					$args['post__in'] = $pids;
				}
			}
		}
		if($fields['hier'] == 1){
			return emd_shc_get_hier_list($args,$fields);
		}
		else {	
			$args['context'] = $fields['shc'];
			if(!empty($misc_filters) && !empty($misc_filters['post_id'])){
				$args = Array('post__in' => explode(",",$misc_filters['post_id']), 'post_type' => $args['post_type']);
			}
			$myshc_query = new WP_Query($args);
			if (!is_user_logged_in())
			{
			?>
			<div class="tickets-info">
				<b>Note:</b> As a visitor, you can only see tickets submitted by other visitors.<br/> Please Login to see all tickets.
			</div>
			<?php
			}
			if ($myshc_query->have_posts()) {	
					
				ob_start();
				if (empty($fields['form']) && $fields['theme'] == 'bs') {
					emd_get_template_part($fields['app'], 'shc', "menu");
					/* This makes the emd-container div */
				} ?>
				<div id='<?php echo esc_attr($fields['shc']) . "_" . $fields['shc_count'] . "_" . esc_attr($fields['class']) . "-view"; ?>' class='emd-view-results'>
				<?php
				if ($fields['has_pages']) {
				?>
				<input type='hidden' id='emd_entity' name='emd_entity' value='<?php echo esc_attr($fields['class']); ?>'>
				<input type='hidden' id='emd_view' name='emd_view' value='<?php echo esc_attr($fields['shc']); ?>'>
				<input type='hidden' id='emd_view_count' name='emd_view_count' value='<?php echo esc_attr($fields['shc_count']); ?>'>	
				<input type='hidden' id='emd_app' name='emd_app' value='<?php echo esc_attr($fields['app']); ?>'>
				<?php
				}
				if (is_array($atts) && !empty($atts['set'])) {
					$set_list = $fields['shc'] . "_set_list";
					global $$set_list;
					$atts_set_list = explode(";", $atts['set']);
					foreach ($atts_set_list as $myset) {
						if (!empty($myset)) {
							$atts_set_arr = explode("::", $myset);
							if(count($atts_set_arr) == 4) {
								${$set_list}[$atts_set_arr[1]] = $atts_set_arr[3];
							}
						}
					}
				}
				if(is_array($atts) && ((isset($fields['has_json']) && $fields['has_json'] == 1) || !empty($atts['filter']))){
					foreach($atts as $keyatt => $myatt){
						if(preg_match('/filter=/',$myatt)){
							$filter_list = explode('=',$myatt);
							$filter_list[1] = trim($filter_list[1],'"');
							?>
							<input type='hidden' id='atts_filter' name='atts_filter' value='<?php echo esc_attr($filter_list[1]); ?>'>
						<?php
						}
						if($keyatt == 'filter'){
							?>
							<input type='hidden' id='atts_filter' name='atts_filter' value='<?php echo esc_attr($myatt); ?>'>
						<?php
						}
					}
				}
				$shc_count_var = $fields['shc'] . "_shc_count";
				global $$shc_count_var;
				$$shc_count_var = $fields['shc_count'];

				emd_get_template_part($fields['app'], 'shc', str_replace('_', '-', $fields['shc']) . "-header");
				$res_posts = Array();
				$count_var = $fields['shc'] . "_count";
				global $$count_var;
				$$count_var = 0;
				$shc_filter = $fields['shc'] . "_filter";
				global $$shc_filter;
				$$shc_filter = "";
				if(isset($atts['fpass']) && $atts['fpass'] == 1){
					if(!empty($atts['filter'])){
						$$shc_filter = $atts['filter'];
					}
				}
				while ($myshc_query->have_posts()) {
					$myshc_query->the_post();
					$in_post_id = get_the_ID();
					if (!in_array($in_post_id, $res_posts)) {
						$res_posts[] = $in_post_id;
						emd_get_template_part($fields['app'], 'shc', str_replace('_', '-', $fields['shc']) . "-content");
						$$count_var++;
					}
				}
				wp_reset_postdata();
				if (is_array($atts) && !empty($atts['set'])) {
					$$set_list=Array();
				}

				if (is_array($atts) && !empty($atts['filter'])) {
					$emd_query->remove_filters();
				}
				if(!empty($args_default['filter'])){
					$emd_query_def->remove_filters();
				}
				emd_get_template_part($fields['app'], 'shc', str_replace('_', '-', $fields['shc']) . "-footer");
				if ($fields['has_pages'] && $myshc_query->max_num_pages > 1) {
					global $wp_rewrite;
					if ($wp_rewrite->using_permalinks()) {
						if (is_front_page()) {
							$base = '/' . get_post(get_query_var('page_id'))->post_name . '/page/%#%/';
						}
						elseif($wp_rewrite->permalink_structure == '/%postname%/'){
							$base = '/' . get_query_var('pagename') . '/pageno/%#%/';
						}
						else {
							$base = '/' . get_query_var('pagename') . '/page/%#%/';
						}
					} else {
						$base = '/?page_id=' . get_query_var('page_id') . '&pageno=%#%';
					}
					$paging = paginate_links(array(
						'total' => $myshc_query->max_num_pages,
						'current' => $fields['pageno'],
						'base' => home_url() . $base,
						'format' => '%#%',
						'type' => 'array',
						'add_args' => true,
					));
					$paging_html = emd_shc_get_pagination($fields['theme'], $paging, $fields['pageno'], $fields['pgn_class']); ?>
					<div class='pagination-bar'>
					<?php echo $paging_html; ?>
					</div>
				<?php
				} ?>
				</div>
				<?php
				$layout = ob_get_clean();
				return $layout;
			}
			else{
				?>
				<div class="tickets-info"> No search results! </div>
				<?php
			}
		}
		if (is_array($atts) && !empty($atts['filter'])) {
			$emd_query->remove_filters();
		}
		return '';
	}
}
/**
 * Gets shortcode posts list
 *
 * @since WPAS 4.7
 * @param array $atts shortcode attributes
 * @param array $args query args
 * @param array $args_default default query args
 * @param array $fields
 * @return array postids
 */
if (!function_exists('emd_shc_get_posts_list')) {
	function emd_shc_get_posts_list($atts, $args, $args_default, $fields) {
		global $wp_rewrite;
		//fields -- app , class, shc , form, has_pages , pageno, theme
		if(!empty($args_default['filter'])){
			$emd_query_def = new Emd_Query($fields['class'], $fields['app'], $fields['shc']);
			$emd_query_def->args_filter($args_default['filter']);
			$args_default = array_merge($args_default,$emd_query_def->args);
		}

		if (empty($args)) {
			if (is_array($atts) && !empty($atts['filter'])) {
				$emd_query = new Emd_Query($fields['class'], $fields['app'], $fields['shc']);
				$emd_query->args_filter($atts['filter']);
				$args = $emd_query->args;
			}
			elseif(is_array($atts)){
				foreach($atts as $myatt){
					if(preg_match('/filter=/',$myatt)){
						$filter_list = explode('=',$myatt);
						$filter_list[1] = trim($filter_list[1],'"');
						$emd_query = new Emd_Query($fields['class'], $fields['app'], $fields['shc']);
						$emd_query->args_filter($filter_list[1]);
						$args = $emd_query->args;
					}
				}
			}
			$args['post_type'] = $fields['class'];
		}
		$args = array_merge($args, $args_default);
		if ($fields['form'] != '' && class_exists('Emd_Session')) {
			$sess_name = strtoupper($fields['app']);
			$session_class = $sess_name();
			$session_class->session->set($fields['form'] . '_args',$args);
		}
		$front_ents = emd_find_limitby('frontend', $fields['app']);
                if(!empty($front_ents) && in_array($args['post_type'],$front_ents)){
			$pids = apply_filters('emd_limit_by', Array() , $fields['app'], $args['post_type'],'frontend');
			if(!empty($pids)){
				$args['post__in'] = $pids;
			}
		}
		$args['context'] = $fields['shc'];
		$myshc_query = new WP_Query($args);
		$res_posts = Array();
		if ($myshc_query->have_posts()) {
			while ($myshc_query->have_posts()) {
				$myshc_query->the_post();
				$in_post_id = get_the_ID();
				if (!in_array($in_post_id, $res_posts)) {
					$res_posts[] = $in_post_id;
				}
			}
			wp_reset_postdata();
			if (is_array($atts) && !empty($atts['filter'])) {
				$emd_query->remove_filters();
			}
			if(!empty($args_default['filter'])){
				$emd_query_def->remove_filters();
			}
		}
		if (is_array($atts) && !empty($atts['filter'])) {
			$emd_query->remove_filters();
		}
		return $res_posts;
	}
}
/**
 * Creates hierarchial list
 *
 * @since WPAS 4.4
 * @param array $args
 * @param array $fields
 * @return string $layout
 */
if (!function_exists('emd_shc_get_hier_list')) {
	function emd_shc_get_hier_list($args,$fields){
		$myshc_query = new WP_Query($args);
		if ($myshc_query->have_posts()) {
			$count_var = $fields['shc'] . "_count";
			global $$count_var;
			$$count_var = 0;
			while ($myshc_query->have_posts()) {
				$myshc_query->the_post();
				$in_post_id = get_the_ID();
				$mypost = get_post($in_post_id);
				$mylist[$in_post_id]['parent'] = $mypost->post_parent;
				$mylist[$in_post_id]['menu_order'] = $mypost->menu_order;
				$mylist[$mypost->post_parent]['children'][] = $in_post_id;
			}
			wp_reset_postdata();
			ob_start();
			emd_get_template_part($fields['app'], 'shc', str_replace('_', '-', $fields['shc']) . "-header");
			if($fields['hier_type'] != 'none'){
				echo "<" . $fields['hier_type'] . " class='root parent ";
				if(isset($fields['hier_class'])){
					echo $fields['hier_class'];
				}
				echo "'>";
			}
			$root_ch_count = 1;
			foreach($mylist as $pid => $vals){
				//find the highest parent 0
				if(isset($vals['parent']) && $vals['parent'] == 0){
					global $post;
					$post = get_post($pid);
					if($fields['hier_type'] != 'none'){
						echo "<li id='root-item-" . $root_ch_count . "' class='item-" . $root_ch_count;
						if(!empty($vals['children'])){
							echo " parent";
						}
						else {
							echo " noparent";
						}
						echo "'>";
					}
					emd_get_template_part($fields['app'], 'shc', str_replace('_', '-', $fields['shc']) . "-content");
					if(!empty($vals['children']) && $fields['hier_depth'] != 0){
						emd_shc_get_hier_children($vals,$mylist,$fields,$root_ch_count,1);
					}
					$root_ch_count ++;
					$$count_var++;
					if($fields['hier_type'] != 'none'){
						echo "</li>";
					}
				}
			}
			if($fields['hier_type'] != 'none'){
				echo "</" . $fields['hier_type'] . ">";
			}
			$layout = ob_get_clean();
			return $layout;
		}
		return '';
	}
}
/**
 * Creates hierarchial list
 *
 * @since WPAS 4.4
 * @param array $args
 * @param array $fields
 * @return string $layout
 */
if (!function_exists('emd_shc_get_hier_children')) {
	function emd_shc_get_hier_children($vals,$mylist,$fields,$root_id,$cur_depth){
		global $post;
		$count_var = $fields['shc'] . "_count";
		global $$count_var;
		$list_count = 1;
		$hier_depth = $fields['hier_depth'] - 1;
		if($fields['hier_type'] != 'none'){
			echo "<" . $fields['hier_type'] . " id='root-item-" . $root_id . "-list-" . $list_count . "' class='list-" . $list_count . "'>";
		}
		$ch_count = 1;
		foreach($vals['children'] as $child){
			$post = get_post($child);
			if($fields['hier_type'] != 'none'){
				echo "<li id='root-item-" . $root_id . "-list-" . $list_count . "-item-" . $ch_count . "' class='item-" . $ch_count;
				if(!empty($mylist[$child]['children'])){
					echo " parent";
				}
				else {
					echo " noparent";
				}
				echo "'>";
			}
			$$count_var++;
			emd_get_template_part($fields['app'], 'shc', str_replace('_', '-', $fields['shc']) . "-content");
			if(!empty($mylist[$child]['children']) && (($fields['hier_depth'] != -1 && $hier_depth >= $cur_depth) || $fields['hier_depth'] == -1)){
				$cur_depth ++;
				emd_shc_get_hier_children($mylist[$child],$mylist,$fields,$root_id . "-list". $list_count,$cur_depth);
			}
			$ch_count ++;
			if($fields['hier_type'] != 'none'){
				echo "</li>";
			}
		}
		$list_count ++;
		if($fields['hier_type'] != 'none'){
			echo "</" . $fields['hier_type'] . ">";
		}
	}
}
/**
 * Creates pagination html
 *
 * @since WPAS 4.0
 * @param string $type theme type
 * @param array $paging
 * @param int $pageno
 * @return string paging html
 */
if (!function_exists('emd_shc_get_pagination')) {
	function emd_shc_get_pagination($type, $paging, $pageno, $pgn_class) {
		$paging_html = "";
		if ($type == 'bs' || $type == 'na') {
			$paging_html = "<ul class='pagination " . $pgn_class . "'>";
			foreach ($paging as $key_paging => $my_paging) {
				$paging_html.= "<li";
				if(strpos($my_paging,'page-numbers current') !== false){
					$paging_html.= " class='active'";
				}
				$paging_html.= ">" . $my_paging . "</li>";
			}
			$paging_html.= "</ul>";
		} elseif ($type == 'jui') {
			$paging_html = "<div class='nav-pages " . $pgn_class . "'>";
			foreach ($paging as $key_paging => $my_paging) {
				$paging_html.= "<div class='nav-item ui-state-default ui-corner-all";
				if(strpos($my_paging,'page-numbers current') !== false){
					$paging_html.= " ui-state-highlight";
				}
				$paging_html.= "'>" . $my_paging . "</div>";
			}
			$paging_html.= "</div>";
		}
		return $paging_html;
	}
}
/**
 * Add query var pageno
 *
 * @since WPAS 4.0
 * @param array $vars query vars
 * @return array $vars query vars
 */
if (!function_exists('emd_query_vars')) {
	function emd_query_vars($vars) {
		$vars[] = "pageno";
		return $vars;
	}
}
/**
 * Create rewrite rules for pageno
 *
 * @since WPAS 4.0
 * @return wp_rewrite rules
 */
if (!function_exists('emd_create_rewrite_rules')) {
	function emd_create_rewrite_rules() {
		global $wp_rewrite;
		$rewrite_tag = '%pageno%';
		$wp_rewrite->add_rewrite_tag($rewrite_tag, '(.+?)', 'pageno=');
		$rewrite_keywords_structure = $wp_rewrite->root . "%pagename%/%pageno%/$rewrite_tag/";
		$new_rule = $wp_rewrite->generate_rewrite_rules($rewrite_keywords_structure);
		$wp_rewrite->rules = $wp_rewrite->rules + $new_rule;
		return $wp_rewrite->rules;
	}
}
if (!function_exists('emd_get_std_pagenum')) {
	function emd_get_std_pagenum() {
		$response = false;
		$pageno = isset($_GET['pageno']) ? (int) $_GET['pageno'] : 1;
		$myentity = isset($_GET['entity']) ? (string) $_GET['entity'] : '';
		$myview = isset($_GET['view']) ? (string) $_GET['view'] : '';
		$shc_count = isset($_GET['view_count']) ? (int) $_GET['view_count'] : 1;
		$myapp = isset($_GET['app']) ? (string) $_GET['app'] : '';
		$atts_filter = isset($_GET['atts']) ? (string) $_GET['atts'] : '';
		if (!empty($myentity)) {
			$args = Array('ajax_nav'=>1); 
			$func_layout = $myapp . "_" . $myview . "_set_shc";
			$atts['filter'] = $atts_filter;
			$list_layout = $func_layout($atts, $args, '', $pageno, $shc_count);
			if ($list_layout != '') {
				echo $list_layout;
				die();
			}
		}
		echo $response;
		die();
	}
}