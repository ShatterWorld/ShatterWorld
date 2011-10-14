/**
 * GameMap
 * @author Petr Bělohlávek
 */

/**
 * Global functions
 */
var Game = Game || {};
Game.gameMap = {
	/**
	 * Basepath
	 * @var string
	 */
	getBasepath : function () {return $('#map').data()['basepath'];},

	/**
	 * Indexed map
	 * @var JSON
	 */
	map : null,

	/**
	 * The width of field
	 * @var integer
	 */
	fieldWidth : 60,

	/**
	 * The height of field
	 * @var integer
	 */
	fieldHeight : 40,

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
	 * Represents x-offset between real and calculated coord. of fields
	 * @var integer
	 */
	dX : 0,

	/**
	 * Represents y-offset between real and calculated coord. of fields
	 * @var integer
	 */
	dY : 0,

	/**
	 * Represents how much the scroll bar must move to the left
	 * @var integer
	 */
	scrollX : 0,

	/**
	 * Represents how much the scroll bar must move to the bottom
	 * @var integer
	 */
	scrollY : 0,

	/**
	 * Represents the list of fields which are disabled right now
	 * @var array of Field
	 */
	disabledFields : new Array(),

	/**
	 * Sets the this.disabledFields to new Array
	 * @return void
	 */
	nullDisabledFields : function(){
		this.disabledFields = new Array();
		Game.marker.unmarkAll('brown');
	},

	/**
	 * Pushes new field to this.disabledFields, markes it, prevent the native click action, displays type
	 * @param Field
	 * @param String
	 * @return void
	 */
	addDisabledField : function(disField, type){
		this.disabledFields.push(disField);

		div = $('#field_'+disField['x']+'_'+disField['y']);
		div.attr('data-disabled', type);
		this.markDisabledField(div);

		div.unbind('click');
		div.click(function(e){
			alert(type); //tmp
		});

	},

	/**
	 * Marks the disabled field influenced by
	 * @param Object/String
	 * @return void
	 */
	markDisabledField : function(div) {
		/*
		 * case (div.data-disabled) -> mark/label/img //==type
		 *
		 * */
		Game.marker.mark(div, 'brown');


	},

	/**
	 * Cleans the #map and rerender the map (using db-data)
	 * @return void
	 */
	render : function () {

		$('#map').html('');
		Game.fieldInfo.append('#content');

		Game.spinner.show('#mapContainer');
		Game.contextMenu.fetchFacilities();

		/**
		 * ajax that gets JSON data of visibleFields
		 */
		$.getJSON('?do=fetchMap', function(data) {
			Game.gameMap.map = data['fields'];

			/**
			 * finds the center and calculate dX and dY
			 */
			$.each(data['fields'], function(rowKey, row) {
				$.each(row, function(key, field) {
					if(field['owner'] != null){
						if (data['clanId'] == field['owner']['id']){
							if(field['facility'] != null){
								if (field['facility'] == 'headquarters'){
									var posX = Game.gameMap.calculateXPos(field);
									var posY = Game.gameMap.calculateYPos(field);
									Game.gameMap.dX = posX - Game.gameMap.getMapContainerWidth()/2 + Game.gameMap.fieldWidth/2;
									Game.gameMap.dY = posY - Game.gameMap.getMapContainerHeight()/2 + 2*Game.gameMap.fieldHeight;
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
					if(Game.gameMap.calculateXPos(field) - Game.gameMap.dX < 0){
						Game.gameMap.dX -= Game.gameMap.fieldWidth;
						Game.gameMap.scrollX += Game.gameMap.fieldWidth;
					}
					if(Game.gameMap.calculateYPos(field) - Game.gameMap.dY < 0){
						Game.gameMap.dY -= Game.gameMap.fieldHeight;
						Game.gameMap.scrollY += Game.gameMap.fieldHeight;
					}

				});
			});




			/**
			 * renders fields and adds event-listeners to them
			 */
			$.each(data['fields'], function(rowKey, row) {
				$.each(row, function(key, field) {

					var posX = Game.gameMap.calculateXPos(field) - Game.gameMap.dX;
					var posY = Game.gameMap.calculateYPos(field) - Game.gameMap.dY;

					var borderType = 'neutral';
					if(field['owner'] != null){
						if (data['clanId'] == field['owner']['id']) {
							borderType = 'player';
						} else if (field['owner']['alliance'] != null && field['owner']['alliance']['id'] == data['allianceId']) {
							borderType = 'ally';
						} else {
							borderType = 'enemy';
						}
					}

					var background = "url('"+Game.gameMap.getBasepath()+"/images/fields/gen/hex_"+field['type']+"_"+borderType+".png')";
					var div = $('<div class="field" />').attr('id', 'field_'+field['coordX']+'_'+field['coordY']);
					var divStyle = 'width: 60px; height: 40px; position: absolute; left: '+posX+'px; top: '+posY+'px; z-index: '+field['coordX']*field['coordY']+'; background: '+background+';';
					div.attr('style', divStyle);
					div.attr('data-id', field['id']);


					div.css({
						'vertical-align' : 'middle'
					});

					var text = $('<div class="text" />');
					var fieldLabel = $('<span />');

					text.css({
						'font-size' : '75%',
						'text-align' : 'center',
						'color' : 'white',
						'vertical-align' : 'middle',
						//'line-height' : Game.gameMap.fieldHeight+'px'
					});

					if (field['owner'] !== null && data['clanId'] !== null && field['owner']['id'] == data['clanId']){
						if (field['facility'] !== null){
							text.append(fieldLabel);
							Game.descriptions.translate('facility', field['facility'], fieldLabel);
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
						if (Game.gameMap.contextMenuShown){
							return;
						}

						Game.fieldInfo.show();

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
						Game.descriptions.translate('field', type, "#fieldInfo #type");
						$("#fieldInfo #owner").html(owner);
						$("#fieldInfo #alliance").html(alliance);
						Game.descriptions.translate('facility', facility, "#fieldInfo #facility");
						$("#fieldInfo #level").html(level);

					});


					/**
					 * Hides #fieldInfo
					 * @return void
					 */
					div.mouseleave(function(){
						Game.fieldInfo.hide();
					});


					/**
					 * Moves with #infoBox
					 * @return void
					 */
					div.mousemove(function(e) {
						var localCoordinates = Game.utils.globalToLocal(
							div.parent(),
							e.pageX,
							e.pageY
						);

						var leftPos = localCoordinates.x + 30 - $('#mapContainer').scrollLeft();
						var topPos = localCoordinates.y + 30 - $('#mapContainer').scrollTop();

						Game.fieldInfo.position(leftPos, topPos);
					});

					/**
					 * Runs when user click some field and increment markedFields by one
					 * @return void
					 */
					div.click(function(e){

						if(Game.contextMenu.contextMenuShown){
							Game.contextMenu.hide();
							Game.marker.unmarkAll('red');
							return;
						}

						if(Game.contextMenu.initialField === null || Game.contextMenu.action === null){
							Game.contextMenu.initialField = field;
							Game.marker.mark(div, 'red');
							Game.contextMenu.show(div, e, field, data);
						}
						else if(Game.contextMenu.action == "attackSelect2nd"){
							Game.contextMenu.attackSelect2nd(Game.contextMenu.initialField, field, div, data)
						}
						else{
							Game.contextMenu.action(Game.contextMenu.initialField, field);
							Game.contextMenu.hide();
							Game.marker.unmarkAll('red');
						}
					});



				});

				/**
				 * slides the sliders
				 */
				$('#mapContainer').scrollLeft(Game.gameMap.scrollX);
				$('#mapContainer').scrollTop(Game.gameMap.scrollY);

			});

			Game.spinner.hide();
		});
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
};