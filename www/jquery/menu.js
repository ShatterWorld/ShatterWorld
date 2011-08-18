$(document).ready(function(){
	$("#menu").children("div:first-child").children("ul").show("slow"); /*displays the firth submenu*/
	
	$(".header").click(function(){ /*toogles visibility*/
		$(".submenu ul").hide("fast");
 		$(this).parent().children("ul").show("fast");
	});
});
