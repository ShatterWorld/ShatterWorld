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
			/*addCountdown(title, remainingTime)
			each -> jQuery.countdown.addCountdown(...);

			*/
		});


	}



});
