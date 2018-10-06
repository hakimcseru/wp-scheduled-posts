
<?php



load_textdomain('psm', dirname(__FILE__).'/lang/' . get_locale() . '.mo');


$plName = 'Publish to Schedule';
$plUrl = 'https://wordpress.org/extend/plugins/publish-to-schedule/';

$get_pub_op 			= get_option('pub_active_option');
$activate_pub_option 	= html_entity_decode(stripslashes($get_pub_op));

$get_cal_op 			= get_option('cal_active_option');
$activate_cal_option 	= html_entity_decode(stripslashes($get_cal_op));


# activate debug
$pts_debug = False;


#Actions that change post status...
#https://codex.wordpress.org/Post_Status_Transitions

#All possible post status in Jan 2012...

#'new' - When there's no previous status
#'publish' - A published post or page
#'pending' - post in pending review
#'draft' - a post in draft status
#'auto-draft' - a newly created post, with no content
#'future' - a post to publish in the future
#'private' - not visible to users who are not logged in
#'inherit' - a revision. see get_children.
#'trash' - post is in trashbin. added with Version 2.9.

# set the interesting status when plugin will do its magic...



$possibleStatus = array();
array_push($possibleStatus,'new');
array_push($possibleStatus,'pending');
array_push($possibleStatus,'draft');
array_push($possibleStatus,'auto-draft');

# create actions for each one ...
foreach($possibleStatus as $status) {
	add_action($status.'_to_publish','pts_do_publish_schedule',1);	
}



# change the name os publish button...

function pts_change_publish_button($translation, $text) {

	global $activate_pub_option;
	global $activate_cal_option;


	if(isset($activate_pub_option) && !empty($activate_pub_option)){
	    if ($text == 'Publish') {
	        return __('Publish Schedule', 'psm');
	    }
    	return $translation;
	}elseif(isset($activate_cal_option) && !empty($activate_cal_option)){
	    if ($text == 'Publish') {
	        return __('Publish Schedule', 'psm');
	    }
    	return $translation;
	}else{
		if ($text == 'Publish') {
	        return $text;
	    }
    	return $translation;
	}
}

# return the actual version of this plugin

function pts_get_version() {
    $plugin_data = get_plugin_data(__FILE__);
    $plugin_version = $plugin_data['Version'];
    return $plugin_version;
}




# insert Google Analytics code to monitor plugin utilization.

