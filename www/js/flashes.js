$(document).ready(function(){
	$("#menu").children("div:first-child").children("ul").show("slow"); /*displays the firth submenu*/
	
	$("#flashes .flash").click(function(){ /*toogles visibility*/
		$(this).hide("fast");
	});
});
