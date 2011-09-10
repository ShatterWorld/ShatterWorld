jQuery.extend({
	map: {
		/**
		  * Cleans the #map and rerender the map (using db-data)
		  */
		render: function () {

			$('#map').html('');
			jQuery.gameMap.showSpinner();

			/**
			  * ajax that gets JSON data of visibleFields
			  */
			$.getJSON('?do=fetchMap', function(data) {



				/**
				 * finds the center and calculate dX and dY
				 */
				$.each(data['fields'], function(key, field) {
					if(field['owner'] != null){
						if (data['clanId'] == field['owner']['id']){
							if(field['facility'] != null){
								if (field['facility'] == 'headquarters'){
									var posX = jQuery.gameMap.calculateXPos(field);
									var posY = jQuery.gameMap.calculateYPos(field);
									dX = posX - jQuery.gameMap.mapContainerWidth/2 + jQuery.gameMap.fieldWidth/2;
									dY = posY - jQuery.gameMap.mapContainerHeight/2 + 2*jQuery.gameMap.fieldHeight;

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
					if(jQuery.gameMap.calculateXPos(field) - jQuery.gameMap.dX < 0){
						jQuery.gameMap.dX -= jQuery.gameMap.fieldWidth;
						jQuery.gameMap.scrollX += jQuery.gameMap.fieldWidth;
					}
					if(jQuery.gameMap.calculateYPos(field) - jQuery.gameMap.dY < 0){
						jQuery.gameMap.dY -= jQuery.gameMap.fieldHeight;
						jQuery.gameMap.scrollY += jQuery.gameMap.fieldHeight;
					}

				});




				/**
				 * renders fields and adds event-listeners to them
				 */
				$.each(data['fields'], function(key, field) {

					var posX = jQuery.gameMap.calculateXPos(field) - jQuery.gameMap.dX;
					var posY = jQuery.gameMap.calculateYPos(field) - jQuery.gameMap.dY;

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
						if (jQuery.gameMap.contextMenuShown){
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
						var localCoordinates = jQuery.gameMap.globalToLocal(
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
						if(jQuery.gameMap.contextMenuShown){
							jQuery.gameMap.hideContextMenu();
							jQuery.gameMap.unmarkAll();
							return;
						}

						jQuery.gameMap.mark(this);
						if(jQuery.gameMap.initialField === null){
							jQuery.gameMap.initialField = this;
							jQuery.gameMap.showContextMenu(this, e, field, data);
						}
						else if (jQuery.gameMap.initialField == this){
							jQuery.gameMap.hideContextMenu();
							jQuery.gameMap.unmarkAll();
						}
						else{
							jQuery.gameMap.action(jQuery.gameMap.initialField, this);
							jQuery.gameMap.hideContextMenu();
							jQuery.gameMap.unmarkAll();
						}
					});



				});

				/**
				 * slides the sliders
				 */
				$('#mapContainer').scrollLeft(scrollX);
				$('#mapContainer').scrollTop(scrollY);

				jQuery.gameMap.hideSpinner();
			});
		},
		/**
		 * Marks the specified field
		 * @return void
		 */
		mark: function (field) {
			$(field).append(jQuery.gameMap.markerImage.clone());
		},

			/**
	 * Unmarks all fields and sets click to zero
	 * @return void
	 */
		unmarkAll: function(){
			$('.marker').remove();
			jQuery.gameMap.markedFields = 0;
			jQuery.gameMap.initialField = null;
		},

		/**
		 * Displays context menu
		 * @param object - clicked div
		 * @param event - fired event
		 * @return void
		 */
		showContextMenu: function(object, e, field, data){

			var contextMenuClone = jQuery.gameMap.contextMenu.clone();

			$('#fieldInfo').hide();
			var localCoords = jQuery.gameMap.globalToLocal(
				$(object).parent(),
				e.pageX,
				e.pageY
			);

			contextMenuClone.css("left", localCoords.x + 30 - $('#mapContainer').scrollLeft() + 'px');
			contextMenuClone.css("top", localCoords.y + 30 - $('#mapContainer').scrollTop() + 'px');

			jQuery.gameMap.contextMenuShown = true;
			$('#mapContainer').append(contextMenuClone);

			//my
			if (field['owner'] !== null && data['clanId'] !== null && field['owner']['id'] == data['clanId']){
				jQuery.gameMap.addAttackAction();
				/*resources check*/
				if (field['facility'] !== null){
					if(field['facility'] !== 'headquarters'){
						jQuery.gameMap.addUpgradeFacilityAction(field);
						jQuery.gameMap.addDestroyFacilityAction(field);
						if(field['level'] > 1){
							jQuery.gameMap.addDowngradeFacilityAction(field);
						}
					}

				}
				else{
					jQuery.gameMap.addBuildFacilityAction(field);
				}

				if ((field['facility'] === null) || (field['facility'] !== null && field['facility'] !== 'headquarters')){
					jQuery.gameMap.addLeaveFieldAction(field);
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
				jQuery.gameMap.addColonisationAction(field);
			}
			//other neutral
			else {
				jQuery.gameMap.contextMenuShown = false;
				jQuery.gameMap.unmarkAll();
			}

			jQuery.gameMap.addCancelAction();

		},


		/**
		 * Hides context menu
		 * @return void
		 */
		hideContextMenu: function ()
		{
			$('#contextMenu').hide('fast');
			$('#contextMenu').remove();
			jQuery.gameMap.contextMenuShown = false;
		},

		/**
		 * Adds the colonisation action
		 * @return void
		 */
		 addColonisationAction: function(target) {
			var actionDiv = basicActionDiv.clone().html('Kolonizace');
			actionDiv.click(function(){
				jQuery.gameMap.hideContextMenu();
				$.get('?' + $.param({
					'do': 'sendColonisation',
					'targetId': target['id']
				}));
				jQuery.gameMap.unmarkAll();
			});

			jQuery.gameMap.action = null;
			$('#contextMenu').append(actionDiv);
		},

		/**
		 * Adds the attack action
		 * @return void
		 */
		addAttackAction: function (){
			var actionDiv = basicActionDiv.clone().html('Útok*');
			actionDiv.click(function(){
				jQuery.gameMap.hideContextMenu();
				alert('vyberte cíl');
				jQuery.gameMap.action = function(from, target){
					alert('posílám jednotky');
				};
			});

			jQuery.gameMap.action = null;
			$('#contextMenu').append(actionDiv);
		},

		/**
		 * Adds the upgrade building action
		 * @return void
		 */
		addUpgradeFacilityAction: function (target){
			var actionDiv = basicActionDiv.clone().html('Upgradovat budovu*');
			actionDiv.click(function(){
				jQuery.gameMap.hideContextMenu();
				$.get('?' + $.param({
					'do': 'upgradeFacility',
					'targetId': target['id']
				}));
				jQuery.gameMap.unmarkAll();
			});

			jQuery.gameMap.action = null;
			$('#contextMenu').append(actionDiv);
		},

		/**
		 * Adds the downgrade building action
		 * @return void
		 */
		 addDowngradeFacilityAction: function (target){
			var actionDiv = basicActionDiv.clone().html('Downgradovat budovu*');
			actionDiv.click(function(){
				jQuery.gameMap.hideContextMenu();
				$.get('?' + $.param({
					'do': 'downgradeFacility',
					'targetId': target['id']
				}));
				jQuery.gameMap.unmarkAll();
			});

			jQuery.gameMap.action = null;
			$('#contextMenu').append(actionDiv);
		},

		/**
		 * Adds the destroy building action
		 * @return void
		 */
		addDestroyFacilityAction: function (target){
			var actionDiv = basicActionDiv.clone().html('Strhnout budovu*');
			actionDiv.click(function(){
				jQuery.gameMap.hideContextMenu();
				$.get('?' + $.param({
					'do': 'destroyFacility',
					'targetId': target['id']
				}));
				jQuery.gameMap.unmarkAll();
			});

			jQuery.gameMap.action = null;
			$('#contextMenu').append(actionDiv);
		},

		/**
		 * Adds the build building action
		 * @return void
		 */
		 addBuildFacilityAction: function (target){
			var actionDiv = basicActionDiv.clone().html('Postavit budovu*');
			actionDiv.click(function(){
				$('#contextMenu').html('Budovy:');

				var mineDiv = $('<div />')
					.append('Důl')
					.css('cursor', 'pointer')
					.click(function(){
						$.get('?' + $.param({
							'do': 'buildFacility',
							'targetId': target['id'],
							'facility': 'mine'
						}));
						jQuery.gameMap.addCountdown('důl', 11);
						jQuery.gameMap.hideContextMenu();
						jQuery.gameMap.unmarkAll();
					});

				var barracksDiv = $('<div />')
					.append('Kasárny')
					.css('cursor', 'pointer')
					.click(function(){
						$.get('?' + $.param({
							'do': 'buildFacility',
							'targetId': target['id'],
							'facility': 'barracks'
						}));
						jQuery.gameMap.hideContextMenu();
						jQuery.gameMap.unmarkAll();
					});

				$('#contextMenu').append(mineDiv);
				$('#contextMenu').append(barracksDiv);
				jQuery.gameMap.addCancelAction();
			});

			jQuery.gameMap.action = null;
			$('#contextMenu').append(actionDiv);
		},


		/**
		 * Adds the colonisation action
		 * @return void
		 */
		addLeaveFieldAction: function(target) {
			var actionDiv = basicActionDiv.clone().html('Opustit pole');
			actionDiv.click(function(){
				jQuery.gameMap.hideContextMenu();
				$.get('?' + $.param({
					'do': 'leaveField',
					'targetId': target['id']
				}));
				jQuery.gameMap.unmarkAll();
			});

			jQuery.gameMap.action = null;
			$('#contextMenu').append(actionDiv);
		},

		/**
		 * Adds the cancel action
		 * @return void
		 */
		 addCancelAction: function (){
			var actionDiv = basicActionDiv.clone().html('Zrušit');
			actionDiv.click(function(){
				jQuery.gameMap.unmarkAll();
				jQuery.gameMap.hideContextMenu();
			});

			jQuery.gameMap.action = null;
			$('#contextMenu').append(actionDiv);
		},

		/**
		 * Calculates somehow x-position of the field
		 * @param field
		 * @return integer
		 */
		 calculateXPos:function (field)
		{
			return (field['x'] * 43) + (field['y'] * 43);
		},

		/**
		 * Calculates somehow y-position of the field
		 * @param field
		 * @return integer
		 */
		 calculateYPos: function (field)
		{
			return (field['x'] * -20) + (field['y'] * 19);
		},

		/**
		 * Shows spinner
		 * @return void
		 */
		 showSpinner: function ()
		{
			$('#mapContainer').append(jQuery.gameMap.spinner.clone());
		},

		/**
		 * Hides spinner
		 * @return void
		 */
		hideSpinner: function ()
		{
			$('.spinner').remove();
		},


		/**
		 * Calculates relative position
		 * @param object
		 * @param integer
		 * @param integer
		 * @return array of integer
		 */
		 globalToLocal: function (context, globalX, globalY)
		{
			var position = context.offset();

			return({
				x: Math.floor( globalX - position.left ),
				y: Math.floor( globalY - position.top )
			});
		},

		/**
		 * If field is neighbour of (at least) one clan field, returns true
		 * @param field
		 * @param iteger
		 * @return boolean
		 */
		 isNeighbour: function(field, ownerId)
		{
			return true;

		},

		/**
		 * Adds countdown
		 * @param string
		 * @param integer [s]
		 * @return void
		 */
		 addCountdown: function(title, remainingTime)
		{
			var countdownDiv = $('<div class="countdown" />');
			var countdownTitleDiv = $('<span class="countdownTitle" />').html(title+': ');
			var countdownTimeDiv = $('<span class="countdownTime" />');

			countdownDiv.append(countdownTitleDiv);
			countdownDiv.append(countdownTimeDiv);

			$('#countdownDialog').prepend(countdownDiv);

			jQuery.gameMap.countdown(countdownTimeDiv, remainingTime);
		},

		/**
		 * Countdowns and display remaining time
		 * @param object
		 * @param integer [s]
		 * @return void
		 */
		 countdown: function(countdownTimeDiv, remainingTime)
		{

			if (remainingTime < 0){
				jQuery.gameMap.renderMap();
				$(countdownTimeDiv).parent().remove();
				return;
			}
			var t = remainingTime;

			var h = Math.floor(t / 3600);
			t -= h*3600;
			var m = Math.floor(t / 60);
			t -= m*60;
			var s = t;

			if (s < 10){
				s = '0' + s;
			}
			if (m < 10){
				m = '0' + m;
			}

			var time = h + ':' + m + ':' + s;

			$(countdownTimeDiv).html(time);
			setTimeout(function(){
				jQuery.gameMap.countdown(countdownTimeDiv, remainingTime - 1);
			}, 1000);


		},


		/**
		 * @var Field - first clicked field (init. the action)
		 */
		initialField : null,

		/**
		 * @var function - action that runs when the target is selected
		 * @param Field
		 * @param Field
		 * @return void
		 */
		 action : null,

		/**
		 * @var integer - width of field
		 */
		 fieldWidth : 60,

		/**
		 * @var integer - height of field
		 */
		 fieldHeight : 40,

		/**
		 * @var string - Basepath
		 */
		 basepath : $('#map').data()['basepath'],

		/**
		 * @var integer - Number of marked fields
		 */
		 markedFields : 0,


		/**
		  * @var represents marker
		  */
		 markerImage : $('<img class="marker" />').attr('src', basepath + '/images/fields/marker.png'),

		/**
		  * @var string - width of #mapContainer (width+'px')
		  */
		 mapContainerWidthStr : $('#mapContainer').css('width'),

		/**
		  * @var integer - width of #mapContainer
		  */
		 mapContainerWidth : mapContainerWidthStr.substring(0, mapContainerWidthStr.length -2),

		/**
		  * @var string - height of #mapContainer (height+'px')
		  */
		 mapContainerHeightStr : $('#mapContainer').css('height'),

		/**
		  * @var integer - height of #mapContainer
		  */
		 mapContainerHeight : mapContainerHeightStr.substring(0, mapContainerHeightStr.length -2),

		/**
		  * @var represents spinner
		  */
		 spinner : $('<img class="spinner" />').attr('src', basepath + '/images/spinner.gif'),
		spinner.css({
			'position' : 'absolute',
			'top' : mapContainerHeight/2+'px',
			'left' : mapContainerWidth/2+'px',
			'z-index' : '99999999999'
		}),

		/**
		  * @var represents context menu
		  */
		 contextMenu : $('<div id="contextMenu" />')
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
			}),

		 basicActionDiv : $('<div class="action" />')
			.css({
				'cursor' : "pointer",
				'text-decoration' : "underline",
			}),

		/**
		  * @var boolean
		  */
		 contextMenuShown : false,

		/**
		  * @var boolean
		  */
		 selectTarget : false,

		/**
		 * @var represents x-offset between real and calculated coord. of fields
		 */
		 dX : 0,

		/**
		 * @var represents y-offset between real and calculated coord. of fields
		 */
		 dY : 0,

		/**
		 * @var represents how much the scroll bar must move to the left
		 */
		 scrollX : 0,

		/**
		 * @var represents how much the scroll bar must move to the bottom
		 */
		 scrollY : 0






	}
});


/**
 * Map renderer (including action, infobox, context menu, countdown etc.)
 * @author Petr Bělohlávek
 *
 */
$(document).ready(function(){
	jQuery.gameMap.render();
});