function pts_insertAnalytics($getCode = False) {

    $options = get_option(basename(__FILE__, ".php"));

    # do not collect statististcs if now allowed... 	
    if ($options['pts_allowstats'] == 'No') {
        return '';
    }

    $analyticsCode = "<script type=\"text/javascript\">
	var _gaq = _gaq || [];
		_gaq.push(['_setAccount', 'UA-28857513-1']);
		_gaq.push(['_trackPageview']);	
	  (function() {
	    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();
		_gaq.push(['_setCustomVar', 1,'Site URL','" . get_option('home') . "', 1 ]);
		_gaq.push(['_setCustomVar', 2,'Articles scheduled','" . $options['pts_statistics_total_work'] . "',1]); 	
		_gaq.push(['_setCustomVar', 3,'WP Language','" . get_bloginfo('language') . "',1]);
	</script>";


    if ($getCode) {
        return $analyticsCode;
    } else {
        print $analyticsCode;
    }
}





# creates a js function that will compare the cliet time with the server time, passed as variables...
function pts_createJsToCompareTime($HTMLWrong,$HTMLOK){


	$HTMLWrong = trim($HTMLWrong);
	$HTMLOK = trim($HTMLOK);
	
	# minutes...
	$maxAllowedDif = 20;	
	
	# seconds...
	$phplocal = current_time('timestamp', $gmt = 0);	
	
	# minutes...
	$phplocal = $phplocal / 60;
	
	# minutes in a day...
	$phplocal = $phplocal % 1440;
	
	
	$jsCT = '
	
	<script type="text/javascript">	

	
	function jsCompareTimes(){	
		d = new Date();						
		var currentHours = d.getHours();
		var currentMinutes = d.getMinutes();
		
		
		
		var jsLocal = currentHours*60 + currentMinutes;
		var phpLocal = '.$phplocal.';
		
		var maxAllowedDif = '.$maxAllowedDif.';				
		
		difference_in_minutes = Math.abs(jsLocal - phpLocal);				
		
		//alert("diference: " + difference_in_minutes + "\nphpLocal:"+ phpLocal + "\n_jsLocal: "+ jsLocal);
		
		// ignores big differences as being 23 to 00 hour
		if(difference_in_minutes > 60*12){
			difference_in_minutes = 0;
		}		
			
		if (difference_in_minutes > maxAllowedDif){
			//alert("Server time is wrong");			
			document.getElementById("divjsCT").innerHTML=\''.$HTMLWrong.'\';
		}	
		else{
			//alert("Server time is OK! " + difference_in_minutes);
			document.getElementById("divjsCT").innerHTML=\''.$HTMLOK.'\';
			
		}
		
	}		
		
	</script>
	
	
	
	';


	return $jsCT;
}




# show information near the publish button...
function pts_postInfo(){
	global $post;	
	global $pts_donateURL;
	global $plName;
	global $pts_debug;
	
	
	if($pts_debug){
		echo '<div class="misc-pub-section misc-pub-section-last">';
		echo '<div style="margin: 0 0 5px 0">';
		echo '<strong style="color:red;">'.$plName.' - <span style="text-decoration:blink">Debug active!</span></strong>';
		echo '</div>';
		echo '</div>';		
	}
	
	
	# do not show info for published posts...
	if($post->post_status == 'publish'){
		return;
	}	
	
	# do not show info for scheduled posts...
	if($post->post_status == 'future'){
		return;
	}	

	# do not show info for pages...
	if($post->post_type != 'post'){
		return;
	}	
	
	
	
	# only change the text of publish button when plugin is active...
	add_filter( 'gettext', 'pts_change_publish_button', 10, 2 );	
	
	
	# insert Google analytics code to monitor plugin usage.
	add_action('admin_footer', 'pts_insertAnalytics',12);

	
	
	
	
	echo '<div class="misc-pub-section misc-pub-section-last" style="font-size:11px;">';
	
	
	# show diferent messages for admin and non admin users...
	if(current_user_can('install_plugins')){
		$msgTimeWrong = '<div style="margin: 0 0 7px 0"><span style="color:red">'.
		__('Your WordPress timezone settings might be incorrect!','psm').
		'</span>  ( <a href="options-general.php?page=publish-to-schedule/publish-to-schedule.php" target="_blank">'.
		__('See details','psm').'</a> )</div>';
	}
	else{
		$msgTimeWrong = '<div style="margin: 0 0 7px 0"><span style="color:red">'.
		__('Your WordPress timezone settings might be incorrect!','psm').
		'</span>  ( '.
		__('Please tell the blog admin!','psm').
		'</a> )</div>';
			
	}
	
	
	
	echo pts_createJsToCompareTime($msgTimeWrong,'');					
	# div usada para reportar hora incorreta...		
	echo '<div style="padding-left:20px;" id="divjsCT"></div>';
	
	echo '<script type="text/javascript">	
			jsCompareTimes();
		</script>';
	
	echo pts_findNextSlot($post);		
	
	
	echo '</div>';
}
add_action( 'post_submitbox_misc_actions', 'pts_postInfo' );









function pts_getMaxPostsDay($datetimeCheck){

	global $options;
	

	# id day of week is allowed... (replaces <BBB>)
	$opt = 'pts_'.date('w',$datetimeCheck);
	
	/*
	print_r($datetimeCheck);	
	print_r($options);
	print_r($opt);
	echo '<br>';
	*/
	
	# translate the old style option  no\yes para 0\1+
	if($options[$opt] == 'no'){
		return 0;	
	}
	if($options[$opt] == 'yes'){
		return 1;	
	}
	if($options[$opt] != ''){
		return $options[$opt];
	}
	else{
		return 1;
	}	
}



# return the next date and time for post.
function pts_findNextSlot($post,$changePost = False){
	global $wpdb;
	global $table_prefix;
	global $pts_debug;
	global $activate_pub_option;

	//if(isset($activate_pub_option) && !empty($activate_pub_option)){

		# if is a draft or pending with a date in future, means that it were published already, mas back to draft or pending...
		if(($post->post_status == 'draft') or ($post->post_status == 'pending')){		
			if(   strtotime($post->post_date)   >     strtotime(date(current_time('mysql', $gmt = 0)))    ){
				$msg = '';
				$msg .= __('Post already scheduled for a future date!',  'psm');
				$msg .= '<br>';
				$msg .= __('In this case, the plugin will do nothing!',  'psm');
				if($changePost == False){
					return $msg;			
				}
				else{
					return null;
				}			
			}		
		}

		
		# load plugin configurations...	
		$options = get_option(basename(__FILE__, ".php"));

		# get start and end minutes from 0 to 1440-1
		$startMinute =  date('H',strtotime($options['pts_start'])) * 60 + date('i',strtotime($options['pts_start']));;
		$endMinute = date('H',strtotime($options['pts_end'])) * 60 + date('i',strtotime($options['pts_end']));;

		$msg = '';
		
		
		# dates from today...
		$startDate = date('Ymd', strtotime(current_time('mysql', $gmt = 0)));
		
		
		
		if($pts_debug and True){
			$msg .= 'DEBUG: $startDate = ' . $startDate . '<br>';
		}
		
		if($pts_debug and True){
			$msg .= 'DEBUG: $options = ' . print_r($options,True) . '<br>';		
		}
		
		
		
		$sql = '
			select 
				ID,			
				DATE_FORMAT(post_date,"%Y%m%d") as dtfind,
				post_author,
				post_date,
				post_date_gmt,
				post_title,
				post_status,
				guid 
			from '. $table_prefix . 'posts 
			where ID <> '.$post->ID.' 
				and post_status in ("publish","future") 
				and post_type = "post" 
				and post_date >= "'. $startDate .'" 
				order by post_date ASC
			
		
		';	
		$recentPosts = $wpdb->get_results($sql);
		
		$maxDaysFuture = 5000;


		if($pts_debug and True){
			$msg .= $sql;		
		}

		
		
		
		# next dates allowed to publish...
		for($offset=0;$offset<$maxDaysFuture;$offset+=1){
			
			# must be set every run of this for...
			$cssDayAllowed = 'color:green; text-decoration:none;';
			$cssDayForbid = 'color:red; text-decoration:line-through;';
			$cssDayTooLate = 'color:green; text-decoration:line-through;';
		
			$datetimeCheck = strtotime(current_time('mysql', $gmt = 0) . ' + '.$offset.' days');	
			$dt = date("Ymd",$datetimeCheck);				
			$msg .=  '' . date(get_option('date_format'),$datetimeCheck) . ' - <span style="<BBB>"> '.__(date("l",$datetimeCheck),'pts').'</span><CCC><DDD><EEE><br>';
			

			$maxPostsThisDay = pts_getMaxPostsDay($datetimeCheck);
			$nPostsDay = 0;

		
		
			# if there are no posts in the day...
			if(count($recentPosts)){
				
				$thereArePosts = False;
				
				foreach($recentPosts as $rp){
					
					if($rp->dtfind == $dt){					
						$thereArePosts = True;
						$nPostsDay += 1;
								
						
						# garante o agendamento para hoarios posteriores no mesmo dia.		
						#$startMinute =  date('H',$rp->post_date) * 60 + date('i',$rp->post_date);
						#echo date('i',$rp->post_date);
						#echo $startMinute . '<br>';							
						
						#break;
					}
				}
				
				
				
				
				
				if($nPostsDay >= $maxPostsThisDay){
					
					$msgThereIsPost	= '';
					
					if(($nPostsDay == 1) & ($maxPostsThisDay == 1)){		
						$msgThereIsPost .= ' | ' . __('post at ','psm');
						$msgThereIsPost .= ' ';				
						$msgThereIsPost .= '<a title="'.__('Edit post',  'psm').' : '.$rp->post_title.
							'" target="_blank" href="post.php?post='.$rp->ID.'&action=edit">'.
						date(get_option('time_format'),strtotime($rp->post_date)).'</a>';
					}
					else{
						$msgThereIsPost .= " ($nPostsDay "  . __('of','psm') . ' '. "$maxPostsThisDay)";
					}
					 
					$msg = str_replace('<CCC>',$msgThereIsPost,$msg);				
					# default style for positive day of week
					$msg = str_replace('<BBB>',$cssDayAllowed,$msg);				
					$msg = str_replace('<EEE>','',$msg);
					
					continue;
				}
				else{
					$msg = str_replace('<CCC>','',$msg);
				}
			}
			else{
				$msg = str_replace('<CCC>','',$msg);
			}
			
		

		
			
			
					
			if($nPostsDay >= $maxPostsThisDay){			
				# change style for not allowed
				$msg = str_replace('<BBB>',$cssDayForbid,$msg);
				$msg = str_replace('<EEE>','',$msg);
				continue;
			}	
		
			#choose the start time that will be used to sort the post time...
			$startSort = $startMinute;	
			
			/*
			if($pts_debug and True){
				$msg .= 'DEBUG: $dt = ' . $dt . '<br>';
				$msg .= 'DEBUG: date("Ymd",$startDate) = ' . date("Ymd",strtotime($startDate)) . '<br>';
			}
			*/
			
			
			$msgDayAvailble = '';
			
			$msgDayAvailble .= " (   $nPostsDay  "  . __('of','psm') . ' '. "$maxPostsThisDay ) ";
			
			$msgDayAvailble .= ' | <strong>' . __('Availble day!','psm') . '</strong>';				
			
			# if the day is today... check to see if there is time to publish within the time window configured...
			if($dt == date("Ymd",strtotime($startDate))){
				#$msg .=  '- esta data e hoje! Ainda da tempo?<br>';
				# https://codex.wordpress.org/Function_Reference/current_time
				$nowLocal = current_time('mysql', $gmt = 0); 
				# gete user local time in minutes...
				$nowTotalMinutes =  date('H',strtotime($nowLocal)) * 60 + date('i',strtotime($nowLocal));;
				
				if($nowTotalMinutes > $endMinute){
					$msgTooLateToday = ' | ' . __('Too late to publish','psm');				
					$msg = str_replace('<BBB>',$cssDayTooLate,$msg);
					$msg = str_replace('<DDD>',$msgTooLateToday,$msg);
					
					#$msg .=  '- Hoje mas ja passou da hora de publicar.<br>';
					continue;
				}			
				if($nowTotalMinutes < $startMinute){
					#$msg .=  '- OK! Artigo sera agendado. <br>';
					$msg = str_replace('<EEE>',$msgDayAvailble,$msg);
				}			
				if($nowTotalMinutes >= $startMinute){
					#$msg .=  '- OK! Artigo sera agendado. <br>';
					$msg = str_replace('<EEE>',$msgDayAvailble,$msg);
					$startSort = $nowTotalMinutes;
				}						
			}
			else{
				$msg = str_replace('<EEE>',$msgDayAvailble,$msg);
				#$msg .=  '- OK! Artigo sera agendado. <br>';
			}		
			
			$msg = str_replace('<BBB>',$cssDayAllowed,$msg);
			
			
			
			
			
			
			# replaces if were not replaced before...
			$msg = str_replace('<DDD>','',$msg);
			
			
			# find the time... randon!
			# even not necessary... but using seed for rand... 
			# using post-id to guarante the same time after click post...
			# http://www.php.net/manual/pt_BR/function.srand.php		
			srand(intval(sqrt($post->ID) * 10000));
					
			$minutePublish = rand($startSort,$endMinute);		
			if($minutePublish==0){
				#avoid divide by zero on module (%)...
				$minutePublish += 1;
			}				
			
			
			/*
			if($pts_debug and False){			
				$msg .= 'DEBUG: $datetimeCheck = ' . date("Ymd",$datetimeCheck) . '<br>';
				$msg .= 'date("Ymd",strtotime($startDate)) = ' .  date("Ymd",strtotime($startDate))  . '<br>';
			}
			*/
			
			
			# if next date is today... and it is the first post... publish 3 minute in future!
			if((date("Ymd",$datetimeCheck) == date("Ymd",strtotime($startDate)) & ($nPostsDay == 0))){
				$minutePublish = $startSort + 3;
			}
			
			$dthrPublish = date("Y-m-d",$datetimeCheck) .' '.  intval($minutePublish/60) .':'. $minutePublish%60;

			/*
			if($pts_debug){
				# sets the publish time to 1 minute in the future... to test cron!
				#$dthrPublish = date('Y-m-d H:i',strtotime(current_time('mysql', $gmt = 0) . ' + 65 minutes'));						
			}
			*/


			// next time schedule fix

				global $wpdb;
				$my_prefix = 'psm_';
				$my_table = $my_prefix. 'manage_schedule';
				date_default_timezone_set('Asia/Dhaka');

				$future_post_date=array();
				$my_query = new WP_Query( array( 'post_type'=>'post', 'posts_per_page' => -1, 'order' => 'ASC','post_status' => array( 'future' ), ) );

				while ($my_query->have_posts()) : $my_query->the_post();
					//$post_date = strtotime(the_date('Y-m-d H:i:s', '', '', FALSE));
					$post_date = strtotime(get_the_time( 'Y-m-d H:i' ));
					array_push($future_post_date,$post_date);
				endwhile;


				
				$sql 			= "SELECT * FROM ".$my_table;
				$day_schedules 	= $wpdb->get_results($sql, ARRAY_A);

				$all_day_schedule = array();
				$today_bar=date("l"); // use timezone  return:Sunday
				$todat_timestamp=date("Y-m-d H:i"); // use timezone  return:2018-11-11 23:10
				foreach($day_schedules as $day_schedule){
					//echo strtolower($today_bar)."--".strtolower($day_schedule['day'])."<br>";
					if(strtolower($today_bar) === strtolower($day_schedule['day']))
					{		
						 $nvar_2 = strtotime(date("Y-m-d")." ".$day_schedule['schedule']);
						 $todat_timestamp = strtotime($todat_timestamp);
						 if($nvar_2 > $todat_timestamp)
						 array_push($all_day_schedule,$nvar_2);	
					} 
					else
					{	
						$new_var = strtotime("Next ".$day_schedule['day']." ".$day_schedule['schedule']);
						array_push($all_day_schedule,$new_var);
					}
				}

				
				function nextWeek($presentWk){
					$pr_wk = count($presentWk);
					for($d=0;$d<$pr_wk;$d++){
						$presentWk[$d] = strtotime(date('Y-m-d H:i',$presentWk[$d]).' +7 day');

					}
					return $presentWk;
				}
				
				sort($all_day_schedule);

				
				$tt 			= count($all_day_schedule);
				$fp 			= count($future_post_date);


				// for($j=0;$j<$fp;$j++) {
				// 		echo date("Y-m-d H:i",$future_post_date[$j])."<br>";
				// }
				// echo "<br><br><br>";

				// $deserved_date	= strtotime(date("Y-m-d H:i:s"));

				$deserved_date = "";
				//for($i=0;$i<52;$i++) {
					for($j=0;$j<$tt;$j++) {
						//date("Y-m-d H:i",$all_day_schedule[$j])."<br>"; 
						if(!in_array($all_day_schedule[$j],$future_post_date)) { 
							$deserved_date = $all_day_schedule[$j]; 
							//date("Y-m-d H:i",$deserved_date)."<br>"; 
							break;
						}
						
						if($deserved_date){
						 	break;
						} 
						else{
						 	$all_day_schedule = nextWeek($all_day_schedule);
						}
						//echo date('Y-m-d H:i:s',$all_day_schedule[$j])."<br>";
							
					}
				//}
			
			
			
			
			# parcial message... not complete.
			$msgT = '';				
			
			$msgByPass =  __('To publish in a different date and bypass the plugin, first choose the schedule date from the WordPress controls above and then click the Schedule button!',  'psm');
			
			#$msgByPass = '<span style="font-size:11px;">' .  $msgByPass . '</span>';		
			#$msgT .= '<br>';	
			global $activate_pub_option;	
			global $activate_cal_option;
			$get_cal_op 			= get_option('cal_active_option');
			$activate_cal_option 	= html_entity_decode(stripslashes($get_cal_op));	
			if(!empty($activate_cal_option)){
				$dthrPublish=date("Y-m-d H:i:s",$deserved_date);
			}
			
			$msgT .=  '<p title="'.$msgByPass.'">';
			$msgT .= __('Will be schedule to','psm') . ': <br>';
			$msgT .= '<strong>';
			
			 	$msgT .= __(date("l",strtotime($dthrPublish)),'psm') . ', ' . date(get_option('date_format'),strtotime($dthrPublish)) . ' '. __('at','') .' ' . date(get_option('time_format') , strtotime($dthrPublish));
			 

			$msgT .= '</strong>';
			$msgT .= '</p>';				
			
			#$msgT .= '<br>';		
		
		
			# uses only to debug and show logs on main screen...
			if(!$changePost){		
				if($options['pts_infosize'] == 'all'){
					return $msg . $msgT;
				}
				else{
					return $msgT;
				}
				
			}
			else{
				# statistics to show how many post the plugin helps to schedule...
				if(array_key_exists('pts_statistics_total_work',$options)){
					$options['pts_statistics_total_work'] = $options['pts_statistics_total_work'] + 1;
				}
				else{
					$options['pts_statistics_total_work'] = 1;
				}
				update_option(basename(__FILE__, ".php"), $options);
				return $dthrPublish;		
			}
		}		
		
		if(!$changePost){
			return 
				__('Could not find a suitable date to this post!','psm').'<br>'.
				__('No changes will be made to this post date!','psm').'<br>'.
				__('Something is wrong!','psm').'<br>'.
				__('Please contact the plugin developer!','psm');
		}
		else{
			return null;		
		}

	//}//end
	
}



# this is where the magic happens... :)
function pts_do_publish_schedule($post){
	global $wpdb;
	global $pts_debug;	
	global $activate_pub_option;


	//if(isset($activate_pub_option) && !empty($activate_pub_option)){
		$newDate = pts_findNextSlot($post,True);

		# do nothing if cant find date...
		if($newDate == null){
			return $post;
		}

		# do nothing for pages! Should act only on post!
		if($post->post_type != 'post'){
			return;
		}	
		
		
		
		# changes post_date and pos_date_gmt
		$post->post_date = $newDate;
		
		# sum the timezone offset to get the right gmt time...
		$gmt_offset = get_option('gmt_offset') * (-1);
		
		# treatment to deal with things like GMT-2:30...
		$gmt_offsetHours = intval($gmt_offset); 
		$gmt_offsetMinutes = ($gmt_offset - $gmt_offsetHours) * 60;	
		# add the plus signal to concatenate on string time math below... the minus comes by default...
		if($gmt_offsetHours > 0){
			$gmt_offsetHours = '+'.$gmt_offsetHours; 
		}
		if($gmt_offsetMinutes > 0){
			$gmt_offsetMinutes = '+'.$gmt_offsetMinutes; 
		}
		
		$post->post_date_gmt =  date('Y-m-d H:i:s', strtotime($newDate .' ' .$gmt_offsetHours. ' hours ' .$gmt_offsetMinutes. ' minutes' ));
		
		if($pts_debug){
			echo 'Date and GMT date for the new post:<br>';
			echo $post->post_date;
			echo '<br>';
			echo $post->post_date_gmt;
			echo '<br>';
		}
		
		
		
		# changes post_status to be scheduled...
		$post->post_status = 'future';
		
		wp_update_post($post);		
		return $post;
	//}	
}



#	Define the options menu
function pts_option_menu() {
	global $plName;	
	if (function_exists('current_user_can')) {
		if (!current_user_can('manage_options')) return;
	} else {
		global $user_level;
		get_currentuserinfo();
		if ($user_level < 8) return;
	}
	if (function_exists('add_menu_page')) {
		//add_options_page($plName, $plName, "manage_options", __FILE__, 'pts_options_page');
		add_submenu_page( pluginsFOLDER,__( 'Manage Schedule'), __( 'Manage Schedule'), "manage_options", 'manage-schedule', 'pts_options_page');
	}
}
# Install the option in the WordPress configuration menu
add_action('admin_menu', 'pts_option_menu');




# Prepare the default set of options
$default_options['pts_start'] = '00:00';
$default_options['pts_end'] = '23:59';
$default_options['pts_infosize'] = 'parcial';
$default_options['pts_allowstats'] = 'yes';


// the plugin options are stored in the options table under the name of the plugin file sans extension
add_option(basename(__FILE__, ".php"), $default_options);

// This method displays, stores and updates all the options
function pts_options_page(){
	global $wpdb;
	global $plName;
	global $plUrl;
    global $pts_debug;
    global $pts_show_donate;
    global $activate_pub_option;


	# insert Google analytics code to monitor plugin usage.
	add_action('admin_footer', 'pts_insertAnalytics',12);
	
	
	$bit = explode("&",$_SERVER['REQUEST_URI']);
	// This bit stores any updated values when the Update button has been pressed
	if (isset($_POST['update_options'])) {

		if(isset($_POST['pub_check'])) {
			$pubs = $_POST['pub_check'];
			add_option('pub_active_option',$pubs);
			delete_option('cal_active_option');
			echo "<h3>Activated!</h3>";
		}elseif(isset($_POST['cal_check'])){
			$cals = $_POST['cal_check'];
			add_option('cal_active_option',$cals);
			delete_option('pub_active_option');
			echo "<h3>Manual Time Activated!</h3>";
		}else{
			delete_option('pub_active_option');
			echo "<h3>Deactivated!</h3>";
		}
		//else{
		//	echo 'nai';
		//}
		
		# loads before change with post values...
		$options = get_option(basename(__FILE__, ".php"));
		
		// Fill up the options array as necessary					
		$options['pts_start'] = $_POST['pts_start']; // like having business hours
		$options['pts_end'] = $_POST['pts_end'];		
		
		$options['pts_0'] = $_POST['pts_0'];
		$options['pts_1'] = $_POST['pts_1'];
		$options['pts_2'] = $_POST['pts_2'];
		$options['pts_3'] = $_POST['pts_3'];
		$options['pts_4'] = $_POST['pts_4'];
		$options['pts_5'] = $_POST['pts_5'];
		$options['pts_6'] = $_POST['pts_6'];
		
		$options['pts_infosize'] = $_POST['pts_infosize'];
		
		$options['pts_allowstats'] = $_POST['pts_allowstats'];
		
		
		# if all weeks are NO... change the monday to YES
		$allNo = 0;
		for($i=0;$i<7;$i++){
			if($options['pts_'.$i] == 'no'){
				$allNo += 1;
			}
			else{
				break;
			}
		}
		if($allNo == 7){
			$options['pts_1'] = 'Yes';
		}
		
		
		
		while (strlen($options['pts_start']) < 5) $options['pts_start'] = "0" . $options['pts_start'];
		while (strlen($options['pts_end']) < 5) $options['pts_end'] = "0" . $options['pts_end'];		
		if (!gmdate('H:i',$options['pts_start'])) $options['pts_start'] = '00:00'; //guarantee a valid time
		if (!gmdate('H:i',$options['pts_end'])) $options['pts_end'] = '23:59';
		$time = explode(":",$options['pts_start']);
		if (strlen($time[0]) < 2) $time[0] = '0' . $time[0];
		if (strlen($time[1]) < 2) $time[1] = '0' . $time[1];
		$options['pts_start'] = date("H:i",mktime($time[0],$time[1],0,9,11,2001)); // convert overruns
		$time = explode(":",$options['pts_end']);
		if (strlen($time[0]) < 2) $time[0] = '0' . $time[0];
		if (strlen($time[1]) < 2) $time[1] = '0' . $time[1];
		$options['pts_end'] = date("H:i",mktime($time[0],$time[1],0,9,11,2001));
		
		// store the option values under the plugin filename
		update_option(basename(__FILE__, ".php"), $options);
		
		// Show a message to say we've done something
		if($allNo == 7){
			echo '<div class="updated"><p>' . __('You must check "Yes" for at least 1 day of week! ', 'psm') . '</p></div>';	
		}
		else{
			echo '<div class="updated"><p>' . __('Options saved!', 'psm') . '</p></div>';	
		}		
		
	} else {
		$options = get_option(basename(__FILE__, ".php"));
	}
	

	
	# OPTIONS SCREEN
	//now we drop into html to display the option page form
	?>
		<div class="wrap">
		
		
		<h2 style="margin-bottom: 30px;"  title="<?php 
		_e('Plugin version','psm');
		echo ': ';
		echo pts_get_version() 
		?>"><?php echo ucwords(str_replace('-', ' ', basename(__FILE__, ".php"))) .' - '. __('Options', 'psm'); ?></h2>
		
		

		<form method="post" action="" >
			<div id="pts_form">

				<input type="checkbox" id="pub_check" name="pub_check" value="<?php if(!empty($activate_pub_option)){ echo $activate_pub_option;}else{ echo 'ok'; } ?>" <?php if ( isset($_POST['pub_check']) || !empty($activate_pub_option) ) { echo 'checked="checked"'; }?> >Check To Active

				
				<fieldset class="options" style="margin-top: 30px;">				
				

				<?php
				if($pts_debug){
					echo '<h3><strong style="color:red;">'.$plName.' - <span style="text-decoration:blink">Debug active!</span></strong></h3>';								
				}
				?>

				<?php				
					$days = array('sunday','monday','tuesday','wednesday','thursday','friday','saturday');
					
					/*
					$a = _e('Sunday','pts');
					$a = _e('Monday','pts');
					$a = _e('Tuesday','pts');
					$a = _e('Wednesday','pts');
					$a = _e('Thursday','pts');
					$a = _e('Friday','pts');
					$a = _e('Saturday','pts');
					*/
					
				?>
				
				
				<table>	
					
					<?php
					$iday = 0;
					foreach($days as $day){
						#echo $day;
						
					?>
						
						<tr valign="top">
							<th scope="row" align="left" style="padding:5px;"><?php _e(ucfirst($day), 'pts') ?>:</th>
							
							<td style="padding:5px;">					
								<input 
									type="text" 
									id="<?php echo $day; ?>"
									name="<?php echo "pts_$iday"; ?>" 
									value="<?php if ($options["pts_$iday"] == 'no') echo '0'; else if ($options["pts_$iday"] == 'yes') echo '1'; else echo $options["pts_$iday"]; ?>" 
									style="width: 40px;"/>
							</td>
							
						</tr>


					<?php
						
						$iday += 1;
					}
					
					?>

				</table>
				
				
				<h3 style="margin-top:10px;"><?php _e('Specify the time interval in which you want to have your posts scheduled!',  'psm')?></h3>
				
				<table class="optiontable">
					<tr valign="top">
						<th scope="row" align="left"><?php _e('Start Time', 'psm') ?>:</th>
						<td><input name="pts_start" type="text" id="start" value="<?php echo $options['pts_start']; ?>" size="10" /><?php _e(' (defaults to 00:00)', 'psm') ?>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row" align="left"><?php _e('End Time', 'psm') ?>:</th>
						<td><input name="pts_end" type="text" id="end" value="<?php echo $options['pts_end']; ?>" size="10" /><?php _e(' (defaults to 23:59)', 'psm') ?>
						</td>
					</tr>
				</table>


				<?php
				
				
				$msgTimeWrong = '

				<h3 style="margin-top:20px;">'. __('Your WordPress timezone settings might be incorrect!', 'psm').		
				'</h3>'
				 . __('The date and time we detect : ') . '<span style="color:blue;font-weight:bold;">'
				.date(get_option('date_format').', '.get_option('time_format'),current_time('timestamp', $gmt = 1)).
				'</span>';
				
				
				
				$msgTimeOK ='

				<h3 style="margin-top:20px;">'. __('Your timezone configuration and server time seems to be OK!','psm').		
				'</h3>'
				 .' <span style="color:green;font-weight:bold;">'.date(get_option('date_format').', '.get_option('time_format'),current_time('timestamp', $gmt = 0)).'</span>';
				
				
				$msgTimeWrong = '<h3 style="margin-top:20px;">'		
				. __('Your WordPress timezone settings might be incorrect!', 'psm').								
				'</h3>'
				. __('According to your web server','psm') .		
				', '
				. __('the GMT time is: ','psm') . ' <span style="color:blue;font-weight:bold;">'.date(get_option('date_format').', '.get_option('time_format'),current_time('timestamp', $gmt = 1)).'.</span>'.
				'<br>'
				. __('The timezone configured in your','psm').' <a target="_blank" href="options-general.php">'.__('WordPress settings','psm').'</a> '.__('is','psm') .': <span style="color:blue;font-weight:bold;">'.get_option('gmt_offset').', </span>'.
				'<br>'			
				. __('so your server think that is your local time is: ','psm') . ' <span style="color:red;font-weight:bold;">'.date(get_option('date_format').', '.get_option('time_format'),current_time('timestamp', $gmt = 0)).'</span> ... '
				. __('but this is different from time on you machine now!','psm').
				'<br>'
				. __('If the difference is not too big (less than 2h or 3h) you problably will not have side effects and the plugin should work fine!','psm').
				'<br>'
				. __('Othewise, with big time differences, you can have issues with the real time that each post will be scheduled!','psm').
				'<br>'
				. __('Sometimes you have to set a different timezone to compensate daylight saving time or a missconfigured server time! ','psm').
				
				'<br>'
				. __('If you can, change the timezone to correct this, refresh this page and this message will be shown anymore!','psm')		
				;		
				
				
				
				
				# javascript to compare the times...
				echo pts_createJsToCompareTime($msgTimeWrong,$msgTimeOK);			
				
				# div usada para reportar hora incorreta...		
				echo '<div id="divjsCT"></div>';
				
				echo '<script type="text/javascript">	
						jsCompareTimes();
					</script>';

				?>
				
				</fieldset>
				
			</div>
			<?php 
			$future_post_date=array();
				$my_query = new WP_Query( array( 'post_type'=>'post', 'posts_per_page' => -1, 'order' => 'ASC','post_status' => array( 'future' ), ) );

				while ($my_query->have_posts()) : $my_query->the_post();
					$post_date = the_date('Y-m-d H:i:s', '', '', FALSE); 
					//array_push($future_post_date,$post_date);
				endwhile;
				// // next time schedule fix

				// global $wpdb;
				// $my_prefix = 'psm_';
				// $my_table = $my_prefix. 'manage_schedule';

				// $future_post_date=array();
				// $my_query = new WP_Query( array( 'post_type'=>'post', 'posts_per_page' => -1, 'order' => 'ASC','post_status' => array( 'future' ), ) );

				// while ($my_query->have_posts()) : $my_query->the_post();
				// 	$post_date = strtotime(the_date('Y-m-d H:i:s', '', '', FALSE));
				// 	array_push($future_post_date,$post_date);
				// endwhile;


				

				
				// $sql 			= "SELECT * FROM ".$my_table;
				// $day_schedules 	= $wpdb->get_results($sql, ARRAY_A);

				// $all_day_schedule = array();
				// $today_bar=date("l"); // use timezone  return:Sunday
				// $todat_timestamp=strtotime(date("Y-m-d H:i")); // use timezone  return:2018-11-11 23:10
				// foreach($day_schedules as $day_schedule){
					
				// 	if(strtolower($today_bar)==strtolower($day_schedule['day']))
				// 	{	
				// 		 $nvar_2=strtotime(date("Y-m-d")." ".$day_schedule['schedule']);
				// 		 if($nvar_2>$todat_timestamp)
				// 		 array_push($all_day_schedule,$nvar_2);	
				// 	} 
				// 	else{
				// 	$new_var = strtotime("Next ".$day_schedule['day']." ".$day_schedule['schedule']);
				// 	array_push($all_day_schedule,$new_var);
				// 	}
				// }

				// function nextWeek($presentWk){
				// 	$pr_wk = count($presentWk);
				// 	for($d=0;$d<$pr_wk;$d++){
				// 		$presentWk[$d] = strtotime(date('Y-m-d H:i',$presentWk[$d]).' +7 day');

				// 	}
				// 	return $presentWk;
				// }
				
				// $nxtWk = nextWeek($all_day_schedule);
				// $loop_c = count($nxtWk);

				// sort($nxtWk);

				// for($p=0;$p<$loop_c;$p++){
				// 	echo date("Y-m-d H:i",$nxtWk[$p])."<br>";
				// } 

				// echo "<pre>";
				// print_r( $nxtWk );
				// echo "</pre>";
				

			 ?>

			<?php
			//start here
				global $wpdb;
				$my_prefix = 'psm_';
				$my_table = $my_prefix. 'manage_schedule';

				$get_cal_op 			= get_option('cal_active_option');
				$activate_cal_option 	= html_entity_decode(stripslashes($get_cal_op));

				if( isset($_POST['man_submit']) )
				{

					$man_days 	= $_POST['man_days'];
					$man_times 	= $_POST['man_times'];

					if(!empty($man_days) && !empty($man_times))
		  			{
			  			//insert query for psm_manage_schedule table
					  	$sql = "INSERT INTO  ".$my_table." (`day`,`schedule`) VALUES ('$man_days','$man_times')";
						$insert = $wpdb->query($sql);

						if($insert)
						{
							$massage = "Manual schedule has been set for ".$man_days;
						}else
						{
						  	$massage = "Something Wrong!";
						}
		  			}else
					{
					  	$massage = "Field must not be empty!";
					}

				}


				//select psm_manage_schedule data
				$sql 		= "SELECT * FROM ".$my_table;
				$schedules 	= $wpdb->get_results($sql, ARRAY_A);

			 ?>

			<div id="man_form">

				<input type="checkbox" id="cal_check" name="cal_check" value="<?php if(!empty($activate_cal_option)){ echo $activate_cal_option;}else{ echo 'ok'; } ?>" <?php if ( isset($_POST['cal_check']) || !empty($activate_cal_option) ) { echo 'checked="checked"'; }?> >Active

				<div class="submit">
					<input type="submit" name="update_options" value="<?php _e('Save all changes', 'psm') ?>"  style="font-weight:bold;" />
				</div>

				<form action=""  method="post">

					<h2 style="color: green;"><?php if( isset($massage) ){  echo $massage; } ?></h2> 
					<div class="man_options">
						<select name="man_days" id="man_days">
							<option value="">Select Days</option>
							<option value="saturday">saturday</option>
							<option value="sunday">sunday</option>
							<option value="monday">monday</option>
							<option value="tuesday">tuesday</option>
							<option value="wednesday">wednesday</option>
							<option value="thursday">thursday</option>
							<option value="friday">friday</option>
						</select>
						<select name="man_times" id="man_times">
							<option value="">Select Time</option>
							<!-- Loop for custom manage time after 30min per option -->
							<?php
								$start = "00:00";
								$end = "24:00";

								$tStart = strtotime($start);
								$tEnd = strtotime($end);
								$tNow = $tStart;

								while($tNow <= $tEnd){
								  $time_sch = date("H:i",$tNow)."\n";
								  echo '<option value="'.$time_sch.'">'.$time_sch.'</option>';
								  $tNow = strtotime('+30 minutes',$tNow);
								}
							?>

						</select>
						<input type="submit" name="man_submit" value="SET">
					</div>
					<?php 
						global $wpdb;
						$my_prefix = 'psm_';
						$my_table = $my_prefix. 'manage_schedule';


						$sql 			= "SELECT * FROM ".$my_table." WHERE day='saturday'";
						$sat_schedules 	= $wpdb->get_results($sql, ARRAY_A);
						

						$sql 			= "SELECT * FROM ".$my_table." WHERE day='sunday'";
						$sun_schedules 	= $wpdb->get_results($sql, ARRAY_A);

						$sql 			= "SELECT * FROM ".$my_table." WHERE day='monday'";
						$mon_schedules 	= $wpdb->get_results($sql, ARRAY_A);

						$sql 			= "SELECT * FROM ".$my_table." WHERE day='tuesday'";
						$tue_schedules 	= $wpdb->get_results($sql, ARRAY_A);

						$sql 			= "SELECT * FROM ".$my_table." WHERE day='wednesday'";
						$wed_schedules 	= $wpdb->get_results($sql, ARRAY_A);

						$sql 			= "SELECT * FROM ".$my_table." WHERE day='thursday'";
						$thu_schedules 	= $wpdb->get_results($sql, ARRAY_A);

						$sql 			= "SELECT * FROM ".$my_table." WHERE day='friday'";
						$fri_schedules 	= $wpdb->get_results($sql, ARRAY_A);
					 ?>

					<ul class="schedule-list" style="margin-top: 20px;">
						<li>
							<span>sat</span>
							<?php  
								// loop of saturday data
								$count = count($sat_schedules);
								for($i=0;$i<=$count;$i++){
									$sat = $sat_schedules[$i]['schedule']."<br>";
									echo '<span>'.$sat.'</span>';
								}
							?>
							
						</li>
						<li>
							<span>sun</span>
							<?php  
								// loop of sunday data
								$count = count($sun_schedules);
								for($i=0;$i<=$count;$i++){
									$sun = $sun_schedules[$i]['schedule']."<br>";
									if( !empty($sun) ){
										echo '<span>'.$sun.'</span>';
									}else{
										echo '<span>-</span>';
									}
								}
							?>
						</li>
						<li>
							<span>mon</span>
							<?php  
								// loop of monday data
								$count = count($mon_schedules);
								for($i=0;$i<=$count;$i++){
									$mon = $mon_schedules[$i]['schedule']."<br>";
									if( isset($mon) && !empty($mon) ){
										echo '<span>'.$mon.'</span>';
									}else{
										echo '<span>-</span>';
									}
								}
							?>
						</li>
						<li>
							<span>tue</span>
							<?php  
								// loop of tuesday data
								$count = count($tue_schedules);
								for($i=0;$i<=$count;$i++){
									$tue = $tue_schedules[$i]['schedule']."<br>";
									if( isset($tue) && !empty($tue) ){
										echo '<span>'.$tue.'</span>';
									}else{
										echo '<span>-</span>';
									}
								}
							?>
						</li>
						<li>
							<span>wed</span>
							<?php  
								// loop of wednesday data
								$count = count($wed_schedules);
								for($i=0;$i<=$count;$i++){
									$wed = $wed_schedules[$i]['schedule']."<br>";
									if( isset($wed) && !empty($wed) ){
										echo '<span>'.$wed.'</span>';
									}else{
										echo '<span>-</span>';
									}
								}
							?>
						</li>
						<li>
							<span>thu</span>
							<?php  
								// loop of thursday data
								$count = count($thu_schedules);
								for($i=0;$i<=$count;$i++){
									$thu = $thu_schedules[$i]['schedule']."<br>";
									if( isset($thu) && !empty($thu) ){
										echo '<span>'.$thu.'</span>';
									}else{
										echo '<span>-</span>';
									}
								}
							?>
						</li>
						<li>
							<span>fri</span>
							<?php  
								// loop of friday data
								$count = count($fri_schedules);
								for($i=0;$i<=$count;$i++){
									$fri = $fri_schedules[$i]['schedule']."<br>";
									if( isset($fri) && !empty($fri) ){
										echo '<span>'.$fri.'</span>';
									}else{
										echo '<span>-</span>';
									}
								}
							?>
						</li>
					</ul>
				</form>		
			</div>

			
			
					
		</form>


		
		</div>
	<?php	
}
$options = get_option(basename(__FILE__, ".php"));


	
// Add settings link on plugin page
function pts_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page='.plugin_basename(__FILE__).'">' . __('Settings','psm') .'</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
} 
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'pts_settings_link' );	

