$(document).ready(function(){

	/**
	  * @var int
	  * asoc array representing the balance of each resource
	  */
	var resources = new Array();
	resources['food'] = 0;
	resources['stone'] = 0;
	resources['metal'] = 0;
	resources['fuel'] = 0;

	/**
	  * @var int
	  * asoc array representing the productin of each resource
	  */
	var production = new Array();
	production['food'] = 0;
	production['stone'] = 0;
	production['metal'] = 0;
	production['fuel'] = 0;

	//fetchEvents();
	//fetchFacilities();
	fetchResources();

	//countdownEvents();


/*countdown events etc move to renderer*/


	/**
	  * Fetches all events data (price, time etc)
	  */
	/*function fetchEvents()
	{
		$.getJSON('?do=fetchEvents', function(data) {
			//addCountdown(title, remainingTime)

		});


	}*/

	/**
	  * Fetches all facilities data (price, time etc)
	  */
	/*function fetchFacilities()
	{
		$.getJSON('?do=fetchEvents', function(data) {
			//facilities = ...

		});
	}*/

	/**
	  * Fetches clans resources
	  */
	function fetchResources()
	{
		$.getJSON('?do=fetchResources', function(data) {
			resources['food'] = data['resources']['food']['balance'];
			production['food'] = data['resources']['food']['production'];

			resources['stone'] = data['resources']['stone']['balance'];
			production['stone'] = data['resources']['stone']['production'];

			resources['metal'] = data['resources']['metal']['balance'];
			production['metal'] = data['resources']['metal']['production'];

			resources['fuel'] = data['resources']['fuel']['balance'];
			production['fuel'] = data['resources']['fuel']['production'];

			incrementResources();
		});
	}

	/**
	  * Starts events countdown
	  */
/*	function countdownEvents()
	{

	}*/

	/**
	  * Starts resources incrementation
	  */
	function incrementResources()
	{
		incrementResource('food', '#infoBar #resourceBar #food');
		incrementResource('stone', '#infoBar #resourceBar #stone');
		incrementResource('metal', '#infoBar #resourceBar #metal');
		incrementResource('fuel', '#infoBar #resourceBar #fuel');
	}

	/**
	  * Increments resource
	  */
	function incrementResource(resource, span)
	{
		$(span).html(Math.floor(resources[resource]));

		period = 0;
		if (production[resource] == 0){
			return;
		}
		else if (production[resource] < 0){
			resources[resource]--;
			period = -1000/production[resource];
		}
		else{
			resources[resource]++;
			period = 1000/production[resource];
		}

		setTimeout(function(){
			incrementResource(resource, span);
		}, period);
	}
});
