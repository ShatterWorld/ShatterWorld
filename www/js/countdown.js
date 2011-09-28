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
		 * @param integer
		 * @param integer
		 * @param integer [s]
		 * @return void
		 */
		addCountdown: function(title, x, y, remainingTime)
		{
			var countdownTr = $('<tr class="countdown" />');

			countdownTr.mouseenter(function(){
				jQuery.marker.mark('#field_' + x + '_' + y, 'blue');
			});

			countdownTr.mouseleave(function(){
				jQuery.marker.unmarkAll('blue');
			});

			var titleTd = $('<td class="countdownTitle" />').html(title + ' [' + x + ';' + y + ']')
				.css({
					'font-weight' : 'bold',
					'width' : '90%'
				});

			var timeTd = $('<td class="countdownTime" />')
				.css({
					'text-align' : 'right',

				});

			countdownTr.append(titleTd);
			countdownTr.append(timeTd);

			$('#countdownDialog').append(countdownTr);

			this.countdown(timeTd, remainingTime);
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
			$('#countdownDialog').html('<table id="countdownTable">');
		},

		/**
		 * Adds </table> tag
		 * @return void
		 */
		closeTable: function(){
			$('#countdownDialog').append('</table>');
		},

		/**
		 * @var boolean, true if dialog is shown, otherwise false
		 */
		dialogShown : false

	}
});

/**
 * Manages countdownDialog
 */

$(document).ready(function(){
	$('#countdownBar').click(function(){
		if (!jQuery.countdown.dialogShown){
			$('#countdownDialog').dialog({
				title: "Odpočítávání"
			});
			jQuery.countdown.dialogShown = true;
		}
		else{
			$('#countdownDialog').dialog("close");
			jQuery.countdown.dialogShown = false;
		}

	});
});

