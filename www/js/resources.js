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
	 * Stack of waiting trans. functions
	 * @var array of functions
	 */
	callbackStack : new Array(),

	/**
	 * True if fetching is in progress, false otherwise
	 * @var boolean
	 */
	isFetching : false,

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
			var span = $('#resourceBar #' + resource + ' .text');
			var incrementFunction = function () {
				var production = Game.resources.data[resource].production;
				var balance = Game.resources.data[resource].balance;
				var storage = Game.resources.data[resource].storage;
				$(span).children('.balance').html(Math.floor(balance));
/*
				$(span).children('.balance').fadeOut(250, function() {
					$(this).html(Math.floor(balance)).fadeIn(250);
				});
*/
				if (balance >= storage) {
					$(span).children('.balance').addClass('resourceFull');
					return;
				} else {
					$(span).children('.balance').removeClass('resourceFull');
				}

				if (balance < 0) {
					period = 0;
					$(span).children('.balance').html('0');
					return;
				} else if (production == 0) {
					period = 0;
					return;
				} else {
					period = 1000/Math.abs(production);
				}

				if (production < 0) {
					Game.resources.data[resource]['balance']--;
				} else {
					Game.resources.data[resource]['balance']++;
				}

			};

			incrementFunction();

			if(period != 0){
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
		this.isFetching = true;
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

		this.isFetching = false;

		while (fnc = this.callbackStack.pop()){
			fnc();
		}
	},

	/**
	 * Checks if player has enought resources
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
	},

	/**
	 * Prints the count of available units ready to be trained into the selector, depanding on their costs
	 * @param array of int
	 * @param array of int
	 * @param array of int
	 * @param array of int
	 * @param String/Object
	 * @return void
	 */
	printAvailableUnitCount : function (costs, totalCosts, slots, availableSlots, selector){


		if (this.isFetching){
			this.callbackStack.push(function(){

				var min = null;
				$.each(slots, function(key, slot){
					if(Game.utils.isset(availableSlots[key])){
						var actCount = Math.floor(availableSlots[key]/slot);
						if(Game.utils.isset(min)){
							min = (actCount < min) ? actCount : min;
						}
						else{
							min = actCount;
						}
					}
					else{
						min = 0;
						return false;
					}

				});

				$.each(costs, function(key, cost){

					if(Game.utils.isset(Game.resources.data[key].balance)){
						var actCount = Math.floor((Game.resources.data[key].balance - (Game.utils.isset(totalCosts[key]) ? totalCosts[key] : 0)) / cost);
						if(Game.utils.isset(min)){
							min = (actCount < min) ? actCount : min;
						}
						else{
							min = actCount;
						}
					}
					else{
						min = 0;
						return false;
					}

				});


				$(selector).html(min);
			});

		}
		else{
			var min = null;
			$.each(slots, function(key, slot){
				if(Game.utils.isset(availableSlots[key])){
					var actCount = Math.floor(availableSlots[key]/slot);
					if(Game.utils.isset(min)){
						min = (actCount < min) ? actCount : min;
					}
					else{
						min = actCount;
					}
				}
				else{
					min = 0;
					return false;
				}

			});

			$.each(costs, function(key, cost){

				if(Game.utils.isset(Game.resources.data[key].balance)){
					var actCount = Math.floor((Game.resources.data[key].balance - (Game.utils.isset(totalCosts[key]) ? totalCosts[key] : 0)) / cost);
					if(Game.utils.isset(min)){
						min = (actCount < min) ? actCount : min;
					}
					else{
						min = actCount;
					}
				}
				else{
					min = 0;
					return false;
				}

			});


			$(selector).html(min);

		}


	},


};


$(document).ready(function(){
	Game.resources.fetchResources();
});
