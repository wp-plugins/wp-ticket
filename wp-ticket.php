<?php
/** 
 * Plugin Name: Wp Ticket
 * Plugin URI: https://emdplugins.com
 * Description: WP Ticket enables support staff to receive, process, and respond to service requests efficiently and effectively.
 * Version: 5.0.1
 * Author: eMarket Design
 * Author URI: https://emarketdesign.com
 * Text Domain: wp-ticket-com
 * Domain Path: /lang
 * @package WP_TICKET_COM
 * @since WPAS 4.0
 */
/*
 * LICENSE:
 * Wp Ticket is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Wp Ticket is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * Please see <http://www.gnu.org/licenses/> for details.
*/
if (!defined('ABSPATH')) exit;
if (!class_exists('Wp_Ticket')):
	/**
	 * Main class for Wp Ticket
	 *
	 * @class Wp_Ticket
	 */
	final class Wp_Ticket {
		/**
		 * @var Wp_Ticket single instance of the class
		 */
		private static $_instance;
		public $textdomain = 'wp-ticket-com';
		public $app_name = 'wp_ticket_com';
		public $session;
		/**
		 * Main Wp_Ticket Instance
		 *
		 * Ensures only one instance of Wp_Ticket is loaded or can be loaded.
		 *
		 * @static
		 * @see WP_TICKET_COM()
		 * @return Wp_Ticket - Main instance
		 */
		public static function instance() {
			if (!isset(self::$_instance)) {
				self::$_instance = new self();
				self::$_instance->define_constants();
				self::$_instance->includes();
				self::$_instance->load_plugin_textdomain();
				self::$_instance->session = new Emd_Session('wp_ticket_com');
				add_action('admin_menu', array(
					self::$_instance,
					'display_settings'
				));
				add_filter('template_include', array(
					self::$_instance,
					'show_template'
				));
				add_action('widgets_init', array(
					self::$_instance,
					'include_widgets'
				));
			}
			return self::$_instance;
		}
		/**
		 * Cloning is forbidden.
		 */
		public function __clone() {
			_doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', $this->textdomain) , '1.0');
		}
		/**
		 * Define Wp_Ticket Constants
		 *
		 * @access private
		 * @return void
		 */
		private function define_constants() {
			define('WP_TICKET_COM_VERSION', '5.0.1');
			define('WP_TICKET_COM_AUTHOR', 'eMarket Design');
			define('WP_TICKET_COM_NAME', 'Wp Ticket');
			define('WP_TICKET_COM_PLUGIN_FILE', __FILE__);
			define('WP_TICKET_COM_PLUGIN_DIR', plugin_dir_path(__FILE__));
			define('WP_TICKET_COM_PLUGIN_URL', plugin_dir_url(__FILE__));
		}
		/**
		 * Include required files
		 *
		 * @access private
		 * @return void
		 */
		private function includes() {
			//these files are in all apps
			if (!function_exists('emd_mb_meta')) {
				require_once WP_TICKET_COM_PLUGIN_DIR . 'assets/ext/emd-meta-box/emd-meta-box.php';
			}
			if (!function_exists('emd_translate_date_format')) {
				require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/date-functions.php';
			}
			if (!function_exists('emd_get_hidden_func')) {
				require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/common-functions.php';
			}
			if (!class_exists('Emd_Entity')) {
				require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/entities/class-emd-entity.php';
			}
			if (!function_exists('emd_get_template_part')) {
				require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/layout-functions.php';
			}
			if (!class_exists('EDD_SL_Plugin_Updater')) {
				require_once WP_TICKET_COM_PLUGIN_DIR . 'assets/ext/edd/EDD_SL_Plugin_Updater.php';
			}
			if (!class_exists('Emd_License')) {
				require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/admin/class-emd-license.php';
			}
			if (!function_exists('emd_show_license_page')) {
				require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/admin/license-functions.php';
			}
			//the rest
			if (!function_exists('emd_send_email')) {
				require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/admin/notify-actions.php';
			}
			if (!class_exists('Emd_Query')) {
				require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/class-emd-query.php';
			}
			if (!function_exists('emd_get_p2p_connections')) {
				require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/relationship-functions.php';
				require_once WP_TICKET_COM_PLUGIN_DIR . 'assets/ext/posts-to-posts/posts-to-posts.php';
			}
			if (!class_exists('Zebra_Form')) {
				require_once WP_TICKET_COM_PLUGIN_DIR . '/assets/ext/zebraform/Zebra_Form.php';
			}
			if (!function_exists('emd_submit_form')) {
				require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/form-functions.php';
			}
			if (!function_exists('emd_shc_get_layout_list')) {
				require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/shortcode-functions.php';
			}
			if (!class_exists('Emd_Widget')) {
				require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/class-emd-widget.php';
			}
			if (!class_exists('Emd_Session')) {
				require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/class-emd-session.php';
			}
			if (!function_exists('emd_show_login_register_forms')) {
				require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/login-register-functions.php';
			}
			do_action('emd_ext_include_files');
			//app specific files
			if (!function_exists('emd_show_settings_page')) {
				require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/admin/settings-functions.php';
			}
			if (!function_exists('emd_form_register_settings')) {
				require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/admin/settings-functions-forms.php';
			}
			if (!function_exists('emd_misc_register_settings')) {
				require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/admin/settings-functions-misc.php';
			}
			if (is_admin()) {
				//these files are in all apps
				if (!function_exists('emd_display_store')) {
					require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/admin/store-functions.php';
				}
				//the rest
				if (!function_exists('emd_shc_button')) {
					require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/admin/wpas-btn-functions.php';
				}
				if (!class_exists('Emd_Single_Taxonomy')) {
					require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/admin/singletax/class-emd-single-taxonomy.php';
					require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/admin/singletax/class-emd-walker-radio.php';
				}
				if (!function_exists('emd_dashboard_widget')) {
					require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/admin/dashboard-widget-functions.php';
				}
				if (!class_exists('Emd_Notifications')) {
					require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/admin/class-emd-notifications.php';
				}
				require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/admin/glossary.php';
				require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/admin/dashboard-widgets.php';
			}
			require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/class-install-deactivate.php';
			require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/entities/class-emd-ticket.php';
			require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/entities/class-emd-agent.php';
			require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/entities/emd-ticket-shortcodes.php';
			require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/forms.php';
			require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/scripts.php';
			if (!function_exists('emd_limit_by')) {
				require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/filter-functions.php';
			}
			require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/query-filters.php';
			require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/plugin-feedback-functions.php';
			require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/content-functions.php';
		}
		/**
		 * Loads plugin language files
		 *
		 * @access public
		 * @return void
		 */
		public function load_plugin_textdomain() {
			$locale = apply_filters('plugin_locale', get_locale() , 'wp-ticket-com');
			$mofile = sprintf('%1$s-%2$s.mo', 'wp-ticket-com', $locale);
			$mofile_shared = sprintf('%1$s-emd-plugins-%2$s.mo', 'wp-ticket-com', $locale);
			$lang_file_list = Array(
				'emd-plugins' => $mofile_shared,
				'wp-ticket-com' => $mofile
			);
			foreach ($lang_file_list as $lang_key => $lang_file) {
				$localmo = WP_TICKET_COM_PLUGIN_DIR . '/lang/' . $lang_file;
				$globalmo = WP_LANG_DIR . '/wp-ticket-com/' . $lang_file;
				if (file_exists($globalmo)) {
					load_textdomain($lang_key, $globalmo);
				} elseif (file_exists($localmo)) {
					load_textdomain($lang_key, $localmo);
				} else {
					load_plugin_textdomain($lang_key, false, WP_TICKET_COM_PLUGIN_DIR . '/lang/');
				}
			}
		}
		/**
		 * Creates plugin page in menu with submenus
		 *
		 * @access public
		 * @return void
		 */
		public function display_settings() {
			add_menu_page(__('WP Ticket', $this->textdomain) , __('WP Ticket', $this->textdomain) , 'manage_options', $this->app_name, array(
				$this,
				'display_glossary_page'
			));
			add_submenu_page($this->app_name, __('Glossary', $this->textdomain) , __('Glossary', $this->textdomain) , 'manage_options', $this->app_name, array(
				$this,
				'display_glossary_page'
			));
			add_submenu_page($this->app_name, __('Settings', $this->textdomain) , __('Settings', $this->textdomain) , 'manage_options', $this->app_name . '_settings', array(
				$this,
				'display_settings_page'
			));
			add_submenu_page($this->app_name, __('Add-Ons', $this->textdomain) , __('Add-Ons', $this->textdomain) , 'manage_options', $this->app_name . '_store', array(
				$this,
				'display_store_page'
			));
			add_submenu_page($this->app_name, __('Designs', $this->textdomain) , __('Designs', $this->textdomain) , 'manage_options', $this->app_name . '_designs', array(
				$this,
				'display_design_page'
			));
			add_submenu_page($this->app_name, __('Support', $this->textdomain) , __('Support', $this->textdomain) , 'manage_options', $this->app_name . '_support', array(
				$this,
				'display_support_page'
			));
			add_submenu_page($this->app_name, __('Notifications', $this->textdomain) , __('Notifications', $this->textdomain) , 'manage_options', $this->app_name . '_notify', array(
				$this,
				'display_notify_page'
			));
			$emd_lic_settings = get_option('emd_license_settings', Array());
			$show_lic_page = 0;
			if (!empty($emd_lic_settings)) {
				foreach ($emd_lic_settings as $key => $val) {
					if ($key == $this->app_name) {
						$show_lic_page = 1;
						break;
					} else if ($val['type'] == 'ext') {
						$show_lic_page = 1;
						break;
					}
				}
				if ($show_lic_page == 1) {
					add_submenu_page($this->app_name, __('Licenses', $this->textdomain) , __('Licenses', $this->textdomain) , 'manage_options', $this->app_name . '_licenses', array(
						$this,
						'display_licenses_page'
					));
				}
			}
			//add submenu page under app settings page
			do_action('emd_ext_add_menu_pages', $this->app_name);
		}
		/**
		 * Calls settings function to display glossary page
		 *
		 * @access public
		 * @return void
		 */
		public function display_glossary_page() {
			do_action($this->app_name . '_settings_glossary');
		}
		public function display_store_page() {
			emd_display_store($this->textdomain);
		}
		public function display_design_page() {
			emd_display_design($this->textdomain);
		}
		public function display_support_page() {
			emd_display_support($this->textdomain, 2, 'wp-ticket');
		}
		public function display_licenses_page() {
			do_action('emd_show_license_page', $this->app_name);
		}
		public function display_settings_page() {
			do_action('emd_show_settings_page', $this->app_name);
		}
		public function display_notify_page() {
			$notify_init_list = get_option($this->app_name . '_notify_init_list');
			do_action('emd_display_settings_notify', $this->app_name, $notify_init_list);
		}
		/**
		 * Displays single, archive, tax and no-access frontend views
		 *
		 * @access public
		 * @return string, $template:emd template or template
		 */
		public function show_template($template) {
			return emd_show_template($this->app_name, WP_TICKET_COM_PLUGIN_DIR, $template);
		}
		/**
		 * Loads sidebar widgets
		 *
		 * @access public
		 * @return void
		 */
		public function include_widgets() {
			require_once WP_TICKET_COM_PLUGIN_DIR . 'includes/entities/class-emd-ticket-widgets.php';
		}
	}
endif;
/**
 * Returns the main instance of Wp_Ticket
 *
 * @return Wp_Ticket
 */
function WP_TICKET_COM() {
	return Wp_Ticket::instance();
}
// Get the Wp_Ticket instance
WP_TICKET_COM();
