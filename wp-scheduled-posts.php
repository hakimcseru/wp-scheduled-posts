<?php
/*
 * Plugin Name: WP Scheduled Posts
 * Plugin URI: https://wpdeveloper.net/free-plugin/wp-scheduled-posts/
 * Description: A complete solution for WordPress Post Schedule. Get an admin Bar & Dashboard Widget showing all your scheduled posts. And full control.
 * Version: 1.4.4
 * Author: WP Developer
 * Author URI: https://wpdeveloper.net
 * License: GPL2+
 * Text Domain: wp-scheduled-posts
 * Min WP Version: 2.5.0
 * Max WP Version: 4.8
 */

if (!class_exists('wp_scheduled_posts')) {
	class wp_scheduled_posts {
		function wp_scheduled_posts() {
			$this->define_constant();
			$this->load_dependencies();
			//$this->add_wpscp_menu_pages();
			$this->plugin_name = plugin_basename(__FILE__);
			register_activation_hook( $this->plugin_name, array(&$this, 'activate') );
			register_deactivation_hook( $this->plugin_name, array(&$this, 'deactivate') );
			register_uninstall_hook( $this->plugin_name, array(&$this, 'uninstall') );
			add_action( 'plugins_loaded', array(&$this, 'start_plugin') );

			include_once('includes/wpscp-options.php');
			include_once('includes/wpdev-dashboard-widget.php');
			include_once('includes/wpscp-main-functions.php');
			
		}


		
		function define_constant() {
		    //echo WP_PLUGIN_URL; die;
			define('pluginsFOLDER', plugin_basename( dirname(__FILE__)) );
			define('plugins_ABSPATH', trailingslashit( str_replace("\\","/", WP_PLUGIN_DIR . '/' . plugin_basename( dirname(__FILE__) ) ) ) );
			define('plugins_URLPATH', trailingslashit( plugins_url() . '/' . plugin_basename( dirname(__FILE__) ) ) );

			define("WPSCP_PLUGIN_SLUG",'wp-scheduled-posts');
			define("WPSCP_PLUGIN_URL",plugins_url("",__FILE__ ));#without trailing slash (/)
			define("WPSCP_PLUGIN_PATH",plugin_dir_path(__FILE__)); #with trailing slash (/)
		}
		
		function load_dependencies() {
			if ( is_admin() ) {	
				require_once (dirname (__FILE__) . '/admin/admin.php');
				$this->optionAdminPanel = new optionAdminPanel();
			}
		}
		
		function activate() {
			include_once (dirname (__FILE__) . '/admin/install.php');
			psm_install();
		}

		function deactivate(){}
		function uninstall(){}
		
		function start_plugin() {
			if ( is_admin() ) {
				wp_enqueue_style( 'custom-style', plugins_URLPATH . 'admin/css/custom-style.css' );
				wp_enqueue_style( 'admin-style', plugins_URLPATH . 'admin/css/admin.css' );
				wp_enqueue_style( 'font-awesome', plugins_URLPATH . 'admin/css/font-awesome.min.css' );
				wp_enqueue_script( 'custom-script', plugins_URLPATH . 'admin/js/custom-script.js', array('jquery'), '1.0.0', false );
			}
		}
		
		
	}
	global $psm;
	$psm = new wp_scheduled_posts();
		
}

include('admin/editorial-calendar/edcal.php');
include('admin/publish-to-schedule/publish-to-schedule.php');
include('admin/wp-missed-schedule-master/wp-missed-schedule.php');
?>