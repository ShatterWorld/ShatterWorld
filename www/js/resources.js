/**
 * Countdown
 * @author Petr Bělohlávek
 */

/**
 * Global functions
 */
jQuery.extend({
	resources: {

		/**
		  * asoc array representing the balance of each resource
		  * @var int
		  */
		resources : new Array(),

		/**
		  * asoc array representing the productin of each resource
		  * @var int
		  */
		production : new Array(),

		init: function(){
			this.resources['food'] = 0;
			this.resources['stone'] = 0;
			this.resources['metal'] = 0;
			this.resources['fuel'] = 0;

			this.production['food'] = 0;
			this.production['stone'] = 0;
			this.production['metal'] = 0;
			this.production['fuel'] = 0;
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
				jQuery.resources.resources['food'] = data['resources']['food']['balance'];
				jQuery.resources.production['food'] = data['resources']['food']['production'];

				jQuery.resources.resources['stone'] = data['resources']['stone']['balance'];
				jQuery.resources.production['stone'] = data['resources']['stone']['production'];

				jQuery.resources.resources['metal'] = data['resources']['metal']['balance'];
				jQuery.resources.production['metal'] = data['resources']['metal']['production'];

				jQuery.resources.resources['fuel'] = data['resources']['fuel']['balance'];
				jQuery.resources.production['fuel'] = data['resources']['fuel']['production'];

				$('#infoBar #resourceBar #foodProduction').html(((jQuery.resources.production['food']>=0)?'+':'-')+jQuery.resources.production['food']*3600);
				$('#infoBar #resourceBar #stoneProduction').html(((jQuery.resources.production['stone']>=0)?'+':'-')+jQuery.resources.production['stone']*3600);
				$('#infoBar #resourceBar #metalProduction').html(((jQuery.resources.production['metal']>=0)?'+':'-')+jQuery.resources.production['metal']*3600);
				$('#infoBar #resourceBar #fuelProduction').html(((jQuery.resources.production['fuel']>=0)?'+':'-')+jQuery.resources.production['fuel']*3600);


				jQuery.resources.incrementResource('food', '#infoBar #resourceBar #food');
				jQuery.resources.incrementResource('stone', '#infoBar #resourceBar #stone');
				jQuery.resources.incrementResource('metal', '#infoBar #resourceBar #metal');
				jQuery.resources.incrementResource('fuel', '#infoBar #resourceBar #fuel');
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
			$(span).html(Math.floor(this.resources[resource]));

			period = 0;
			if (this.production[resource] == 0){
				return;
			}
			else if (this.production[resource] < 0){
				this.resources[resource]--;
				period = -1000/this.production[resource];
			}
			else{
				this.resources[resource]++;
				period = 1000/this.production[resource];
			}

			setTimeout(function(){
				jQuery.resources.incrementResource(resource, span);
			}, period);
		},

		/**
		  * Checks if player has enought resources
		  * @param integer
		  * @param integer
		  * @param integer
		  * @param integer
		  * @return boolean
		  */
		hasSufficientResources : function(stone, metal, food, fuel){
			if(stone !== null){
				if(stone > this.resources['stone']){
					return false;
				}
			}
			if(metal !== null){
				if(metal > this.resources['metal']){
					return false;
				}
			}
			if(food !== null){
				if(food > this.resources['food']){
					return false;
				}
			}
			if(fuel !== null){
				if(fuel > this.resources['fuel']){
					return false;
				}
			}


			return true;

		}




	}
});


$(document).ready(function(){
	jQuery.resources.init();
	jQuery.resources.fetchResources();
});
