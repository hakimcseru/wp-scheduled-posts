jQuery(document).ready(function($) {
	// timepicker function
	$('#man_times').chungTimePicker({
		viewType: 1
	});

	// check uncheck active option
	$("#pts_form input:checkbox,#man_form input:checkbox").click(function(){
		var this_name = $(this).attr("name");
		//alert(this_name);
		if(this_name === 'pub_check'){
			$('#pub_check').attr("checked",true);
			$('#cal_check').attr("checked",false);
		}else if(this_name === 'cal_check'){
			$('#cal_check').attr("checked",true);
			$('#pub_check').attr("checked",false);
		}else{
			return false;
		}
	    // var group = "input:checkbox[name='"+$(this).attr("name")+"']";
	    // $(group).attr("checked",false);
	    // $(this).attr("checked",true);
	});


	
});




