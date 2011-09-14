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

				jQuery.countdown.cleanDialog();
				var count = 0;
				$.each(data['events'], function(key, event) {

					var type = event['type'];
					var label = 'Neznámá akce';

					if (type == 'colonisation'){
						label = 'Kolonizace [' + event['info']['target']['coordX'] + ';'+event['info']['target']['coordX'] + ']';
					}
					else if (type == 'facilityConstruction'){
						label = event['info']['constructionType']+' (lvl '+event['info']['level']+') [' + event['info']['field']['coordX'] + ';'+event['info']['field']['coordX'] + ']';
					}

					jQuery.countdown.addCountdown(label, event['countdown']);
					count++
				});
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
