var Game = Game || {};
Game.descriptions = {
	/**
	 * Translated descriptions
	 * @var JSON
	 */
	data: null,

	/**
	 * Stack of waiting trans. functions
	 * @var array of functions
	 */
	callbackStack : new Array(),

	/**
	 * True if fetching is in progress, false otherwise
	 * @var boolean
	 */
	isFetching : true,

	/**
	 * Fetches descriptions
	 * @return void
	 */
	fetchDescriptions : function () {
		this.isFetching=true;
		$.get('?do=fetchDescriptions',
			function (data) {
				Game.descriptions.data = data['descriptions'];
				Game.descriptions.isFetching = false;

				while (fnc = Game.descriptions.callbackStack.pop()){
					fnc();
				}


			});
	},

	/**
	 * Translates key phrase of type type and prints it to the selector
	 * @param string
	 * @param string
	 * @param object/string
	 * @return void
	 */
	translate : function (type, key, selector){
		if (this.isFetching || this.data == null){
			this.callbackStack.push(function(){
				var trans = Game.descriptions.data[type][key];
				if (typeof(trans) !== 'undefined' && trans !== 'undefined') {
					$(selector).html(trans);
				} else {
					$(selector).html(key);
				}
			});

		}
		else{
			var trans = Game.descriptions.data[type][key];
			if (typeof(trans) !== 'undefined' && trans !== 'undefined') {
				$(selector).html(trans);
			} else {
				$(selector).html(key);
			}

		}


	},

};

$(document).ready(function () {
	Game.descriptions.fetchDescriptions();
});
