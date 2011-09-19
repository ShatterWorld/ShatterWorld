/**
 * GameMap
 * @author Petr Bělohlávek
 */

/**
 * Global functions
 */
jQuery.extend({
	gameMap: {
		/**
		 * @var string - Basepath
		 */
		getBasepath : function () {return $('#map').data()['basepath'];},

		/**
		 * The size of the marker
		 * @var integer
		 */
		markerSize : 5,

		/**
		 * @var JSON - indexed map
		 */
		map : null,

		/**
		 * @var integer - width of field
		 */
		fieldWidth : 60,

		/**
		 * @var integer - height of field
		 */
		fieldHeight : 40,

		/**
		 * @var integer - Number of marked fields
		 */
		markedFields : 0,


		/**
		 * Returns object representing the marker
		 * @return object
		 */
		getMarkerImage : function () {return $('<img class="marker" />').attr('src', this.getBasepath() + '/images/fields/marker.png');},

		/**
		 * Returns the width of #mapContainer
		 * @return int
		 */
		getMapContainerWidth : function () {
			var width = $('#mapContainer').css('width');
			return width.substring(0, width.length -2);
		},

		/**
		 * Returns the height of #mapContainer
		 * @return int
		 */
		getMapContainerHeight : function () {
			var height = $('#mapContainer').css('height');
			return height.substring(0, height.length -2)
		},

		/**
		 * @var boolean
		 */
		selectTarget : false,

		/**
		 * @var integer represents x-offset between real and calculated coord. of fields
		 */
		dX : 0,

		/**
		 * @var integer represents y-offset between real and calculated coord. of fields
		 */
		dY : 0,

		/**
		 * @var integer represents how much the scroll bar must move to the left
		 */
		scrollX : 0,

		/**
		 * @var integer represents how much the scroll bar must move to the bottom
		 */
		scrollY : 0,

		/**
		  * Cleans the #map and rerender the map (using db-data)
		  * @return void
		  */
		render : function () {

			$('#map').html('');
			jQuery.fieldInfo.append('#content');

			jQuery.spinner.show('#mapContainer');
			jQuery.contextMenu.fetchFacilities();

			/**
			  * ajax that gets JSON data of visibleFields
			  */
			$.getJSON('?do=fetchMap', function(data) {
				jQuery.gameMap.map = data['fields'];

				/**
				 * finds the center and calculate dX and dY
				 */
				$.each(data['fields'], function(rowKey, row) {
					$.each(row, function(key, field) {
						if(field['owner'] != null){
							if (data['clanId'] == field['owner']['id']){
								if(field['facility'] != null){
									if (field['facility'] == 'headquarters'){
										var posX = jQuery.gameMap.calculateXPos(field);
										var posY = jQuery.gameMap.calculateYPos(field);
										jQuery.gameMap.dX = posX - jQuery.gameMap.getMapContainerWidth()/2 + jQuery.gameMap.fieldWidth/2;
										jQuery.gameMap.dY = posY - jQuery.gameMap.getMapContainerHeight()/2 + 2*jQuery.gameMap.fieldHeight;
										return false;
									}
								}

							}
						}

					});
				});

				/**
				 * checks negative coords. of fields, slides them and sets scrollX and scrollY
				 */
				$.each(data['fields'], function(rowKey, row) {
					$.each(row, function(key, field) {
						if(jQuery.gameMap.calculateXPos(field) - jQuery.gameMap.dX < 0){
							jQuery.gameMap.dX -= jQuery.gameMap.fieldWidth;
							jQuery.gameMap.scrollX += jQuery.gameMap.fieldWidth;
						}
						if(jQuery.gameMap.calculateYPos(field) - jQuery.gameMap.dY < 0){
							jQuery.gameMap.dY -= jQuery.gameMap.fieldHeight;
							jQuery.gameMap.scrollY += jQuery.gameMap.fieldHeight;
						}

					});
				});




				/**
				 * renders fields and adds event-listeners to them
				 */
				$.each(data['fields'], function(rowKey, row) {
					$.each(row, function(key, field) {

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

						var background = "url('"+jQuery.gameMap.getBasepath()+"/images/fields/gen/hex_"+field['type']+"_"+borderType+".png')";
						var div = $('<div class="field" />').attr('id', 'field_'+field['coordX']+'_'+field['coordY']);
						var divStyle = 'width: 60px; height: 40px; position: absolute; left: '+posX+'px; top: '+posY+'px; z-index: '+field['coordX']*field['coordY']+'; background: '+background+';';
						div.attr('style', divStyle);
						div.attr('data-id', field['id']);


						div.css({
							'vertical-align' : 'middle'
						});

						var text = $('<div class="text" />');
						text.css({
							'font-size' : '75%',
							'text-align' : 'center',
							'color' : 'white',
							'vertical-align' : 'middle',
							//'line-height' : jQuery.gameMap.fieldHeight+'px'
						});

						if (field['owner'] !== null && data['clanId'] !== null && field['owner']['id'] == data['clanId']){
							if (field['facility'] !== null){
								text.append(field['facility']);
								if(field['facility'] !== 'headquarters'){
									text.append(' ('+field['level']+')');
								}
							}
						}
						div.append(text);
						$('#map').append(div);

						/**
						 * Shows and fills fieldInfo when user gets mouse over a field
						 * @return void
						 */
						div.mouseenter(function(e){
							if (jQuery.gameMap.contextMenuShown){
								return;
							}

							jQuery.fieldInfo.show();

							var secret = '???';
							var none = '---';

							var coords = '['+field['coordX']+';'+field['coordY']+']';
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
							jQuery.fieldInfo.hide();
						});


						/**
						 * Moves with #infoBox
						 * @return void
						 */
						div.mousemove(function(e) {
							var localCoordinates = jQuery.utils.globalToLocal(
								div.parent(),
								e.pageX,
								e.pageY
							);

							var leftPos = localCoordinates.x + 30 - $('#mapContainer').scrollLeft();
							var topPos = localCoordinates.y + 30 - $('#mapContainer').scrollTop();

							jQuery.fieldInfo.position(leftPos, topPos);
						});

						/**
						 * Runs when user click some field and increment markedFields by one
						 * @return void
						 */
						div.click(function(e){

							if(jQuery.contextMenu.contextMenuShown){
								jQuery.contextMenu.hide();
								jQuery.gameMap.unmarkAll();
								return;
							}

							jQuery.gameMap.mark(div);
							if(jQuery.contextMenu.initialField === null || jQuery.contextMenu.action === null){
								jQuery.contextMenu.initialField = field;
								jQuery.contextMenu.show(div, e, field, data);
							}
							else{
								jQuery.contextMenu.action(jQuery.contextMenu.initialField, field);
								jQuery.contextMenu.hide();
								jQuery.gameMap.unmarkAll();
							}
						});



					});

					/**
					 * slides the sliders
					 */
					$('#mapContainer').scrollLeft(jQuery.gameMap.scrollX);
					$('#mapContainer').scrollTop(jQuery.gameMap.scrollY);

				});
				jQuery.spinner.hide();
			});
		},
		/**
		 * Marks the specified field
		 * @return void
		 */
		mark : function (field) {
			$(field).drawEllipse(this.markerSize, this.markerSize, this.fieldWidth-2*this.markerSize, this.fieldHeight-2*this.markerSize, {color: 'red', stroke: this.markerSize});
			$(field).attr('class', 'markedField');
		},

		/**
		* Unmarks all fields and sets click to zero
		* @return void
		*/
		unmarkAll : function(){
			$('.markedField canvas').remove();
			$('.markedField').attr('class', 'field');
			this.markedFields = 0;
			this.initialField = null;
		},

		/**
		 * Calculates somehow x-position of the field
		 * @param field
		 * @return integer
		 */
		calculateXPos : function (field) {
			return (field['coordX'] * 43) + (field['coordY'] * 43);
		},

		/**
		 * Calculates somehow y-position of the field
		 * @param field
		 * @return integer
		 */
		calculateYPos : function (field) {
			return (field['coordX'] * -20) + (field['coordY'] * 19);
		}

	}
});
