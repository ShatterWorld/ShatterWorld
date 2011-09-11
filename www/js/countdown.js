/**
 * Countdown
 * @author Petr Bělohlávek
 */

/**
 * Global functions
 */
jQuery.extend({
	countdown: {
		/**
		 * Adds countdown
		 * @param string
		 * @param integer [s]
		 * @return void
		 */
		addCountdown: function(title, remainingTime)
		{
			var countdownDiv = $('<div class="countdown" />');
			var countdownTitleDiv = $('<span class="countdownTitle" />').html(title+': ');
			var countdownTimeDiv = $('<span class="countdownTime" />');

			countdownDiv.append(countdownTitleDiv);
			countdownDiv.append(countdownTimeDiv);

			$('#countdownDialog').append(countdownDiv);

			this.countdown(countdownTimeDiv, remainingTime);
		},

		/**
		 * Countdowns and display remaining time
		 * @param object
		 * @param integer [s]
		 * @return void
		 */
		countdown: function(countdownTimeDiv, remainingTime)
		{
			if (remainingTime < 0){
				if (typeof(jQuery.gameMap) != 'undefined'){
					jQuery.gameMap.render();
				}
				jQuery.events.fetchEvents();
				return;
			}
			var t = remainingTime;

			var h = Math.floor(t / 3600);
			t -= h*3600;
			var m = Math.floor(t / 60);
			t -= m*60;
			var s = t;

			if (s < 10){
				s = '0' + s;
			}
			if (m < 10){
				m = '0' + m;
			}

			var time = h + ':' + m + ':' + s;

			$(countdownTimeDiv).html(time);
			setTimeout(function(){
				jQuery.countdown.countdown(countdownTimeDiv, remainingTime - 1);
			}, 1000);


		},

		/**
		 * Cleans the #countdownDialog
		 * @return void
		 */
		cleanDialog: function(){
			$('#countdownDialog').html('');
		}


	}
});

/**
 * Manages countdownDialog
 */

$(document).ready(function(){
	$('#countdownBar').click(function(){
		//$('#countdownDialog').toggle('fast');

		$('#countdownDialog').dialog({
			autoOpen: true,
			//width: 600,
			buttons: {
				"Zavřít": function() {
					$('#countdownDialog').dialog("close");
				}
			},
			title: "Odpočítávání"
		});


	});
});

