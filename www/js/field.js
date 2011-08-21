/**
* Deals with field actions
* @author Petr Bělohlávek
*/
$(document).ready(function(){

	/**
	* Number of clicks
	*/
	var clicks = 0;

	/**
	* Last clicked field
	*/
	var prevField = null;

	/**
	* Data (indexed by html tags data-STH, e.g. data-coords -> index='coords')
	*/
	var data = null;
	
	/**
	* Fills #fieldInfo when user gets mouse over a field
	* @return void
	*/
	$(".field").mouseenter(function(e){

		fetchData(this);

		$("#fieldInfo #coords").html('Souřadnice ['+data['coords'][0]+';'+data['coords'][1]+']');
		$("#fieldInfo #realCoords").html('Souřadnice ['+data['realcoords'][0]+';'+data['realcoords'][1]+']');
		$("#fieldInfo #owner").html('Vlastník '+data['owner']);
		$("#fieldInfo #type").html('Typ '+data['type']);
			
	});	
		
	
	/**
	* Runs when user click some field and increment clicks by one
	* Bugs:	-doesnt mark the second field
	* @return void
	*/
	$(".field").click(function(){

		mark(this, data['coords'][0], data['coords'][1]);
		clicks++;		

		if (clicks < 2)
		{
			clearActions();
			prevField = this;
		
		}
		else if ((clicks > 2) || (this == prevField))
		{
			unMarkAll();
			clearActions();
			prevField = null;
		
		}
		else if (clicks == 2)
		{
			showMenu();
//			unMarkAll();
			prevField = this;
		}
		
				
	});

	/**
	* Marks the specified field
	* @return void
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
	* @return void
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
	* @return void
	*/
	function showMenu()
	{
		var x = data['realcoords'][0];
		var y = data['realcoords'][1];
//		alert('tmp');
		
		/*TODO: action-click clears actions and unmarks fields*/
		
		clearActions();
		addAction('Útok<br/>');
		addAction('Podpora<br/>');
		addAction('Poslat suroviny<br/>');
		
		//$('#mapContainer').prepend('<div id="#contextMenu" style="background: url(\"../images/hex_marked.png\"); position: absolute; top: +'x'+; left: +'y'+;">bla<br/>bla2</br></div>');
		
		//alert('<div id="#contextMenu" style="background: url(\'../images/hex_marked.png\'); position: absolute; top: +'x'+; left: +'y'+; width: 50px; height:50px;">bla<br/>bla2<br/>bla3</div>');	
	}
	
	
	/**
	* Sets global data (JSON) from object named e.g. data-coord
	* @param object
	* @return void
	*/
	function fetchData(object)
	{
		data = $(object).data();
	}
	
	/**
	* Clears #fieldActions
	* @return void
	*/
	function clearActions()
	{
		$('#fieldActions').html('');
	}
	
	/**
	* Clears #fieldActions
	* @param action
	* @return void
	*/
	function addAction(action)
	{
		$('#fieldActions').append(action);
	}
	
});
