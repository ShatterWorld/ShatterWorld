/**
* Map renderer (including events, infobox, action menu etc.)
* @author Petr Bělohlávek
*
*/
$(document).ready(function(){
	/**
	* @var integer - width of field
	*/
	var fieldWidth = 60;

	/**
	* @var integer - height of field
	*/
	var fieldHeight = 40;

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

		/**
		 * @var represents x-offset between real and calculated coord. of fields
		 */
		var dX = 0;

		/**
		 * @var represents y-offset between real and calculated coord. of fields
		 */
		var dY = 0;

		/**
		 * @var represents how much the scroll bar must move to the left
		 */
		var scrollX = 0;

		/**
		 * @var represents how much the scroll bar must move to the bottom
		 */
		var scrollY = 0;

		/**
		 * finds the center and calculate dX and dY
		 */
		$.each(data['fields'], function(key, field) {
			if(field['owner'] != null){
				if (data['clanId'] == field['owner']['id']){
					if(field['facility'] != null){
						if (field['facility'] == 'headquarters'){
							var posX = calculateXPos(field);
							var posY = calculateYPos(field);

							var centerXString = $('#mapContainer').css('width');
							var centerX = centerXString.substring(0, centerXString.length -2);
							dX = posX - centerX/2 + fieldWidth/2;

							var centerYString = $('#mapContainer').css('width');
							var centerY = centerYString.substring(0, centerYString.length -2);
							dY = posY - centerY/2 + 2*fieldHeight;

							return false;
						}
					}

				}
			}

		});

		/**
		 * checks negative coords. of fields, slides them and sets scrollX and scrollY
		 */
		$.each(data['fields'], function(key, field) {
			if(calculateXPos(field) - dX < 0){
				dX -= fieldWidth;
				scrollX += fieldWidth;
			}
			if(calculateYPos(field) - dY < 0){
				dY -= fieldHeight;
				scrollY += fieldHeight;
			}

		});



		/**
		 * @var integer
		 * TODO : fix
		 */
		var zIndex = data['mapSize'];

		/**
		 * renders fields and adds event-listeners to them
		 */
		$.each(data['fields'], function(key, field) {

			var posX = calculateXPos(field) - dX;
			var posY = calculateYPos(field) - dY;

			var borderType = 'neutral';
			if(field['owner'] != null){
				if (data['clanId'] == field['owner']['id']) {
					borderType = 'player';
				} else if (field['owner']['alliance'] != null) {
					borderType = 'ally';
				} else {
					borderType = 'enemy';
				}
			}

			var background = "url('"+basepath+"/images/fields/gen/hex_"+field['type']+"_"+borderType+".png')";
			var div = $('<div class="field" />').attr('id', 'field_'+posX+'_'+posY);
			var divStyle = 'width: 60px; height: 40px; position: absolute; left: '+posX+'px; top: '+posY+'px; z-index: '+zIndex+'; background: '+background+';';
			div.attr('style', divStyle);



// 			var borderStyle = 'position: absolute; left: 0px; top: 0px; z-index: 9999999';
// 			var border = $('<img class="border" />').attr('src', basepath + '/images/fields/border_'+borderType+'.png');
// 			border.attr('id', 'field_'+posX+'_'+posY);
// 			border.attr('style', borderStyle);


// 			div.append(border);
			$('#map').append(div);



			/**
			* Shows and fills #fieldInfo and #fieldActions when user gets mouse over a field
			* @return void
			*/
			div.mouseenter(function(){
				$('#fieldInfo').show();

				$("#fieldInfo #coords").html('Souřadnice ['+field['x']+';'+field['y']+']');

				var owner = '---';
				var alliance = '---';
				if (field['owner'] != null){
					owner = field['owner']['name'];
					if (field['owner']['alliance'] != null){
						alliance = field['owner']['alliance']['name'];
					}
				}
				$("#fieldInfo #owner").html('Vlastník '+ owner);
				$("#fieldInfo #alliance").html('Aliance '+ alliance);

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

				$('#fieldDetail #detailCoords').html('['+field['x']+';'+field['y']+']');




				var owner = '---';
				var alliance = '---';
				if (field['owner'] != null){
					owner = field['owner']['name'];
					if (field['owner']['alliance'] != null){
						//alert(field['owner']['alliance']['name']);
						alliance = field['owner']['alliance']['name'];
					}
				}
				$('#fieldDetail #detailOwner').html(owner);
				$("#fieldDetail #detailAlliance").html(alliance);

				$('#fieldDetail #detailType').html(field['type']);

				var facility = '---';
				if (field['facility'] != null){
					facility = field['facility'];
				}
				$('#fieldDetail #detailFacility').html(facility);

				$('#fieldDetail #detailLevel').html(field['level']);






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

		/**
		 * slides the sliders
		 */
		$('#mapContainer').scrollLeft(scrollX);
		$('#mapContainer').scrollTop(scrollY);

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
	* Hides #fieldDetail
	* @return void
	*/
	$('#closeFieldDetail').click(function(){
		$('#fieldDetail').hide('fast');
	});


	/**
	* Calculates somehow x-position of the field
	* @param field
	* @return integer
	*/
	function calculateXPos(field)
	{
		return (field['x'] * 43) + (field['y'] * 43);
	}

	/**
	* Calculates somehow y-position of the field
	* @param field
	* @return integer
	*/
	function calculateYPos(field)
	{
		return (field['x'] * -20) + (field['y'] * 19);
	}
});
