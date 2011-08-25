$(document).ready(function(){
	$("#menu").children("div:first-child").children("ul").show("slow"); /*displays the firth submenu*/
	
	/**
	* Hides flash message
	*/
	$("#flashes .flash").click(function(){ 
		$(this).hide("fast");
	});
});
