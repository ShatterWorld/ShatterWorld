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
	* @var integer - Number of clicks
	*/
	var clicks = 0;

	/**
	* @var Field - Last clicked field
	*/
	var prevField = null;

	/**
	* @var JSON - Data (indexed by html tags data-STH, e.g. data-coords -> index='coords')
	*/
	var data = null;

	/**
	* @var string - Basepath
	*/
	var basepath = $('#map').data()['basepath'];

/* tohle je sakra co? */
	var markerImage = $('<img class="marker" />').attr('src', basepath + '/images/fields/marker.png');

	/**
	* Shows and fills #fieldInfo and #fieldActions when user gets mouse over a field
	* @return void
	*/
	$(".field").mouseenter(function(){

		fetchData(this);
		$('#fieldInfo').show();

		$("#fieldInfo #coords").html('Souřadnice ['+data['coords'][0]+';'+data['coords'][1]+']');
		$("#fieldInfo #owner").html('Vlastník '+data['owner']);
		$("#fieldInfo #type").html('Typ '+data['type']);

	});


	/**
	* Hides #fieldInfo
	* @return void
	*/
	$(".field").mouseleave(function(){
		$('#fieldInfo').hide();
	});


	/**
	* Moves with #infoBox
	* @return void
	*/
	//$(".field").mouseover(function(e){

	$(".field").mousemove(function(e) {
		$('#fieldInfo').css("left", e.pageX + 20);
		$('#fieldInfo').css("top", e.pageY + 20);
	});

	/**
	* Shows #fieldDetail filled with details of field and possible actions
	* @return void
	*/
	$(".field").dblclick(function(){
		$('#fieldDetail').hide();
		$('#fieldDetail').show('fast');

		$('#fieldImg').attr('src', basepath+'/images/fields/hex_'+data['color']+'.png');

		$('#detailCoords').html('['+data['coords'][0]+';'+data['coords'][1]+']');
		$('#detailOwner').html(data['owner']);
		$('#detailType').html(data['type']);
		$('#detailFacility').html(data['facility']);
		$('#detailLevel').html(data['level']);



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
			unmarkAll();
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
		$('#field_'+data['coords'][0]+'_'+data['coords'][1]).append(markerImage.clone());
	}

	/**
	* Unmarks all fields and sets click to zero
	* @return void
	*/
	function unmarkAll()
	{
		$('.marker').remove();
		clicks = 0;
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



	/**
	 * @var integer represents speed of navigation [ms]
	 */
	var navigSpeed = 400;

	/**
	 * @var integer how far the scroller moves [px]
	 */
	var slide = 250;


	$('#navig #up').click(function(){
			var pos = $('#mapContainer').scrollTop();
			$('#mapContainer').animate({scrollTop: pos-slide}, navigSpeed);
	});
	$('#navig #down').click(function(){
			var pos = $('#mapContainer').scrollTop();
			$('#mapContainer').animate({scrollTop: pos+slide}, navigSpeed);
	});

	$('#navig #left').click(function(){
			var pos = $('#mapContainer').scrollLeft();
			$('#mapContainer').animate({scrollLeft: pos-slide}, navigSpeed);
	});
	$('#navig #right').click(function(){
			var pos = $('#mapContainer').scrollLeft();
			$('#mapContainer').animate({scrollLeft: pos+slide}, navigSpeed);
	});

	$('#navig #leftup').click(function(){
			var posTop = $('#mapContainer').scrollTop();
			$('#mapContainer').animate({scrollTop: posTop-slide}, navigSpeed);
			var posLeft = $('#mapContainer').scrollLeft();
			$('#mapContainer').animate({scrollLeft: posLeft-slide}, navigSpeed);
	});
	$('#navig #rightup').click(function(){
			var posTop = $('#mapContainer').scrollTop();
			$('#mapContainer').animate({scrollTop: posTop-slide}, navigSpeed);
			var posLeft = $('#mapContainer').scrollLeft();
			$('#mapContainer').animate({scrollLeft: posLeft+slide}, navigSpeed);
	});

	$('#navig #leftdown').click(function(){
			var posTop = $('#mapContainer').scrollTop();
			$('#mapContainer').animate({scrollTop: posTop+slide}, navigSpeed);
			var posLeft = $('#mapContainer').scrollLeft();
			$('#mapContainer').animate({scrollLeft: posLeft-slide}, navigSpeed);
	});
	$('#navig #rightdown').click(function(){
			var posTop = $('#mapContainer').scrollTop();
			$('#mapContainer').animate({scrollTop: posTop+slide}, navigSpeed);
			var posLeft = $('#mapContainer').scrollLeft();
			$('#mapContainer').animate({scrollLeft: posLeft+slide}, navigSpeed);
	});


});
