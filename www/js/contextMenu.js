/**
 * Context menu
 * @author Petr Bělohlávek
 */

/**
 * Global functions
 */
jQuery.extend({
	contextMenu: {
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
			var localCoords = jQuery.utils.globalToLocal(
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
				jQuery.marker.unmarkAll('red');
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

			jQuery.spinner.show(jQuery.contextMenu.contextMenu);
			$.getJSON('?' + $.param({
							'do': 'fetchColonisationCost',
							'targetId': target['id']
						}),
				function(data) {
					jQuery.spinner.hide();
					if (jQuery.resources.hasSufficientResources(data['cost'])){
						actionDiv.click(function(){
							jQuery.spinner.show(jQuery.contextMenu.contextMenu);
							$.get('?' + $.param({
									'do': 'sendColonisation',
									'targetId': target['id']
								}),
								function(){
									jQuery.events.fetchEvents();
									jQuery.resources.fetchResources();
									jQuery.marker.unmarkAll('red');
									jQuery.gameMap.addDisabledField(target);
									jQuery.spinner.hide();
									jQuery.contextMenu.hide();
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

				var table = $('<table id="units" />');
				table.append('<tr><th>Jméno</th><th>Počet</th><th>Max</th></tr>');
				attackDialog.append(table);
				$.each(from['units'], function(key, unit){

					var tr = $('<tr id="'+unit['id']+'" />');
					tr.append('<td class="name">'+key+'</td><td class="count"><input type="text" name="'+key+'" /></td><td class="max">('+unit['count']+')</td>');
					table.append(tr);
					tr.children('.max').click(function(){
						tr.children('.count').children('input').val(unit['count']);
					})
					.css({
						'cursor' : 'pointer'
					});


				});

				var tmp;
				tmp = jQuery.cookie.get('#attackDialogWidth');
				var w = (tmp !== null) ? tmp : 300;

				tmp = jQuery.cookie.get('#attackDialogHeight');
				var h = (tmp !== null) ? tmp : 120;

				var pos = new Array();
				tmp = jQuery.cookie.get('#attackDialogPosX');
				pos[0] = (tmp !== null) ? parseInt(tmp) : 'center';

				tmp = jQuery.cookie.get('#attackDialogPosY');
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
						jQuery.cookie.set('#attackDialogPosX', $(attackDialog).dialog("option", "position")[0], 7);
						jQuery.cookie.set('#attackDialogPosY', $(attackDialog).dialog("option", "position")[1], 7);
					},
					resizeStop: function(event, ui) {
						jQuery.cookie.set('#attackDialogWidth', $(attackDialog).dialog("option", "width"), 7);
						jQuery.cookie.set('#attackDialogHeight', $(attackDialog).dialog("option", "height"), 7);
					},
					beforeClose: function(event, ui) {
						jQuery.marker.unmarkAll('yellow');
						jQuery.marker.unmarkAll('red');
						jQuery.contextMenu.action = null;
					}
				});

				jQuery.contextMenu.hide();
				jQuery.contextMenu.action = "attackSelect2nd";
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

				jQuery.marker.unmarkAll('yellow');
				jQuery.marker.mark(div, 'yellow');
				targetX.html(target['coordX']);
				targetY.html(target['coordY']);



				$(attackDialog).dialog(
					"option",
						"buttons",
						[
							{
								text: "Zaútočit",
								click: function() {
									jQuery.spinner.show(attackDialog);
									var inputs = $('#units .count input');
									var trs = $('#units tr');

									var params = '?' + $.param({
											'do': 'attack',
											'fromId': from['id'],
											'targetId': target['id']
										});

									var counts = new Array();
									var ids = new Array();

									$.each(inputs, function(key, input){
										var unitCount = $(input).val();
										var unitId = $(trs[key+1]).attr('id');

										counts.push(unitCount);
										ids.push(unitId);

										params += '&' + unitId + '=' + unitCount;

									});


									$.get(params,
										function(){
											jQuery.events.fetchEvents();
											jQuery.spinner.hide();
											$(this).dialog("close");
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
				if (jQuery.resources.hasSufficientResources(upgrade['cost'])){
					actionDiv.click(function(){
						jQuery.spinner.show(jQuery.contextMenu.contextMenu);
						$.get('?' + $.param({
								'do': 'upgradeFacility',
								'targetId': target['id']
							}),
							function(){
								jQuery.events.fetchEvents();
								jQuery.resources.fetchResources();
								jQuery.marker.unmarkAll('red');
								jQuery.gameMap.addDisabledField(target);
								jQuery.spinner.hide();
								jQuery.contextMenu.hide();
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
				if (jQuery.resources.hasSufficientResources(downgrade['cost'])){
					actionDiv.click(function(){
						jQuery.spinner.show(jQuery.contextMenu.contextMenu);
						$.get('?' + $.param({
								'do': 'downgradeFacility',
								'targetId': target['id']
							}),
							function(){
								jQuery.events.fetchEvents();
								jQuery.resources.fetchResources();
								jQuery.marker.unmarkAll('red');
								jQuery.gameMap.addDisabledField(target);
								jQuery.spinner.hide();
								jQuery.contextMenu.hide();
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
				if (jQuery.resources.hasSufficientResources(destroy['cost'])){
					actionDiv.click(function(){
						jQuery.spinner.show(jQuery.contextMenu.contextMenu);
						$.get('?' + $.param({
								'do': 'destroyFacility',
								'targetId': target['id']
							}),
							function(){
								jQuery.events.fetchEvents();
								jQuery.resources.fetchResources();
								jQuery.marker.unmarkAll('red');
								jQuery.gameMap.addDisabledField(target);
								jQuery.spinner.hide();
								jQuery.contextMenu.hide();
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

				$.each(jQuery.contextMenu.facilities, function(name, facility) {
					var facilityDiv = jQuery.contextMenu.basicActionDiv.clone().html(name)
					if (jQuery.resources.hasSufficientResources(facility['cost'])){

						facilityDiv.click(function(){

							jQuery.spinner.show(jQuery.contextMenu.contextMenu);
							$.get('?' + $.param({
								'do': 'buildFacility',
								'targetId': target['id'],
								'facility': name
								}),
								function(data){
									jQuery.events.fetchEvents();
									jQuery.resources.fetchResources();
									jQuery.marker.unmarkAll('red');
									jQuery.gameMap.addDisabledField(target);
									jQuery.spinner.hide();
									jQuery.contextMenu.hide();
								}
							);
						});
					}
					else{
						facilityDiv.css('text-decoration', 'line-through');
					}
					jQuery.contextMenu.contextMenu.append(facilityDiv);

				});

				jQuery.contextMenu.addCancelAction();

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

				jQuery.spinner.show(jQuery.contextMenu.contextMenu);
				$.get('?' + $.param({
						'do': 'leaveField',
						'targetId': target['id']
					}),
					function(){
						jQuery.events.fetchEvents();
						jQuery.marker.unmarkAll('red');
						jQuery.gameMap.addDisabledField(target);
						jQuery.spinner.hide();
						jQuery.contextMenu.hide();
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
				jQuery.marker.unmarkAll('red');
				jQuery.contextMenu.hide();
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
				jQuery.contextMenu.facilities = data['facilities'];
				jQuery.contextMenu.upgrades = data['upgrades'];
				jQuery.contextMenu.downgrades = data['downgrades'];
				jQuery.contextMenu.demolitions = data['demolitions'];
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

			if (jQuery.gameMap.map !== null){
				if (
					(typeof(jQuery.gameMap.map[x-1][y+1]) !== 'undefined' && jQuery.gameMap.map[x-1][y+1]['owner'] !== null && jQuery.gameMap.map[x-1][y+1]['owner']['id'] == ownerId)
					||
					(typeof(jQuery.gameMap.map[x+1][y-1]) !== 'undefined' && jQuery.gameMap.map[x+1][y-1]['owner'] !== null && jQuery.gameMap.map[x+1][y-1]['owner']['id'] == ownerId)
					||
					(typeof(jQuery.gameMap.map[x][y-1]) !== 'undefined' && jQuery.gameMap.map[x][y-1]['owner'] !== null && jQuery.gameMap.map[x][y-1]['owner']['id'] == ownerId)
					||
					(typeof(jQuery.gameMap.map[x-1][y]) !== 'undefined' && jQuery.gameMap.map[x-1][y]['owner'] !== null && jQuery.gameMap.map[x-1][y]['owner']['id'] == ownerId)
					||
					(typeof(jQuery.gameMap.map[x+1][y]) !== 'undefined' && jQuery.gameMap.map[x+1][y]['owner'] !== null && jQuery.gameMap.map[x+1][y]['owner']['id'] == ownerId)
					||
					(typeof(jQuery.gameMap.map[x][y+1]) !== 'undefined' && jQuery.gameMap.map[x][y+1]['owner'] !== null && jQuery.gameMap.map[x][y+1]['owner']['id'] == ownerId)
				){
					return true;
				}
			}
			return false;
		},

	}
});
