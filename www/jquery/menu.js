$(document).ready(function(){
  $(".header").click(function(){
	$(".submenu ul").hide();
    $(this).parent().children("ul").show();
  });
});
