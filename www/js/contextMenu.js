/**
 * Context menu
 * @author Petr Bělohlávek
 */

/**
 * Global functions
 */
var Game = Game || {};
Game.contextMenu = {
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
			Game.marker.unmarkAll('red');
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

		Game.spinner.show(Game.contextMenu.contextMenu);
		$.getJSON('?' + $.param({
						'do': 'fetchColonisationCost',
						'targetId': target['id']
					}),
			function(data) {
				Game.spinner.hide();
				if (Game.resources.hasSufficientResources(data['cost'])){
					actionDiv.click(function(){
						Game.spinner.show(Game.contextMenu.contextMenu);
						$.get('?' + $.param({
								'do': 'sendColonisation',
								'targetId': target['id']
							}),
							function(){
								Game.events.fetchEvents();
								Game.resources.fetchResources();
								Game.marker.unmarkAll('red');
								Game.map.addDisabledField(target);
								Game.spinner.hide();
								Game.contextMenu.hide();
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
					Game.marker.unmarkAll('yellow');
					Game.marker.unmarkAll('red');
					Game.contextMenu.action = null;
				}
			});

			Game.contextMenu.hide();
			Game.contextMenu.action = "attackSelect2nd";
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

			Game.marker.unmarkAll('yellow');
			Game.marker.mark(div, 'yellow');
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
					Game.spinner.show(Game.contextMenu.contextMenu);
					$.get('?' + $.param({
							'do': 'upgradeFacility',
							'targetId': target['id']
						}),
						function(){
							Game.events.fetchEvents();
							Game.resources.fetchResources();
							Game.marker.unmarkAll('red');
							Game.map.addDisabledField(target);
							Game.spinner.hide();
							Game.contextMenu.hide();
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
					Game.spinner.show(Game.contextMenu.contextMenu);
					$.get('?' + $.param({
							'do': 'downgradeFacility',
							'targetId': target['id']
						}),
						function(){
							Game.events.fetchEvents();
							Game.resources.fetchResources();
							Game.marker.unmarkAll('red');
							Game.map.addDisabledField(target);
							Game.spinner.hide();
							Game.contextMenu.hide();
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
					Game.spinner.show(Game.contextMenu.contextMenu);
					$.get('?' + $.param({
							'do': 'destroyFacility',
							'targetId': target['id']
						}),
						function(){
							Game.events.fetchEvents();
							Game.resources.fetchResources();
							Game.marker.unmarkAll('red');
							Game.map.addDisabledField(target);
							Game.spinner.hide();
							Game.contextMenu.hide();
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

			$.each(Game.contextMenu.facilities, function(name, facility) {
				var facilityDiv = Game.contextMenu.basicActionDiv.clone().html(name)
				if (Game.resources.hasSufficientResources(facility['cost'])){

					facilityDiv.click(function(){

						Game.spinner.show(Game.contextMenu.contextMenu);
						$.get('?' + $.param({
							'do': 'buildFacility',
							'targetId': target['id'],
							'facility': name
							}),
							function(data){
								Game.events.fetchEvents();
								Game.resources.fetchResources();
								Game.marker.unmarkAll('red');
								Game.map.addDisabledField(target);
								Game.spinner.hide();
								Game.contextMenu.hide();
							}
						);
					});
				}
				else{
					facilityDiv.css('text-decoration', 'line-through');
				}
				Game.contextMenu.contextMenu.append(facilityDiv);

			});

			Game.contextMenu.addCancelAction();

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

			Game.spinner.show(Game.contextMenu.contextMenu);
			$.get('?' + $.param({
					'do': 'leaveField',
					'targetId': target['id']
				}),
				function(){
					Game.events.fetchEvents();
					Game.marker.unmarkAll('red');
					Game.map.addDisabledField(target);
					Game.spinner.hide();
					Game.contextMenu.hide();
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
			Game.marker.unmarkAll('red');
			Game.contextMenu.hide();
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
			Game.contextMenu.facilities = data['facilities'];
			Game.contextMenu.upgrades = data['upgrades'];
			Game.contextMenu.downgrades = data['downgrades'];
			Game.contextMenu.demolitions = data['demolitions'];
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
				(typeof(Game.map.map[x-1][y+1]) !== 'undefined' && Game.map.map[x-1][y+1]['owner'] !== null && Game.map.map[x-1][y+1]['owner']['id'] == ownerId)
				||
				(typeof(Game.map.map[x+1][y-1]) !== 'undefined' && Game.map.map[x+1][y-1]['owner'] !== null && Game.map.map[x+1][y-1]['owner']['id'] == ownerId)
				||
				(typeof(Game.map.map[x][y-1]) !== 'undefined' && Game.map.map[x][y-1]['owner'] !== null && Game.map.map[x][y-1]['owner']['id'] == ownerId)
				||
				(typeof(Game.map.map[x-1][y]) !== 'undefined' && Game.map.map[x-1][y]['owner'] !== null && Game.map.map[x-1][y]['owner']['id'] == ownerId)
				||
				(typeof(Game.map.map[x+1][y]) !== 'undefined' && Game.map.map[x+1][y]['owner'] !== null && Game.map.map[x+1][y]['owner']['id'] == ownerId)
				||
				(typeof(Game.map.map[x][y+1]) !== 'undefined' && Game.map.map[x][y+1]['owner'] !== null && Game.map.map[x][y+1]['owner']['id'] == ownerId)
			){
				return true;
			}
		}
		return false;
	},

};