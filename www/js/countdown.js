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
	 * Countdown function pointer
	 * @var array of function
	 */
	countdowns : new Array(),

	/**
	 * Nulls all the countdown-function pointers
	 * @return void
	 */
	nullCountdowns : function(){
		$.each(Game.countdown.countdowns, function(key, ctd){
			Game.countdown.countdowns[key] = null;
		});
	},

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

		var coord = '';
		if (x >= 0 && y >= 0){
			if (typeof(Game.map) != 'undefined'){
				countdownTr.mouseenter(function(){
					Game.marker.mark('#field_' + x + '_' + y, 'blue');
				});

				countdownTr.mouseleave(function(){
					Game.marker.unmarkAll('blue');
					Game.map.markDisabledField('#field_' + x + '_' + y);

				});

			}
			coord = ' [' + x + ';' + y + ']';
		}

		var titleTd = $('<td class="countdownTitle" />').html(title + coord)
			.css({
				'font-weight' : 'bold',
				'width' : '90%',
				'vertical-align' : 'top'
			});

		var timeTd = $('<td class="countdownTime" />')
			.css({
				'text-align' : 'right',

			});

		countdownTr.append(titleTd);
		countdownTr.append(timeTd);

		$('#countdownDialog').append(countdownTr);

		var id = this.countdowns.push(this.countdown);
		id--;
		this.countdowns[id](timeTd, remainingTime, id);
	},

	/**
	 * Countdowns and display remaining time
	 * @param object
	 * @param integer [s]
	 * @return void
	 */
	countdown: function(countdownTimeDiv, remainingTime, id)
	{
		if (remainingTime < 0){
			if (typeof(Game.map) != 'undefined'){
				Game.map.render();
			}
			Game.events.fetchEvents();

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
			if(Game.countdown.countdowns[id] !== null){
				Game.countdown.countdown(countdownTimeDiv, remainingTime - 1, id);
			}
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

