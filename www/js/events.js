/**
 * Events
 * @author Petr Bělohlávek
 */

/**
 * Global functions
 */
var Game = Game || {};
Game.events = {
	getEvents: function ()
	{
		return $('#countdownBar').data('events');
	},

	refresh: function ()
	{
		Game.utils.signal('fetchEvents', {}, function (data) {
			Game.events.data = Game.events.getEvents();
			Game.events.setup();
			Game.resources.fetchResources();
		});
	},

	setup: function ()
	{
		$.each(this.getEvents(), function () {
			var row = $('#countdown_' + this.id);
			Game.events.startCountdown(row.children('.countdown'), this.remainingTime);
			if (Game.utils.isset(Game.map)) {
				if (this.target) {
					var x = this.target.x;
					var y = this.target.y;
					var getTarget = function () { return Game.map.getField(x, y); };
					Game.map.disableField(getTarget, this.type);
					row.mouseenter(function () {
						Game.map.marker.mark(getTarget(), 'focus');
					});
					row.mouseleave(function () {
						Game.map.marker.unmarkByType('focus');
						Game.map.disableField(getTarget);
					});
				}
			}
		});
	},

	startCountdown: function (element, timeout)
	{
		var countdownFunction = function () {
			if (timeout < 0){
				if (Game.utils.isset(Game.map) && Game.map.loaded){
					Game.map.render();
				}
				Game.events.refresh();
<<<<<<< HEAD
				Game.resources.fetchResources();
=======
>>>>>>> eb21b8a6d1e15c034d6e337ba8bd36d348792d9e
				clearInterval(interval);
				return;
			}
			$(element).html(Game.utils.formatTime(timeout));
			timeout--;
		};
		var interval = setInterval(countdownFunction, 1000);
	},

	toggle: function ()
	{
		var element = $('#countdownTooltip');
		if (this.shown) {
			element.hide();
			this.shown = false;
		} else {
			element.show();
			this.shown = true;
		}
		element.data('fixed', this.shown);
	}
};

/**
 * Manages countdownDialog click
 */

$(document).ready(function(){
	Game.events.setup();
	$('#countdownBar').live('click', function (e) {
		e.preventDefault();
		Game.events.toggle();
	});
});
