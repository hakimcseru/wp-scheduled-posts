<?php
if ( is_admin() ) {
    global $edcal;
    if ( empty($edcal) )
        $edcal = new wpsp_scheduled();
}


/*
 * This error code matches CONCURRENCY_ERROR from edcal.js
 */
define( 'EDCAL_CONCURRENCY_ERROR', 4 );

/*
 * This error code matches PERMISSION_ERROR from edcal.js
 */
define( 'EDCAL_PERMISSION_ERROR', 5 );

/*
 * This error code matches NONCE_ERROR from edcal.js
 */
define( 'EDCAL_NONCE_ERROR', 6 );

class wpsp_scheduled {
    
    protected $supports_custom_types;
    protected $default_time;

    function ava_test_init() {
        wp_enqueue_script( 'dhtmlxscheduler-js', plugins_url( '/admin/scheduled-calendar/codebase/dhtmlxscheduler.js', __FILE__ ));
    }
        

    function __construct() {
        //add_action('wp_ajax_edcal_saveoptions', array(&$this, 'wpsp_scheduled_saveoptions'));
        //add_action('wp_ajax_edcal_changedate', array(&$this, 'wpsp_scheduled_changedate'));
        //add_action('wp_ajax_edcal_savepost', array(&$this, 'wpsp_scheduled_savepost'));
        //add_action('wp_ajax_edcal_changetitle', array(&$this, 'wpsp_scheduled_changetitle'));
        add_action('admin_menu', array(&$this, 'wpsp_scheduled_list_add_management_page'));
        //add_action('wp_ajax_edcal_posts', array(&$this, 'wpsp_scheduled_posts'));
        //add_action('wp_ajax_edcal_getpost', array(&$this, 'wpsp_scheduled_getpost'));
       // add_action('wp_ajax_edcal_deletepost', array(&$this, 'wpsp_scheduled_deletepost'));
        //add_action("init", array(&$this, 'wpsp_scheduled_load_language'));
        //add_action( 'admin_menu', array(&$this, 'add_sub_menu_here') );

        add_action('wp_enqueue_scripts','ava_test_init');

    
        /*
         * This boolean variable will be used to check whether this 
         * installation of WordPress supports custom post types.
         */
        $this->supports_custom_types = function_exists('get_post_types') && function_exists('get_post_type_object');

        /*
         * This is the default time that posts get created at, for now 
         * we are using 10am, but this could become an option later.
         */
        $this->default_time = get_option("edcal_default_time") != "" ? get_option("edcal_default_time") : '10:00';
         
         /*
          * This is the default status used for creating new posts.
          */
        $this->default_status = get_option("edcal_default_status") != "" ? get_option("edcal_default_status") : 'draft';
        
        /*
         * We use these variables to hold the post dates for the filter when 
         * we do our post query.
         */
        //$edcal_startDate;
        //$edcal_endDate;
    }

    function wpsp_scheduled_list_add_management_page() {
        if (function_exists('add_management_page') ) {
            // $page = add_posts_page( __('Calendar', 'editorial-calendar'), __('Calendar', 'editorial-calendar'), 'edit_posts', 'cal', array(&$this, 'edcal_list_admin'));
            // add_action( "admin_print_scripts-$page", array(&$this, 'edcal_scripts'));

            $page = add_submenu_page( pluginsFOLDER, __('Schedule Calendar', 'psm'), __('Schedule Calendar', 'psm'), 'manage_options', 'cal', array(&$this, 'edcal_list_admin'));
            //add_action( "admin_print_scripts-$page", array(&$this, 'edcal_scripts'));
            
            //add_submenu_page( pluginsFOLDER, __('Free VS Pro', 'psm'), __('Free VS Pro', 'psm'), 'manage_options', 'f_vs_p', array(&$this, 'show_menu'));



            if ($this->supports_custom_types) {


                /* 
                 * We add one calendar for Posts and then we add a separate calendar for each
                 * custom post type.  This calendar will have an URL like this:
                 * /wp-admin/edit.php?post_type=podcasts&page=cal_podcasts
                 *
                 * We can then use the post_type parameter to show the posts of just that custom
                 * type and update the labels for each post type.
                 */
                $args = array(
                    'public'   => get_option("edcal_custom_posts_public") != "" ? get_option("edcal_custom_posts_public") : true,
                    '_builtin' => false
                ); 
                $output = 'names'; // names or objects
                $operator = 'and'; // 'and' or 'or'
                $post_types = get_post_types($args,$output,$operator); 

                foreach ($post_types as $post_type) {
                    $show_this_post_type = apply_filters("edcal_show_calendar_$post_type", true);
                    if ($show_this_post_type) {
                        $page = add_submenu_page('edit.php?post_type=' . $post_type, __('Calendar', 'editorial-calendar'), __('Calendar', 'editorial-calendar'), 'edit_posts', 'cal_' . $post_type, array(&$this, 'edcal_list_admin'));
                        add_action( "admin_print_scripts-$page", array(&$this, 'edcal_scripts'));
                    }
                }


            }
        }
    }
    function edcal_list_admin()
    {?>
    
       <?php //echo '<script src="http://wpnayan.local/wp-content/plugins/wp-scheduled-posts-free/admin/scheduled-calendar/codebase/dhtmlxscheduler.js" type="text/javascript" charset="utf-8"></script>';
	echo '<link rel="stylesheet" href="http://wpnayan.local/wp-content/plugins/wp-scheduled-posts-free/admin/scheduled-calendar/codebase/dhtmlxscheduler_material.css" type="text/css" title="no title" charset="utf-8">';

	echo '<script src="http://wpnayan.local/wp-content/plugins/wp-scheduled-posts-free/admin/scheduled-calendar/codebase/ext/dhtmlxscheduler_minical.js" type="text/javascript" charset="utf-8"></script>';
?>
	<style type="text/css">
		html,
		body {
			margin: 0px;
			padding: 0px;
			height: 100%;
			overflow: hidden;
		}
	</style>

	<script type="text/javascript" charset="utf-8">

		function init123() {
			scheduler.config.multi_day = true;

			scheduler.config.event_duration = 35;

			scheduler.config.xml_date = "%Y-%m-%d %H:%i";
			scheduler.init('scheduler_here', new Date(2018, 0, 10), "week");
			/*scheduler.load("../common/events.json", "json", function () {
				scheduler.showLightbox(3);
			});*/

			scheduler.config.lightbox.sections = [
				{ name: "description", height: 50, map_to: "text", type: "textarea", focus: true },
				{ name: "time", height: 72, type: "calendar_time", map_to: "auto" }
			];

		}

	</script>
	<div id="scheduler_here" class="dhx_cal_container" style='width:100%; height:100%;'>
		<div class="dhx_cal_navline">
			<div class="dhx_cal_prev_button">&nbsp;</div>
			<div class="dhx_cal_next_button">&nbsp;</div>
			<div class="dhx_cal_today_button"></div>
			<div class="dhx_cal_date"></div>
			<div class="dhx_cal_tab" name="day_tab" style="right:204px;"></div>
			<div class="dhx_cal_tab" name="week_tab" style="right:140px;"></div>
			<div class="dhx_cal_tab" name="month_tab" style="right:76px;"></div>
		</div>
		<div class="dhx_cal_header">
		</div>
		<div class="dhx_cal_data">
		</div>
	</div>
    <script>init123();</script>
    <?php
    }

    
    
}?>