/**
* Deals with field actions
* @author Petr Bělohlávek
*
*
*	TODO:	- improve marking
			- improve actions
			- create context menu
*
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
	* Array of marked fields
	*/
	//var markedFields;
	
	/**
	* Array of marked fields data
	*/
	//var markedFieldsData[];
	
	/**
	* Fills #fieldInfo and #fieldActions when user gets mouse over a field
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
	* Shows #fieldDetail filled with details of field and possible actions
	* @return void
	*/
	$(".field").dblclick(function(){
		$('#fieldDetail').hide();
		$('#fieldDetail').show('fast');
		
		//$('#fieldImg').attr('src', '');
		$('#fieldImg').attr('src', data['basepath']+'/images/fields/hex_'+data['color']+'.png');

		$('#detailCoords').html('['+data['coords'][0]+';'+data['coords'][1]+']');
		$('#detailOwner').html(data['owner']);
		$('#detailType').html(data['type']);
		$('#detailBuilding').html(/*data['']*/'SthBuilding');
		$('#detailBuildingLevel').html(/*data['']*/'SthBuildingLvl');
		
		
			
	});	

	/**
	* Hides #fieldDetail
	* @return void
	*/
	$('#closeFieldDetail').click(function(){
		$('#fieldDetail').hide('fast');
	});		


	/**
	* Runs when user click some field and increment clicks by one
	* Bugs:	-doesnt mark the second field
	* @return void
	*/
	$(".field").click(function(){

		clicks++;		
		mark(this);

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
			prevField = this;
		}
		
				
	});

	/**
	* Marks the specified field
	* @return void
	*/
	function mark(field)
	{
	/*
		var markedFields;
		markedFields.push("ahoj");
		markedFieldsData.push(data);
		$(field).attr('src', data['basepath']+'/images/fields/hex_'+data['color']+'_marked.png');
	*/		
		
		/*
		var src = $(field).attr("src");
		$(field).attr("class", "field marked");
		var newSrc = src.replace('.png', '_marked.png');
		$(field).attr("src", newSrc);
*/
	
		
		$(field).css(
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
		
		//markedFields = null;
		//markedFieldsData = null;
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
		
		//$('#mapContainer').prepend('<div id="#contextMenu" style="background: url(\'../images/hex_marked.png\'); position: absolute; top: +'x'+; left: +'y'+; width: 50px; height:50px;">bla<br/>bla2<br/>bla3</div>');	
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
