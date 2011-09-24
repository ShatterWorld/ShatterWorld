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
					}
					else if (type == 'facilityConstruction'){
						label = event['construction'] + ' (' + event['level'] + ')';
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
				}
				else{
					$('#countdownBar').hide();
					$('#countdownDialog').dialog("close");
				}

			});


		}

	}

});

$(document).ready(function(){
	jQuery.events.fetchEvents();
});
