$(document).ready(function(){

	/**
	* Hides all submenus except the first one
	*/
	$("#menu .submenu ul").hide();
	$("#menu").children("div:first-child").children("ul").show("slow"); /*displays the firth submenu*/
	
	/**
	* Toogles visibility of submenus
	*/
	$("#menu .submenu h3").click(function(){ 
		$(this).parent().children("ul").toggle("fast");
	});

	/**
	* Click the link even it user clicks only <li>
	*/
	$("#menu .submenu li").click(function(){ 
		location.href = $(this).children("a").attr('href');
	});
});
