/**
 * Countdown
 * @author Petr Bělohlávek
 */

/**
 * Global functions
 */
var Game = Game || {};
Game.resources = {
	/**
	 * Data of resources
	 * @var object
	 */
	data: null,

	/**
	 * Countdown intervals
	 * @var array of interval
	 */
	intervals: null,

	/**
	 * True if init., false otherwise
	 * @var boolean
	 */
	initialized: false,

	/**
	 * Sets up data attributes etc.
	 * @return void
	 */
	setup: function ()
	{
		this.update();
		this.initialized = true;
	},

	/**
	 * Updates data
	 * @return void
	 */
	update: function ()
	{
		if (Game.utils.isset(this.intervals)) {
			$.each(this.intervals, function (key, interval) {
				clearInterval(interval);
			});
		}
		this.intervals = new Array();

		var period;

		$.each(this.data, function (resource, value) {
			var element = $('#resourceBar .' + resource);
			var incrementFunction = function () {
				var production = Game.resources.data[resource].production;
				var balance = Game.resources.data[resource].balance;
				var storage = Game.resources.data[resource].storage;
				$(element).find('.balance').html(Math.floor(balance));
				if (balance >= storage) {
					$(element).find('.balance').addClass('resourceFull');
					return;
				} else {
					$(element).find('.balance').removeClass('resourceFull');
				}

				if (balance < 0) {
					period = 0;
					$(element).find('.balance').html('0');
					return;
				} else if (production == 0) {
					period = 0;
					return;
				} else {
					period = 1000 / Math.abs(production);
				}

				if (production < 0) {
					Game.resources.data[resource]['balance']--;
				} else {
					Game.resources.data[resource]['balance']++;
				}

			};

			incrementFunction();

			if (period != 0) {
				Game.resources.intervals.push(setInterval(incrementFunction, period));
			}
		})
	},

	/**
	 * Fetches clans resources
	 * @return void
	 */
	fetchResources: function ()
	{
		data = $('#resourceBar').data('resources');
		if (data === null) {
			return;
		}

		this.data = data;
		if (!this.initialized) {
			this.setup();
		} else {
			this.update();
		}
	},

	getBalance: function (resource)
	{
		return this.data[resource].balance
	},
	
	/**
	 * Checks if player has enough resources
	 * @param array
	 * @return boolean
	 */
	hasSufficientResources : function (price) {
		var valid = true;
		$.each(price, function (resource, cost) {
			if (cost > Game.resources.data[resource].balance) {
				valid = false;
				return false;
			}
		})
		return valid;
	}
};


$(document).ready(function(){
	Game.resources.fetchResources();
	$('#resourceBar .text').live('mouseenter', function (e) {
		var element = $(this).parent().find('.tooltip.' + $(this).attr('id'));
		$(element).css({
			display: 'block',
			left: e.pageX + 10,
			top: e.pageY + 20
		});
	});
	$('#resourceBar .text').live('mousemove', function (e) {
		var element = $(this).parent().find('.tooltip.' + $(this).attr('id'));
		$(element).css({
			left: e.pageX + 10,
			top: e.pageY + 20
		});
	});
	$('#resourceBar .text').live('mouseleave', function (e) {
		$(this).parent().find('.tooltip.' + $(this).attr('id')).css({
			display: 'none'
		});
	});
});
