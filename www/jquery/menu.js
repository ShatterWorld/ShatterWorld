$(document).ready(function(){
  $(".header").click(function(){
	$(".submenu ul").hide("fast");
    $(this).parent().children("ul").show("fast");
  });
});
