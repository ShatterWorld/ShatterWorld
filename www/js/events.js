/**
 * Events
 * @author Petr Bělohlávek
 */

/**
 * Global functions
 */
jQuery.extend({
	events: {

		/**
		  * Fetches and displays countdowns of events
		  */
		fetchEvents: function (){
			$.getJSON('?do=fetchEvents', function(data) {

				if (data === null || data['events'] === null){
					return;
				}

				jQuery.countdown.cleanDialog();
				var isMap = typeof(jQuery.gameMap) != 'undefined';
				if (isMap){
					jQuery.gameMap.nullDisabledFields();
				}

				jQuery.countdown.nullCountdowns();

				var count = 0;
				$.each(data['events'], function(key, event) {

					var type = event['type'];
					var label = 'Neznámá akce';
					var x = -1;
					var y = -1;

					if (type == 'colonisation'){
						label = 'Kolonizace';

						x = event['target']['x'];
						y = event['target']['y'];

						if (isMap){
							jQuery.gameMap.addDisabledField(event['target'], 'colonisation');
						}

					}
					else if (type == 'facilityConstruction'){
						if(event['level'] > 1){
							label = 'Upgrade '+event['construction'] + ' (' + event['level'] + ')';
						}
						else{
							label = 'Stavba '+event['construction'];
						}

						x = event['target']['x'];
						y = event['target']['y'];

						if (isMap){
							jQuery.gameMap.addDisabledField(event['target'], 'facilityConstruction');
						}

					}
					else if (type == 'facilityDemolition'){
						if(event['construction'] == 'downgrade'){
							label = 'Downgrade '+event['target']['facility'] + ' (' + event['level'] + ')';
						}
						else{
							label = 'Demolice budovy';
						}

						x = event['target']['x'];
						y = event['target']['y'];

						if (isMap){
							jQuery.gameMap.addDisabledField(event['target'], 'facilityConstruction');
						}

					}
					else if (type == 'unitTraining'){
						label = 'Výcvik ' + event['construction'] + ' (' + '' + ')';
						x = event['target']['x'];
						y = event['target']['y'];
					}


					jQuery.countdown.addCountdown(label, x, y, event['countdown']);
					count++
				});

				jQuery.countdown.closeTable();

				if (count > 0){
					$('#countdownBar').show();
					$('#countdownCount').html(count);
					if (jQuery.cookie.get('#countdownDialogShown') == 'true'){
						jQuery.countdown.showDialog();
					}
					else{
						jQuery.countdown.hideDialog();
					}
				}
				else{
					$('#countdownBar').hide();
					jQuery.countdown.hideDialog();
				}

			});


		}

	}

});

$(document).ready(function(){
	jQuery.events.fetchEvents();
});
