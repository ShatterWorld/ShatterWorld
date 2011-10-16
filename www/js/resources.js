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
		var first = true;
		$('#resourceBar').html('');
		$.each(keys, function() {
			var element = $('<span />').attr('id', this);
			element.html(first ? '' : '| ');
			element.append('<span class="label"></span>: <span class="balance"></span>/<span class="storage"></span> (<span class="production"></span>) ');
			$('#resourceBar').append(element);
			Game.descriptions.translate('resource', this, '#resourceBar #' + this + ' .label');
			first = false;
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
		$.each(this.data, function (resource, value) {
			Game.resources.incrementResource(resource, $('#resourceBar #' + resource));
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
	 * Increments resource
	 * @param String
	 * @param String/Object
	 * @return void
	 */
	incrementResource: function(resource, span)
	{
		var production = this.data[resource].production;
		var balance = this.data[resource].balance;
		var storage = this.data[resource].storage;
		$(span).children('.balance').html(Math.floor(balance));
		$(span).children('.storage').html(storage);

		$(span).children('.production').html((production >= 0 ? '+' : '-') + Math.floor(production * 3600));
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
			this.data[resource]['balance']--;
		} else {
			this.data[resource]['balance']++;
		}
		period = 1000/Math.abs(production);
		setTimeout(function(){
			Game.resources.incrementResource(resource, span);
		}, period);
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
	 * @deprecated
	 * @param int
	 * @param int
	 * @param int
	 * @param int
	 * @param String/Object
	 * @return void
	 */
	printAvailableUnitCount : function (metal, stone, food, fuel, selector){
		var a=2;
		if (this.isFetching || this.data == null){
			this.callbackStack.push(function(){

				var metalMin = (metal > 0) ? Math.floor(Game.resources.data['metal'].balance / metal) : -1;
				var stoneMin = (stone > 0) ? Math.floor(Game.resources.data['stone'].balance / stone) : -1;
				var foodMin = (food > 0) ? Math.floor(Game.resources.data['food'].balance / food) : -1;
				var fuelMin = (fuel > 0) ? Math.floor(Game.resources.data['fuel'].balance / fuel) : -1;

				var max = Math.max(metalMin, Math.max(stoneMin, Math.max(foodMin, fuelMin)));
				var min;

				if (max >= 0){
					min = Math.min((metalMin <= 0) ? max : metalMin, Math.min((stoneMin <= 0) ? max : stoneMin, Math.min((foodMin <= 0) ? max : foodMin, (fuelMin <= 0) ? max : fuelMin)));
				}
				else{
					min = 0;
				}

				$(selector).html(min);
			});

		}
		else{
			var metalMin = (metal > 0) ? Math.floor(Game.resources.data['metal'].balance / metal) : -1;
			var stoneMin = (stone > 0) ? Math.floor(Game.resources.data['stone'].balance / stone) : -1;
			var foodMin = (food > 0) ? Math.floor(Game.resources.data['food'].balance / food) : -1;
			var fuelMin = (fuel > 0) ? Math.floor(Game.resources.data['fuel'].balance / fuel) : -1;

			var max = Math.max(metalMin, Math.max(stoneMin, Math.max(foodMin, fuelMin)));
			var min;

			if (max >= 0){
				min = Math.min((metalMin <= 0) ? max : metalMin, Math.min((stoneMin <= 0) ? max : stoneMin, Math.min((foodMin <= 0) ? max : foodMin, (fuelMin <= 0) ? max : fuelMin)));
			}
			else{
				min = 0;
			}

			$(selector).html(min);

		}


	},


};


$(document).ready(function(){
	Game.resources.fetchResources();
});
