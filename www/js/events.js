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
				$.each(data['events'], function(key, event) {
					jQuery.countdown.addCountdown('ahoj', event['countdown']);
				});

			});


		}

	}

});

$(document).ready(function(){
	jQuery.events.fetchEvents();
});
