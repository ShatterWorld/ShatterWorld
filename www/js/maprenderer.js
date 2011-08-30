/**
* Map renderer (including events, infobox, action menu etc.)
* @author Petr Bělohlávek
*
*/
$(document).ready(function(){

	/**
	* @var Field - first clicked field (init. the action)
	*/
	var initialField = null;

	/**
	* @var function - action that runs when the target is selected
	* @param Field
	* @param Field
	* @return void
	*/
	var action = null;

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
			var divStyle = 'width: 60px; height: 40px; position: absolute; left: '+posX+'px; top: '+posY+'px; z-index: '+field['x']*field['y']+'; background: '+background+';';
			div.attr('style', divStyle);

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
			* Runs when user click some field and increment markedFields by one
			* Bugs:	-doesnt mark the second field
			* @return void
			*/
			div.click(function(){
				mark(div);
				if(initialField == null){
					initialField = this;
					showContextMenu(posX, posY);
				}
				else if (initialField == this){
					hideContextMenu();
					unmarkAll();
				}
				else{
					action(initialField, this);
					hideContextMenu();
					unmarkAll();
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
		initialField = null;
	}

	/**
	* Displays context menu
	* @return void
	*/
	function showContextMenu(x, y)
	{

		/*
		 * should be cloned
		 * improve cursor, text styling
		 * only this should by clickable
		 * disable #fieldInfo
		*/
		var contextMenu = $('<div id="contextMenu" />').html('<h3>Nabídka akcí</h3>');
		contextMenu.css('background', "#5D6555");
		contextMenu.css('border', "white 1px solid");
		contextMenu.css('color', "white");
		contextMenu.css('text-decoration', "underline");
		contextMenu.css('cursor', "pointer");
		contextMenu.css('width', "350px");
		contextMenu.css('height', "200px");
		contextMenu.css('position', "absolute");
		contextMenu.css('top', y+fieldHeight+"px");
		contextMenu.css('left', x+fieldWidth+"px");
		contextMenu.css('z-index', "999999999999999999999999999");
		contextMenu.css('-ms-filter', "'progid:DXImageTransform.Microsoft.Alpha(Opacity=90)'");
		contextMenu.css('-moz-opacity', "0.9");
		contextMenu.css('opacity', "0.9");

		$('#mapContainer').append(contextMenu);


		//some conditions what to display
		addAttackAction();
		addImproveBuildingAction();
		addCancelAction();
	}

	/**
	* Hides context menu
	* @return void
	*/
	function hideContextMenu()
	{
		$('#contextMenu').hide('fast');
		$('#contextMenu').remove();
	}


	/**
	* Adds the attack action
	* @return void
	*/
	function addAttackAction(){
		var actionDiv = $('<div class="action" />').html('Útok');
		actionDiv.click(function(){
			hideContextMenu();
			alert('vyberte cíl');
			action = function(from, target){
				alert('posílám jednotky');
			};
		});

		$('#contextMenu').append(actionDiv);
	}

	/**
	* Adds the inmprove building action
	* @return void
	*/
	function addImproveBuildingAction(){
		var actionDiv = $('<div class="action" />').html('Vylepčit budovu');
		actionDiv.click(function(){
			hideContextMenu();
			alert('budova vylepšena');
			unmarkAll();
		});

		$('#contextMenu').append(actionDiv);
	}

	/**
	* Adds the cancel action
	* @return void
	*/
	function addCancelAction(){
		var actionDiv = $('<div class="action" />').html('Zrušit');
		actionDiv.click(function(){
			unmarkAll();
			hideContextMenu();

		});

		$('#contextMenu').append(actionDiv);
	}

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
