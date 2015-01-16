<?php
/**
 * Settings Functions
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       1.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Displays settings page
 *
 * @since WPAS 4.0
 * @param string $app
 *
 */
function emd_settings_page($app) {
	$notify_list = get_option($app . '_notify_list');
	$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'glossary';
	$tabs['glossary'] = __('Glossary', 'emd-plugins');
	if (!empty($notify_list)) {
		$tabs['notify'] = __('Notifications', 'emd-plugins');
	}
	$has_license = get_option($app . '_has_license');
	if(!empty($has_license)){
		$tabs = apply_filters('settings_tab_license', $tabs);
	}
	echo '<div class="wrap">
		<h2 class="nav-tab-wrapper">';
	foreach ($tabs as $ktab => $mytab) {
		$tab_url[$ktab] = add_query_arg(array(
			'tab' => $ktab
		));
		$active = "";
		if ($active_tab == $ktab) {
			$active = "nav-tab-active";
		}
		echo '<a href="' . esc_url($tab_url[$ktab]) . '" class="nav-tab ' . $active . '">' . $mytab . '</a>';
	}
?>
		</h2>
		<div id="tab_container">
		<form method="post" action="options.php">
		<?php
	switch ($active_tab) {
		case 'notify':
			do_action('emd_display_settings_notify', $app, $notify_list);
		break;
		case 'license':
			do_action('display_settings_license', $app);
		break;
		case 'glossary':
		case 'default':
			do_action($app . '_settings_glossary');
		break;
	}
?>
		</form>
		</div><!-- #tab_container-->
		</div><!-- .wrap -->
		<?php
}
