<?php
/*
Plugin Name: wp scheduled post pro
Plugin URI: https://wpdeveloper.net/free-plugin/wp-scheduled-posts/
Description: This Plugin shows your scheduled posts in calendar, manages your automatic or custom posts publish time, catches missing posts and updates them .
Author: WP Developer
Author URI: https://wpdeveloper.net
Version: 2.0
Text Domain: wp-scheduled-posts
License: GPL2+

*/ 

if (!class_exists('wpsp_addon')) {
	class wpsp_addon {
		function __construct() {
			$this->define_constant();
			$this->load_dependencies();
			$this->plugin_name = plugin_basename(__FILE__);
			$parent_plugin_file = 'wp-scheduled-posts/wp-scheduled-posts.php';

			
			register_deactivation_hook( $this->plugin_name, array(&$this, 'deactivate') );
			register_activation_hook( $this->plugin_name, array(&$this, 'activate') );
			register_uninstall_hook( $this->plugin_name, 'uninstall' );

			

			add_action( 'admin_enqueue_scripts', array(&$this, 'start_plugin') );
			add_action( 'admin_init', array(&$this, 'check_some_other_plugin') );
			add_action('admin_notices', array(&$this,'wpse120377_error') );

		}
		
		function define_constant() {
		    //echo WP_PLUGIN_URL; die;
			define('pluginsFOLDER', plugin_basename( dirname(__FILE__)) );
			define('plugins_ABSPATH', trailingslashit( str_replace("\\","/", WP_PLUGIN_DIR . '/' . plugin_basename( dirname(__FILE__) ) ) ) );
			define('plugins_URLPATH', trailingslashit( plugins_url() . '/' . plugin_basename( dirname(__FILE__) ) ) );
		}
		
		function load_dependencies() {
			if ( is_admin() ) {	
				require_once (dirname (__FILE__) . '/admin/admin.php');
				$this->optionAdminPanel = new optionAdminPanel();
			}
		}

		function check_some_other_plugin() {
			remove_submenu_page( 'options-general.php', 'wp-scheduled-posts' );
		}

		function activate() {
			include_once (dirname (__FILE__) . '/admin/install.php');
			psm_install();
			return true;
		}


		function wpse120377_error()
		{
			if ( is_plugin_active('wp-scheduled-posts/wp-scheduled-posts.php') ) {
					return false;

			}else{
	    ?>
		    <div class="error">
		        <p>
		            <?php _e('"WP Scheduled Posts pro" requires "WP Scheduled Posts" Plugin. Please install it. ', 'wp-scheduled-posts'); ?>
		            <a href="https://wordpress.org/plugins/wp-scheduled-posts/" target="_blank">WP Scheduled Posts</a>
		        </p>
		    </div>
	    <?php
			}
		}

		

		function deactivate(){
			return true;
		}
		function uninstall(){
			return true;
		}
		
		function start_plugin() {
			if ( is_admin() ) {
				wp_enqueue_style( 'custom-style', plugins_URLPATH . 'admin/css/custom-style.css' );
				wp_enqueue_style( 'admin-style', plugins_URLPATH . 'admin/css/admin.css' );
				wp_enqueue_style( 'font-awesome', plugins_URLPATH . 'admin/css/font-awesome.min.css' );
				wp_enqueue_style( 'chung-timepicker', plugins_URLPATH . 'admin/css/chung-timepicker.css' );
				wp_enqueue_script( 'custom-script', plugins_URLPATH . 'admin/js/custom-script.js', array('jquery'), '1.0.0', false );
				wp_enqueue_script( 'main-chung-timepicker', plugins_URLPATH . 'admin/js/chung-timepicker.js', array('jquery'), '1.0.0', false );
			}
		}
		
		
	}
	global $wpsp_op;
	$wpsp_op = new wpsp_addon();
		
include('admin/scheduled-calendar/scheduled.php');
include('admin/manage-schedule/manage-schedule.php');
include('admin/wpsp-missed-schedule/wpsp-missed-schedule.php');
}

?>