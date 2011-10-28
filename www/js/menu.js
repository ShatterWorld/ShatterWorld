$(document).ready(function(){

	/**
	* Hides all submenus except the first one
	*/
	$("#menu .submenu ul").hide();
	$("#menu .active ul").show();

	/**
	* Toggles visibility of submenus
	*/
	$("#menu .submenu h3").click(function(){
		$(this).parent().children("ul").toggle("fast");
	});

});
