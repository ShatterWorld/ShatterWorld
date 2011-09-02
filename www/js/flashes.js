$(document).ready(function(){	
	/**
	* Hides flash message
	*/
	$("#flashes .flash").live('click', function(){ 
		$(this).hide("fast");
	});
});
