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

			if (typeof(jQuery.gameMap) != 'undefined'){
				countdownTr.mouseenter(function(){
					jQuery.marker.mark('#field_' + x + '_' + y, 'blue');
				});

				countdownTr.mouseleave(function(){
					jQuery.marker.unmarkAll('blue');
				});
			}

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
		 * Shows the dialog
		 * @return void
		 */
		showDialog: function(){
			var tmp;
			tmp = jQuery.cookie.get('#countdownDialogWidth');
			var w = (tmp !== null) ? tmp : 300;

			tmp = jQuery.cookie.get('#countdownDialogHeight');
			var h = (tmp !== null) ? tmp : 120;

			var pos = new Array();
			tmp = jQuery.cookie.get('#countdownDialogPosX');
			pos[0] = (tmp !== null) ? parseInt(tmp) : 'center';

			tmp = jQuery.cookie.get('#countdownDialogPosY');
			pos[1] = (tmp !== null) ? parseInt(tmp) : 'center';

			$('#countdownDialog').dialog({
				title: 'Odpočítávání',
				width: w,
				height: h,
				position: pos, // doesnt work
				//position: [pos[0], pos[1]], // doesnt work
				//position: [7,50], //works
				dragStop: function(event, ui){
					//alert($("#countdownDialog").dialog("option", "position")[0]);
					jQuery.cookie.set('#countdownDialogPosX', $("#countdownDialog").dialog("option", "position")[0], 7);
					jQuery.cookie.set('#countdownDialogPosY', $("#countdownDialog").dialog("option", "position")[1], 7);
				},
				resizeStop: function(event, ui) {
					jQuery.cookie.set('#countdownDialogWidth', $("#countdownDialog").dialog("option", "width"), 7);
					jQuery.cookie.set('#countdownDialogHeight', $("#countdownDialog").dialog("option", "height"), 7);

				},
				beforeClose: function(event, ui) {
					jQuery.countdown.setDialogShown(false);
				}
			});

			this.setDialogShown(true);
		},

		/**
		 * Hides the dialog
		 * @return void
		 */
		hideDialog: function(){
			$('#countdownDialog').dialog("close");
		},

		/**
		 * Sets the cookie and this.dialogShown
		 * @param boolean
		 * @return void
		 */
		setDialogShown: function(val){
			this.dialogShown = val;
			jQuery.cookie.set('#countdownDialogShown', val, 7);
		},

		/**
		 * @var boolean, true if dialog is shown, otherwise false
		 */
		dialogShown : false

	}
});

/**
 * Manages countdownDialog click
 */

$(document).ready(function(){
	$('#countdownBar').click(function(){

		if (!jQuery.countdown.dialogShown){
			jQuery.countdown.showDialog();
		}
		else{
			jQuery.countdown.hideDialog();
		}

	});
});

