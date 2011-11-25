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
	 * The largest z-index value
	 * @var integer
	 */
	maxZ : 0,

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
	 * @param Field
	 * @param String
	 * @return void
	 */
	disableField : function(field, type){
		this.disabledFields.push(field);
		field.element.attr('data-disabled', type);
		Game.map.marker.mark(field, 'disabled');
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

			/**
			 * finds the center and calculate dX and dY
			 */
			$.each(data['fields'], function(rowKey, row) {
				$.each(row, function(key, field) {
					if(field['owner'] != null){
						if (Game.map.clan == field['owner']['id']){
							if(field['facility'] != null){
								if (field['facility'] == 'headquarters'){
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
					var z = field['coordX']*field['coordY'];
					Game.map.maxZ = Math.max(z, Game.map.maxZ);
					Game.map.maxXPos = Math.max(posX, Game.map.maxXPos);
					Game.map.maxYPos = Math.max(posY, Game.map.maxYPos);

					var borderType = 'neutral';
					var background = "url('"+basePath+"/images/fields/hex_"+field['type']+".png')";
					var div = $('<div class="field" />').attr('id', 'field_'+field['coordX']+'_'+field['coordY']);
					var divStyle = 'width: 60px; height: 40px; position: absolute; left: '+posX+'px; top: '+posY+'px; z-index: '+z+'; background: '+background+';';
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

					if (field['owner'] !== null && Game.map.clan !== null && field['owner']['id'] == Game.map.clan){
						if (field['facility'] !== null){
							text.append(fieldLabel);
							Game.descriptions.translate('facility', field['facility'], fieldLabel);
							if(field['facility'] !== 'headquarters'){
								text.append(' ('+field['level']+')');
							}
						}
					}
					div.append(text);
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
			Game.map.overlayDiv.css({'width' : canvasWidth, 'height' : canvasHeight, 'position' : 'absolute', 'left' : 0, 'top' : 0});
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

						Game.map.overlay.canvas.style.zIndex = Game.map.maxZ + 1;
					}
				});
			});

			Game.map.loaded = true;

			while (f = Game.map.disabledFieldsStack.pop()) {
				Game.map.disableField(Game.map.map[f.x][f.y], f.type);
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
	
	determineField : function (e)
	{
		var local = Game.utils.globalToLocal(Game.map.overlayDiv, e.pageX, e.pageY);
		var mouseX = local['x'];
		var mouseY = local['y'];
		var breaker = false;
		var result = null;
		var map = Game.map.map;
		$.each(Game.map.fieldsByCoords, function(xKey, x){
			if (xKey > mouseX - Game.map.fieldWidth && xKey < mouseX){
				$.each(x, function(yKey, field){
					if (yKey > mouseY - Game.map.fieldHeight && yKey < mouseY){
						var dX = mouseX - xKey;
						var dY = -(mouseY - yKey); // More comfortable for analythic geometry
						if ((4*dX + 3*dY - 172) > 0) { // Hardcore f**king analythic geometry
							result = Game.utils.isset(map[field.coordX + 1]) ? map[field.coordX + 1][field.coordY] : null;
						} else if ((4*dX - 3*dY - 292) > 0) { // More hardcore f**king analythic geometry
							result = map[field.coordX][field.coordY + 1];
						} else {
							result = field;
						}
						breaker = true;
					}
					if (breaker) return false;
				});
				if (breaker) return false;
			}
		});
		return result !== undefined ? result : null;
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

		$(field.element).attr('class', 'markedField'+type);

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
				facility = field['facility'];
			}

			if (field['level'] !== null && field['facility'] !== null && field['facility'] != 'headquarters'){
				level = field['level'];
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
		this.element.css('z-index', Game.map.maxZ + 3);
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
	 * Representing the context menu
	 * @var String/Object
	 */
	contextMenu : $('<div class="toolbox" />')
		.html('<h3>Akce:</h3>'),

	/**
	 * Displays context menu
	 * @param object - clicked div
	 * @param event - fired event
	 * @return void
	 */
	show: function(field){

		this.contextMenu.html('');
		this.contextMenu.css('z-index', Game.map.maxZ + 2);
		this.contextMenu.show();

		$('#fieldInfo').hide();

		var x = parseInt(field.element.css('left'));
		var y = parseInt(field.element.css('top'));

		var coords = Game.utils.localToGlobal($('#map'), x, y);
		coords = Game.utils.globalToLocal($('#mapContainer'), coords.x, coords.y);

		this.contextMenu.css("left", coords.x + 50);
		this.contextMenu.css("top", coords.y + 30);

		this.shown = true;
		$('#mapContainer').append(this.contextMenu);
		var actions = new Array();
		//my
		if (field['owner'] !== null && Game.map.clan !== null && field['owner']['id'] == Game.map.clan) {
			if (field['units'] != null) {
				actions.push(this.actions.attack);
				actions.push(this.actions.exploration);
			}
			if (field['facility'] !== null) {
				if (field['facility'] !== 'headquarters') {
					actions.push(this.actions.upgradeFacility);
					actions.push(this.actions.destroyFacility);
					if(field['level'] > 1){
						actions.push(this.actions.downgradeFacility);
					}
				}
			} else {
				actions.push(this.actions.buildFacility);
			}
			if ((field['facility'] === null) || (field['facility'] !== null && field['facility'] !== 'headquarters')){
				actions.push(this.actions.leaveField);
			}
		}
		/*//alliance
		else if(field['owner'] !== null && field['owner']['alliance'] !== null && Game.map.alliance !== null && field['owner']['alliance']['id'] == Game.map.alliance){
			alert('aliance');
		}
		//enemy
		else if(field['owner'] !== null){
			alert('enemy');
 		}*/
		//neutral neighbour
		else if (this.isNeighbour(field, Game.map.clan)) {
			actions.push(this.actions.colonisation)
		}

		actions.push({title: 'Zrušit', click: function () {
			Game.map.marker.unmarkByType('selected');
			Game.map.contextMenu.hide();
		}});
		
		$.each(actions, function () {
			var element = $('<a href="#">').html(this.title).click({action: this}, function (e) {
				e.preventDefault();
				Game.map.contextMenu.hide();
				e.data.action.click(field);
			});
			Game.map.contextMenu.contextMenu.append(element);
		})

	},
	
	/**
	 * Hides context menu
	 * @return void
	 */
	hide: function ()
	{
		this.contextMenu.hide();
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
		attack: {
			title: 'Útok',
			click: function (origin) {
				var dialog = new Game.map.contextMenu.AttackDialog('attackDialog', origin);
				dialog.show();
			}
		},
		upgradeFacility: {
			title: 'Vylepšit budovu',
			click: function (target) {
				var dialog = new Game.map.contextMenu.ConstructionDialog('facilityDialog');
				dialog.setTitle('Vylepšit budovu');
				var label = $('<span>');
				Game.descriptions.translate('facility', target.facility, label);
				dialog.setBody($('<span>').append(label).append(' (' + (target.level + 1) + ')'));
				dialog.setCost(Game.map.contextMenu.upgrades[target.facility][target.level + 1].cost, Game.map.contextMenu.upgrades[target.facility][target.level + 1].time);
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
				Game.descriptions.translate('facility', target.facility, label);
				dialog.setBody(label);
				dialog.setCost(Game.map.contextMenu.downgrades[target.facility][target.level - 1].cost, Game.map.contextMenu.downgrades[target.facility][target.level - 1].time);
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
				Game.descriptions.translate('facility', target.facility, label);
				dialog.setBody(label);
				dialog.setCost(Game.map.contextMenu.demolitions[target.facility][target.level].cost, Game.map.contextMenu.demolitions[target.facility][target.level].time);
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
			tr.append('<td class="name" style="width:100px">'+key+'</td><td class="count"><input type="text" size="5" name="'+key+'" /></td><td class="max" style="width:50px; text-align:right">('+unit['count']+')</td>');
			table.append(tr);
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
		body.append('Typ útoku: <select id="attackType"><option value="pillaging">Loupeživý</option><option value="occupation">Dobyvačný</option></select>');
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
			jQuery.extend(params, context.getUnitList());
			Game.utils.signal('sendAttack', params, function () {
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
			jQuery.extend(params, context.getUnitList());
			Game.utils.signal('sendExploration', params, function () {
				Game.events.refresh();
				Game.spinner.hide();
				$(context.element).dialog("close");
			});
		}
	}
});