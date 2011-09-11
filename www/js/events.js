/**
 * Events
 * @author Petr Bělohlávek
 */
$(document).ready(function(){
	fetchEvents();
	/**
	  * Fetches all events data (name, time etc.)
	  */
	function fetchEvents()
	{
		$.getJSON('?do=fetchEvents', function(data) {

			/*$.each(data['events'], function(key, event) {
				jQuery.countdown.addCountdown(event['name'], event['time']);//-now()
			}*/

		});


	}



});
