<?php
/**
 * Settings Functions
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       WPAS 4.4
 */
if (!defined('ABSPATH')) exit;

add_action('emd_show_settings_page','emd_show_settings_page',1);
/**
 * Show settings page for global variables
 *
 * @param string $app
 * @since WPAS 4.4
 *
 * @return html page content
 */

function emd_show_settings_page($app){
	global $title;
	$variables = get_option($app . '_glob_list');
	$form_variables = get_option($app . '_glob_forms_list');
?>
	<div class="wrap">
	<h2><?php echo $title; ?></h2>
<?php	
	if(!empty($variables)){
		$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'global';
		$tabs['global'] = __('Global', 'emd_plugins');
		echo '<p>' . settings_errors($app . '_glob_list') . '</p>';
	}
	elseif(!empty($form_variables)){
		$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'forms';
		$tabs['forms'] = __('Forms', 'emd_plugins');
		echo '<p>' . settings_errors($app . '_glob_forms_list') . '</p>';
	}
	else {
		echo '<h4>' . __('No settings found.','emd-plugins');
		echo '</div>';
		return;
	}
	if(isset($_GET['settings-updated']) && $_GET['settings-updated'] == true){
		echo '<div id="message" class="updated">' . __('Settings Saved.','emd-plugins') . '</div>';
	}
	echo '<h2 class="nav-tab-wrapper">';
	foreach ($tabs as $ktab => $mytab) {
		$tab_url[$ktab] = esc_url(add_query_arg(array(
					'tab' => $ktab
				)));
		$active = "";
		if ($active_tab == $ktab) {
			$active = "nav-tab-active";
		}
		echo '<a href="' . esc_url($tab_url[$ktab]) . '" class="nav-tab ' . $active . '" id="nav-' . $ktab . '">' . $mytab . '</a>';
	}
	echo '</h2><form method="post" action="options.php">';
	if(!empty($variables)){
		emd_glob_view_tab($app,$active_tab,$variables);
	}
	if(!empty($form_variables)){
		emd_glob_forms_tab($app,$active_tab,$form_variables);
	}
	submit_button(); 
	echo '</form></div>';
}
function emd_glob_register_settings($app){
	register_setting($app . '_glob_list', $app . '_glob_list', 'emd_glob_sanitize');
	register_setting($app . '_glob_forms_list', $app . '_glob_forms_list', 'emd_glob_forms_sanitize');
	$variables = get_option($app . '_glob_list');
	if(!empty($variables)){
		foreach($variables as $id => $myvar){
			$args['key'] = $id;
			$args['val'] = "";
			add_settings_field($app . '_glob_list[' . $id . ']', $myvar['label'], 'emd_glob_' . $myvar['type'] . '_callback',$app . '_settings','',$args);
		}
	}
}
function emd_glob_sanitize($input){
	$variables = get_option($input['app'] . '_glob_list');
	foreach($variables as $kv => $val){
		if(isset($input[$kv])){
			$variables[$kv]['val'] = $input[$kv];
		}
		elseif($val['type'] == 'checkbox') {
			$variables[$kv]['val'] = 0;
		}
		if($val['required'] == 1 && empty($input[$kv])){
			$error_message = sprintf(__( "%s is required.", 'emd-plugins'),$val['label']);
                	add_settings_error($input['app'] . '_glob_list','required-' . $kv,$error_message,'error');
		}

	}
	return $variables;
}
function emd_glob_forms_sanitize($input){
	$form_variables = get_option($input['app'] . '_glob_forms_list');
	$attr_list = get_option($input['app'] . '_attr_list');
	$tax_list = get_option($input['app']. '_tax_list');
	$rel_list = get_option($input['app'] . '_rel_list');
	$shc_list = get_option($input['app'] . '_shc_list');
	foreach($form_variables as $kv => $val){
		foreach($val as $kfield => $vfield){
			if($kfield != 'captcha'){
				if(!empty($attr_list[$shc_list['forms'][$kv]['ent']][$kfield]) && $shc_list['forms'][$kv]['type'] == 'submit' && $attr_list[$shc_list['forms'][$kv]['ent']][$kfield]['required'] == 1){
					$form_variables[$kv][$kfield]['show'] = 1;
				}
				elseif(!empty($attr_list[$shc_list['forms'][$kv]['ent']][$kfield]) && $shc_list['forms'][$kv]['type'] == 'search' && $attr_list[$shc_list['forms'][$kv]['ent']][$kfield]['srequired'] == 1){
					$form_variables[$kv][$kfield]['show'] = 1;
				}
				elseif(!empty($tax_list[$shc_list['forms'][$kv]['ent']][$kfield]) && $shc_list['forms'][$kv]['type'] == 'submit' && $tax_list[$shc_list['forms'][$kv]['ent']][$kfield]['required'] == 1){
					$form_variables[$kv][$kfield]['show'] = 1;
				}
				elseif(!empty($tax_list[$shc_list['forms'][$kv]['ent']][$kfield]) && $shc_list['forms'][$kv]['type'] == 'search' && $tax_list[$shc_list['forms'][$kv]['ent']][$kfield]['srequired'] == 1){
					$form_variables[$kv][$kfield]['show'] = 1;
				}
				elseif(!empty($rel_list[$kfield]) && $shc_list['forms'][$kv]['type'] == 'submit' && $rel_list[$kfield]['required'] == 1){
					$form_variables[$kv][$kfield]['show'] = 1;
				}
				elseif(!empty($rel_list[$kfield]) && $shc_list['forms'][$kv]['type'] == 'search' && $rel_list[$kfield]['srequired'] == 1){
					$form_variables[$kv][$kfield]['show'] = 1;
				}
				elseif(!empty($vfield['required']) && $vfield['required'] == 1){
					$form_variables[$kv][$kfield]['show'] = 1;
				} 
				elseif(isset($input[$kv][$kfield]['show']) && $input[$kv][$kfield]['show'] == 1){
					$form_variables[$kv][$kfield]['show'] = 1;
				}
				else {
					$form_variables[$kv][$kfield]['show'] = 0;
				}
				if(isset($input[$kv][$kfield]['size'])){
					$form_variables[$kv][$kfield]['size'] = $input[$kv][$kfield]['size'];
				}
				else {
					$form_variables[$kv][$kfield]['size'] = '';
				}
			}
			else {
				$form_variables[$kv][$kfield] = $input[$kv][$kfield];
			}
		}
	}
	return $form_variables;
}
function emd_glob_text_callback($args){
	$html = '<input type="text" class="' . $size . '-text" id="' . esc_attr($args['key']) . '" name="' . esc_attr($args['key']) . '" value="' . esc_attr(stripslashes($args['val'])) . '"/>';
	echo $html;
}
function emd_glob_val($app,$key){
	$variables = get_option(str_replace("-","_",$app) . '_glob_list');
	if($variables[$key]['type'] == 'checkbox_list' || $variables[$key]['type'] == 'multi_select'){
		if(!empty($variables[$key]['val'])){
			return implode(',',$variables[$key]['val']);
		}
	}
	elseif(isset($variables[$key]['val'])) {
		return $variables[$key]['val'];
	}
	return '';
}
function emd_glob_view_tab($app,$active_tab,$variables){
	settings_fields($app .'_glob_list'); ?>
	<div class='tab-content' id='tab-global' <?php if ( 'global' != $active_tab ) { echo 'style="display:none;"'; } ?>>
	<?php if(!empty($variables)){
	echo '<input type="hidden" name="' . esc_attr($app) . '_glob_list[app]" id="' . esc_attr($app) . '_glob_list_app" value="' . $app . '">';
	echo '<table class="form-table">
		<tbody>';
	foreach($variables as $id => $myvar){
		echo '<tr>
			<th scope="row">
			<label for="' . $id . '">';
		echo $myvar['label']; 
		if($myvar['required'] == 1){
			echo '<span class="dashicons dashicons-star-filled" style="font-size:10px;color:red;"></span>';
		}
		echo '</label>
			</th>
			<td>';
		$val = "";
		if(isset($myvar['val'])){
			$val = $myvar['val'];
			if($myvar['type'] == 'checkbox' && $val == 1){
				$val = 'checked';
			}
		}
		elseif(!empty($myvar['dflt'])){
			if(($myvar['type'] == 'checkbox_list' || $myvar['type'] == 'multi_select') && !is_array($myvar['dflt'])){
				$dflt = $myvar['dflt'];
				$val= Array("$dflt");
			}
			else {
				$val = $myvar['dflt'];
			}
		}
		switch($myvar['type']){
			case 'text':
				echo "<input id='" . esc_attr($app) . "_glob_list_" . $id . "' name='" . esc_attr($app) . "_glob_list[" . $id . "]' type='text' value='" . $val ."'></input>";
				break;
			case 'textarea':
				echo "<textarea id='" . esc_attr($app) . "_glob_list_" . $id . "' name='" . esc_attr($app) . "_glob_list[" . $id . "]'>" . $val ."</textarea>";
				break;
			case 'wysiwyg':
				echo wp_editor($val, esc_attr($app) . "_glob_list_" . $id, array(
							'tinymce' => false,
							'textarea_rows' => 10,
							'media_buttons' => true,
							'textarea_name' => esc_attr($app) . "_glob_list[" . $id . "]",
							'quicktags' => Array(
								'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,spell'
								)
							));
				break;
			case 'checkbox':
				echo "<input id='" . esc_attr($app) . "_glob_list_" . $id . "' name='" . esc_attr($app) . "_glob_list[" . $id . "]' type='checkbox' value='1'";
				if($val === 'checked'){
					echo " checked";
				}
				echo "></input>";
				break;
			case 'checkbox_list':
				if (!empty($myvar['values'])) {
					foreach($myvar['values'] as $kvalue => $mvalue){
						if (in_array($kvalue,$val)) {
							$checked = 'checked';
						} else {
							$checked = '';
						}
						echo "<input name='" . esc_attr($app) . "_glob_list[" . $id . "][] id='" . esc_attr($app) . "_glob_list[" . $id . "]" . "' type='checkbox' value='" . $kvalue . "' " . $checked . "/>&nbsp;";
						echo "<label for='" . esc_attr($app) . "_glob_list[" . $id . "]'>" . $mvalue . "</label><br/>";
					}
				}
				break;
			case 'radio':
				if (!empty($myvar['values'])) {
					foreach($myvar['values'] as $kvalue => $mvalue){
						if ($val == $kvalue) {
							$checked = 'checked';
						} else {
							$checked = '';
						}
						echo "<input name='" . esc_attr($app) . "_glob_list[" . $id . "] id='" . esc_attr($app) . "_glob_list_" . $id .  "' type='radio' value='" . $kvalue . "' " . $checked . "/>&nbsp;";
						echo "<label for='" . esc_attr($app) . "_glob_list[" . $id . "]'>" . $mvalue . "</label><br/>";
					}
				}
				break;
			case 'select':
				echo "<select id='" . esc_attr($app) . "_glob_list_" . $id . "' name='" . esc_attr($app) . "_glob_list[" . $id . "]'>";
				foreach($myvar['values'] as $kvalue => $mvalue){
					if($val == $kvalue){
						$selected = "selected";
					}
					else {
						$selected = "";
					}
					echo "<option value='" . $kvalue . "' " . $selected . "/>";
					echo  $mvalue . "</option>";
				}
				echo "</select>";
				break;
			case 'multi_select':
				echo "<select id='" . esc_attr($app) . "_glob_list_" . $id . "' name='" . esc_attr($app) . "_glob_list[" . $id . "][]' multiple>";
				foreach($myvar['values'] as $kvalue => $mvalue){
					if(in_array($kvalue,$val)){
						$selected = "selected";
					}
					else {
						$selected = "";
					}
					echo "<option value='" . $kvalue . "' " . $selected . "/>";
					echo  $mvalue . "</option>";
				}
				echo "</select>";
				break;
		}
		if(!empty($myvar['desc'])){
			echo "<p class='description'>" . $myvar['desc'] . "</p>";
		}
		
		echo '</td>
			</tr>';
	}
	echo '</tbody></table>';
}
?>
	</tbody>
	</table>
	</div>
<?php
}
function emd_glob_forms_tab($app,$active_tab,$form_variables){
	$attr_list = get_option($app . '_attr_list');
	$tax_list = get_option($app . '_tax_list');
	$rel_list = get_option($app . '_rel_list');
	$shc_list = get_option($app . '_shc_list');
	settings_fields($app .'_glob_forms_list'); 
?>
<div class='tab-content' id='tab-forms' <?php if ( 'forms' != $active_tab ) { echo 'style="display:none;"'; } ?>>
	<h4><?php _e('Use the section below to show or hide corresponding form elements. Size column refers to the form elements length relative to the other elements in the same row. Total element size in each row can not exceed 12 units. When you hide or show an element you may adjust sizes of the other elements in the same row. The required form elements are disabled however you can change their sizes. The form elements which are in the same row are color coded.','emd-plugins'); ?></h4>
	<div id="forms-list" class="accordion-container"><ul class="outer-border">
	<?php if(!empty($form_variables)){
		foreach($form_variables as $key => $val){
			echo '<li id="' . esc_attr($key) . '" class="control-section accordion-section">
			<h3 class="accordion-section-title hndle" tabindex="0">' . $shc_list['forms'][$key]['page_title'] . '</h3>';
			echo '<div class="accordion-section-content"><div class="inside">';
			echo '<input type="hidden" name="' . esc_attr($app) . '_glob_forms_list[app]" id="' . esc_attr($app) . '_glob_forms_list_app" value="' . $app . '">';
			echo '<table class="form-table">';
			$row = 1;
			foreach($val as $elm_key => $elm_val){
				$label = "";
				$req = 0;
				if($elm_key != 'captcha'){
					echo '<tr'; 
					if($row == $elm_val['row']){
						echo ' style="background-color: rgb(245, 245, 255);"';
					}
					else {
						$row = $elm_val['row'] + 1;
					}
					echo '>
						<th scope="row">
						<label for="' . $elm_key . '">';
					if(!empty($attr_list[$shc_list['forms'][$key]['ent']][$elm_key])){
						$label = $attr_list[$shc_list['forms'][$key]['ent']][$elm_key]['label'];
						if($shc_list['forms'][$key]['type'] == 'submit'){
							$req = $attr_list[$shc_list['forms'][$key]['ent']][$elm_key]['required'];
						}
						elseif($shc_list['forms'][$key]['type'] == 'search'){
							$req = $attr_list[$shc_list['forms'][$key]['ent']][$elm_key]['srequired'];
						}
					}
					elseif(!empty($tax_list[$shc_list['forms'][$key]['ent']][$elm_key])){
						$label = $tax_list[$shc_list['forms'][$key]['ent']][$elm_key]['label'];
						if($shc_list['forms'][$key]['type'] == 'submit'){
							$req = $tax_list[$shc_list['forms'][$key]['ent']][$elm_key]['required'];
						}
						elseif($shc_list['forms'][$key]['type'] == 'search'){
							$req = $tax_list[$shc_list['forms'][$key]['ent']][$elm_key]['srequired'];
						}
					}
					elseif(!empty($rel_list[$elm_key])){
						if($shc_list['forms'][$key]['ent'] == $rel_list[$elm_key]['from']){
							$label = $rel_list[$elm_key]['from_title'];
						}
						else {
							$label = $rel_list[$elm_key]['to_title'];
						}
						if($shc_list['forms'][$key]['type'] == 'submit'){
							$req = $rel_list[$elm_key]['required'];
						}
						elseif($shc_list['forms'][$key]['type'] == 'search'){
							$req = $rel_list[$elm_key]['srequired'];
						}
					}
					elseif($elm_val['label']) {
						$label = $elm_val['label'];
						$req = $elm_val['required'];
					}
					printf(__('Show %s','emd-plugins'),$label); 
					echo '</label>
						</th>
						<td>';
					echo '<input id="' . esc_attr($app) . '_glob_forms_list_' . $elm_key . '_show" name="' . esc_attr($app) . '_glob_forms_list[' . $key . '][' . $elm_key . '][show]" type="checkbox" value=1';
					if($req == 1){
						echo " disabled checked";
					}
					elseif($elm_val['show'] == 1){
						echo " checked";
					}
					if(!$elm_val['size']){
						$elm_val['size'] = "";
					}
					echo '></input></td><th scope="row">Size:</th><td>
					<input type="text" class="small-text" id="' . esc_attr($app) . '_glob_forms_list_' . $elm_key . '" name="' . esc_attr($app) . '_glob_forms_list[' . $key . '][' . $elm_key . '][size]" value="' . $elm_val['size'] . '">
					</td></tr>';
				}
				else {
					$captcha_str = '<tr><th scope="row"><label for="captcha">' . __('Show Captcha','emd-plugins') . '</label></th>';
					$captcha_str .= '<td><select id="' . esc_attr($app) . '_glob_forms_list_captcha" name="' . esc_attr($app) . '_glob_forms_list[' . $key . '][captcha]">';
					$captcha_vals = Array('never-show' => __('Never Show','emd-plugins'),
							'show-always' => __('Always Show','emd-plugins'),
							'show-to-visitors' => __('Visitors Only','emd-plugins')
						);
					foreach($captcha_vals as $ckey => $cval){
						$captcha_str .= '<option value="' . $ckey . '"';
						if($elm_val == $ckey){
							$captcha_str .= ' selected';
						}		
						$captcha_str .= '>' . $cval . '</option>';
					}
					$captcha_str .= '</select>';
				}
			}
			echo $captcha_str;
			echo '</table>';
			echo '</div></div></li>';
		}
	}
?>
</div>
<?php 
}
