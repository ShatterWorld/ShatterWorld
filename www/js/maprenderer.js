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
	  * @var string - width of #mapContainer (width+'px')
	  */
	var mapContainerWidthStr = $('#mapContainer').css('width');

	/**
	  * @var integer - width of #mapContainer
	  */
	var mapContainerWidth = mapContainerWidthStr.substring(0, mapContainerWidthStr.length -2);

	/**
	  * @var string - height of #mapContainer (height+'px')
	  */
	var mapContainerHeightStr = $('#mapContainer').css('height');

	/**
	  * @var integer - height of #mapContainer
	  */
	var mapContainerHeight = mapContainerHeightStr.substring(0, mapContainerHeightStr.length -2);

	/**
	  * @var represents spinner
	  */
	var spinner = $('<img class="spinner" />').attr('src', basepath + '/images/spinner.gif');
	spinner.css({
		'position' : 'absolute',
		'top' : mapContainerHeight/2+'px',
		'left' : mapContainerWidth/2+'px',
		'z-index' : '99999999999'
	});

	/**
	  * @var represents context menu
	  */
	var contextMenu = $('<div id="contextMenu" />')
		.html('<h3>Akce:</h3>')
		.css({
			'background' : "#5D6555",
			'border' : "white 1px solid",
			'color' : "white",
			'padding' : '5px',
			'width' : "150px",
			'position' : "absolute",
			'z-index' : "999999999999999999999999999",
			'-ms-filter' : "'progid:DXImageTransform.Microsoft.Alpha(Opacity=90)'",
			'-moz-opacity' : "0.9",
			'opacity' : "0.9"
		});

	var basicActionDiv = $('<div class="action" />')
		.css({
			'cursor' : "pointer",
			'text-decoration' : "underline",
		});

	/**
	  * @var boolean
	  */
	var contextMenuShown = false;

	/**
	  * @var boolean
	  */
	var selectTarget = false;



	showSpinner();


	/**
	  * ajax that gets JSON data of visibleFields
	  *
	  */
	$.getJSON('?do=fetchMap', function(data) {

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
							dX = posX - mapContainerWidth/2 + fieldWidth/2;
							dY = posY - mapContainerHeight/2 + 2*fieldHeight;

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
			div.attr('data-id', field['id']);

			$('#map').append(div);



			/**
			 * Shows and fills #fieldInfo and #fieldActions when user gets mouse over a field
			 * @return void
			 */
			div.mouseenter(function(e){
				if (contextMenuShown){
					return;
				}

				$('#fieldInfo').show();

				var secret = '???';
				var none = '---';

				var coords = '['+field['x']+';'+field['y']+']';
				var type = field['type'];
				var owner = none;
				var alliance = none;
				var facility = none;
				var level = none;


				var ownerId = null;
				var allianceId = null;

				if (field['owner'] != null){
					owner = field['owner']['name'];
					ownerId = field['owner']['id'];

					if (field['owner']['alliance'] != null){
						alliance = field['owner']['alliance']['name'];
						allianceId = field['owner']['alliance']['id'];
					}
				}


				if ((ownerId !== null && ownerId == data['clanId']) || (allianceId !== null && allianceId == data['allianceId'])){
					if (field['facility'] != null){
						facility = field['facility'];
					}

					if (field['level'] !== null && field['facility'] !== null && field['facility'] != 'headquarters'){
						level = field['level'];
					}

				}
				else if ((ownerId !== null && ownerId != data['clanId']) || (allianceId !== null && allianceId != data['allianceId'])){
					facility = secret;
					level = secret;

				}

				$("#fieldInfo #coords").html(coords);
				$("#fieldInfo #type").html(type);
				$("#fieldInfo #owner").html(owner);
				$("#fieldInfo #alliance").html(alliance);
				$("#fieldInfo #facility").html(facility);
				$("#fieldInfo #level").html(level);

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
				var localCoordinates = globalToLocal(
					div.parent(),
					e.pageX,
					e.pageY
				);

				$('#fieldInfo').css("left", localCoordinates.x + 30 - $('#mapContainer').scrollLeft() + 'px');
				$('#fieldInfo').css("top", localCoordinates.y + 30 - $('#mapContainer').scrollTop() + 'px');
			});


			/**
			 * Runs when user click some field and increment markedFields by one
			 * Bugs:	-doesnt mark the second field
			 * @return void
			 */
			div.click(function(e){
				if(contextMenuShown){
					hideContextMenu();
					unmarkAll();
					return;
				}

				mark(this);
				if(initialField === null){
					initialField = this;
					showContextMenu(this, e, field, data);
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

		hideSpinner();
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
	 * @param object - clicked div
	 * @param event - fired event
	 * @return void
	 */
	function showContextMenu(object, e, field, data)
	{

		var contextMenuClone = contextMenu.clone();

		$('#fieldInfo').hide();
		var localCoords = globalToLocal(
			$(object).parent(),
			e.pageX,
			e.pageY
		);

		contextMenuClone.css("left", localCoords.x + 30 - $('#mapContainer').scrollLeft() + 'px');
		contextMenuClone.css("top", localCoords.y + 30 - $('#mapContainer').scrollTop() + 'px');

		contextMenuShown = true;
		$('#mapContainer').append(contextMenuClone);

		//my
		if (field['owner'] !== null && data['clanId'] !== null && field['owner']['id'] == data['clanId']){
			addAttackAction();
			/*resources check*/
			if (field['facility'] !== null){
				if(field['facility'] !== 'headquarters'){
					addUpgradeFacilityAction(field);
					addDestroyFacilityAction(field);
				}
				if(field['level'] > 1){
					addDowngradeFacilityAction(field);
				}
			}
			else{
				addBuildFacilityAction(field);
			}

			if ((field['facility'] === null) || (field['facility'] !== null && field['facility'] !== 'headquarters')){
				addLeaveFieldAction(field);
			}
		}
		//alliance
		else if(field['owner'] !== null && field['owner']['alliance'] !== null && data['allianceId'] !== null && field['owner']['alliance']['id'] == data['allianceId']){
			alert('aliance');
		}
		//enemy
		else if(field['owner'] !== null){
			alert('enemy');
		}
		//neutral neighbour
		else if(isNeighbour(field, data['clanId'])){
			addColonisationAction(field);
		}
		//other neutral
		else {
			contextMenuShown = false;
			unmarkAll();
			return;
		}

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
		contextMenuShown = false;
	}

	/**
	 * Adds the colonisation action
	 * @return void
	 */
	function addColonisationAction (target) {
		var actionDiv = basicActionDiv.clone().html('Kolonizace');
		actionDiv.click(function(){
			hideContextMenu();
			$.get('?' + $.param({
				'do': 'sendColonisation',
				'targetId': target['id']
			}));
			unmarkAll();
		});

		action = null;
		$('#contextMenu').append(actionDiv);
	}

	/**
	 * Adds the attack action
	 * @return void
	 */
	function addAttackAction (){
		var actionDiv = basicActionDiv.clone().html('Útok*');
		actionDiv.click(function(){
			hideContextMenu();
			alert('vyberte cíl');
			action = function(from, target){
				alert('posílám jednotky');
			};
		});

		action = null;
		$('#contextMenu').append(actionDiv);
	}

	/**
	 * Adds the upgrade building action
	 * @return void
	 */
	function addUpgradeFacilityAction (target){
		var actionDiv = basicActionDiv.clone().html('Upgradovat budovu*');
		actionDiv.click(function(){
			hideContextMenu();
			$.get('?' + $.param({
				'do': 'upgradeFacility',
				'targetId': target['id']
			}));
			unmarkAll();
		});

		action = null;
		$('#contextMenu').append(actionDiv);
	}

	/**
	 * Adds the downgrade building action
	 * @return void
	 */
	function addDowngradeFacilityAction (target){
		var actionDiv = basicActionDiv.clone().html('Downgradovat budovu*');
		actionDiv.click(function(){
			hideContextMenu();
			$.get('?' + $.param({
				'do': 'downgradeFacility',
				'targetId': target['id']
			}));
			unmarkAll();
		});

		action = null;
		$('#contextMenu').append(actionDiv);
	}

	/**
	 * Adds the destroy building action
	 * @return void
	 */
	function addDestroyFacilityAction (target){
		var actionDiv = basicActionDiv.clone().html('Strhnout budovu*');
		actionDiv.click(function(){
			hideContextMenu();
			$.get('?' + $.param({
				'do': 'destroyFacility',
				'targetId': target['id']
			}));
			unmarkAll();
		});

		action = null;
		$('#contextMenu').append(actionDiv);
	}

	/**
	 * Adds the build building action
	 * @return void
	 */
	function addBuildFacilityAction (target){
		var actionDiv = basicActionDiv.clone().html('Postavit budovu*');
		actionDiv.click(function(){
			hideContextMenu();
			$.get('?' + $.param({
				'do': 'buildFacility',
				'targetId': target['id'],
				'facility': 'mine'
			}));
			unmarkAll();
		});

		action = null;
		$('#contextMenu').append(actionDiv);
	}


	/**
	 * Adds the colonisation action
	 * @return void
	 */
	function addLeaveFieldAction(target) {
		var actionDiv = basicActionDiv.clone().html('Opustit pole');
		actionDiv.click(function(){
			hideContextMenu();
			$.get('?' + $.param({
				'do': 'leaveField',
				'targetId': target['id']
			}));
			unmarkAll();
		});

		action = null;
		$('#contextMenu').append(actionDiv);
	}

	/**
	 * Adds the cancel action
	 * @return void
	 */
	function addCancelAction (){
		var actionDiv = basicActionDiv.clone().html('Zrušit');
		actionDiv.click(function(){
			unmarkAll();
			hideContextMenu();
		});

		action = null;
		$('#contextMenu').append(actionDiv);
	}

	/**
	 * Calculates somehow x-position of the field
	 * @param field
	 * @return integer
	 */
	function calculateXPos (field)
	{
		return (field['x'] * 43) + (field['y'] * 43);
	}

	/**
	 * Calculates somehow y-position of the field
	 * @param field
	 * @return integer
	 */
	function calculateYPos (field)
	{
		return (field['x'] * -20) + (field['y'] * 19);
	}

	/**
	 * Shows spinner
	 * @return void
	 */
	function showSpinner ()
	{
		$('#mapContainer').append(spinner.clone());
	}

	/**
	 * Hides spinner
	 * @return void
	 */
	function hideSpinner ()
	{
		$('.spinner').remove();
	}


	/**
	 * Calculates relative position
	 * @param object
	 * @param integer
	 * @param integer
	 * @return array of integer
	 */
	function globalToLocal (context, globalX, globalY)
	{
		var position = context.offset();
		//alert('global2local: '+position.left+' '+position.top);

		return({
			x: Math.floor( globalX - position.left ),
			y: Math.floor( globalY - position.top )
		});
	}

	/**
	 * If field is neighbour of (at least) one clan field, returns true
	 * @param field
	 * @param iteger
	 * @return boolean
	 */
	function isNeighbour(field, ownerId)
	{
		return true;

	}



});


