<?php
/*
Plugin Name: wp scheduled posts - wp scheduled post free
Plugin URI: http://www.8pears.com
Description: This Plugin shows your scheduled posts in calendar, manages your automatic posts publish time, catches missing posts and updates them .
Author: Nazrul Islam Nayan
Author URI: http://www.nazrulislamnayan.com
Version: 1.0


Copyright 2018 by 8pears solution limited

*/ 


if (!class_exists('post_scheduler_monster')) {
	class post_scheduler_monster {
		function post_scheduler_monster() {
			$this->define_constant();
			$this->load_dependencies();
			$this->plugin_name = plugin_basename(__FILE__);
			$parent_plugin_file = 'wp-scheduled-posts/wp-scheduled-posts.php';

			
			register_deactivation_hook( $this->plugin_name, array(&$this, 'deactivate') );
			register_uninstall_hook( $this->plugin_name, array(&$this, 'uninstall') );
			add_action( 'plugins_loaded', array(&$this, 'start_plugin') );
			add_action( 'admin_init', array(&$this,'check_some_other_plugin' ) );
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
			if ( is_plugin_active('wp-scheduled-posts/wp-scheduled-posts.php') ) {
				register_activation_hook( plugin_basename(__FILE__), 'activate' );
				remove_submenu_page( 'options-general.php', 'wp-scheduled-posts' );

			}else{
				//echo "plugin nai";
				//exit;

			}
		}

		
		function activate() {
			
			include_once (dirname (__FILE__) . '/admin/install.php');
			psm_install();
		}

		function deactivate(){

		}
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
	$psm = new post_scheduler_monster();
		
include('admin/editorial-calendar/edcal.php');
include('admin/wp-missed-schedule-master/wp-missed-schedule.php');
}

?>