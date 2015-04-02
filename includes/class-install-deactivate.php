<?php
/**
 * Install and Deactivate Plugin Functions
 * @package WP_TICKET_COM
 * @version 1.4
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
if (!class_exists('Wp_Ticket_Com_Install_Deactivate')):
	/**
	 * Wp_Ticket_Com_Install_Deactivate Class
	 * @since WPAS 4.0
	 */
	class Wp_Ticket_Com_Install_Deactivate {
		private $option_name;
		/**
		 * Hooks for install and deactivation and create options
		 * @since WPAS 4.0
		 */
		public function __construct() {
			$this->option_name = 'wp_ticket_com';
			$curr_version = get_option($this->option_name . '_version', 1);
			$new_version = constant(strtoupper($this->option_name) . '_VERSION');
			if (version_compare($curr_version, $new_version, '<')) {
				$this->set_options();
				update_option($this->option_name . '_version', $new_version);
			}
			register_activation_hook(WP_TICKET_COM_PLUGIN_FILE, array(
				$this,
				'install'
			));
			register_deactivation_hook(WP_TICKET_COM_PLUGIN_FILE, array(
				$this,
				'deactivate'
			));
			add_action('admin_init', array(
				$this,
				'setup_pages'
			));
			add_action('admin_notices', array(
				$this,
				'install_notice'
			));
			add_action('generate_rewrite_rules', 'emd_create_rewrite_rules');
			add_filter('query_vars', 'emd_query_vars');
			if (is_admin()) {
				$this->stax = new Emd_Single_Taxonomy('wp-ticket-com');
			}
			add_action('before_delete_post', array(
				$this,
				'delete_post_file_att'
			));
			add_filter('tiny_mce_before_init', array(
				$this,
				'tinymce_fix'
			));
		}
		/**
		 * Runs on plugin install to setup custom post types and taxonomies
		 * flushing rewrite rules, populates settings and options
		 * creates roles and assign capabilities
		 * @since WPAS 4.0
		 *
		 */
		public function install() {
			Emd_Ticket::register();
			flush_rewrite_rules();
			$this->set_roles_caps();
			$this->set_options();
		}
		/**
		 * Runs on plugin deactivate to remove options, caps and roles
		 * flushing rewrite rules
		 * @since WPAS 4.0
		 *
		 */
		public function deactivate() {
			flush_rewrite_rules();
			$this->remove_caps_roles();
			$this->reset_options();
		}
		/**
		 * Sets caps and roles
		 *
		 * @since WPAS 4.0
		 *
		 */
		public function set_roles_caps() {
			global $wp_roles;
			if (class_exists('WP_Roles')) {
				if (!isset($wp_roles)) {
					$wp_roles = new WP_Roles();
				}
			}
			if (is_object($wp_roles)) {
				$this->set_reset_caps($wp_roles, 'add');
			}
		}
		/**
		 * Removes caps and roles
		 *
		 * @since WPAS 4.0
		 *
		 */
		public function remove_caps_roles() {
			global $wp_roles;
			if (class_exists('WP_Roles')) {
				if (!isset($wp_roles)) {
					$wp_roles = new WP_Roles();
				}
			}
			if (is_object($wp_roles)) {
				$this->set_reset_caps($wp_roles, 'remove');
			}
		}
		/**
		 * Set , reset capabilities
		 *
		 * @since WPAS 4.0
		 * @param object $wp_roles
		 * @param string $type
		 *
		 */
		public function set_reset_caps($wp_roles, $type) {
			$caps['enable'] = Array(
				'edit_published_emd_tickets' => Array(
					'administrator',
					'editor',
					'author'
				) ,
				'manage_ticket_topic' => Array(
					'administrator'
				) ,
				'delete_ticket_topic' => Array(
					'administrator'
				) ,
				'edit_emd_tickets' => Array(
					'administrator',
					'editor',
					'author',
					'contributor'
				) ,
				'edit_dashboard' => Array(
					'administrator'
				) ,
				'edit_private_emd_tickets' => Array(
					'administrator',
					'editor'
				) ,
				'edit_ticket_priority' => Array(
					'administrator'
				) ,
				'delete_emd_tickets' => Array(
					'administrator',
					'editor',
					'author',
					'contributor'
				) ,
				'view_recent_tickets_dashboard' => Array(
					'administrator',
					'editor',
					'author'
				) ,
				'assign_ticket_priority' => Array(
					'administrator',
					'editor',
					'author'
				) ,
				'read_private_emd_tickets' => Array(
					'administrator',
					'editor'
				) ,
				'edit_ticket_topic' => Array(
					'administrator'
				) ,
				'edit_ticket_status' => Array(
					'administrator'
				) ,
				'assign_ticket_status' => Array(
					'administrator',
					'editor',
					'author'
				) ,
				'manage_ticket_priority' => Array(
					'administrator'
				) ,
				'delete_others_emd_tickets' => Array(
					'administrator',
					'editor'
				) ,
				'view_wp_ticket_com_dashboard' => Array(
					'administrator'
				) ,
				'publish_emd_tickets' => Array(
					'administrator',
					'editor',
					'author'
				) ,
				'delete_ticket_priority' => Array(
					'administrator'
				) ,
				'manage_ticket_status' => Array(
					'administrator'
				) ,
				'delete_ticket_status' => Array(
					'administrator'
				) ,
				'configure_recent_tickets_dashboard' => Array(
					'administrator'
				) ,
				'assign_ticket_topic' => Array(
					'administrator',
					'editor',
					'author'
				) ,
				'edit_others_emd_tickets' => Array(
					'administrator',
					'editor'
				) ,
				'delete_private_emd_tickets' => Array(
					'administrator',
					'editor'
				) ,
				'delete_published_emd_tickets' => Array(
					'administrator',
					'editor',
					'author'
				) ,
			);
			foreach ($caps as $stat => $role_caps) {
				foreach ($role_caps as $mycap => $roles) {
					foreach ($roles as $myrole) {
						if (($type == 'add' && $stat == 'enable') || ($stat == 'disable' && $type == 'remove')) {
							$wp_roles->add_cap($myrole, $mycap);
						} else if (($type == 'remove' && $stat == 'enable') || ($type == 'add' && $stat == 'disable')) {
							$wp_roles->remove_cap($myrole, $mycap);
						}
					}
				}
			}
		}
		/**
		 * Set app specific options
		 *
		 * @since WPAS 4.0
		 *
		 */
		private function set_options() {
			update_option($this->option_name . '_setup_pages', 1);
			$ent_list = Array(
				'emd_ticket' => Array(
					'label' => __('Tickets', 'wp-ticket-com') ,
					'unique_keys' => Array(
						'emd_ticket_id'
					)
				) ,
			);
			update_option($this->option_name . '_ent_list', $ent_list);
			$shc_list['app'] = 'Wp Ticket';
			$shc_list['forms']['submit_tickets'] = Array(
				'name' => 'submit_tickets',
				'page_title' => __('Open a Ticket', 'wp-ticket-com')
			);
			$shc_list['forms']['search_tickets'] = Array(
				'name' => 'search_tickets',
				'page_title' => __('Search Tickets', 'wp-ticket-com')
			);
			if (!empty($shc_list)) {
				update_option($this->option_name . '_shc_list', $shc_list);
			}
			$attr_list['emd_ticket']['emd_ticket_id'] = Array(
				'visible' => 1,
				'label' => __('Ticket ID', 'wp-ticket-com') ,
				'display_type' => 'hidden',
				'required' => 0,
				'filterable' => 1,
				'desc' => __('Unique identifier for a ticket', 'wp-ticket-com') ,
				'type' => 'char',
				'hidden_func' => 'unique_id',
				'uniqueAttr' => true,
			);
			$attr_list['emd_ticket']['emd_ticket_first_name'] = Array(
				'visible' => 1,
				'label' => __('First Name', 'wp-ticket-com') ,
				'display_type' => 'text',
				'required' => 1,
				'filterable' => 1,
				'type' => 'char',
			);
			$attr_list['emd_ticket']['emd_ticket_last_name'] = Array(
				'visible' => 1,
				'label' => __('Last Name', 'wp-ticket-com') ,
				'display_type' => 'text',
				'required' => 1,
				'filterable' => 1,
				'type' => 'char',
			);
			$attr_list['emd_ticket']['emd_ticket_email'] = Array(
				'visible' => 1,
				'label' => __('Email', 'wp-ticket-com') ,
				'display_type' => 'text',
				'required' => 1,
				'filterable' => 1,
				'desc' => __('Our responses to your ticket will be sent to this email address.', 'wp-ticket-com') ,
				'type' => 'char',
				'email' => true,
			);
			$attr_list['emd_ticket']['emd_ticket_phone'] = Array(
				'visible' => 1,
				'label' => __('Phone', 'wp-ticket-com') ,
				'display_type' => 'text',
				'required' => 0,
				'filterable' => 0,
				'desc' => __('Please enter a phone number in case we need to contact you.', 'wp-ticket-com') ,
				'type' => 'char',
			);
			$attr_list['emd_ticket']['emd_ticket_duedate'] = Array(
				'visible' => 1,
				'label' => __('Due', 'wp-ticket-com') ,
				'display_type' => 'datetime',
				'required' => 0,
				'filterable' => 1,
				'desc' => __('The due date of the ticket', 'wp-ticket-com') ,
				'type' => 'datetime',
				'dformat' => array(
					'dateFormat' => 'mm-dd-yy',
					'timeFormat' => 'hh:mm'
				) ,
				'date_format' => 'm-d-Y H:i',
				'time_format' => 'hh:mm',
			);
			$attr_list['emd_ticket']['emd_ticket_attachment'] = Array(
				'visible' => 1,
				'label' => __('Attachments', 'wp-ticket-com') ,
				'display_type' => 'file',
				'required' => 0,
				'filterable' => 0,
				'desc' => __('Attach related files to the ticket.', 'wp-ticket-com') ,
				'type' => 'char',
			);
			$attr_list['emd_ticket']['wpas_form_name'] = Array(
				'visible' => 1,
				'label' => __('Form Name', 'wp-ticket-com') ,
				'display_type' => 'hidden',
				'required' => 0,
				'filterable' => 1,
				'type' => 'char',
				'options' => array() ,
				'no_update' => 1,
				'std' => 'admin',
			);
			$attr_list['emd_ticket']['wpas_form_submitted_by'] = Array(
				'visible' => 1,
				'label' => __('Form Submitted By', 'wp-ticket-com') ,
				'display_type' => 'hidden',
				'required' => 0,
				'filterable' => 1,
				'type' => 'char',
				'options' => array() ,
				'hidden_func' => 'user_login',
				'no_update' => 1,
			);
			$attr_list['emd_ticket']['wpas_form_submitted_ip'] = Array(
				'visible' => 1,
				'label' => __('Form Submitted IP', 'wp-ticket-com') ,
				'display_type' => 'hidden',
				'required' => 0,
				'filterable' => 1,
				'type' => 'char',
				'options' => array() ,
				'hidden_func' => 'user_ip',
				'no_update' => 1,
			);
			if (!empty($attr_list)) {
				update_option($this->option_name . '_attr_list', $attr_list);
			}
			$tax_list['emd_ticket']['ticket_priority'] = Array(
				'label' => __('Priorities', 'wp-ticket-com') ,
				'default' => Array(
					__('Uncategorized', 'wp-ticket-com')
				) ,
				'type' => 'single'
			);
			$tax_list['emd_ticket']['ticket_topic'] = Array(
				'label' => __('Topics', 'wp-ticket-com') ,
				'default' => '',
				'type' => 'single'
			);
			$tax_list['emd_ticket']['ticket_status'] = Array(
				'label' => __('Statuses', 'wp-ticket-com') ,
				'default' => Array(
					__('Open', 'wp-ticket-com')
				) ,
				'type' => 'single'
			);
			if (!empty($tax_list)) {
				update_option($this->option_name . '_tax_list', $tax_list);
			}
			if (!empty($rel_list)) {
				update_option($this->option_name . '_rel_list', $rel_list);
			}
			$emd_activated_plugins = get_option('emd_activated_plugins');
			if (!$emd_activated_plugins) {
				update_option('emd_activated_plugins', Array(
					'wp-ticket-com'
				));
			} elseif (!in_array('wp-ticket-com', $emd_activated_plugins)) {
				array_push($emd_activated_plugins, 'wp-ticket-com');
				update_option('emd_activated_plugins', $emd_activated_plugins);
			}
			//conf parameters for incoming email
			$has_incoming_email = Array(
				'emd_ticket' => Array(
					'label' => 'Tickets',
					'status' => 'publish',
					'vis_submit' => 1,
					'vis_status' => 'publish',
					'tax' => 'ticket_topic',
					'subject' => 'blt_title',
					'date' => Array(
						'post_date'
					) ,
					'body' => 'emd_blt_content',
					'att' => 'emd_ticket_attachment',
					'email' => 'emd_ticket_email',
					'name' => Array(
						'emd_ticket_first_name',
						'emd_ticket_last_name',
					)
				)
			);
			update_option($this->option_name . '_has_incoming_email', $has_incoming_email);
			$emd_inc_email_apps = get_option('emd_inc_email_apps');
			$emd_inc_email_apps[$this->option_name] = $this->option_name . '_inc_email_conf';
			update_option('emd_inc_email_apps', $emd_inc_email_apps);
			//conf parameters for inline entity
			$has_inline_ent = Array(
				'emd_ticket' => Array(
					'canned_response' => Array(
						'location' => Array(
							'wp_comment',
						) ,
						'button_label' => 'Canned Response',
						'button_icon' => '',
						'entity' => Array(
							'name' => 'emd_canned_response',
							'label' => 'Canned Responses',
							'singular' => 'Canned Response',
							'all_items' => 'Canned Responses',
						) ,
						'taxonomies' => Array(
							'cannedresponse_category' => Array(
								'label' => 'CR Categories',
								'singular' => 'CR Category',
								'type' => 'single',
								'hierarchical' => false,
								'values' => Array(
									Array(
										'name' => __('Business', 'wp-ticket-com') ,
										'slug' => sanitize_title('Business')
									) ,
									Array(
										'name' => __('Education', 'wp-ticket-com') ,
										'slug' => sanitize_title('Education')
									) ,
									Array(
										'name' => __('Science', 'wp-ticket-com') ,
										'slug' => sanitize_title('Science')
									) ,
									Array(
										'name' => __('Technology', 'wp-ticket-com') ,
										'slug' => sanitize_title('Technology')
									)
								) ,
								'default' => Array(
									__('Science', 'wp-ticket-com')
								) ,
							) ,
							'cannedresponse_tag' => Array(
								'label' => 'CR Tags',
								'singular' => 'CR Tag',
								'type' => 'multi',
								'hierarchical' => false,
							) ,
						)
					) ,
				)
			);
			update_option($this->option_name . '_has_inline_ent', $has_inline_ent);
			$emd_inline_ent_apps = get_option('emd_inline_entity_apps', Array());
			$emd_inline_ent_apps[$this->option_name] = $this->option_name . '_has_inline_ent';
			update_option('emd_inline_entity_apps', $emd_inline_ent_apps);
			//action to configure different extension conf parameters for this plugin
			do_action('emd_extension_set_conf');
		}
		/**
		 * Reset app specific options
		 *
		 * @since WPAS 4.0
		 *
		 */
		private function reset_options() {
			delete_option($this->option_name . '_ent_list');
			delete_option($this->option_name . '_shc_list');
			delete_option($this->option_name . '_attr_list');
			delete_option($this->option_name . '_tax_list');
			delete_option($this->option_name . '_rel_list');
			delete_option($this->option_name . '_adm_notice1');
			delete_option($this->option_name . '_adm_notice2');
			delete_option($this->option_name . '_setup_pages');
			$emd_activated_plugins = get_option('emd_activated_plugins');
			if (!empty($emd_activated_plugins)) {
				$emd_activated_plugins = array_diff($emd_activated_plugins, Array(
					'wp-ticket-com'
				));
				update_option('emd_activated_plugins', $emd_activated_plugins);
			}
			$incemail_settings = get_option('emd_inc_email_apps', Array());
			unset($incemail_settings[$this->option_name]);
			update_option('emd_inc_email_apps', $incemail_settings);
			delete_option($this->option_name . '_has_incoming_email');
			$emd_inline_ent_apps = get_option('emd_inline_entity_apps', Array());
			unset($emd_inline_ent_apps[$this->option_name]);
			update_option('emd_inline_entity_apps', $emd_inline_ent_apps);
			delete_option($this->option_name . '_has_inline_ent');
		}
		/**
		 * Show install notices
		 *
		 * @since WPAS 4.0
		 *
		 * @return html
		 */
		public function install_notice() {
			if (isset($_GET[$this->option_name . '_adm_notice1'])) {
				update_option($this->option_name . '_adm_notice1', true);
			}
			if (current_user_can('manage_options') && get_option($this->option_name . '_adm_notice1') != 1) {
?>
<div class="updated">
<?php
				printf('<p><a href="%1s" target="_blank"> %2$s </a>%3$s<a style="float:right;" href="%4$s"><span class="dashicons dashicons-dismiss" style="font-size:15px;"></span>%5$s</a></p>', 'https://docs.emdplugins.com/docs/wp-ticket-community-documentation/?pk_campaign=wpticket&pk_source=plugin&pk_medium=link&pk_content=notice', __('New To WP Ticket? Review the documentation!', 'wpas') , __('&#187;', 'wpas') , esc_url(add_query_arg($this->option_name . '_adm_notice1', true)) , __('Dismiss', 'wpas'));
?>
</div>
<?php
			}
			if (isset($_GET[$this->option_name . '_adm_notice2'])) {
				update_option($this->option_name . '_adm_notice2', true);
			}
			if (current_user_can('manage_options') && get_option($this->option_name . '_adm_notice2') != 1) {
?>
<div class="updated">
<?php
				printf('<p><a href="%1s" target="_blank"> %2$s </a>%3$s<a style="float:right;" href="%4$s"><span class="dashicons dashicons-dismiss" style="font-size:15px;"></span>%5$s</a></p>', 'https://emdplugins.com/plugins/wp-ticket-professional/?pk_campaign=wpticket&pk_source=plugin&pk_medium=link&pk_content=notice', __('Upgrade to Professional Version Now!', 'wpas') , __('&#187;', 'wpas') , esc_url(add_query_arg($this->option_name . '_adm_notice2', true)) , __('Dismiss', 'wpas'));
?>
</div>
<?php
			}
			if (current_user_can('manage_options') && get_option($this->option_name . '_setup_pages') == 1) {
				echo "<div id=\"message\" class=\"updated\"><p><strong>" . __('Welcome to Wp Ticket', 'wp-ticket-com') . "</strong></p>
           <p class=\"submit\"><a href=\"" . add_query_arg('setup_wp_ticket_com_pages', 'true', admin_url('index.php')) . "\" class=\"button-primary\">" . __('Setup Wp Ticket Pages', 'wp-ticket-com') . "</a> <a class=\"skip button-primary\" href=\"" . add_query_arg('skip_setup_wp_ticket_com_pages', 'true', admin_url('index.php')) . "\">" . __('Skip setup', 'wp-ticket-com') . "</a></p>
         </div>";
			}
		}
		/**
		 * Setup pages for components and redirect to dashboard
		 *
		 * @since WPAS 4.0
		 *
		 */
		public function setup_pages() {
			if (!is_admin()) {
				return;
			}
			global $wpdb;
			if (!empty($_GET['setup_' . $this->option_name . '_pages'])) {
				$shc_list = get_option($this->option_name . '_shc_list');
				$types = Array(
					'forms',
					'charts',
					'shcs',
					'datagrids',
					'integrations'
				);
				foreach ($types as $shc_type) {
					if (!empty($shc_list[$shc_type])) {
						foreach ($shc_list[$shc_type] as $keyshc => $myshc) {
							if (isset($myshc['page_title'])) {
								$pages[$keyshc] = $myshc;
							}
						}
					}
				}
				foreach ($pages as $key => $page) {
					$found = "";
					$page_content = "[" . $key . "]";
					$found = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_type='page' AND post_content LIKE %s LIMIT 1;", "%{$page_content}%"));
					if ($found != "") {
						continue;
					}
					$page_data = array(
						'post_status' => 'publish',
						'post_type' => 'page',
						'post_author' => get_current_user_id() ,
						'post_title' => $page['page_title'],
						'post_content' => $page_content,
						'comment_status' => 'closed'
					);
					$page_id = wp_insert_post($page_data);
				}
				delete_option($this->option_name . '_setup_pages');
				wp_redirect(admin_url('index.php?wp-ticket-com-installed=true'));
				exit;
			}
			if (!empty($_GET['skip_setup_' . $this->option_name . '_pages'])) {
				delete_option($this->option_name . '_setup_pages');
				wp_redirect(admin_url('index.php?'));
				exit;
			}
		}
		/**
		 * Delete file attachments when a post is deleted
		 *
		 * @since WPAS 4.0
		 * @param $pid
		 *
		 * @return bool
		 */
		public function delete_post_file_att($pid) {
			$entity_fields = get_option($this->option_name . '_attr_list');
			$post_type = get_post_type($pid);
			if (!empty($entity_fields[$post_type])) {
				//Delete fields
				foreach (array_keys($entity_fields[$post_type]) as $myfield) {
					if (in_array($entity_fields[$post_type][$myfield]['display_type'], Array(
						'file',
						'image',
						'plupload_image',
						'thickbox_image'
					))) {
						$pmeta = get_post_meta($pid, $myfield);
						if (!empty($pmeta)) {
							foreach ($pmeta as $file_id) {
								wp_delete_attachment($file_id);
							}
						}
					}
				}
			}
			return true;
		}
		public function tinymce_fix($init) {
			$init['wpautop'] = false;
			return $init;
		}
	}
endif;
return new Wp_Ticket_Com_Install_Deactivate();
