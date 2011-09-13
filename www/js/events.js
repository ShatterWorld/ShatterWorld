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
					jQuery.countdown.addCountdown('ahoj', event['countdown']);
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
