<?php
/**
 * Entity Class
 *
 * @package WP_TICKET_COM
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Emd_Ticket Class
 * @since WPAS 4.0
 */
class Emd_Ticket extends Emd_Entity {
	protected $post_type = 'emd_ticket';
	protected $textdomain = 'wp-ticket-com';
	protected $sing_label;
	protected $plural_label;
	protected $menu_entity;
	/**
	 * Initialize entity class
	 *
	 * @since WPAS 4.0
	 *
	 */
	public function __construct() {
		add_action('init', array(
			$this,
			'set_filters'
		) , 1);
		add_action('admin_init', array(
			$this,
			'set_metabox'
		));
		add_filter('wp_dropdown_users', array(
			$this,
			'author_override'
		));
		add_action('save_post', array(
			$this,
			'update_form_submitted_by'
		) , 11, 3);
		add_filter('post_updated_messages', array(
			$this,
			'updated_messages'
		));
		add_action('manage_emd_ticket_posts_custom_column', array(
			$this,
			'custom_columns'
		) , 10, 2);
		add_filter('manage_emd_ticket_posts_columns', array(
			$this,
			'column_headers'
		));
		add_filter('is_protected_meta', array(
			$this,
			'hide_attrs'
		) , 10, 2);
		add_filter('postmeta_form_keys', array(
			$this,
			'cust_keys'
		) , 10, 2);
		add_filter('emd_ext_form_var_init', array(
			$this,
			'add_cust_fields'
		) , 10, 3);
		add_filter('emd_get_cust_fields', array(
			$this,
			'get_cust_fields'
		) , 10, 2);
		add_filter('enter_title_here', array(
			$this,
			'change_title_text'
		));
	}
	/**
	 * Get custom attribute list
	 * @since WPAS 4.9
	 *
	 * @param array $cust_fields
	 * @param string $post_type
	 *
	 * @return array $new_keys
	 */
	public function get_cust_fields($cust_fields, $post_type) {
		global $wpdb;
		if ($post_type == $this->post_type) {
			$sql = "SELECT DISTINCT meta_key
               FROM $wpdb->postmeta a
               WHERE a.post_id IN (SELECT id FROM $wpdb->posts b WHERE b.post_type='" . $this->post_type . "')";
			$keys = $wpdb->get_col($sql);
			if (!empty($keys)) {
				foreach ($keys as $i => $mkey) {
					if (!preg_match('/^(_|wpas_|emd_)/', $mkey)) {
						$ckey = str_replace('-', '_', sanitize_title($mkey));
						$cust_fields[$ckey] = $mkey;
					}
				}
			}
		}
		return $cust_fields;
	}
	/**
	 * Set form variables for custom attributes
	 * @since WPAS 4.9
	 *
	 * @param array $form_variables
	 * @param string $app_name
	 * @param string $form_name
	 *
	 * @return array $form_variables
	 */
	public function add_cust_fields($form_variables, $app_name, $form_name) {
		//get cust keys for this entity
		$keys = $this->get_cust_fields(Array() , $this->post_type);
		if (!empty($keys)) {
			$shc_list = get_option($app_name . '_shc_list');
			if ($form_name == '') {
				foreach ($form_variables as $fkey => $fval) {
					$ent_form = $shc_list['forms'][$fkey]['ent'];
					if ($ent_form == $this->post_type) {
						$max_row = count($fval);
						foreach ($keys as $mycust_key) {
							$ckey = str_replace("-", "_", sanitize_title($mycust_key));
							if (empty($form_variables[$fkey][$ckey])) {
								$form_variables[$fkey][$ckey] = Array(
									'show' => 0,
									'row' => $max_row + 1,
									'req' => 0,
									'size' => 12,
									'label' => $mycust_key,
								);
							}
						}
					}
				}
			} else {
				$ent_form = $shc_list['forms'][$form_name]['ent'];
				if ($ent_form == $this->post_type) {
					$max_row = count($form_variables);
					foreach ($keys as $mycust_key) {
						$ckey = str_replace("-", "_", sanitize_title($mycust_key));
						if (empty($form_variables[$ckey])) {
							$form_variables[$ckey] = Array(
								'show' => 0,
								'row' => $max_row + 1,
								'req' => 0,
								'size' => 12,
								'label' => $mycust_key,
							);
						}
					}
				}
			}
		}
		return $form_variables;
	}
	/**
	 * Set new custom attributes dropdown in admin edit entity
	 * @since WPAS 4.9
	 *
	 * @param array $keys
	 * @param object $post
	 *
	 * @return array $keys
	 */
	public function cust_keys($keys, $post) {
		global $post_type, $wpdb;
		if ($post_type == $this->post_type) {
			$sql = "SELECT DISTINCT meta_key
                FROM $wpdb->postmeta a
                WHERE a.post_id IN (SELECT id FROM $wpdb->posts b WHERE b.post_type='" . $this->post_type . "')";
			$keys = $wpdb->get_col($sql);
		}
		return $keys;
	}
	/**
	 * Hide all emd attributes
	 * @since WPAS 4.9
	 *
	 * @param bool $protected
	 * @param string $meta_key
	 *
	 * @return bool $protected
	 */
	public function hide_attrs($protected, $meta_key) {
		if (preg_match('/^(emd_|wpas_)/', $meta_key)) return true;
		foreach ($this->boxes as $mybox) {
			foreach ($mybox['fields'] as $fkey => $mybox_field) {
				if ($meta_key == $fkey) return true;
			}
		}
		return $protected;
	}
	public function update_form_submitted_by($post_id, $post, $update) {
		if ($update && $post->post_type == 'emd_ticket') {
			$ulogin = "";
			if (isset($_REQUEST['post_author_override']) && $_REQUEST['post_author_override'] == 0) {
				$ulogin = 'Visitor';
				//unhook this function so it doesn't loop infinitely
				remove_action('save_post', array(
					$this,
					'update_form_submitted_by'
				) , 11, 3);
				//update the post
				wp_update_post(array(
					'ID' => $post_id,
					'post_author' => 0
				));
				//re-hook this function
				add_action('save_post', array(
					$this,
					'update_form_submitted_by'
				) , 11, 3);
			} elseif (!empty($post->post_author)) {
				$user = get_user_by('id', $post->post_author);
				$ulogin = $user->user_login;
			}
			if (!empty($_REQUEST['wpas_form_submitted_by']) && $ulogin != $_REQUEST['wpas_form_submitted_by']) {
				update_post_meta($post_id, 'wpas_form_submitted_by', $ulogin);
			}
		}
	}
	public function author_override($output) {
		global $pagenow, $post, $user_ID;
		if ('post-new.php' === $pagenow || 'post.php' === $pagenow) {
			if ((isset($_GET['post_type']) && $this->post_type === $_GET['post_type']) || (isset($_GET['post']) && get_post_type($_GET['post']) === $this->post_type)) {
				// return if this isn't the theme author override dropdown
				if (!preg_match('/post_author_override/', $output)) return $output;
				// return if we've already replaced the list (end recursion)
				if (preg_match('/post_author_override_replaced/', $output)) return $output;
				//get dropdown values all users who have edit cap for this entity
				// Get valid roles
				global $wp_roles;
				$roles = $wp_roles->role_objects;
				$valid_roles = array();
				$user_ids = array();
				if (!current_user_can('set_author_' . $this->post_type . 's')) {
					//current user
					$user_ids[] = get_current_user_id();
				} else {
					foreach ($roles as $role) {
						if (isset($role->capabilities['edit_' . $this->post_type . 's'])) {
							$valid_roles[] = $role->name;
						}
					}
					if (empty($valid_roles)) return $output;
					// Get user IDs
					foreach ($valid_roles as $role) {
						$users = get_users(array(
							'role' => $role
						));
						if (!empty($users)) {
							foreach ($users as $user) {
								$user_ids[] = $user->ID;
							}
						}
					}
				}
				if (empty($user_ids)) return $output;
				// replacement call to wp_dropdown_users
				$output = wp_dropdown_users(array(
					'echo' => 0,
					'show_option_none' => 'Visitor',
					'option_none_value' => '0',
					'name' => 'post_author_override_replaced',
					'selected' => empty($post->ID) ? $user_ID : $post->post_author,
					'include' => implode(',', $user_ids) ,
					'include_selected' => true
				));
				// put the original name back
				$output = preg_replace('/post_author_override_replaced/', 'post_author_override', $output);
			}
		}
		return $output;
	}
	/**
	 * Get column header list in admin list pages
	 * @since WPAS 4.0
	 *
	 * @param array $columns
	 *
	 * @return array $columns
	 */
	public function column_headers($columns) {
		foreach ($this->boxes as $mybox) {
			foreach ($mybox['fields'] as $fkey => $mybox_field) {
				if (!in_array($fkey, Array(
					'wpas_form_name',
					'wpas_form_submitted_by',
					'wpas_form_submitted_ip'
				)) && !in_array($mybox_field['type'], Array(
					'textarea',
					'wysiwyg'
				)) && $mybox_field['list_visible'] == 1) {
					$columns[$fkey] = $mybox_field['name'];
				}
			}
		}
		$taxonomies = get_object_taxonomies($this->post_type, 'objects');
		if (!empty($taxonomies)) {
			foreach ($taxonomies as $taxonomy) {
				$columns[$taxonomy->name] = $taxonomy->label;
			}
		}
		$rel_list = get_option(str_replace("-", "_", $this->textdomain) . '_rel_list');
		if (!empty($rel_list)) {
			foreach ($rel_list as $krel => $rel) {
				if ($rel['from'] == $this->post_type && in_array($rel['show'], Array(
					'any',
					'from'
				))) {
					$columns[$krel] = $rel['from_title'];
				} elseif ($rel['to'] == $this->post_type && in_array($rel['show'], Array(
					'any',
					'to'
				))) {
					$columns[$krel] = $rel['to_title'];
				}
			}
		}
		return $columns;
	}
	/**
	 * Get custom column values in admin list pages
	 * @since WPAS 4.0
	 *
	 * @param int $column_id
	 * @param int $post_id
	 *
	 * @return string $value
	 */
	public function custom_columns($column_id, $post_id) {
		if (taxonomy_exists($column_id) == true) {
			$terms = get_the_terms($post_id, $column_id);
			$ret = array();
			if (!empty($terms)) {
				foreach ($terms as $term) {
					$url = add_query_arg(array(
						'post_type' => $this->post_type,
						'term' => $term->slug,
						'taxonomy' => $column_id
					) , admin_url('edit.php'));
					$a_class = preg_replace('/^emd_/', '', $this->post_type);
					$ret[] = sprintf('<a href="%s"  class="' . $a_class . '-tax ' . $term->slug . '">%s</a>', $url, $term->name);
				}
			}
			echo implode(', ', $ret);
			return;
		}
		$rel_list = get_option(str_replace("-", "_", $this->textdomain) . '_rel_list');
		if (!empty($rel_list) && !empty($rel_list[$column_id])) {
			$rel_arr = $rel_list[$column_id];
			if ($rel_arr['from'] == $this->post_type) {
				$other_ptype = $rel_arr['to'];
			} elseif ($rel_arr['to'] == $this->post_type) {
				$other_ptype = $rel_arr['from'];
			}
			$column_id = str_replace('rel_', '', $column_id);
			if (function_exists('p2p_type') && p2p_type($column_id)) {
				$rel_args = apply_filters('emd_ext_p2p_add_query_vars', array(
					'posts_per_page' => - 1
				) , Array(
					$other_ptype
				));
				$connected = p2p_type($column_id)->get_connected($post_id, $rel_args);
				$ptype_obj = get_post_type_object($this->post_type);
				$edit_cap = $ptype_obj->cap->edit_posts;
				$ret = array();
				if (empty($connected->posts)) return '&ndash;';
				foreach ($connected->posts as $myrelpost) {
					$rel_title = get_the_title($myrelpost->ID);
					$rel_title = apply_filters('emd_ext_p2p_connect_title', $rel_title, $myrelpost, '');
					$url = get_permalink($myrelpost->ID);
					$url = apply_filters('emd_ext_connected_ptype_url', $url, $myrelpost, $edit_cap);
					$ret[] = sprintf('<a href="%s" title="%s" target="_blank">%s</a>', $url, $rel_title, $rel_title);
				}
				echo implode(', ', $ret);
				return;
			}
		}
		$value = get_post_meta($post_id, $column_id, true);
		$type = "";
		foreach ($this->boxes as $mybox) {
			foreach ($mybox['fields'] as $fkey => $mybox_field) {
				if ($fkey == $column_id) {
					$type = $mybox_field['type'];
					break;
				}
			}
		}
		switch ($type) {
			case 'plupload_image':
			case 'image':
			case 'thickbox_image':
				$image_list = emd_mb_meta($column_id, 'type=image');
				$value = "";
				if (!empty($image_list)) {
					$myimage = current($image_list);
					$value = "<img style='max-width:100%;height:auto;' src='" . $myimage['url'] . "' >";
				}
			break;
			case 'user':
			case 'user-adv':
				$user_id = emd_mb_meta($column_id);
				if (!empty($user_id)) {
					$user_info = get_userdata($user_id);
					$value = $user_info->display_name;
				}
			break;
			case 'file':
				$file_list = emd_mb_meta($column_id, 'type=file');
				if (!empty($file_list)) {
					$value = "";
					foreach ($file_list as $myfile) {
						$fsrc = wp_mime_type_icon($myfile['ID']);
						$value.= "<a href='" . $myfile['url'] . "' target='_blank'><img src='" . $fsrc . "' title='" . $myfile['name'] . "' width='20' /></a>";
					}
				}
			break;
			case 'radio':
			case 'checkbox_list':
			case 'select':
			case 'select_advanced':
				$value = emd_get_attr_val(str_replace("-", "_", $this->textdomain) , $post_id, $this->post_type, $column_id);
			break;
			case 'checkbox':
				if ($value == 1) {
					$value = '<span class="dashicons dashicons-yes"></span>';
				} elseif ($value == 0) {
					$value = '<span class="dashicons dashicons-no-alt"></span>';
				}
			break;
			case 'rating':
				$value = apply_filters('emd_get_rating_value', $value, Array(
					'meta' => $column_id
				) , $post_id);
			break;
		}
		if (is_array($value)) {
			$value = "<div class='clonelink'>" . implode("</div><div class='clonelink'>", $value) . "</div>";
		}
		echo $value;
	}
	/**
	 * Register post type and taxonomies and set initial values for taxs
	 *
	 * @since WPAS 4.0
	 *
	 */
	public static function register() {
		$labels = array(
			'name' => __('Tickets', 'wp-ticket-com') ,
			'singular_name' => __('Ticket', 'wp-ticket-com') ,
			'add_new' => __('Add New', 'wp-ticket-com') ,
			'add_new_item' => __('Add New Ticket', 'wp-ticket-com') ,
			'edit_item' => __('Edit Ticket', 'wp-ticket-com') ,
			'new_item' => __('New Ticket', 'wp-ticket-com') ,
			'all_items' => __('All Tickets', 'wp-ticket-com') ,
			'view_item' => __('View Ticket', 'wp-ticket-com') ,
			'search_items' => __('Search Tickets', 'wp-ticket-com') ,
			'not_found' => __('No Tickets Found', 'wp-ticket-com') ,
			'not_found_in_trash' => __('No Tickets Found In Trash', 'wp-ticket-com') ,
			'menu_name' => __('Tickets', 'wp-ticket-com') ,
		);
		$ent_map_list = get_option('wp_ticket_com_ent_map_list', Array());
		if (!empty($ent_map_list['emd_ticket']['rewrite'])) {
			$rewrite = $ent_map_list['emd_ticket']['rewrite'];
		} else {
			$rewrite = 'tickets';
		}
		$supports = Array(
			'author',
			'custom-fields',
			'comments'
		);
		if (empty($ent_map_list['emd_ticket']['attrs']['blt_title']) || $ent_map_list['emd_ticket']['attrs']['blt_title'] != 'hide') {
			$supports[] = 'title';
		}
		if (empty($ent_map_list['emd_ticket']['attrs']['blt_content']) || $ent_map_list['emd_ticket']['attrs']['blt_content'] != 'hide') {
			$supports[] = 'editor';
		}
		register_post_type('emd_ticket', array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'description' => __('A tickets represents a help request.', 'wp-ticket-com') ,
			'show_in_menu' => true,
			'menu_position' => 8,
			'has_archive' => true,
			'exclude_from_search' => false,
			'rewrite' => array(
				'slug' => $rewrite
			) ,
			'can_export' => true,
			'show_in_rest' => false,
			'hierarchical' => false,
			'menu_icon' => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE2LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiDQoJIHdpZHRoPSI1MTJweCIgaGVpZ2h0PSI1MTJweCIgdmlld0JveD0iMCAwIDUxMiA1MTIiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDUxMiA1MTI7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxwYXRoIGZpbGw9IiNENzY0NEMiIGQ9Ik00NjMuMjAxLDQ3LjczMWMtMTAuMzg4LTEwLjM4OC0yNC4xOTktMTYuMTA5LTM4Ljg5MS0xNi4xMDlzLTI4LjUwMyw1LjcyMS0zOC44OSwxNi4xMDhsLTY0LjI3Myw2NC4yNzJINDUuOTQNCgljLTcuMzE5LDAtMTMuMjUxLDUuOTM1LTEzLjI1MSwxMy4yNTJ2MjQyLjQ4YzAsNy4zMiw1LjkzMiwxMy4yNTIsMTMuMjUxLDEzLjI1MmgyMDIuNjczdjg2LjEzOGMwLDUuMzU5LDMuMjI4LDEwLjE5Miw4LjE4MSwxMi4yNDQNCgljMS42MzksMC42NzgsMy4zNiwxLjAwOSw1LjA2NywxLjAwOWMzLjQ0NywwLDYuODM4LTEuMzQ4LDkuMzczLTMuODgzbDk2LjE3MS05NS41MDhoNTYuOTc2YzcuMzE5LDAsMTMuMjUyLTUuOTMyLDEzLjI1Mi0xMy4yNTINCglWMTUxLjA4MmwyNS41NjgtMjUuNTY4YzEwLjM4OC0xMC4zODgsMTYuMTA5LTI0LjIsMTYuMTA5LTM4Ljg5MUM0NzkuMzExLDcxLjkzLDQ3My41ODksNTguMTE5LDQ2My4yMDEsNDcuNzMxeiBNMjc1LjY4MSwyNDQuNTM2DQoJYy0yLjQ3MiwwLjUwNy01LjAzMS0wLjI2LTYuODE0LTIuMDQ0Yy0xLjc4NC0xLjc4NC0yLjU1Mi00LjM0My0yLjA0NC02LjgxNGw4LjE3OS0zOS44MzdsNDAuNTE3LDQwLjUxN0wyNzUuNjgxLDI0NC41MzZ6DQoJIE0zMzAuMTc3LDIyMy42MDhsLTQyLjQyNy00Mi40MjdsOTUuMTktOTUuMTlsNDIuNDI3LDQyLjQyN0wzMzAuMTc3LDIyMy42MDh6IE00NDUuMTksMTA4LjU5NGwtNi4xMTksNi4xMmwtNDIuNDI3LTQyLjQyNw0KCWw2LjExOS02LjExOWMxMS43MTYtMTEuNzE2LDMwLjcxMS0xMS43MTYsNDIuNDI3LDBDNDU2LjkwNiw3Ny44ODMsNDU2LjkwNiw5Ni44NzgsNDQ1LjE5LDEwOC41OTR6Ii8+DQo8L3N2Zz4NCg==',
			'map_meta_cap' => 'true',
			'taxonomies' => array() ,
			'capability_type' => 'emd_ticket',
			'supports' => $supports,
		));
		$ticket_status_nohr_labels = array(
			'name' => __('Statuses', 'wp-ticket-com') ,
			'singular_name' => __('Status', 'wp-ticket-com') ,
			'search_items' => __('Search Statuses', 'wp-ticket-com') ,
			'popular_items' => __('Popular Statuses', 'wp-ticket-com') ,
			'all_items' => __('All', 'wp-ticket-com') ,
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __('Edit Status', 'wp-ticket-com') ,
			'update_item' => __('Update Status', 'wp-ticket-com') ,
			'add_new_item' => __('Add New Status', 'wp-ticket-com') ,
			'new_item_name' => __('Add New Status Name', 'wp-ticket-com') ,
			'separate_items_with_commas' => __('Seperate Statuses with commas', 'wp-ticket-com') ,
			'add_or_remove_items' => __('Add or Remove Statuses', 'wp-ticket-com') ,
			'choose_from_most_used' => __('Choose from the most used Statuses', 'wp-ticket-com') ,
			'menu_name' => __('Statuses', 'wp-ticket-com') ,
		);
		$tax_settings = get_option('wp_ticket_com_tax_settings', Array());
		if (empty($tax_settings['ticket_status']['hide']) || (!empty($tax_settings['ticket_status']['hide']) && $tax_settings['ticket_status']['hide'] != 'hide')) {
			if (!empty($tax_settings['ticket_status']['rewrite'])) {
				$rewrite = $tax_settings['ticket_status']['rewrite'];
			} else {
				$rewrite = 'ticket_status';
			}
			register_taxonomy('ticket_status', array(
				'emd_ticket'
			) , array(
				'hierarchical' => false,
				'labels' => $ticket_status_nohr_labels,
				'public' => true,
				'show_ui' => true,
				'show_in_nav_menus' => true,
				'show_in_menu' => true,
				'show_tagcloud' => true,
				'update_count_callback' => '_update_post_term_count',
				'query_var' => true,
				'rewrite' => array(
					'slug' => $rewrite,
				) ,
				'capabilities' => array(
					'manage_terms' => 'manage_ticket_status',
					'edit_terms' => 'edit_ticket_status',
					'delete_terms' => 'delete_ticket_status',
					'assign_terms' => 'assign_ticket_status'
				) ,
			));
		}
		$ticket_topic_nohr_labels = array(
			'name' => __('Topics', 'wp-ticket-com') ,
			'singular_name' => __('Topic', 'wp-ticket-com') ,
			'search_items' => __('Search Topics', 'wp-ticket-com') ,
			'popular_items' => __('Popular Topics', 'wp-ticket-com') ,
			'all_items' => __('All', 'wp-ticket-com') ,
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __('Edit Topic', 'wp-ticket-com') ,
			'update_item' => __('Update Topic', 'wp-ticket-com') ,
			'add_new_item' => __('Add New Topic', 'wp-ticket-com') ,
			'new_item_name' => __('Add New Topic Name', 'wp-ticket-com') ,
			'separate_items_with_commas' => __('Seperate Topics with commas', 'wp-ticket-com') ,
			'add_or_remove_items' => __('Add or Remove Topics', 'wp-ticket-com') ,
			'choose_from_most_used' => __('Choose from the most used Topics', 'wp-ticket-com') ,
			'menu_name' => __('Topics', 'wp-ticket-com') ,
		);
		$tax_settings = get_option('wp_ticket_com_tax_settings', Array());
		if (empty($tax_settings['ticket_topic']['hide']) || (!empty($tax_settings['ticket_topic']['hide']) && $tax_settings['ticket_topic']['hide'] != 'hide')) {
			if (!empty($tax_settings['ticket_topic']['rewrite'])) {
				$rewrite = $tax_settings['ticket_topic']['rewrite'];
			} else {
				$rewrite = 'ticket_topic';
			}
			register_taxonomy('ticket_topic', array(
				'emd_ticket'
			) , array(
				'hierarchical' => false,
				'labels' => $ticket_topic_nohr_labels,
				'public' => true,
				'show_ui' => true,
				'show_in_nav_menus' => true,
				'show_in_menu' => true,
				'show_tagcloud' => true,
				'update_count_callback' => '_update_post_term_count',
				'query_var' => true,
				'rewrite' => array(
					'slug' => $rewrite,
				) ,
				'capabilities' => array(
					'manage_terms' => 'manage_ticket_topic',
					'edit_terms' => 'edit_ticket_topic',
					'delete_terms' => 'delete_ticket_topic',
					'assign_terms' => 'assign_ticket_topic'
				) ,
			));
		}
		$ticket_priority_nohr_labels = array(
			'name' => __('Priorities', 'wp-ticket-com') ,
			'singular_name' => __('Priority', 'wp-ticket-com') ,
			'search_items' => __('Search Priorities', 'wp-ticket-com') ,
			'popular_items' => __('Popular Priorities', 'wp-ticket-com') ,
			'all_items' => __('All', 'wp-ticket-com') ,
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __('Edit Priority', 'wp-ticket-com') ,
			'update_item' => __('Update Priority', 'wp-ticket-com') ,
			'add_new_item' => __('Add New Priority', 'wp-ticket-com') ,
			'new_item_name' => __('Add New Priority Name', 'wp-ticket-com') ,
			'separate_items_with_commas' => __('Seperate Priorities with commas', 'wp-ticket-com') ,
			'add_or_remove_items' => __('Add or Remove Priorities', 'wp-ticket-com') ,
			'choose_from_most_used' => __('Choose from the most used Priorities', 'wp-ticket-com') ,
			'menu_name' => __('Priorities', 'wp-ticket-com') ,
		);
		$tax_settings = get_option('wp_ticket_com_tax_settings', Array());
		if (empty($tax_settings['ticket_priority']['hide']) || (!empty($tax_settings['ticket_priority']['hide']) && $tax_settings['ticket_priority']['hide'] != 'hide')) {
			if (!empty($tax_settings['ticket_priority']['rewrite'])) {
				$rewrite = $tax_settings['ticket_priority']['rewrite'];
			} else {
				$rewrite = 'ticket_priority';
			}
			register_taxonomy('ticket_priority', array(
				'emd_ticket'
			) , array(
				'hierarchical' => false,
				'labels' => $ticket_priority_nohr_labels,
				'public' => true,
				'show_ui' => true,
				'show_in_nav_menus' => true,
				'show_in_menu' => true,
				'show_tagcloud' => true,
				'update_count_callback' => '_update_post_term_count',
				'query_var' => true,
				'rewrite' => array(
					'slug' => $rewrite,
				) ,
				'capabilities' => array(
					'manage_terms' => 'manage_ticket_priority',
					'edit_terms' => 'edit_ticket_priority',
					'delete_terms' => 'delete_ticket_priority',
					'assign_terms' => 'assign_ticket_priority'
				) ,
			));
		}
		if (!get_option('wp_ticket_com_emd_ticket_terms_init')) {
			$set_tax_terms = Array(
				Array(
					'name' => __('Open', 'wp-ticket-com') ,
					'slug' => sanitize_title('Open') ,
					'desc' => __('This ticket is in the initial state, ready for the assignee to start work on it.', 'wp-ticket-com')
				) ,
				Array(
					'name' => __('In Progress', 'wp-ticket-com') ,
					'slug' => sanitize_title('In Progress') ,
					'desc' => __('This ticket is being actively worked on at the moment.', 'wp-ticket-com')
				) ,
				Array(
					'name' => __('Closed', 'wp-ticket-com') ,
					'slug' => sanitize_title('Closed') ,
					'desc' => __('This ticket is complete.', 'wp-ticket-com')
				)
			);
			self::set_taxonomy_init($set_tax_terms, 'ticket_status');
			$set_tax_terms = Array(
				Array(
					'name' => __('Feature request', 'wp-ticket-com') ,
					'slug' => sanitize_title('Feature request')
				) ,
				Array(
					'name' => __('Task', 'wp-ticket-com') ,
					'slug' => sanitize_title('Task')
				) ,
				Array(
					'name' => __('Bug', 'wp-ticket-com') ,
					'slug' => sanitize_title('Bug')
				) ,
				Array(
					'name' => __('Uncategorized', 'wp-ticket-com') ,
					'slug' => sanitize_title('Uncategorized')
				) ,
				Array(
					'name' => __('Order', 'wp-ticket-com') ,
					'slug' => sanitize_title('Order')
				) ,
				Array(
					'name' => __('Presales', 'wp-ticket-com') ,
					'slug' => sanitize_title('Presales')
				)
			);
			self::set_taxonomy_init($set_tax_terms, 'ticket_topic');
			$set_tax_terms = Array(
				Array(
					'name' => __('Critical', 'wp-ticket-com') ,
					'slug' => sanitize_title('Critical') ,
					'desc' => __('A problem or issue impacting a significant group of customers or any mission critical issue affecting a single customer.', 'wp-ticket-com')
				) ,
				Array(
					'name' => __('Major', 'wp-ticket-com') ,
					'slug' => sanitize_title('Major') ,
					'desc' => __('Non critical but significant issue affecting a single user or an issue that is degrading the performance and reliability of supported services, however, the services are still operational. Support issues that could escalate to Critical if not addressed quickly.', 'wp-ticket-com')
				) ,
				Array(
					'name' => __('Normal', 'wp-ticket-com') ,
					'slug' => sanitize_title('Normal') ,
					'desc' => __('Routine support requests that impact a single user or non-critical software or hardware error.', 'wp-ticket-com')
				) ,
				Array(
					'name' => __('Minor', 'wp-ticket-com') ,
					'slug' => sanitize_title('Minor') ,
					'desc' => __('Work that has been scheduled in advance with the customer, a minor service issue, or general inquiry.', 'wp-ticket-com')
				) ,
				Array(
					'name' => __('Uncategorized', 'wp-ticket-com') ,
					'slug' => sanitize_title('Uncategorized') ,
					'desc' => __('No priority assigned', 'wp-ticket-com')
				)
			);
			self::set_taxonomy_init($set_tax_terms, 'ticket_priority');
			update_option('wp_ticket_com_emd_ticket_terms_init', true);
		}
	}
	/**
	 * Set metabox fields,labels,filters, comments, relationships if exists
	 *
	 * @since WPAS 4.0
	 *
	 */
	public function set_filters() {
		do_action('emd_ext_class_init', $this);
		$search_args = Array();
		$filter_args = Array();
		$this->sing_label = __('Ticket', 'wp-ticket-com');
		$this->plural_label = __('Tickets', 'wp-ticket-com');
		$this->menu_entity = 'emd_ticket';
		$this->boxes['emd_ticket_info_emd_ticket_0'] = array(
			'id' => 'emd_ticket_info_emd_ticket_0',
			'title' => __('Ticket Info', 'wp-ticket-com') ,
			'app_name' => 'wp_ticket_com',
			'pages' => array(
				'emd_ticket'
			) ,
			'context' => 'normal',
		);
		list($search_args, $filter_args) = $this->set_args_boxes();
		if (!post_type_exists($this->post_type) || in_array($this->post_type, Array(
			'post',
			'page'
		))) {
			self::register();
		}
		$ent_map_list = get_option(str_replace('-', '_', $this->textdomain) . '_ent_map_list');
	}
	/**
	 * Initialize metaboxes
	 * @since WPAS 4.5
	 *
	 */
	public function set_metabox() {
		if (class_exists('EMD_Meta_Box') && is_array($this->boxes)) {
			foreach ($this->boxes as $meta_box) {
				new EMD_Meta_Box($meta_box);
			}
		}
	}
	/**
	 * Add operations and add new submenu hook
	 * @since WPAS 4.4
	 */
	public function add_menu_link() {
		add_submenu_page(null, __('Operations', 'wp-ticket-com') , __('Operations', 'wp-ticket-com') , 'manage_operations_emd_tickets', 'operations_emd_ticket', array(
			$this,
			'get_operations'
		));
	}
	/**
	 * Display operations page
	 * @since WPAS 4.0
	 */
	public function get_operations() {
		if (current_user_can('manage_operations_emd_tickets')) {
			$myapp = str_replace("-", "_", $this->textdomain);
			do_action('emd_operations_entity', $this->post_type, $this->plural_label, $this->sing_label, $myapp, $this->menu_entity);
		}
	}
	public function change_title_text($title) {
		$screen = get_current_screen();
		if ($this->post_type == $screen->post_type) {
			$title = __('Enter Subject here', 'wp-ticket-com');
		}
		return $title;
	}
}
new Emd_Ticket;
