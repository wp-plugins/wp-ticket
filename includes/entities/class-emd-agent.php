<?php
/**
 * Entity Class
 *
 * @package WP_TICKET_COM
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Emd_Agent Class
 * @since WPAS 4.0
 */
class Emd_Agent extends Emd_Entity {
	protected $post_type = 'emd_agent';
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
		add_action('admin_init', array(
			$this,
			'include_tabs_acc'
		));
		add_action('save_post', array(
			$this,
			'change_title'
		) , 99, 2);
		add_filter('post_updated_messages', array(
			$this,
			'updated_messages'
		));
		add_action('manage_emd_agent_posts_custom_column', array(
			$this,
			'custom_columns'
		) , 10, 2);
		add_filter('manage_emd_agent_posts_columns', array(
			$this,
			'column_headers'
		));
		add_filter('p2p_admin_box_show', array(
			$this,
			'show_p2p_admin_box'
		) , 10, 3);
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
	public function show_p2p_admin_box($show, $directed, $post) {
		$rel_name = "limitby_author_backend_" . $this->post_type . "s";
		if ((in_array($this->post_type, $directed->side['from']->query_vars['post_type']) || in_array($this->post_type, $directed->side['to']->query_vars['post_type'])) && (!(is_multisite() && is_super_admin()) && current_user_can($rel_name))) {
			return false;
		}
		return $show;
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
			'name' => __('Agents', 'wp-ticket-com') ,
			'singular_name' => __('Agent', 'wp-ticket-com') ,
			'add_new' => __('Add New', 'wp-ticket-com') ,
			'add_new_item' => __('Add New Agent', 'wp-ticket-com') ,
			'edit_item' => __('Edit Agent', 'wp-ticket-com') ,
			'new_item' => __('New Agent', 'wp-ticket-com') ,
			'all_items' => __('Agents', 'wp-ticket-com') ,
			'view_item' => __('View Agent', 'wp-ticket-com') ,
			'search_items' => __('Search Agents', 'wp-ticket-com') ,
			'not_found' => __('No Agents Found', 'wp-ticket-com') ,
			'not_found_in_trash' => __('No Agents Found In Trash', 'wp-ticket-com') ,
			'menu_name' => __('Agents', 'wp-ticket-com') ,
		);
		$ent_map_list = get_option('wp_ticket_com_ent_map_list', Array());
		if (!empty($ent_map_list['emd_agent']['rewrite'])) {
			$rewrite = $ent_map_list['emd_agent']['rewrite'];
		} else {
			$rewrite = 'agents';
		}
		$supports = Array(
			'custom-fields',
		);
		register_post_type('emd_agent', array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'description' => __('Agents are employees from the company that addresses issues to the customer\'s satisfaction.', 'wp-ticket-com') ,
			'show_in_menu' => "edit.php?post_type=emd_ticket",
			'menu_position' => null,
			'has_archive' => true,
			'exclude_from_search' => true,
			'rewrite' => array(
				'slug' => $rewrite
			) ,
			'can_export' => true,
			'show_in_rest' => false,
			'hierarchical' => false,
			'map_meta_cap' => 'true',
			'taxonomies' => array() ,
			'capability_type' => 'emd_agent',
			'supports' => $supports,
		));
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
		$this->sing_label = __('Agent', 'wp-ticket-com');
		$this->plural_label = __('Agents', 'wp-ticket-com');
		$this->menu_entity = 'emd_ticket';
		$this->boxes['tab_emd_agent_0'] = array(
			'id' => 'tab_emd_agent_0',
			'title' => __('Agent Info', 'wp-ticket-com') ,
			'app_name' => 'wp_ticket_com',
			'pages' => array(
				'emd_agent'
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
		if (!function_exists('p2p_register_connection_type')) {
			return;
		}
		$rel_list = get_option(str_replace('-', '_', $this->textdomain) . '_rel_list');
		if (empty($ent_map_list['emd_agent']['hide_rels']['rel_tickets_assigned_to']) || $ent_map_list['emd_agent']['hide_rels']['rel_tickets_assigned_to'] != 'hide') {
			$rel_fields = Array();
			p2p_register_connection_type(array(
				'name' => 'tickets_assigned_to',
				'from' => 'emd_agent',
				'to' => 'emd_ticket',
				'sortable' => 'any',
				'reciprocal' => false,
				'cardinality' => 'one-to-many',
				'title' => array(
					'from' => __('Tickets Assigned', 'wp-ticket-com') ,
					'to' => __('Assignee', 'wp-ticket-com')
				) ,
				'from_labels' => array(
					'singular_name' => __('Agent', 'wp-ticket-com') ,
					'search_items' => __('Search Agents', 'wp-ticket-com') ,
					'not_found' => __('No Agents found.', 'wp-ticket-com') ,
				) ,
				'to_labels' => array(
					'singular_name' => __('Ticket', 'wp-ticket-com') ,
					'search_items' => __('Search Tickets', 'wp-ticket-com') ,
					'not_found' => __('No Tickets found.', 'wp-ticket-com') ,
				) ,
				'fields' => $rel_fields,
				'admin_box' => 'to',
			));
		}
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
		add_submenu_page(null, __('Operations', 'wp-ticket-com') , __('Operations', 'wp-ticket-com') , 'manage_operations_emd_agents', 'operations_emd_agent', array(
			$this,
			'get_operations'
		));
	}
	/**
	 * Display operations page
	 * @since WPAS 4.0
	 */
	public function get_operations() {
		if (current_user_can('manage_operations_emd_agents')) {
			$myapp = str_replace("-", "_", $this->textdomain);
			do_action('emd_operations_entity', $this->post_type, $this->plural_label, $this->sing_label, $myapp, $this->menu_entity);
		}
	}
}
new Emd_Agent;
