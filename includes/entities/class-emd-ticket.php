<?php
/**
 * Entity Class
 *
 * @package WP_TICKET_COM
 * @version 1.1
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
	private $boxes = Array();
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
		));
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
				))) {
					$columns[$fkey] = $mybox_field['name'];
				}
			}
		}
		$args = array(
			'_builtin' => false,
			'object_type' => Array(
				$this->post_type
			)
		);
		$taxonomies = get_taxonomies($args, 'objects');
		if (!empty($taxonomies)) {
			foreach ($taxonomies as $taxonomy) {
				$columns[$taxonomy->name] = $taxonomy->label;
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
					$ret[] = sprintf('<a href="%s">%s</a>', $url, $term->name);
				}
			}
			echo implode(', ', $ret);
			return;
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
				$image_list = rwmb_meta($column_id, 'type=image');
				if (!empty($image_list)) {
					$value = "";
					foreach ($image_list as $myimage) {
						$value.= "<img src='" . $myimage['url'] . "' >";
					}
				}
			break;
			case 'user':
			case 'user-adv':
				$user_id = rwmb_meta($column_id);
				if (!empty($user_id)) {
					$user_info = get_userdata($user_id);
					$value = $user_info->display_name;
				}
			break;
			case 'file':
				$file_list = rwmb_meta($column_id, 'type=file');
				if (!empty($file_list)) {
					$value = "";
					foreach ($file_list as $myfile) {
						$value.= "<a href='" . $myfile['url'] . "' target='_blank'>" . $myfile['name'] . "</a>, ";
					}
					$value = rtrim($value, ", ");
				}
			break;
			case 'checkbox_list':
				$checkbox_list = rwmb_meta($column_id, 'type=checkbox_list');
				if (!empty($checkbox_list)) {
					$value = implode(', ', $checkbox_list);
				}
			break;
			case 'select':
			case 'select_advanced':
				$select_list = get_post_meta($post_id, $column_id, false);
				if (!empty($select_list)) {
					$value = implode(', ', $select_list);
				}
			break;
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
		register_post_type('emd_ticket', array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'description' => __('A tickets represents a help request.', 'wp-ticket-com') ,
			'show_in_menu' => true,
			'menu_position' => 6,
			'has_archive' => true,
			'exclude_from_search' => false,
			'rewrite' => array(
				'slug' => 'tickets'
			) ,
			'can_export' => true,
			'hierarchical' => false,
			'menu_icon' => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE2LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiDQoJIHdpZHRoPSI1MTJweCIgaGVpZ2h0PSI1MTJweCIgdmlld0JveD0iMCAwIDUxMiA1MTIiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDUxMiA1MTI7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxwYXRoIGZpbGw9IiNENzY0NEMiIGQ9Ik00NjMuMjAxLDQ3LjczMWMtMTAuMzg4LTEwLjM4OC0yNC4xOTktMTYuMTA5LTM4Ljg5MS0xNi4xMDlzLTI4LjUwMyw1LjcyMS0zOC44OSwxNi4xMDhsLTY0LjI3Myw2NC4yNzJINDUuOTQNCgljLTcuMzE5LDAtMTMuMjUxLDUuOTM1LTEzLjI1MSwxMy4yNTJ2MjQyLjQ4YzAsNy4zMiw1LjkzMiwxMy4yNTIsMTMuMjUxLDEzLjI1MmgyMDIuNjczdjg2LjEzOGMwLDUuMzU5LDMuMjI4LDEwLjE5Miw4LjE4MSwxMi4yNDQNCgljMS42MzksMC42NzgsMy4zNiwxLjAwOSw1LjA2NywxLjAwOWMzLjQ0NywwLDYuODM4LTEuMzQ4LDkuMzczLTMuODgzbDk2LjE3MS05NS41MDhoNTYuOTc2YzcuMzE5LDAsMTMuMjUyLTUuOTMyLDEzLjI1Mi0xMy4yNTINCglWMTUxLjA4MmwyNS41NjgtMjUuNTY4YzEwLjM4OC0xMC4zODgsMTYuMTA5LTI0LjIsMTYuMTA5LTM4Ljg5MUM0NzkuMzExLDcxLjkzLDQ3My41ODksNTguMTE5LDQ2My4yMDEsNDcuNzMxeiBNMjc1LjY4MSwyNDQuNTM2DQoJYy0yLjQ3MiwwLjUwNy01LjAzMS0wLjI2LTYuODE0LTIuMDQ0Yy0xLjc4NC0xLjc4NC0yLjU1Mi00LjM0My0yLjA0NC02LjgxNGw4LjE3OS0zOS44MzdsNDAuNTE3LDQwLjUxN0wyNzUuNjgxLDI0NC41MzZ6DQoJIE0zMzAuMTc3LDIyMy42MDhsLTQyLjQyNy00Mi40MjdsOTUuMTktOTUuMTlsNDIuNDI3LDQyLjQyN0wzMzAuMTc3LDIyMy42MDh6IE00NDUuMTksMTA4LjU5NGwtNi4xMTksNi4xMmwtNDIuNDI3LTQyLjQyNw0KCWw2LjExOS02LjExOWMxMS43MTYtMTEuNzE2LDMwLjcxMS0xMS43MTYsNDIuNDI3LDBDNDU2LjkwNiw3Ny44ODMsNDU2LjkwNiw5Ni44NzgsNDQ1LjE5LDEwOC41OTR6Ii8+DQo8L3N2Zz4NCg==',
			'map_meta_cap' => 'true',
			'taxonomies' => array() ,
			'capability_type' => 'emd_ticket',
			'supports' => array(
				'title',
				'editor',
				'comments'
			)
		));
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
		register_taxonomy('ticket_topic', array(
			'emd_ticket'
		) , array(
			'hierarchical' => false,
			'labels' => $ticket_topic_nohr_labels,
			'public' => true,
			'show_ui' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud' => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var' => true,
			'rewrite' => array(
				'slug' => 'ticket_topic'
			) ,
			'capabilities' => array(
				'manage_terms' => 'manage_ticket_topic',
				'edit_terms' => 'edit_ticket_topic',
				'delete_terms' => 'delete_ticket_topic',
				'assign_terms' => 'assign_ticket_topic'
			) ,
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
		register_taxonomy('ticket_status', array(
			'emd_ticket'
		) , array(
			'hierarchical' => false,
			'labels' => $ticket_status_nohr_labels,
			'public' => true,
			'show_ui' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud' => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var' => true,
			'rewrite' => array(
				'slug' => 'ticket_status'
			) ,
			'capabilities' => array(
				'manage_terms' => 'manage_ticket_status',
				'edit_terms' => 'edit_ticket_status',
				'delete_terms' => 'delete_ticket_status',
				'assign_terms' => 'assign_ticket_status'
			) ,
		));
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
		register_taxonomy('ticket_priority', array(
			'emd_ticket'
		) , array(
			'hierarchical' => false,
			'labels' => $ticket_priority_nohr_labels,
			'public' => true,
			'show_ui' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud' => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var' => true,
			'rewrite' => array(
				'slug' => 'ticket_priority'
			) ,
			'capabilities' => array(
				'manage_terms' => 'manage_ticket_priority',
				'edit_terms' => 'edit_ticket_priority',
				'delete_terms' => 'delete_ticket_priority',
				'assign_terms' => 'assign_ticket_priority'
			) ,
		));
		if (!get_option('wp_ticket_com_emd_ticket_terms_init')) {
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
				)
			);
			self::set_taxonomy_init($set_tax_terms, 'ticket_topic');
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
					'name' => __('Reopened', 'wp-ticket-com') ,
					'slug' => sanitize_title('Reopened') ,
					'desc' => __('This ticket was once \'Resolved\' or \'Closed\', but is now being re-visited, e.g. an ticket with a Resolution of \'Cannot Reproduce\' is Reopened when more information becomes available and the ticket becomes reproducible. The next ticket states are either marked In Progress, Resolved or Closed.', 'wp-ticket-com')
				) ,
				Array(
					'name' => __('Closed', 'wp-ticket-com') ,
					'slug' => sanitize_title('Closed') ,
					'desc' => __('This ticket is complete.', 'wp-ticket-com')
				) ,
				Array(
					'name' => __('Resolved - Fixed', 'wp-ticket-com') ,
					'slug' => sanitize_title('Resolved - Fixed') ,
					'desc' => __('A fix for this ticket has been implemented.', 'wp-ticket-com')
				) ,
				Array(
					'name' => __('Resolved - Won\'t Fix', 'wp-ticket-com') ,
					'slug' => sanitize_title('Resolved - Won\'t Fix') ,
					'desc' => __('This ticket will not be fixed, e.g. it may no longer be relevant.', 'wp-ticket-com')
				) ,
				Array(
					'name' => __('Resolved - Duplicate', 'wp-ticket-com') ,
					'slug' => sanitize_title('Resolved - Duplicate') ,
					'desc' => __('This ticket is a duplicate of an existing ticket. It is recommended you create a link to the duplicated ticket by creating a related ticket connection.', 'wp-ticket-com')
				) ,
				Array(
					'name' => __('Resolved - Incomplete', 'wp-ticket-com') ,
					'slug' => sanitize_title('Resolved - Incomplete') ,
					'desc' => __('There is not enough information to work on this ticket.', 'wp-ticket-com')
				) ,
				Array(
					'name' => __('Resolved - CNR', 'wp-ticket-com') ,
					'slug' => sanitize_title('Resolved - CNR') ,
					'desc' => __('This ticket could not be reproduced at this time, or not enough information was available to reproduce the ticket issue. If more information becomes available, reopen the ticket.', 'wp-ticket-com')
				)
			);
			self::set_taxonomy_init($set_tax_terms, 'ticket_status');
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
		$filter_args = array();
		$this->sing_label = __('Ticket', 'wp-ticket-com');
		$this->plural_label = __('Tickets', 'wp-ticket-com');
		$this->boxes[] = array(
			'id' => 'emd_ticket_info_emd_ticket_0',
			'title' => __('Ticket Info', 'wp-ticket-com') ,
			'pages' => array(
				'emd_ticket'
			) ,
			'context' => 'normal',
			'fields' => array(
				'emd_ticket_id' => array(
					'name' => __('Ticket ID', 'wp-ticket-com') ,
					'id' => 'emd_ticket_id',
					'type' => 'hidden',
					'hidden_func' => 'unique_id',
					'multiple' => false,
					'desc' => __('Unique identifier for a ticket', 'wp-ticket-com') ,
					'class' => 'emd_ticket_id',
				) ,
				'emd_ticket_first_name' => array(
					'name' => __('First Name', 'wp-ticket-com') ,
					'id' => 'emd_ticket_first_name',
					'type' => 'text',
					'multiple' => false,
					'class' => 'emd_ticket_first_name',
				) ,
				'emd_ticket_last_name' => array(
					'name' => __('Last Name', 'wp-ticket-com') ,
					'id' => 'emd_ticket_last_name',
					'type' => 'text',
					'multiple' => false,
					'class' => 'emd_ticket_last_name',
				) ,
				'emd_ticket_email' => array(
					'name' => __('Email', 'wp-ticket-com') ,
					'id' => 'emd_ticket_email',
					'type' => 'text',
					'multiple' => false,
					'desc' => __('Our responses to your ticket will be sent to this email address.', 'wp-ticket-com') ,
					'class' => 'emd_ticket_email',
				) ,
				'emd_ticket_phone' => array(
					'name' => __('Phone', 'wp-ticket-com') ,
					'id' => 'emd_ticket_phone',
					'type' => 'text',
					'multiple' => false,
					'desc' => __('Please enter a phone number in case we need to contact you.', 'wp-ticket-com') ,
					'class' => 'emd_ticket_phone',
				) ,
				'emd_ticket_duedate' => array(
					'name' => __('Due', 'wp-ticket-com') ,
					'id' => 'emd_ticket_duedate',
					'type' => 'datetime',
					'multiple' => false,
					'js_options' => array(
						'dateFormat' => 'mm-dd-yy',
						'timeFormat' => 'hh:mm'
					) ,
					'desc' => __('The due date of the ticket', 'wp-ticket-com') ,
					'class' => 'emd_ticket_duedate',
				) ,
				'emd_ticket_attachment' => array(
					'name' => __('Attachments', 'wp-ticket-com') ,
					'id' => 'emd_ticket_attachment',
					'type' => 'file',
					'multiple' => false,
					'desc' => __('Attach related files to the ticket.', 'wp-ticket-com') ,
					'class' => 'emd_ticket_attachment',
				) ,
				'wpas_form_name' => array(
					'name' => __('Form Name', 'wp-ticket-com') ,
					'id' => 'wpas_form_name',
					'type' => 'hidden',
					'no_update' => 1,
					'multiple' => false,
					'std' => 'admin',
					'class' => 'wpas_form_name',
				) ,
				'wpas_form_submitted_by' => array(
					'name' => __('Form Submitted By', 'wp-ticket-com') ,
					'id' => 'wpas_form_submitted_by',
					'type' => 'hidden',
					'hidden_func' => 'user_login',
					'no_update' => 1,
					'multiple' => false,
					'class' => 'wpas_form_submitted_by',
				) ,
				'wpas_form_submitted_ip' => array(
					'name' => __('Form Submitted IP', 'wp-ticket-com') ,
					'id' => 'wpas_form_submitted_ip',
					'type' => 'hidden',
					'hidden_func' => 'user_ip',
					'no_update' => 1,
					'multiple' => false,
					'class' => 'wpas_form_submitted_ip',
				) ,
			) ,
			'validation' => array(
				'onfocusout' => false,
				'onkeyup' => false,
				'onclick' => false,
				'rules' => array(
					'emd_ticket_id' => array(
						'required' => false,
					) ,
					'emd_ticket_first_name' => array(
						'required' => true,
					) ,
					'emd_ticket_last_name' => array(
						'required' => true,
					) ,
					'emd_ticket_email' => array(
						'required' => true,
						'email' => true,
					) ,
					'emd_ticket_phone' => array(
						'required' => false,
					) ,
					'emd_ticket_duedate' => array(
						'required' => false,
					) ,
					'emd_ticket_attachment' => array(
						'required' => false,
					) ,
					'wpas_form_name' => array(
						'required' => false,
					) ,
					'wpas_form_submitted_by' => array(
						'required' => false,
					) ,
					'wpas_form_submitted_ip' => array(
						'required' => false,
					) ,
				) ,
			)
		);
		if (!post_type_exists($this->post_type) || in_array($this->post_type, Array(
			'post',
			'page'
		))) {
			self::register();
		}
		global $pagenow;
		if ('post-new.php' === $pagenow || 'post.php' === $pagenow) {
			if (class_exists('RW_Meta_Box') && is_array($this->boxes)) {
				foreach ($this->boxes as $meta_box) {
					new RW_Meta_Box($meta_box);
				}
			}
		}
	}
	/**
	 * Change content for created frontend views
	 * @since WPAS 4.0
	 * @param string $content
	 *
	 * @return string $content
	 */
	public function change_content($content) {
		global $post;
		$layout = "";
		if (get_post_type() == $this->post_type && is_single()) {
			ob_start();
			emd_get_template_part($this->textdomain, 'single', 'emd-ticket');
			$layout = ob_get_clean();
		}
		if ($layout != "") {
			$content = $layout;
		}
		return $content;
	}
}
new Emd_Ticket;
