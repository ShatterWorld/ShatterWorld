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
		var keys = new Array();
		$.each(this.data, function (key, value) {
			keys.push(key);
		});
		keys.sort();
		$('#resourceBar').html('');
		$.each(keys, function() {
			var element = $('<span />').attr('id', this);
			element.html('<img src="'+basePath+'/images/resources/'+this+'.png"/> <span class="text"><span class="balance"></span>/<span class="storage"></span> (<span class="production"></span>)</span> ');
			$('#resourceBar').append(element);
		})
		this.update();
		this.initialized = true;
	},

	/**
	 * Updates data
	 * @return void
	 */
	update: function ()
	{

		if(Game.utils.isset(this.intervals)){
			$.each(this.intervals, function (key, interval) {
				clearInterval(interval);
			});
}
		this.intervals = new Array();

		$.each(this.data, function (resource, value) {

			var period;
			var span = $('#resourceBar #' + resource + ' .text');

			var incrementFunction = function(){
				var production = Game.resources.data[resource].production;
				var balance = Game.resources.data[resource].balance;
				var storage = Game.resources.data[resource].storage;
				$(span).children('.balance').html(Math.floor(balance));
				$(span).children('.storage').html(storage);

				$(span).children('.production').html((production >= 0 ? '+' : '') + Math.floor(production * 3600));
				if (balance >= storage) {
					$(span).children('.balance').addClass('resourceFull');
					return;
				} else {
					$(span).children('.balance').removeClass('resourceFull');
				}
				if (production == 0) {
					return;
				}
				if (production < 0) {
					Game.resources.data[resource]['balance']--;
				} else {
					Game.resources.data[resource]['balance']++;
				}
				period = 1000/Math.abs(production);
			};

			incrementFunction();
			Game.resources.intervals.push(setInterval(incrementFunction, period));
		})
	},

	/**
	 * Fetches clans resources
	 * @return void
	 */
	fetchResources: function ()
	{
		this.isFetching=true;
		$.getJSON('?do=fetchResources', function(data) {
			if (data === null || data['resources'] === null){
				return;
			}

			Game.resources.data = data['resources'];
			if (!Game.resources.initialized) {
				Game.resources.setup();
			} else {
				Game.resources.update();
			}

			Game.descriptions.isFetching = false;

			while (fnc = Game.resources.callbackStack.pop()){
				fnc();
			}

		});
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


		if (this.isFetching || !Game.utils.isset(null)){
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
