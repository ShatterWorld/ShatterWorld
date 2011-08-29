/**
* Map renderer
* @author Petr Bělohlávek
*
*/
$(document).ready(function(){

	alert("render");

	/**
	* @var string - Basepath
	*/
	var basepath = $('#map').data()['basepath'];

	/**
	* @var integer - Number of marked fields
	*/
	var markedFields = 0;

	/**
	* @var Field - Last clicked field
	*/
	var prevField = null;


	/**
	 * @var represents marker
	 */
	var markerImage = $('<img class="marker" />').attr('src', basepath + '/images/fields/marker.png');


	/**
	 * ajax that gets JSON data of visibleFields
	 *
	 */
	$.getJSON('?do=fetchMap', function(data) {

		//$('#menu').append('<div>ajax</div>');

		$.each(data['fields'], function(key, field) {

			var posX = (field['x'] * 43) + (field['y']*43);
			var posY = (field['x'] * -20) + (field['y'] * 19);
			var zIndex = '5';
			var background = "url('"+basepath+"/images/fields/hex_"+field['type']+".png')";

			var div = $('<div class="field" />').attr('id', 'field_'+posX+'_'+posY);
			var divStyle = 'width: 60px; height: 40px; position: absolute; left: '+posX+'px; top: '+posY+'px; z-index: '+zIndex+'; background: '+background+';';
			div.attr('style', divStyle);

			div.attr('data-coords', '[' + field['x'] + ',' + field['y'] + ']');
			div.attr('data-type', field['type']);
			div.attr('data-level', field['level']);
			div.attr('data-facility', (field['facility'] == null) ? "žádná" : field['facility']);
			div.attr('data-owner', (field['owner'] == null) ? "---" : field['owner']);

			var borderType = 'mine';
			var borderStyle = 'position: absolute; left: 0px; top: 0px; z-index: 9999999';
			var border = $('<img class="border" />').attr('src', basepath + '/images/fields/border_'+borderType+'.png');
			border.attr('id', 'field_'+posX+'_'+posY);
			border.attr('style', borderStyle);

			div.append(border);
			$('#map').append(div);



			/**
			* Shows and fills #fieldInfo and #fieldActions when user gets mouse over a field
			* @return void
			*/
			div.mouseenter(function(){
				$('#fieldInfo').show();

				$("#fieldInfo #coords").html('Souřadnice ['+field['x']+';'+field['y']+']');
				$("#fieldInfo #owner").html('Vlastník '+field['owner']);
				$("#fieldInfo #type").html('Typ '+field['type']);

			});


			/**
			* Hides #fieldInfo
			* @return void
			*/
			div.mouseleave(function(){
				$('#fieldInfo').hide();
			});


			/**
			* Moves with #infoBox
			* @return void
			*/
			div.mousemove(function(e) {
				$('#fieldInfo').css("left", e.pageX + 20);
				$('#fieldInfo').css("top", e.pageY + 20);
			});

			/**
			* Shows #fieldDetail filled with details of field and possible actions
			* @return void
			*/
			div.dblclick(function(){
				$('#fieldDetail').hide();
				$('#fieldDetail').show('fast');

				$('#fieldImg').attr('src', basepath+'/images/fields/hex_'+field['type']+'.png');

				$('#detailCoords').html('['+field['x']+';'+field['y']+']');
				$('#detailOwner').html(field['owner']);
				$('#detailType').html(field['type']);
				$('#detailFacility').html(field['facility']);
				$('#detailLevel').html(field['level']);



			});



			/**
			* Runs when user click some field and increment markedFields by one
			* Bugs:	-doesnt mark the second field
			* @return void
			*/
			div.click(function(){

				markedFields++;
				mark(div);

				if (markedFields < 2)
				{
					clearActions();
					prevField = this;

				}
				else if ((markedFields > 2) || (this == prevField))
				{
					unmarkAll();
					clearActions();
					prevField = null;

				}
				else if (markedFields == 2)
				{
					showMenu();
					prevField = this;
				}


			});






		});


		//$('#menu').append('<div>end</div>');

	});


	/**
	* Marks the specified field
	* @return void
	*/
	function mark(field)
	{
		$(field).append(markerImage.clone());
	}

	/**
	* Unmarks all fields and sets click to zero
	* @return void
	*/
	function unmarkAll()
	{
		$('.marker').remove();
		markedFields = 0;
	}

	/**
	* Displays context menu
	* @return void
	*/
	function showMenu()
	{
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
