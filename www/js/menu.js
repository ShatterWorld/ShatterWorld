$(document).ready(function(){
	$("#menu .submenu ul").hide();
	$("#menu").children("div:first-child").children("ul").show("slow"); /*displays the firth submenu*/
	
	$("#menu .submenu h3").click(function(){ /*toogles visibility*/
		$(this).parent().children("ul").toggle("fast");
	});
});
