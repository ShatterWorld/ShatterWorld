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
					.append('kliknutim vyberte cíl:<div id="coords">Z ['+from['coordX']+';'+from['coordY']+'] do [<span id="targetX">?</span>;<span id="targetY">?</span>]</div>');

					var table = $('<table id="units" />');
					table.append('<tr><th>Jméno</th><th>Počet</th><th>Max</th></tr>');
					attackDialog.append(table);
					$.each(from['units'], function(key, unit){
						table.append('<tr><td>'+key+'</td><td><input type="text" name="'+key+'" /></td><td>('+unit['count']+')</td></tr>');
					});


				$(attackDialog).dialog({
					title: 'Útok',
					width: 300,
					height: 300,

					buttons: [
						{
							text: "Zrušit",
							click: function() {
								jQuery.marker.unmarkAll('yellow');
								jQuery.marker.unmarkAll('red');
								jQuery.contextMenu.action = null;
								$(this).dialog("close");
							}
						}
					],

					/*position: pos,
					dragStop: function(event, ui){
						jQuery.cookie.set('#countdownDialogPosX', $("#countdownDialog").dialog("option", "position")[0], 7);
						jQuery.cookie.set('#countdownDialogPosY', $("#countdownDialog").dialog("option", "position")[1], 7);
					},
					resizeStop: function(event, ui) {
						jQuery.cookie.set('#countdownDialogWidth', $("#countdownDialog").dialog("option", "width"), 7);
						jQuery.cookie.set('#countdownDialogHeight', $("#countdownDialog").dialog("option", "height"), 7);
					},
					beforeClose: function(event, ui) {
						jQuery.countdown.setDialogShown(false);
					}*/
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
		 * @param object/string
		 * @return void
		 */
		attackSelect2nd : function(field, div){
			var attackDialog = $('#attackDialog');
			var targetX = $('#attackDialog #targetX');
			var targetY = $('#attackDialog #targetY');

			jQuery.marker.unmarkAll('yellow');
			jQuery.marker.mark(div, 'yellow');
			targetX.html(field['coordX']);
			targetY.html(field['coordY']);



			$(attackDialog).dialog(
				"option",
					"buttons",
					[
						{
							text: "Zaútočit",
							click: function() {
								jQuery.marker.unmarkAll('yellow');
								jQuery.marker.unmarkAll('red');
								jQuery.contextMenu.action = null;
								alert('utocim!');
								$(this).dialog("close");

								/*ajax*/
							}
						},
						{
							text: "Zrušit",
							click: function() {
								jQuery.marker.unmarkAll('yellow');
								jQuery.marker.unmarkAll('red');
								jQuery.contextMenu.action = null;
								$(this).dialog("close");
							}
						}
					]
			);

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
