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
			Game.countdown.render(Game.events.data);
		});
	},
};

Game.countdown = {
	render: function (data)
	{
		if (!data) {
			return;
		}
		this.dialog = new Game.UI.Dialog('countdownDialog');
		this.dialog.setTitle('Odpočítávání');
		this.dialog.showButtons = false;
		this.dialog.table = $('<table>');
		var empty = true;
		$.each(data, function(key, event) {
			var type = event.type;
			var label = $('<span>');
			var coords = null;
			var subject = null;

			var formatCoords = function (x, y) {
				return '[' + x + ';' + y + ']';
			}

			var text = $('<span>');
			label.append(text);
			Game.descriptions.translate('event', type, text);

			if (type == 'facilityConstruction') {
				var facility = $('<span>');
				label.append(' ');
				label.append(facility);
				if (event.level > 1) {
					label.append(' (' + event.level + ')');
				}
				Game.descriptions.translate('facility', event.construction, facility);
			} else if (type == 'facilityDemolition') {
				var facility = $('<span>');
				label.append(' ');
				label.append(facility);
				if (event.level > 1) {
					label.append(' (' + event.level + ')');
				}
				Game.descriptions.translate('facility', event.construction, facility);
			} else if (type == 'unitTraining') {

				var data = jQuery.parseJSON(event['construction']);

// 					label = "<div style='float:left;font-weight:bold'>Výcvik</div><div style='float:right; width:70%'><table style='text-align:left;border:1px solid white'>";
				$.each(data, function(unit, quantity){
// 						label += '<tr><td>' + unit + '</td><td>' + quantity + '</td></tr>';
				});
// 					label += "</table></div>";
			}

			if (['colonisation', 'abandonment', 'facilityConstruction', 'facilityDemolition', 'exploration'].indexOf(type) >= 0) {
				if (Game.utils.isset(Game.map)) {
					if (Game.map.loaded) {
						Game.map.disableField(Game.map.getField(event.target.x, event.target.y), type);
					} else {
						Game.map.disabledFieldsStack.push({
							'x': event['target']['x'],
							'y': event['target']['y'],
							'type': type
						});
					}
				}
				label.append(' ' + formatCoords(event.target.x, event.target.y));
			}
			empty = false;
			Game.countdown.addCountdown(label, coords, event['countdown']);
		});
		this.dialog.setBody(this.dialog.table);
		var shown = this.dialog.getConfig().show;
		if (!empty) {
			if (shown) {
				this.dialog.show();
				this.initialized = true;
				this.shown = true;
			} else {
				this.shown = false;
			}
		} else {
			this.shown = false;
		}

		this.dialog.closeHandler = function (context) {
			Game.countdown.shown = false;
			context.setConfig('show', false);
		}
	},
	
	/**
	 * Adds new countdown, maneges own countdowning etc.
	 * @param string
	 * @param integer
	 * @param integer
	 * @param integer [s]
	 * @return void
	 */
	addCountdown: function (label, coords, timeout)
	{
		var row = $('<tr class="countdown" />');
		if (coords) {
			var x = coords.x;
			var y = coords.y;
			if (Game.utils.isset(Game.map) && Game.map.loaded) {
				row.mouseenter(function () {
					Game.map.marker.mark(Game.map.getField(x, y), 'focus');
				});
				row.mouseleave(function () {
					Game.map.marker.unmarkByType('focus');
					Game.map.disableField(Game.map.getField(x, y));
				});
			}
		}

		var timeTd = $('<td class="countdownTime">');
		row.append($('<td class="countdownTitle">').html(label));
		row.append(timeTd);
		this.dialog.table.append(row);

		var countdownFunction = function () {
			if (timeout < 0){
				if (Game.utils.isset(Game.map) && Game.map.loaded){
					Game.map.render();
				}
				Game.events.refresh();
				//Game.resources.fetchResources();
				clearInterval(interval);
				return;
			}
			$(timeTd).html(Game.utils.formatTime(timeout));
			timeout--;
		};
		countdownFunction();
		var interval = setInterval(countdownFunction, 1000);

	},

	toggle: function ()
	{
		if (this.shown) {
			$(this.dialog.element).dialog('close');
			this.shown = false;
		} else {
			this.dialog.show();
			this.shown = true;
		}
	}
};

/**
 * Manages countdownDialog click
 */

$(document).ready(function(){
	Game.countdown.render(Game.events.getEvents());
	$('a#showCountdowns').live('click', function (e) {
		e.preventDefault();
		Game.countdown.toggle();
	});
});