/**
 * GameMap
 * @author Petr Bělohlávek
 */

/**
 * Global functions
 */
var Game = Game || {};
Game.map = {
	/**
	 * Indexed map
	 * @var JSON
	 */
	map : null,

	clan : 0,

	alliance : 0,

	/**
	 * All fields by coodrs
	 * @var Object
	 */
	fieldsByCoords : {},

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
	getMapContainerWidth : function ()
	{
		return parseInt($('#mapContainer').css('width'));
	},

	/**
	 * Returns the height of #mapContainer
	 * @return int
	 */
	getMapContainerHeight : function ()
	{
		return parseInt($('#mapContainer').css('height'));
	},

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
	 * The largest X-position value
	 * @var integer
	 */
	maxXPos : 0,

	/**
	 * The largest Y-position value
	 * @var integer
	 */
	maxYPos : 0,

	loaded : false,

	disabledFieldsStack : new Array(),

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
		Game.map.marker.unmarkAll('disabled');
	},

	/**
	 * Pushes new field to this.disabledFields, markes it, prevent the native click action, displays type
	 * @param function returning field
	 * @param String
	 * @return void
	 */
	disableField : function (getter, type)
	{
		var disable = function () {
			var field = typeof(getter) == 'function' ? getter() : getter;
			Game.map.disabledFields.push(field);
			field.element.attr('data-disabled', type);
			Game.map.marker.mark(field, 'disabled');
		}
		if (this.loaded) {
			disable();
		} else {
			this.disabledFieldsStack.push(disable);
		}
	},

	getField : function (x, y) {
		return this.map[x][y];
	},

	/**
	 * Cleans the #map and rerender the map (using db-data)
	 * @return void
	 */
	render : function ()
	{
		Game.map.loaded = false;
		$('#map').html('');
		Game.map.tooltip.append('#content');

		Game.spinner.show('#mapContainer');
		Game.map.contextMenu.fetchFacilities();

		/**
		 * ajax that gets JSON data of visibleFields
		 */
		$.get('?do=fetchMap', function(data) {
			Game.map.map = data['fields'];
			Game.map.clan = data['clanId'];
			Game.map.alliance = data['allianceId'];
			Game.map.rallyPoint = data.rallyPointId;

			/**
			 * finds the center and calculate dX and dY
			 */
			$.each(data['fields'], function(rowKey, row) {
				$.each(row, function(key, field) {
					if(field['owner'] != null){
						if (Game.map.clan == field['owner']['id']){
							if(field['facility'] != null){
								if (field['facility']['type'] == 'headquarters'){
									var posX = Game.map.calculateXPos(field);
									var posY = Game.map.calculateYPos(field);
									Game.map.dX = posX - Game.map.getMapContainerWidth()/2 + Game.map.fieldWidth/2;
									Game.map.dY = posY - Game.map.getMapContainerHeight()/2 + 2*Game.map.fieldHeight;
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
					if(Game.map.calculateXPos(field) - Game.map.dX < 0){
						Game.map.dX -= Game.map.fieldWidth;
						Game.map.scrollX += Game.map.fieldWidth;
					}
					if(Game.map.calculateYPos(field) - Game.map.dY < 0){
						Game.map.dY -= Game.map.fieldHeight;
						Game.map.scrollY += Game.map.fieldHeight;
					}

				});
			});

			/**
			 * renders fields and adds event-listeners to them
			 */
			if (!Game.utils.isset(Game.map.fieldsByCoords)){
				Game.map.fieldsByCoords = {};
			}
			$.each(data['fields'], function(rowKey, row) {
				$.each(row, function(key, field) {

					var posX = Game.map.calculateXPos(field) - Game.map.dX;
					var posY = Game.map.calculateYPos(field) - Game.map.dY;
					Game.map.maxXPos = Math.max(posX, Game.map.maxXPos);
					Game.map.maxYPos = Math.max(posY, Game.map.maxYPos);

					var borderType = 'neutral';
					var background = "url('"+basePath+"/images/fields/hex_"+field['type']+".png')";
					var div = $('<div class="field" />').attr('id', 'field_'+field['coordX']+'_'+field['coordY']);
					var divStyle = 'width: 60px; height: 40px; position: absolute; left: '+posX+'px; top: '+posY+'px; z-index: 1; background: '+background+';';
					div.attr('style', divStyle);
					div.attr('data-id', field['id']);
					div.attr('data-coordx', field['coordX']);
					div.attr('data-coordy', field['coordY']);
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
						//'line-height' : Game.map.fieldHeight+'px'
					});

					var gameAli = Game.map.alliance;
					if (field['owner'] !== null){
						var owner = field['owner']['id'];
						if (field['owner']['alliance'] !== null){
							var alliance = field['owner']['alliance']['id'];

						}
					}

					if ((Game.map.clan !== null && field['owner'] !== null && field['owner']['id'] == Game.map.clan) || (Game.map.alliance !== null && field['owner'] !== null && field['owner']['alliance'] !== null && field['owner']['alliance']['id'] == Game.map.alliance)){
						if (field['facility'] !== null){
							var status = 'active';
							if (field['facility']['damaged']){
								status = 'damaged';
							}
							var img = $('<img src="' + basePath + '/images/facilities/' + status + '/' + field['facility']['type'] + '.png"/>');
							div.append(img);
						}
					}
					field.element = div;
					$('#map').append(div);


					/**
					 * Fills the fieldsByCoords
					 */
					if (!Game.utils.isset(Game.map.fieldsByCoords[posX])){
						Game.map.fieldsByCoords[posX] = {};
					}
					if (!Game.utils.isset(Game.map.fieldsByCoords[posX][posY])){
						Game.map.fieldsByCoords[posX][posY] = {};
					}
					Game.map.fieldsByCoords[posX][posY] = field;


				});

				/**
				 * slides the sliders
				 */
				$('#mapContainer').scrollLeft(Game.map.scrollX);
				$('#mapContainer').scrollTop(Game.map.scrollY);
			});

			var canvasWidth = Game.map.maxXPos + Game.map.fieldWidth;
			var canvasHeight = Game.map.maxYPos + Game.map.fieldHeight;
			Game.map.overlayDiv = $('<div id="overlay">');
			Game.map.overlayDiv.css({'width' : canvasWidth, 'height' : canvasHeight, 'position' : 'absolute', 'left' : 0, 'top' : 0, 'z-index' : 2});
			$('#map').append(Game.map.overlayDiv);
			Game.map.overlay = new Raphael('overlay', canvasWidth, canvasHeight);


			/**
			 * outlining
			 */
			$.each(data['fields'], function(rowKey, row) {
				$.each(row, function(key, field) {
					var posX = Game.map.calculateXPos(field) - Game.map.dX;
					var posY = Game.map.calculateYPos(field) - Game.map.dY;

					var borderType = 'neutral';
					if(field['owner'] != null){

						var x = parseInt(rowKey);
						var y = parseInt(key);

						var pathStack = new Array();

						/*
						 * north
						 * */
						if(Game.utils.isset(data['fields'][x+1]) && Game.utils.isset(data['fields'][x+1][y-1]) && (!Game.utils.isset(data['fields'][x+1][y-1]['owner']) || (Game.utils.isset(data['fields'][x+1][y-1]['owner']) && field['owner']['id'] != data['fields'][x+1][y-1]['owner']['id']))){
							pathStack.push(Game.map.overlay.path('M ' + (posX + Game.map.fieldWidth/4) + ' ' + posY + ' l ' + (Game.map.fieldWidth/2) + ' 0'));
						}

						/*
						 * south
						 * */
						if(Game.utils.isset(data['fields'][x-1]) && Game.utils.isset(data['fields'][x-1][y+1]) && (!Game.utils.isset(data['fields'][x-1][y+1]['owner']) || (Game.utils.isset(data['fields'][x-1][y+1]['owner']) && field['owner']['id'] != data['fields'][x-1][y+1]['owner']['id']))){
							pathStack.push(Game.map.overlay.path('M ' + (posX + Game.map.fieldWidth/4) + ' ' + (posY + Game.map.fieldHeight) + ' l ' + (Game.map.fieldWidth/2) + ' 0'));
						}

						/*
						 * north-west
						 * */
						if(Game.utils.isset(data['fields'][x]) && Game.utils.isset(data['fields'][x][y-1]) && (!Game.utils.isset(data['fields'][x][y-1]['owner']) || (Game.utils.isset(data['fields'][x][y-1]['owner']) && field['owner']['id'] != data['fields'][x][y-1]['owner']['id']))){
							pathStack.push(Game.map.overlay.path('M ' + (posX + Game.map.fieldWidth/4) + ' ' + posY + ' l ' + ((-1)*Game.map.fieldWidth/4) + ' 20'));
						}

						/*
						 * north-east
						 * */
						if(Game.utils.isset(data['fields'][x+1]) && Game.utils.isset(data['fields'][x+1][y]) && (!Game.utils.isset(data['fields'][x+1][y]['owner']) || (Game.utils.isset(data['fields'][x+1][y]['owner']) && field['owner']['id'] != data['fields'][x+1][y]['owner']['id']))){
							pathStack.push(Game.map.overlay.path('M ' + (posX + Game.map.fieldWidth - Game.map.fieldWidth/4) + ' ' + posY + ' l ' + (Game.map.fieldWidth/4) + ' ' + Game.map.fieldHeight/2 + ''));
						}

						/*
						 * south-east
						 * */
						if(Game.utils.isset(data['fields'][x]) && Game.utils.isset(data['fields'][x][y+1]) && (!Game.utils.isset(data['fields'][x][y+1]['owner']) || (Game.utils.isset(data['fields'][x][y+1]['owner']) && field['owner']['id'] != data['fields'][x][y+1]['owner']['id']))){
							pathStack.push(Game.map.overlay.path('M ' + (posX + Game.map.fieldWidth/4 + Game.map.fieldWidth/2) + ' ' + (posY + Game.map.fieldHeight) + ' l ' + (Game.map.fieldWidth/4) + ' -20'));
						}

						/*
						 * south-west
						 * */
						if(Game.utils.isset(data['fields'][x-1]) && Game.utils.isset(data['fields'][x-1][y]) && (!Game.utils.isset(data['fields'][x-1][y]['owner']) || (Game.utils.isset(data['fields'][x-1][y]['owner']) && field['owner']['id'] != data['fields'][x-1][y]['owner']['id']))){
							pathStack.push(Game.map.overlay.path('M ' + (posX) + ' ' + (posY + Game.map.fieldHeight/2) + ' l ' + (Game.map.fieldWidth/4) + ' 20'));
						}

						var color;
						if (Game.map.clan == field['owner']['id']) {
							//color = '#7aee3c';
							color = 'cyan';
						} else if (field['owner']['alliance'] != null && field['owner']['alliance']['id'] == Game.map.alliance) {
							//color = '#4380d3';
							color = '#9f3ed5';
						} else {
							color = 'red';
						}

						$.each(pathStack, function(key, path){
							path.attr({stroke: color, 'stroke-width': 4});
						});

					}
				});
			});

			Game.map.loaded = true;

			while (fnc = Game.map.disabledFieldsStack.pop()) {
				fnc();
			}

			/**
			 * Shows and fills fieldInfo when user gets mouse over a field
			 * @return void
			 */
			Game.map.overlayDiv.mouseenter(function(e){
				if (Game.map.contextMenu.shown){
					return;
				}
				var field = Game.map.determineField(e);
				if (field) {
					Game.map.tooltip.show(field);
				}
			});


			/**
			 * Hides #fieldInfo
			 * @return void
			 */
			Game.map.overlayDiv.mouseleave(function(){
				Game.map.tooltip.hide();
			});


			/**
			 * Moves with #infoBox
			 * @return void
			 */
			Game.map.overlayDiv.mousemove(function(e) {
				var field = Game.map.determineField(e);

				if (field !== Game.map.tooltip.field) {
					if (field === null) {
						Game.map.tooltip.hide();
						Game.map.overlayDiv.css('cursor', 'auto');
					} else {
						Game.map.tooltip.show(field);
						Game.map.overlayDiv.css('cursor', 'pointer');
					}
				}
				if (field !== null) {
					var coords = Game.utils.globalToLocal($('#mapContainer'), e.pageX, e.pageY);
					Game.map.tooltip.element.css({
						'left': coords['x'] + 30,
						'top': coords['y'] + 30
					});
				}
			});

			/**
			 * Runs when user clicks a field
			 * @return void
			 */
			Game.map.overlayDiv.click(Game.map.openMenu);
			Game.spinner.hide();
		});
	},

	openMenu: function (e)
	{
		var field = Game.map.determineField(e);
		if (field) {
			var div = field.element;

			if(Game.map.contextMenu.shown){
				Game.map.contextMenu.hide();
				Game.map.marker.unmarkByType('selected');
				return;
			} else {
				Game.map.marker.mark(field, 'selected');
				Game.map.contextMenu.show(field);
			}
		}
	},

	/**
	 * Return the field
	 * @param Event
	 * @return Field
	 */
	determineField : function (e)
	{
		var local = Game.utils.globalToLocal(Game.map.overlayDiv, e.pageX, e.pageY);
		var map = Game.map.map;
		var mouseX = local['x'] + Game.map.dX;
		var mouseY = local['y'] + Game.map.dY -18;

		var idY = (mouseY + (20/43)*mouseX) / 39;
		var idX = Math.floor(mouseX/43 - idY);
		idY = Math.floor(idY);

		if (!Game.utils.isset(map[idX]) || !Game.utils.isset(map[idX][idY])){
			return null;
		}

		var posX = Game.map.calculateXPos(map[idX][idY]);
		var posY = Game.map.calculateYPos(map[idX][idY]);

		var c = (-4) * (posX + 57) + 3 * posY; // posX +60, but +57 fits better
		if (0 < 4*(mouseX) - 3*(mouseY) + c){
			idX++;
		}
		else{
			c = (-4) * (posX + 46) - 3 * (posY + 20); // posX +60, but +46 fits better
			if (0 < 4*(mouseX) + 3*(mouseY) + c){
				idY++;
			}
		}

		if (!Game.utils.isset(map[idX]) || !Game.utils.isset(map[idX][idY])){
			return null;
		}

		return map[idX][idY];

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

Game.map.marker = {

	/**
	 * The stack of ellipses
	 * @var array
	 */
	markers : {},

	colors: {
		disabled: 'brown',
		selected: 'red',
		target: 'yellow',
		focus: 'blue',
		hover: 'white'
	},

	/**
	 * Marks the given fields with given valid color
	 * @param object/string
	 * @param string
	 * @return void
	 */
	mark : function (field, type) {
		if (!Game.utils.isset(this.markers[type])){
			this.markers[type] = {};
		}

		$(field.element).addClass('markedField' + type);

		var x = parseInt(field.element.css('left'));
		var y = parseInt(field.element.css('top'));

		var ellipse = Game.map.overlay.ellipse(x+30, y+20, 30, 20); //left,top,x-axis, y-axis
		ellipse.id = 'marker-' + $(field.element).attr('id');

		ellipse.attr({stroke: this.colors[type], 'stroke-width': 4});
		this.markers[type]['marker-' + $(field.element).attr('id')] = ellipse;
	},

	/**
	 * Unmarks the fields specified by its id
	 * @param string
	 * @return void
	 */
	unmarkById : function (id) {
		$.each(this.markers, function(type, arr){
			if (Game.utils.isset(arr['marker-' + id])){
				arr['marker-' + id].remove();
			}
		});
	},

	/**
	 * Unmarks all fields and sets click to zero
	 * @param string
	 * @return void
	 */
	unmarkByType : function (type) {
		if(Game.utils.isset(this.markers[type])){
			$.each(this.markers[type], function(key, ellipse){
				ellipse.remove();
			});
			this.markers[type] = {};
		}
	}
};

Game.map.tooltip = {
	/**
	 * @var object represents field info
	 */
	element :  $('<div id="fieldInfo" />')
		.append('<table>')

		.append('<tr>')
		.append('<th>Souřadnice</th><td id="coords"></td>')
		.append('</tr>')

		.append('<tr>')
		.append('<th>Typ</th><td id="type"></td>')
		.append('</tr>')

		.append('<tr>')
		.append('<th>Vlastník</th><td id="owner"></td>')
		.append('</tr>')

		.append('<tr>')
		.append('<th>Aliance</th><td id="alliance"></td>')
		.append('</tr>')

		.append('<tr>')
		.append('<th>Budova</th><td id="facility"></td>')
		.append('</tr>')

		.append('<tr>')
		.append('<th>Úroveň</th><td id="level"></td>')
		.append('</tr>')

		.append('</table>'),

	field : null,

	/**
	 * Displays the info
	 * @return void
	 */
	show : function (field){
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


		if ((ownerId !== null && ownerId == Game.map.clan) || (allianceId !== null && allianceId == Game.map.alliance)){
			if (field['facility'] != null){
				facility = field['facility'].type;
			}

			if (field.facility !== null && field.facility['level'] !== null && field['facility'] !== null && field['facility'].type != 'headquarters'){
				level = field.facility['level'];
			}

		}
		else if ((ownerId !== null && ownerId != Game.map.clan) || (allianceId !== null && allianceId != Game.map.alliance)){
			facility = secret;
			level = secret;

		}

		$("#fieldInfo #coords").html(coords);
		Game.descriptions.translate('field', type, "#fieldInfo #type");
		$("#fieldInfo #owner").html(owner);
		$("#fieldInfo #alliance").html(alliance);
		Game.descriptions.translate('facility', facility, "#fieldInfo #facility");
		$("#fieldInfo #level").html(level);
		this.element.show();
		this.field = field;
	},

	/**
	 * Hides the info
	 * @return void
	 */
	hide : function(){
		this.element.hide();
		this.field = null;
	},

	/**
	 * Positions the info
	 * @param integer
	 * @param integer
	 * @return void
	 */
	position : function(left, top){
		$('#fieldInfo').css("left", left + 'px');
		$('#fieldInfo').css("top", top + 'px');
	},

	/**
	 * Appends the info to the target
	 * @param object/strng
	 * @return void
	 */
	append : function(target){
		$(target).append(this.element);
	}
};

Game.map.contextMenu = {
	/**
	 * true if the context menu is shown, otherwise false
	 * @var boolean
	 */
	shown : false,

	/**
	 * Represents facilities
	 * @var JSON
	 */
	facilities : null,

	/**
	 * Availible upngrades
	 * @var JSON
	 */
	upgrades : null,

	/**
	 * Availible upngrades
	 * @var JSON
	 */
	downgrades : null,

	/**
	 * Availible upngrades
	 * @var JSON
	 */
	demolitions : null,

	/**
	 * Displays context menu
	 * @param object - clicked div
	 * @param event - fired event
	 * @return void
	 */
	show: function (field) {

		$('#fieldInfo').hide();

		var x = parseInt(field.element.css('left'));
		var y = parseInt(field.element.css('top'));

		var globalCoords = Game.utils.localToGlobal($('#map'), x, y);
		coords = Game.utils.globalToLocal($('#mapContainer'), globalCoords.x, globalCoords.y);

		this.shown = true;
// 		$('#mapContainer').append(this.contextMenu);
		var actions = new Array();
		if (field['owner'] !== null && Game.map.clan !== null && field['owner']['id'] == Game.map.clan) {
			if (field.id != Game.map.rallyPoint) {
				actions.push(this.actions.setRallyPoint);
			}
			var unitContainer = {container: true, title: "Jednotky", items: []};
			if (field.facility && (field.facility.type === 'workshop' || field.facility.type === 'barracks')) {
				unitContainer.items.push(this.actions.redirectTrainUnits);
			}
			if (field.units) {
				unitContainer.items.push(this.actions.attack);
				unitContainer.items.push(this.actions.move);
				unitContainer.items.push(this.actions.exploration);
				if (Game.utils.getValue(field, ['units', 'spy'])) {
					unitContainer.items.push(this.actions.spy);
				}
				actions.push(unitContainer);
			}
			var facilityContainer = {container: true, title: "Budovy", items: []};
			if (field.facility) {
				if (field.facility.type !== 'headquarters') {
					if (field.facility.damaged) {
						facilityContainer.items.push(this.actions.repairFacility);
					} else {
						facilityContainer.items.push(this.actions.upgradeFacility);
						facilityContainer.items.push(this.actions.destroyFacility);
						if (field.facility.level > 1) {
							facilityContainer.items.push(this.actions.downgradeFacility);
						}
					}
				}
			} else {
				var buildContainer = {container: true, title: "Nová", items: []};
				$.each(Game.map.contextMenu.facilities, function (name, facility) {
					var label = $('<span>');
					Game.descriptions.translate('facility', name, label);
					var element = $('<a href="#">').html(label).click();
					buildContainer.items.push({
						title: name,
						translate: true,
						catalog: 'facility',
						click: function (target) {
							var dialog = new Game.map.contextMenu.ConstructionDialog('facilityDialog');
							dialog.setTitle('Postavit budovu');
							dialog.setBody(label.clone());
							dialog.setCost(Game.map.contextMenu.facilities[name].cost, Game.map.contextMenu.facilities[name].time);
							dialog.setSubmit({
								text: 'Zahájit stavbu',
								click: function () {
									Game.utils.signal('buildFacility', {'targetId': target['id'], 'facility': name}, function (data) {
									Game.events.refresh();
									Game.resources.fetchResources();
									Game.map.marker.unmarkByType('selected');
									Game.map.disableField(target);
								});
								}
							});
							dialog.show();
						}
					});
				});
				facilityContainer.items.push(buildContainer);
			}
			if (facilityContainer.items) {
				actions.push(facilityContainer);
			}
			if ((field['facility'] === null) || (field['facility'] !== null && field['facility'].type !== 'headquarters')){
				actions.push(this.actions.leaveField);
			}
		} else if (this.isNeighbour(field, Game.map.clan)) {
			actions.push(this.actions.colonisation)
		}

		this.menu = new Game.UI.Toolbox(null, actions);
		this.menu.setHandler('click', function (action) {
			action.click(field);
			Game.map.contextMenu.menu.hide();
		});
		this.menu.close = function () {
			Game.map.marker.unmarkByType('selected');
			this.shown = false;
		};
		this.menu.show(globalCoords.x + 50, globalCoords.y + 30);
	},

	/**
	 * Hides context menu
	 * @return void
	 */
	hide: function ()
	{
		this.menu.hide();
		this.shown = false;
	},

	actions: {
		colonisation: {
			title: 'Kolonizace',
			click: function (target) {
				Game.spinner.show(Game.map.contextMenu.contextMenu);
				Game.utils.signal('fetchColonisationCost', {'targetId': target.id}, function (data) {
					var dialog = new Game.map.contextMenu.ConstructionDialog('colonisationDialog');
					dialog.setTitle('Kolonizace');
					dialog.setBody('Kolonizace');
					dialog.setCost(data.cost, data.time);
					dialog.setSubmit({
						text: 'Zahájit kolonizaci',
						click: function () {
							Game.utils.signal('sendColonisation', {'targetId': target['id']}, function(){
								Game.events.refresh();
								Game.resources.fetchResources();
								Game.map.marker.unmarkByType('selected');
								Game.map.disableField(target);
							});
						}
					});
					dialog.show();
					Game.spinner.hide();
				});
			}
		},
		exploration: {
			title: 'Průzkum',
			click: function (origin) {
				var dialog = new Game.map.contextMenu.ExplorationDialog('explorationDialog', origin);
				dialog.show();
			}
		},
		move: {
			title: 'Přesun jednotek',
			click: function (origin) {
				var dialog = new Game.map.contextMenu.MoveDialog('moveDialog', origin);
				dialog.show();
			}
		},
		attack: {
			title: 'Útok',
			click: function (origin) {
				var dialog = new Game.map.contextMenu.AttackDialog('attackDialog', origin);
				dialog.show();
			}
		},
		spy: {
			title: 'Špionáž',
			click: function (origin) {
				var dialog = new Game.map.contextMenu.SpyDialog('spyDialog', origin);
				dialog.show();
			}
		},
		upgradeFacility: {
			title: 'Vylepšit budovu',
			click: function (target) {
				var dialog = new Game.map.contextMenu.ConstructionDialog('facilityDialog');
				dialog.setTitle('Vylepšit budovu');
				var label = $('<span>');
				Game.descriptions.translate('facility', target.facility.type, label);
				dialog.setBody($('<span>').append(label).append(' (' + (target.facility.level + 1) + ')'));
				dialog.setCost(Game.map.contextMenu.upgrades[target.facility.type][target.facility.level + 1].cost, Game.map.contextMenu.upgrades[target.facility.type][target.facility.level + 1].time);
				dialog.setSubmit({
					text: 'Zahájit stavbu',
					click: function () {
						Game.map.contextMenu.hide();
						Game.utils.signal('upgradeFacility', {'targetId': target['id']}, function () {
							Game.events.refresh();
							Game.resources.fetchResources();
							Game.map.marker.unmarkByType('selected');
							Game.map.disableField(target);
						});
					}
				});
				dialog.show();
			}
		},
		downgradeFacility: {
			title: 'Downgradovat budovu',
			click: function (target) {
				var dialog = new Game.map.contextMenu.ConstructionDialog('facilityDialog');
				dialog.setTitle('Downgradovat budovu');
				var label = $('<span>');
				Game.descriptions.translate('facility', target.facility.type, label);
				dialog.setBody(label);
				dialog.setCost(Game.map.contextMenu.downgrades[target.facility.type][target.facility.level - 1].cost, Game.map.contextMenu.downgrades[target.facility.type][target.facility.level - 1].time);
				dialog.setSubmit({
					text: 'Zahájit stavbu',
					click: function () {
						Game.map.contextMenu.hide();
						Game.utils.signal('downgradeFacility', {'targetId': target['id']}, function () {
							Game.events.refresh();
							Game.resources.fetchResources();
							Game.map.marker.unmarkByType('selected');
							Game.map.disableField(target);
						});
					}
				});
				dialog.show();
			}
		},
		destroyFacility: {
			title: 'Strhnout budovu',
			click: function (target) {
				var dialog = new Game.map.contextMenu.ConstructionDialog('facilityDialog');
				dialog.setTitle('Strhnout budovu');
				var label = $('<span>');
				Game.descriptions.translate('facility', target.facility.type, label);
				dialog.setBody(label);
				dialog.setCost(Game.map.contextMenu.demolitions[target.facility.type][target.facility.level].cost, Game.map.contextMenu.demolitions[target.facility.type][target.facility.level].time);
				dialog.setSubmit({
					text: 'Zahájit demolici',
					click: function () {
						Game.map.contextMenu.hide();
						Game.utils.signal('destroyFacility', {'targetId': target['id']}, function () {
							Game.events.refresh();
							Game.resources.fetchResources();
							Game.map.marker.unmarkByType('selected');
							Game.map.disableField(target);
							Game.spinner.hide();
						});
					}
				});
				dialog.show();
			}
		},
		buildFacility: {
			title: 'Postavit budovu',
			click: function (target) {
				Game.map.contextMenu.hide();
				var menu = Game.map.contextMenu.contextMenu.clone().html('');
				$.each(Game.map.contextMenu.facilities, function (name, facility) {
					var label = $('<span>');
					Game.descriptions.translate('facility', name, label);
					var element = $('<a href="#">').html(label).click(function (e) {
						e.preventDefault();
						menu.hide();
						var dialog = new Game.map.contextMenu.ConstructionDialog('facilityDialog');
						dialog.setTitle('Postavit budovu');
						dialog.setBody(label.clone());
						dialog.setCost(Game.map.contextMenu.facilities[name].cost, Game.map.contextMenu.facilities[name].time);
						dialog.setSubmit({
							text: 'Zahájit stavbu',
							click: function () {
								Game.utils.signal('buildFacility', {'targetId': target['id'], 'facility': name}, function (data) {
								Game.events.refresh();
								Game.resources.fetchResources();
								Game.map.marker.unmarkByType('selected');
								Game.map.disableField(target);
							});
							}
						});
						dialog.show();
					});
					menu.append(element);
				});
				$('#mapContainer').append(menu);
				menu.show();
			}
		},
		repairFacility: {
			title: 'Opravit budovu',
			click: function (target) {
				var dialog = new Game.map.contextMenu.ConstructionDialog('facilityDialog');
				dialog.setTitle('Opravit budovu');
				var label = $('<span>');
				Game.descriptions.translate('facility', target.facility.type, label);
				dialog.setBody($('<span>').append(label).append(' (' + target.facility.level + ')'));
				dialog.setCost(Game.map.contextMenu.repairs[target.facility.type][target.facility.level].cost, Game.map.contextMenu.repairs[target.facility.type][target.facility.level].time);
				dialog.setSubmit({
					text: 'Zahájit opravy',
					click: function () {
						Game.map.contextMenu.hide();
						Game.utils.signal('repairFacility', {'targetId': target['id']}, function () {
							Game.events.refresh();
							Game.resources.fetchResources();
							Game.map.marker.unmarkByType('selected');
							Game.map.disableField(target);
						});
					}
				});
				dialog.show();
			}
		},
		leaveField: {
			title: 'Opustit pole',
			click: function (target) {
				Game.spinner.show(Game.map.contextMenu.contextMenu);
				Game.utils.signal('leaveField', {'targetId': target['id']}, function () {
					Game.events.refresh();
					Game.map.marker.unmarkByType('selected');
					Game.map.disableField(target);
					Game.spinner.hide();
				});
			}
		},
		setRallyPoint: {
			title: 'Umístit shromáždiště',
			click: function (target) {
				Game.utils.signal('setRallyPoint', {fieldId: target.id}, function () {
					Game.map.render();
				});
			}
		},
		redirectTrainUnits: {
			title: 'Trénovat jednotky',
			click: function (target) {
				Game.utils.signal('redirect', {'target': 'Unit:train'}, function () {});
			}
		}
	},

	/**
	 * Fetches facilities data and saves them into this.facilities
	 * @return void
	 */
	fetchFacilities: function (){
		Game.utils.signal('fetchFacilities', {}, function(data) {
			Game.map.contextMenu.facilities = data['facilities'];
			Game.map.contextMenu.upgrades = data['upgrades'];
			Game.map.contextMenu.downgrades = data['downgrades'];
			Game.map.contextMenu.repairs = data['repairs'];
			Game.map.contextMenu.demolitions = data['demolitions'];
		});

	},

	/**
	 * If field is neighbour of (at least) one clan field, returns true
	 * @param field
	 * @param iteger
	 * @return boolean
	 */
	isNeighbour : function(field, ownerId)
	{
		var x = field['coordX'];
		var y = field['coordY'];

		if (Game.map.map !== null){
			if (
				(Game.utils.isset(Game.map.map[x-1][y+1]) && Game.map.map[x-1][y+1]['owner'] !== null && Game.map.map[x-1][y+1]['owner']['id'] == ownerId)
				||
				(Game.utils.isset(Game.map.map[x+1][y-1]) && Game.map.map[x+1][y-1]['owner'] !== null && Game.map.map[x+1][y-1]['owner']['id'] == ownerId)
				||
				(Game.utils.isset(Game.map.map[x][y-1]) && Game.map.map[x][y-1]['owner'] !== null && Game.map.map[x][y-1]['owner']['id'] == ownerId)
				||
				(Game.utils.isset(Game.map.map[x-1][y]) && Game.map.map[x-1][y]['owner'] !== null && Game.map.map[x-1][y]['owner']['id'] == ownerId)
				||
				(Game.utils.isset(Game.map.map[x+1][y]) && Game.map.map[x+1][y]['owner'] !== null && Game.map.map[x+1][y]['owner']['id'] == ownerId)
				||
				(Game.utils.isset(Game.map.map[x][y+1]) && Game.map.map[x][y+1]['owner'] !== null && Game.map.map[x][y+1]['owner']['id'] == ownerId)
			){
				return true;
			}
		}
		return false;
	}
};

Game.map.contextMenu.ConstructionDialog = Class({
	extends: Game.UI.Dialog,

	constructor: function (id)
	{
		Game.map.contextMenu.ConstructionDialog._superClass.constructor.call(this, id);
	},

	setCost: function (cost, time)
	{
		this.cost = cost;
		this.time = time;
	},

	getBody: function ()
	{
		return $('<div />')
			.append($('<p>').html(this.body))
			.append(Game.UI.resourceTable(this.cost))
			.append(Game.UI.timeTable(this.time));
	},

	show: function ()
	{
		Game.map.contextMenu.ConstructionDialog._superClass.show.call(this);
		if (!Game.resources.hasSufficientResources(this.cost)) {
			$(this.element).parent().find('.submitButton').button('disable');
		}
	},

	closeHandler: function ()
	{
		Game.map.marker.unmarkByType('selected');
	}
});

Game.map.contextMenu.UnitMoveDialog = Class({
	extends: Game.UI.Dialog,

	constructor: function (id, origin)
	{
		Game.map.contextMenu.UnitMoveDialog._superClass.constructor.call(this, id);
		this.origin = origin;
	},

	getBody: function ()
	{
		var element = $('<div>');
		var origin = this.origin;
		$(element).append('kliknutim vyberte cíl:<div id="coords">Z ['+origin['coordX']+';'+origin['coordY']+'] do [<span id="targetX">?</span>;<span id="targetY">?</span>]</div><br/>');
		var table = $('<table id="units" style="border:1px solid white; padding:10px"/>');
		table.append('<tr style="width:100px; text-align:left"><th>Jméno</th><th>Počet</th><th style="width:50px; text-align:right">Max</th></tr>');
		$(element).append(table);
		$.each(this.origin['units'], function (key, unit) {
			var tr = $('<tr id="'+unit['id']+'" />');
			unitName = key;
			tr.append('<td class="name" id="'+key+'" style="width:100px"></td><td class="count"><input type="text" size="5" name="'+key+'" /></td><td class="max" style="width:50px; text-align:right">('+unit['count']+')</td>');
			table.append(tr);
			Game.descriptions.translate('unit', key, tr.children('#' + unit.type));
			tr.children('.max').click(function(){
				tr.children('.count').children('input').val(unit['count']);
			})
			.css({
				'cursor' : 'pointer'
			});
		});
		return element;
	},

	closeHandler: function ()
	{
		Game.map.marker.unmarkByType('selected');
		Game.map.marker.unmarkByType('target');
		Game.map.overlayDiv.unbind('click');
		Game.map.overlayDiv.click({'context': this}, Game.map.openMenu);
	},

	show: function ()
	{
		var element = Game.map.contextMenu.UnitMoveDialog._superClass.show.call(this);
		Game.map.overlayDiv.unbind('click');
		Game.map.overlayDiv.click({'context': this}, this.selectTarget);
	},

	getUnitList: function ()
	{
		var inputs = $('#units .count input');
		var trs = $('#units tr');
		var result = {};

		$.each(inputs, function(key, input){
			var unitCount = $(input).val();

			if (unitCount > 0 && unitCount !== "" && Game.utils.isset(unitCount)){
				var unitId = $(trs[key+1]).attr('id');
				result[unitId] = unitCount;
			}

		});
		return result;
	},

	subtractUnits: function (units)
	{
		var origin = this.origin;
		$.each(units, function(id, count) {
			var name;
			$.each(origin.units, function (type, unit) {
				if (unit.id == id) {
					name = type;
					return false;
				}
			});
			if (name) {
				origin['units'][name]['count'] = parseInt(origin['units'][name]['count']) - parseInt(count);
			}
		});
	},


	selectTarget: function (e)
	{
		var context = e.data.context;
		var target = Game.map.determineField(e);
		context.target = target;
		if (context.validateTarget(target)) {
			var targetX = $('#' + context.id + ' #targetX');
			var targetY = $('#' + context.id + ' #targetY');
			Game.map.marker.unmarkByType('target');
			Game.map.marker.mark(target, 'target');
			targetX.html(target['coordX']);
			targetY.html(target['coordY']);
		}
	}
});

Game.map.contextMenu.AttackDialog = Class({
	extends: Game.map.contextMenu.UnitMoveDialog,

	constructor: function (id, origin)
	{
		Game.map.contextMenu.AttackDialog._superClass.constructor.call(this, id, origin);
	},

	validateTarget: function (target)
	{
		return !(target['owner'] !== null && Game.map.clan !== null && target['owner']['id'] == Game.map.clan)
			&& !(target['owner'] !== null && target['owner']['alliance'] !== null
			&& Game.map.alliance !== null && target['owner']['alliance']['id'] == Game.map.alliance);
	},

	title: 'Útok',

	getBody: function ()
	{
		var body = Game.map.contextMenu.AttackDialog._superClass.getBody.call(this);
		body.append('Typ útoku: <select id="attackType"><option value="pillaging">Loupeživý</option><option value="occupation">Dobyvačný</option><option value="razing">Ničivý</option></select>');
		return body;
	},

	submit: {
		text: "Zaútočit",
		click: function (context) {
			Game.spinner.show(context.element);
			var params = {
				'originId': context.origin['id'],
				'targetId': context.target['id'],
				'type': $('#attackType').val()
			};
			var units = context.getUnitList();
			context.subtractUnits(units);
			jQuery.extend(params, units);
			Game.utils.signal('sendAttack', params, function () {
				Game.events.refresh();
				Game.spinner.hide();
				$(context.element).dialog("close");
			});
		}
	}
});

Game.map.contextMenu.MoveDialog = Class({
	extends: Game.map.contextMenu.UnitMoveDialog,

	constructor: function (id, origin)
	{
		Game.map.contextMenu.AttackDialog._superClass.constructor.call(this, id, origin);
	},

	validateTarget: function (target)
	{
		return (target['owner'] !== null && Game.map.clan !== null && target['owner']['id'] == Game.map.clan)
			|| (Game.map.alliance !== null && target['owner']['alliance']['id'] == Game.map.alliance);
	},

	title: 'Přesun jednotek',

	getBody: function ()
	{
		var body = Game.map.contextMenu.MoveDialog._superClass.getBody.call(this);
		return body;
	},

	submit: {
		text: "Přesun",
		click: function (context) {
			Game.spinner.show(context.element);
			var params = {
				'originId': context.origin['id'],
				'targetId': context.target['id']
			};
			var units = context.getUnitList();
			context.subtractUnits(units);
			jQuery.extend(params, units);
			Game.utils.signal('moveUnits', params, function () {
				Game.events.refresh();
				Game.spinner.hide();
				$(context.element).dialog("close");
			});
		}
	}
});

Game.map.contextMenu.SpyDialog = Class({

	extends: Game.map.contextMenu.UnitMoveDialog,

	constructor: function (id, origin)
	{
		Game.map.contextMenu.SpyDialog._superClass.constructor.call(this, id, origin);
	},

	validateTarget: function (target)
	{
		return !(target['owner'] === null || Game.map.clan === null || target['owner']['id'] == Game.map.clan)
			&& !(target['owner'] !== null && target['owner']['alliance'] !== null
			&& Game.map.alliance !== null && target['owner']['alliance']['id'] == Game.map.alliance);
	},

	title: 'Špionáž',

	getBody: function ()
	{
		var element = $('<div>');
		var origin = this.origin;
		$(element).append('kliknutim vyberte cíl:<div id="coords">Z ['+origin['coordX']+';'+origin['coordY']+'] do [<span id="targetX">?</span>;<span id="targetY">?</span>]</div><br/>');
		var table = $('<table id="units" style="border:1px solid white; padding:10px"/>');
		table.append('<tr style="width:100px; text-align:left"><th>Jméno</th><th>Počet</th><th style="width:50px; text-align:right">Max</th></tr>');
		$(element).append(table);
		var tr = $('<tr id="spy" />');
		tr.append('<td class="name" style="width:100px">Špion</td><td class="count"><input id="spyInput" type="text" size="5" name="spy" /></td><td class="max" style="width:50px; text-align:right">(' + (Game.utils.isset(this.origin['units']['spy']) ? this.origin['units']['spy']['count'] : '0') + ')</td>');
		table.append(tr);
		tr.children('.max').click(function(){
			tr.children('.count').children('input').val(Game.utils.getValue(origin, ['units', 'spy', 'count']));
		})
		.css({
			'cursor' : 'pointer'
		});
		//element.append('Typ akce: <select id="spyType"><option value="investigation">Špionáž</option><option value="sabotage">Sabotáž</option><option value="burglary">Loupež</option></select>');
		element.append('Typ akce: <select id="spyType"><option value="investigation">Špionáž</option></select>');
		return element;
	},

	submit: {
		text: "Poslat",
		click: function (context) {
			Game.spinner.show(context.element);

			var unitId;
			var units = context.origin['units'];
			$.each(context.origin['units'], function (key, unit) {
				if (key === 'spy'){
					unitId = unit['id'];
					return false;
				}
			});

			var params = {
				'originId': context.origin['id'],
				'targetId': context.target['id'],
				'type': $('#spyType').val()
			};

			params[unitId] = $('#spyInput').val();
			var units = Array();
			units[unitId] = $('#spyInput').val();
			context.subtractUnits(units);

			Game.utils.signal('sendSpy', params, function () {
				Game.events.refresh();
				Game.spinner.hide();
				$(context.element).dialog("close");
			});
		}
	}
});

Game.map.contextMenu.ExplorationDialog = Class({
	extends: Game.map.contextMenu.UnitMoveDialog,

	constructor: function (id, origin)
	{
		Game.map.contextMenu.AttackDialog._superClass.constructor.call(this, id, origin);
	},

	validateTarget: function (target)
	{
		return target['owner'] == null;
	},

	title: 'Průzkum',

	submit: {
		text: "Zahájit průzkum",
		click: function (context) {
			Game.spinner.show(context.element);
			var params = {
				'originId': context.origin['id'],
				'targetId': context.target['id']
			};
			var units = context.getUnitList();
			context.subtractUnits(units);
			jQuery.extend(params, units);
			Game.utils.signal('sendExploration', params, function () {
				Game.events.refresh();
				Game.spinner.hide();
				$(context.element).dialog("close");
			});
		}
	}
});
