/**
 * Events
 * @author Petr Bělohlávek
 */

/**
 * Global functions
 */
var Game = Game || {};
Game.events = {
	/**
	 * Fetches and displays countdowns of events
	 */
	fetchEvents: function (){
		$.getJSON('?do=fetchEvents', function(data) {

			if (data === null || data['events'] === null){
				return;
			}

			Game.countdown.cleanDialog();
			var count = 0;
			$.each(data['events'], function(key, event) {
				
				var type = event['type'];
				var label = $('<span>');
				var coords = null;
				var subject = null;

				var formatCoords = function (x, y) {
					return '[' + x + ';' + y + ']';
				}
				
				var text = $('<span>');
				label.append(text);
				Game.descriptions.translate('event', type, text);
				
				if (type == 'facilityConstruction'){
					var facility = $('<span>');
					label.append(' ');
					label.append(facility);
					if (event.level > 1) {
						label.append(' (' + event.level + ')');
					}
					Game.descriptions.translate('facility', event.construction, facility);
				}
				else if (type == 'facilityDemolition'){
					var facility = $('<span>');
					label.append(' ');
					label.append(facility);
					if (event.level > 1) {
						label.append(' (' + event.level + ')');
					}
					Game.descriptions.translate('facility', event.construction, facility);
				}
				else if (type == 'unitTraining'){

					var data = jQuery.parseJSON(event['construction']);

// 					label = "<div style='float:left;font-weight:bold'>Výcvik</div><div style='float:right; width:70%'><table style='text-align:left;border:1px solid white'>";
					$.each(data, function(unit, quantity){
// 						label += '<tr><td>' + unit + '</td><td>' + quantity + '</td></tr>';
					});
// 					label += "</table></div>";
				}

				if (['colonisation', 'abandonment', 'facilityConstruction', 'facilityDemolition'].indexOf(type) >= 0) {
					if (Game.utils.isset(Game.map)) {
						if (Game.map.loaded) {
							Game.map.disableField(Game.map.getField(event.target.x, event.target.y), type);
						} else {
							Game.map.disabledFieldsStack.push({
								'x': event['target']['x'],
								'y': event['target']['y'],
								'type': type
							});
						}
					}
					label.append(' ' + formatCoords(event['target']['x'], event['target']['y']));
				}

				Game.countdown.addCountdown(label, coords, event['countdown']);
				count++
			});

			Game.countdown.closeTable();

			if (count > 0){
				$('#countdownBar').show();
				$('#countdownCount').html(count);
				if (Game.cookie.get('#countdownDialogShown') == 'true'){
					Game.countdown.showDialog();
				}
				else{
					Game.countdown.hideDialog();
				}
			}
			else{
				$('#countdownBar').hide();
				Game.countdown.hideDialog();
			}

		});


	}
};

$(document).ready(function(){
	Game.events.fetchEvents();
});
