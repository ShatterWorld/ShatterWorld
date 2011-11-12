/**
 * Countdown
 * @author Petr Bělohlávek
 */

/**
 * Global functions
 */
var Game = Game || {};
Game.countdown = {

	/**
	 * Adds new countdown, maneges own countdowning etc.
	 * @param string
	 * @param integer
	 * @param integer
	 * @param integer [s]
	 * @return void
	 */
	addCountdown: function(label, coords, timeout)
	{
		var countdownTr = $('<tr class="countdown" />');
		if (coords){
			var x = coords.x;
			var y = coords.y;
			if (Game.utils.isset(Game.map) && Game.map.loaded){
				countdownTr.mouseenter(function(){
					Game.map.marker.mark(Game.map.getField(x, y), 'focus');
				});

				countdownTr.mouseleave(function(){
					Game.map.marker.unmarkByType('focus');
					Game.map.disableField(Game.map.getField(x, y));
				});
			}
		}

		var titleTd = $('<td class="countdownTitle" />').html(label).css({
			'font-weight' : 'bold',
			'width' : '90%',
			'vertical-align' : 'top'
		});
		var timeTd = $('<td class="countdownTime" />').css({
			'text-align' : 'right',
		});

		countdownTr.append(titleTd);
		countdownTr.append(timeTd);

		$('#countdownDialog').append(countdownTr);

		var countdownFunction = function(){
			if (timeout < 0){
				if (Game.utils.isset(Game.map)){
					Game.map.render();
				}
				Game.events.fetchEvents();
				//Game.resources.fetchResources();
				clearInterval(interval);
				return;
			}
			var t = timeout;

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

			$(timeTd).html(time);
			timeout--;
		};
		countdownFunction();
		var interval = setInterval(countdownFunction, 1000);

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
		tmp = Game.cookie.get('#countdownDialogWidth');
		var w = (tmp !== null) ? tmp : 300;

		tmp = Game.cookie.get('#countdownDialogHeight');
		var h = (tmp !== null) ? tmp : 120;

		var pos = new Array();
		tmp = Game.cookie.get('#countdownDialogPosX');
		pos[0] = (tmp !== null) ? parseInt(tmp) : 'center';

		tmp = Game.cookie.get('#countdownDialogPosY');
		pos[1] = (tmp !== null) ? parseInt(tmp) : 'center';

		$('#countdownDialog').dialog({
			title: 'Odpočítávání',
			width: w,
			height: h,
			position: pos,
			dragStop: function(event, ui){
				Game.cookie.set('#countdownDialogPosX', $("#countdownDialog").dialog("option", "position")[0], 7);
				Game.cookie.set('#countdownDialogPosY', $("#countdownDialog").dialog("option", "position")[1], 7);
			},
			resizeStop: function(event, ui) {
				Game.cookie.set('#countdownDialogWidth', $("#countdownDialog").dialog("option", "width"), 7);
				Game.cookie.set('#countdownDialogHeight', $("#countdownDialog").dialog("option", "height"), 7);
			},
			beforeClose: function(event, ui) {
				Game.countdown.setDialogShown(false);
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
		Game.cookie.set('#countdownDialogShown', val, 7);
	},

	/**
	 * @var boolean, true if dialog is shown, otherwise false
	 */
	dialogShown : false

};

/**
 * Manages countdownDialog click
 */

$(document).ready(function(){
	$('#countdownBar').click(function(){

		if (!Game.countdown.dialogShown){
			Game.countdown.showDialog();
		}
		else{
			Game.countdown.hideDialog();
		}

	});
});

