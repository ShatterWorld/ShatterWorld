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
		Game.map.marker.unmarkAll('brown');
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
		Game.map.marker.mark(div, 'brown');


	},

	/**
	 * Cleans the #map and rerender the map (using db-data)
	 * @return void
	 */
	render : function () {

		$('#map').html('');
		Game.map.tooltip.append('#content');

		Game.spinner.show('#mapContainer');
		Game.map.contextMenu.fetchFacilities();

		/**
		 * ajax that gets JSON data of visibleFields
		 */
		$.getJSON('?do=fetchMap', function(data) {
			Game.map.map = data['fields'];

			/**
			 * finds the center and calculate dX and dY
			 */
			$.each(data['fields'], function(rowKey, row) {
				$.each(row, function(key, field) {
					if(field['owner'] != null){
						if (data['clanId'] == field['owner']['id']){
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
			$.each(data['fields'], function(rowKey, row) {
				$.each(row, function(key, field) {

					var posX = Game.map.calculateXPos(field) - Game.map.dX;
					var posY = Game.map.calculateYPos(field) - Game.map.dY;

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

					var background = "url('"+Game.map.getBasepath()+"/images/fields/gen/hex_"+field['type']+"_"+borderType+".png')";
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
						//'line-height' : Game.map.fieldHeight+'px'
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
						if (Game.map.contextMenuShown){
							return;
						}

						Game.map.tooltip.show();

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
						Game.map.tooltip.hide();
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

						Game.map.tooltip.position(leftPos, topPos);
					});

					/**
					 * Runs when user click some field and increment markedFields by one
					 * @return void
					 */
					div.click(function(e){

						if(Game.map.contextMenu.contextMenuShown){
							Game.map.contextMenu.hide();
							Game.map.marker.unmarkAll('red');
							return;
						}

						if(Game.map.contextMenu.initialField === null || Game.map.contextMenu.action === null){
							Game.map.contextMenu.initialField = field;
							Game.map.marker.mark(div, 'red');
							Game.map.contextMenu.show(div, e, field, data);
						}
						else if(Game.map.contextMenu.action == "attackSelect2nd"){
							Game.map.contextMenu.attackSelect2nd(Game.map.contextMenu.initialField, field, div, data)
						}
						else{
							Game.map.contextMenu.action(Game.map.contextMenu.initialField, field);
							Game.map.contextMenu.hide();
							Game.map.marker.unmarkAll('red');
						}
					});



				});

				/**
				 * slides the sliders
				 */
				$('#mapContainer').scrollLeft(Game.map.scrollX);
				$('#mapContainer').scrollTop(Game.map.scrollY);

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

Game.map.marker = {
	/**
	 * Number of marked fields
	 * @var integer
	 */
	markedFields : 0,

	/**
	 * Returns object representing the marker
	 * @return object
	 */
	getMarkerImage : function () {return $('<img class="marker" />').attr('src', this.getBasepath() + '/images/fields/marker.png');},

	/**
	 * The size of the marker
	 * @var integer
	 */
	size : 5,

	mark : function (field, color) {
		$(field).drawEllipse(this.size, this.size, Game.map.fieldWidth-2*this.size, Game.map.fieldHeight-2*this.size, {color: color, stroke: this.size});
		$(field).attr('class', 'markedField'+color);
		/*todo:
		 * rozdělit dle barev
		 * */
	},

	/**
	 * Unmarks all fields and sets click to zero
	 * @return void
	 */
	unmarkAll : function(color){
		$('.markedField'+color+' canvas').remove();
		$('.markedField'+color).attr('class', 'field');
		this.markedFields = 0;
		this.initialField = null;
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

		.append('</table>')

		.css({
			'background' : '#5D6555',
			'color' : 'white',
			'border' : '1px solid white',
			'padding' : '3px',
			'min-width' : '180px',
			'position' : 'absolute',
			'z-index' : '99999999999999999',
			'display' : 'none',
			'-ms-filter' : "progid:DXImageTransform.Microsoft.Alpha(Opacity=90)",
			'-moz-opacity' : '0.9',
			'opacity' : '0.9'
	}),

	/**
	 * Displays the info
	 * @return void
	 */
	show : function(){
		this.element.show();
	},

	/**
	 * Hides the info
	 * @return void
	 */
	hide : function(){
		this.element.hide();
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
	contextMenuShown : false,

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
	 * @var function - action that runs when the target is selected
	 * @param Field
	 * @param Field
	 * @return void
	 */
	action : null,

	/**
	 * First clicked field (init. the action)
	 * @var Field
	 */
	initialField : null,

	/**
	 * Representing the context menu
	 * @var String/Object
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

	/**
	 * Representing a div used in #contextMenu
	 * @var String/Object
	 */
	basicActionDiv : $('<div class="action" />')
		.css({
			'cursor' : "pointer",
			'text-decoration' : "underline"
		}),

	/**
	 * Displays context menu
	 * @param object - clicked div
	 * @param event - fired event
	 * @return void
	 */
	show: function(object, e, field, data){

		this.contextMenu.html('');
		this.contextMenu.show();

		$('#fieldInfo').hide();
		var localCoords = Game.utils.globalToLocal(
			$(object).parent(),
			e.pageX,
			e.pageY
		);

		this.contextMenu.css("left", localCoords.x + 30 - $('#mapContainer').scrollLeft() + 'px');
		this.contextMenu.css("top", localCoords.y + 30 - $('#mapContainer').scrollTop() + 'px');

		this.contextMenuShown = true;
		$('#mapContainer').append(this.contextMenu);

		//my
		if (field['owner'] !== null && data['clanId'] !== null && field['owner']['id'] == data['clanId']){

			if (field['units'] !== null || field['units'] != null){
				this.addAttackAction(field);
			}
			if (field['facility'] !== null){
				if(field['facility'] !== 'headquarters'){
					this.addUpgradeFacilityAction(field);
					this.addDestroyFacilityAction(field);
					if(field['level'] > 1){
						this.addDowngradeFacilityAction(field);
					}
				}

			}
			else{
				this.addBuildFacilityAction(field);
			}

			if ((field['facility'] === null) || (field['facility'] !== null && field['facility'] !== 'headquarters')){
				this.addLeaveFieldAction(field);
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
		else if(this.isNeighbour(field, data['clanId'])){
			this.addColonisationAction(field);
		}
		//other neutral
		else {
			Game.map.marker.unmarkAll('red');
			this.hide();
			return;
		}

		this.addCancelAction();

	},


	/**
	 * Hides context menu
	 * @return void
	 */
	hide: function ()
	{
		this.contextMenu.hide();
		this.contextMenuShown = false;
	},

	/**
	 * Adds the colonisation action
	 * @param field
	 * @return void
	 */
	addColonisationAction: function(target) {
		var actionDiv = this.basicActionDiv.clone().html('Kolonizace');

		Game.spinner.show(Game.map.contextMenu.contextMenu);
		$.getJSON('?' + $.param({
						'do': 'fetchColonisationCost',
						'targetId': target['id']
					}),
			function(data) {
				Game.spinner.hide();
				if (Game.resources.hasSufficientResources(data['cost'])){
					actionDiv.click(function(){
						Game.spinner.show(Game.map.contextMenu.contextMenu);
						$.get('?' + $.param({
								'do': 'sendColonisation',
								'targetId': target['id']
							}),
							function(){
								Game.events.fetchEvents();
								Game.resources.fetchResources();
								Game.map.marker.unmarkAll('red');
								Game.map.addDisabledField(target);
								Game.spinner.hide();
								Game.map.contextMenu.hide();
							}
						);

					});

				}
				else{
					actionDiv.css('text-decoration', 'line-through');
				}
			}
		);


		this.action = null;
		this.contextMenu.append(actionDiv);
	},

	/**
	 * Adds the attack action
	 * @param field
	 * @return void
	 */
	addAttackAction: function (from){
		var actionDiv = this.basicActionDiv.clone().html('Útok*');

		actionDiv.click(function(){
			var attackDialog = $('<div />').attr('id', 'attackDialog')
				.append('kliknutim vyberte cíl:<div id="coords">Z ['+from['coordX']+';'+from['coordY']+'] do [<span id="targetX">?</span>;<span id="targetY">?</span>]</div><br/>');

			var table = $('<table id="units" style="border:1px solid white; padding:10px"/>');
			table.append('<tr style="width:100px; text-align:left"><th>Jméno</th><th>Počet</th><th style="width:50px; text-align:right">Max</th></tr>');
			attackDialog.append(table);
			$.each(from['units'], function(key, unit){

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

			var tmp;
			tmp = Game.cookie.get('#attackDialogWidth');
			var w = (tmp !== null) ? tmp : 200;

			tmp = Game.cookie.get('#attackDialogHeight');
			var h = (tmp !== null) ? tmp : 120;

			var pos = new Array();
			tmp = Game.cookie.get('#attackDialogPosX');
			pos[0] = (tmp !== null) ? parseInt(tmp) : 'center';

			tmp = Game.cookie.get('#attackDialogPosY');
			pos[1] = (tmp !== null) ? parseInt(tmp) : 'center';

			$(attackDialog).dialog({
				title: 'Útok',
				width: w,
				height: h,

				buttons: [
					{
						text: "Zrušit",
						click: function() {
							$(this).dialog("close");
						}
					}
				],

				position: pos,
				dragStop: function(event, ui){
					Game.cookie.set('#attackDialogPosX', $(attackDialog).dialog("option", "position")[0], 7);
					Game.cookie.set('#attackDialogPosY', $(attackDialog).dialog("option", "position")[1], 7);
				},
				resizeStop: function(event, ui) {
					Game.cookie.set('#attackDialogWidth', $(attackDialog).dialog("option", "width"), 7);
					Game.cookie.set('#attackDialogHeight', $(attackDialog).dialog("option", "height"), 7);
				},
				beforeClose: function(event, ui) {
					Game.map.marker.unmarkAll('yellow');
					Game.map.marker.unmarkAll('red');
					Game.map.contextMenu.action = null;
				}
			});

			Game.map.contextMenu.hide();
			Game.map.contextMenu.action = "attackSelect2nd";
		});

		this.action = null;
		this.contextMenu.append(actionDiv);
	},

	/**
	 * Marks the target and sets coords to attackDialog
	 * @param field
	 * @param field
	 * @param object/string
	 * @param JSON
	 * @return void
	 */
	attackSelect2nd : function(from, target, div, data){

		if (!(target['owner'] !== null && data['clanId'] !== null && target['owner']['id'] == data['clanId']) && !(target['owner'] !== null && target['owner']['alliance'] !== null && data['allianceId'] !== null && target['owner']['alliance']['id'] == data['allianceId'])){


			var attackDialog = $('#attackDialog');
			var targetX = $('#attackDialog #targetX');
			var targetY = $('#attackDialog #targetY');

			Game.map.marker.unmarkAll('yellow');
			Game.map.marker.mark(div, 'yellow');
			targetX.html(target['coordX']);
			targetY.html(target['coordY']);



			$(attackDialog).dialog(
				"option",
					"buttons",
					[
						{
							text: "Zaútočit",
							click: function() {
								Game.spinner.show(attackDialog);
								var inputs = $('#units .count input');
								var trs = $('#units tr');

								var params = '?' + $.param({
										'do': 'attack',
										'originId': from['id'],
										'targetId': target['id']
									});

								$.each(inputs, function(key, input){
									var unitCount = $(input).val();
									var unitId = $(trs[key+1]).attr('id');

									params += '&' + unitId + '=' + unitCount;

								});

								$.get(params,
									function(){
										Game.events.fetchEvents();
										Game.spinner.hide();
										$(attackDialog).dialog("close");
									}
								);
							}
						},
						{
							text: "Zrušit",
							click: function() {
								$(this).dialog("close");
							}
						}
					]
			);
		}

	},

	/**
	 * Adds the upgrade building action
	 * @param field
	 * @return void
	 */
	addUpgradeFacilityAction: function (target){
		var actionDiv = this.basicActionDiv.clone().html('Upgradovat budovu');

		var upgrade = this.upgrades[target['facility']][target['level']+1];
		if(upgrade !== null){
			if (Game.resources.hasSufficientResources(upgrade['cost'])){
				actionDiv.click(function(){
					Game.spinner.show(Game.map.contextMenu.contextMenu);
					$.get('?' + $.param({
							'do': 'upgradeFacility',
							'targetId': target['id']
						}),
						function(){
							Game.events.fetchEvents();
							Game.resources.fetchResources();
							Game.map.marker.unmarkAll('red');
							Game.map.addDisabledField(target);
							Game.spinner.hide();
							Game.map.contextMenu.hide();
						}
					);

				});

			}
			else{
				actionDiv.css('text-decoration', 'line-through');
			}
		}

		this.action = null;
		this.contextMenu.append(actionDiv);
	},

	/**
	 * Adds the downgrade building action
	 * @param field
	 * @return void
	 */
	addDowngradeFacilityAction: function (target){
		var actionDiv = this.basicActionDiv.clone().html('Downgradovat budovu');
		var downgrade = this.downgrades[target['facility']][target['level']-1];
		if(downgrade !== null){
			if (Game.resources.hasSufficientResources(downgrade['cost'])){
				actionDiv.click(function(){
					Game.spinner.show(Game.map.contextMenu.contextMenu);
					$.get('?' + $.param({
							'do': 'downgradeFacility',
							'targetId': target['id']
						}),
						function(){
							Game.events.fetchEvents();
							Game.resources.fetchResources();
							Game.map.marker.unmarkAll('red');
							Game.map.addDisabledField(target);
							Game.spinner.hide();
							Game.map.contextMenu.hide();
						}
					);

				});

			}
			else{
				actionDiv.css('text-decoration', 'line-through');
			}
		}

		this.action = null;
		this.contextMenu.append(actionDiv);
	},

	/**
	 * Adds the destroy building action
	 * @param field
	 * @return void
	 */
	addDestroyFacilityAction: function (target){
		var actionDiv = this.basicActionDiv.clone().html('Strhnout budovu');
		var destroy = this.demolitions[target['facility']][target['level']];

		if(destroy !== null){
			if (Game.resources.hasSufficientResources(destroy['cost'])){
				actionDiv.click(function(){
					Game.spinner.show(Game.map.contextMenu.contextMenu);
					$.get('?' + $.param({
							'do': 'destroyFacility',
							'targetId': target['id']
						}),
						function(){
							Game.events.fetchEvents();
							Game.resources.fetchResources();
							Game.map.marker.unmarkAll('red');
							Game.map.addDisabledField(target);
							Game.spinner.hide();
							Game.map.contextMenu.hide();
						}
					);

				});

			}
			else{
				actionDiv.css('text-decoration', 'line-through');
			}
		}
		else{alert('destroy is undefined');}

		this.action = null;
		this.contextMenu.append(actionDiv);
	},

	/**
	 * Adds the build building action
	 * @param field
	 * @return void
	 */
	addBuildFacilityAction: function (target){
		var actionDiv = this.basicActionDiv.clone().html('Postavit budovu');
		actionDiv.click(function(){
			$('#contextMenu').html('Budovy:');

			$.each(Game.map.contextMenu.facilities, function(name, facility) {
				var facilityDiv = Game.map.contextMenu.basicActionDiv.clone();
				if (Game.resources.hasSufficientResources(facility['cost'])){

					facilityDiv.click(function(){

						Game.spinner.show(Game.map.contextMenu.contextMenu);
						$.get('?' + $.param({
							'do': 'buildFacility',
							'targetId': target['id'],
							'facility': name
							}),
							function(data){
								Game.events.fetchEvents();
								Game.resources.fetchResources();
								Game.map.marker.unmarkAll('red');
								Game.map.addDisabledField(target);
								Game.spinner.hide();
								Game.map.contextMenu.hide();
							}
						);
					});
				} else {
					facilityDiv.css('text-decoration', 'line-through');
				}
				Game.map.contextMenu.contextMenu.append(facilityDiv);
				Game.descriptions.translate('facility', name, facilityDiv);

			});

			Game.map.contextMenu.addCancelAction();

		});

		this.action = null;
		this.contextMenu.append(actionDiv);
	},

	/**
	 * Adds the colonisation action
	 * @param field
	 * @return void
	 */
	addLeaveFieldAction: function(target) {
		var actionDiv = this.basicActionDiv.clone().html('Opustit pole');
		actionDiv.click(function(){

			Game.spinner.show(Game.map.contextMenu.contextMenu);
			$.get('?' + $.param({
					'do': 'leaveField',
					'targetId': target['id']
				}),
				function(){
					Game.events.fetchEvents();
					Game.map.marker.unmarkAll('red');
					Game.map.addDisabledField(target);
					Game.spinner.hide();
					Game.map.contextMenu.hide();
				}
			);
		});

		this.action = null;
		this.contextMenu.append(actionDiv);
	},

	/**
	 * Adds the cancel action
	 * @return void
	 */
	addCancelAction: function (){
		var actionDiv = this.basicActionDiv.clone().html('Zrušit');
		actionDiv.click(function(){
			Game.map.marker.unmarkAll('red');
			Game.map.contextMenu.hide();
		});

		this.action = null;
		this.contextMenu.append(actionDiv);
	},

	/**
	 * Fetches facilities data and saves them into this.facilities
	 * @return void
	 */
	fetchFacilities: function (){
		$.getJSON('?do=fetchFacilities', function(data) {
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
	},

};