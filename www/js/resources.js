/**
 * Countdown
 * @author Petr Bělohlávek
 */

/**
 * Global functions
 */
jQuery.extend({
	resources: {
		data: null,
		
		initialized: false,
		
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
				jQuery.descriptions.translate('resource', this, '#resourceBar #' + this + ' .label');
				first = false;
			})
			this.update();
			this.initialized = true;
		},
		
		update: function ()
		{
			$.each(this.data, function (resource, value) {
				jQuery.resources.incrementResource(resource, $('#resourceBar #' + resource));
			})
		},
		
		/**
		  * Fetches clans resources
		  * @return void
		  */
		fetchResources: function ()
		{
			$.getJSON('?do=fetchResources', function(data) {
				if (data === null || data['resources'] === null){
					return;
				}

				jQuery.resources.data = data['resources'];
				if (!jQuery.resources.initialized) {
					jQuery.resources.setup();
				} else {
					jQuery.resources.update();
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
				jQuery.resources.incrementResource(resource, span);
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
				if (cost > jQuery.resources.data[resource].balance) {
					valid = false;
					return false;
				}
			})
			return valid;
		},

		/**
		  * Returns maximal amount of the unit specified by costs
		  * @param int
		  * @param int
		  * @param int
		  * @param int
		  * @return int
		  */
		getAvailibleUnits : function(metal, stone, food, fuel){
			var tmp;
			var min = 999999999999;

			tmp = Math.floor(this.data['metal'].balance / metal);
			if (tmp > min){
				min = tmp;
			}

			tmp = Math.floor(this.data['stone'].balance / stone);
			if (tmp > min){
				min = tmp;
			}

			tmp = Math.floor(this.data['food'].balance / food);
			if (tmp > min){
				min = tmp;
			}

			tmp = Math.floor(this.data['fuel'].balance / fuel);
			if (tmp > min){
				min = tmp;
			}

			return min;

		}
	}
});


$(document).ready(function(){
	jQuery.resources.fetchResources();
});
