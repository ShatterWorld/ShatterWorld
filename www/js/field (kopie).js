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
	* Runs when user click some field
	* - increment clicks
	* - deals with clicking the same filed
	* -
	*/
	$(".field").click(function(){
		var idStr = $(this).attr('id');
		var id = idStr.split('_'); // 0 = prefix; 1 = x; 2 = y

		clicks++;		
		mark(this, id[1], idY[2]);
		
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
		v//ar src = $(object).attr("src");
		//var newSrc = src.subsrt(0, src.lenght()-4) + '_marked.png';
		//alert(newSrc);
		//$(object).attr("src", "{$basePath}/images/fields/hex_marked.png");
		
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
		$(".field").css(
			{
				"border" : "none"
			}
		);	
		
		clicks = 0;

	}
	
	/**
	* Displays context menu
	*/
	function showMenu()
	{
		alert("tmp menu");
	
	}
	
});
