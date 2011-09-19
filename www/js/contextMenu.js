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
		 * @var boolean
		 */
		contextMenuShown : false,

		/**
		 * @var JSON represents facilities
		 */
		facilities : null,

		/**
		 * @var function - action that runs when the target is selected
		 * @param Field
		 * @param Field
		 * @return void
		 */
		action : null,

		/**
		 * @var Field - first clicked field (init. the action)
		 */
		initialField : null,

		/**
		 * @var representing the context menu
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

/*
		contextMenu : $('<div id="contextMenu" />').dialog({
			title : 'Kontextová nabídka',
			width : 150,
		}),*/

		/**
		 * @var representing a div used in #contextMenu
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
				this.addAttackAction();
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
				jQuery.gameMap.unmarkAll();
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
			actionDiv.click(function(){

				jQuery.spinner.show(jQuery.contextMenu.contextMenu);
				$.get('?' + $.param({
						'do': 'sendColonisation',
						'targetId': target['id']
					}),
					function(){
						jQuery.events.fetchEvents();
						jQuery.resources.fetchResources();
						jQuery.gameMap.unmarkAll();
						jQuery.spinner.hide();
						jQuery.contextMenu.hide();
					}
				);

			});

			this.action = null;
			this.contextMenu.append(actionDiv);
		},

		/**
		 * Adds the attack action
		 * @return void
		 */
		addAttackAction: function (){
			var actionDiv = this.basicActionDiv.clone().html('Útok*');

			actionDiv.click(function(){
				jQuery.contextMenu.hide();
				alert('vyberte cíl');
				jQuery.contextMenu.action = function(from, target){
					alert('posílám jednotky');
				};
			});

			this.action = null;
			this.contextMenu.append(actionDiv);
		},

		/**
		 * Adds the upgrade building action
		 * @param field
		 * @return void
		 */
		addUpgradeFacilityAction: function (target){
			var actionDiv = this.basicActionDiv.clone().html('Upgradovat budovu*');
			actionDiv.click(function(){

				jQuery.spinner.show(jQuery.contextMenu.contextMenu);
				$.get('?' + $.param({
						'do': 'upgradeFacility',
						'targetId': target['id']
					}),
					function(){
						jQuery.events.fetchEvents();
						jQuery.gameMap.unmarkAll();
						jQuery.spinner.hide();
						jQuery.contextMenu.hide();
					}
				);
			});

			this.action = null;
			this.contextMenu.append(actionDiv);
		},

		/**
		 * Adds the downgrade building action
		 * @param field
		 * @return void
		 */
		addDowngradeFacilityAction: function (target){
			var actionDiv = this.basicActionDiv.clone().html('Downgradovat budovu*');
			actionDiv.click(function(){

				jQuery.spinner.show(jQuery.contextMenu.contextMenu);
				$.get('?' + $.param({
						'do': 'downgradeFacility',
						'targetId': target['id']
					}),
					function(){
						jQuery.events.fetchEvents();
						jQuery.gameMap.unmarkAll();
						jQuery.spinner.hide();
						jQuery.contextMenu.hide();
					}
				);
			});

			this.action = null;
			this.contextMenu.append(actionDiv);
		},

		/**
		 * Adds the destroy building action
		 * @param field
		 * @return void
		 */
		addDestroyFacilityAction: function (target){
			var actionDiv = this.basicActionDiv.clone().html('Strhnout budovu*');
			actionDiv.click(function(){

				jQuery.spinner.show(jQuery.contextMenu.contextMenu);
				jQuery.contextMenu.hide();
				$.get('?' + $.param({
						'do': 'destroyFacility',
						'targetId': target['id']
					}),
					function(){
						jQuery.events.fetchEvents();
						jQuery.gameMap.unmarkAll();
						jQuery.spinner.hide();
						jQuery.contextMenu.hide();
					}
				);
			});

			this.action = null;
			this.contextMenu.append(actionDiv);
		},

		/**
		 * Adds the build building action
		 * @param field
		 * @return void
		 */
		addBuildFacilityAction: function (target){
			var actionDiv = this.basicActionDiv.clone().html('Postavit budovu*');
			actionDiv.click(function(){
				$('#contextMenu').html('Budovy:');

				$.each(jQuery.contextMenu.facilities, function(name, facility) {
					var facilityDiv = jQuery.contextMenu.basicActionDiv.clone().html(name)
					if (jQuery.resources.resources['metal'] >= facility['cost']['metal'] && jQuery.resources.resources['stone'] >= facility['cost']['stone'] && jQuery.resources.resources['food'] >= facility['cost']['food'] && jQuery.resources.resources['fuel'] >= facility['cost']['fuel']){

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
									jQuery.gameMap.unmarkAll();
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
						jQuery.gameMap.unmarkAll();
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
				jQuery.gameMap.unmarkAll();
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
