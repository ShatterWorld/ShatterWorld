/**
* Marker
* @author Petr Bělohlávek
*/
$(document).ready(function(){

	/**
	* Number of clicks
	*/
	var clicks = 0;
	var prevIdStr = "";

	/**
	* Fills #info when user gets mouse over a field
	* Needs to be fixed
	*/
	$(".field").mouseenter(function(e){
		var idStr = $(this).attr('id');
		var id = idStr.split('_');
	
		$("#info #coord").html('Souřadnice ['+coord[0]+';'+id[2]+']');//Rather JSON
		
	
	
	});	
	
	/**
	* Runs when user click some field and increment clicks by one
	*/
	$(".field").click(function(){
		var idStr = $(this).attr('id');
		var id = idStr.split('_');

		clicks++;		
		mark(this, id[1], id[2]); //doesnt mark the second click.. ?
		
		if ((clicks > 2) || (idStr == prevIdStr))
		{
			unMarkAll();
		
		}
		else if (clicks == 2)
		{
			showMenu();
			unMarkAll();
		}
		
		prevIdStr = idStr;
				
	});

	/**
	* Marks the specified field
	*/
	function mark(object, x, y)
	{
	/*
		var src = $(object).attr("src");
		$(object).attr("class", "field marked");
		var newSrc = src.replace('.png', '_marked.png');
		$(object).attr("src", newSrc);
*/
	
		
		$(object).css(
			{
				"border" : "1px solid red"
			}
		);		
		
	}
	
	/**
	* Unmarks all fields and sets click to zero
	*/
	function unMarkAll()
	{
		/*var src = $(".marked").attr("src");
		var newSrc = src.replace('_marked.png', '.png');
		$(".marked").attr("src", newSrc);
		$(object).attr("class", "field");
*/		
		clicks = 0;

		$(".field").css(
			{
				"border" : "none"
			}
		);

	}
	
	/**
	* Displays context menu
	*/
	function showMenu()
	{
		alert("tmp menu");
	
	}
	
});
